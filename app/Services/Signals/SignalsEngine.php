<?php

namespace App\Services\Signals;

use App\Models\MaintenanceRequest;
use App\Models\Signal;
use App\Models\SignalEvent;
use App\Models\Tenancy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Evaluates the 8 seeded signals and logs signal_events. Safe to run
 * repeatedly (hourly, via `signals:check`) — every fire is deduplicated
 * against any already-open event for the same signal + entity.
 */
class SignalsEngine
{
    /** @var array<string, Signal> */
    protected array $signals = [];

    public function run(): array
    {
        $this->signals = Signal::where('active', true)->get()->keyBy('condition_key')->all();

        $fired = [];
        $fired['rent_overdue'] = $this->checkRentOverdue();
        $fired['lease_expiry_60'] = $this->checkLeaseExpiry('lease_expiry_60', 60);
        $fired['lease_expiry_30'] = $this->checkLeaseExpiry('lease_expiry_30', 30);
        $fired['maintenance_sla_breached'] = $this->checkSlaBreached();
        $fired['recurring_issue_at_property'] = $this->checkRecurringIssue();
        $fired['emergency_request_open_24h'] = $this->checkEmergencyOpen24h();
        $fired['contractor_reopened_request'] = $this->checkContractorReopened();
        $fired['expired_tenancy_no_action'] = $this->checkExpiredTenancyNoAction();

        return $fired;
    }

    protected function checkRentOverdue(): int
    {
        $signal = $this->signals['rent_overdue'] ?? null;
        if (! $signal) {
            return 0;
        }

        $count = 0;
        $today = Carbon::today();

        foreach (Tenancy::where('status', 'active')->get() as $tenancy) {
            $periodStart = $today->copy()->startOfMonth();
            $dueDay = min($tenancy->payment_due_day, $today->daysInMonth);
            $dueDate = $periodStart->copy()->day($dueDay);

            if ($today->lessThanOrEqualTo($dueDate)) {
                continue;
            }

            $paid = $tenancy->rentPayments()->where('payment_date', '>=', $periodStart)->exists();

            if ($paid) {
                continue;
            }

            if ($this->fire($signal, tenancy: $tenancy, property: $tenancy->property)) {
                $count++;
            }
        }

        return $count;
    }

    protected function checkLeaseExpiry(string $key, int $daysAway): int
    {
        $signal = $this->signals[$key] ?? null;
        if (! $signal) {
            return 0;
        }

        $count = 0;
        $target = Carbon::today()->addDays($daysAway);

        $tenancies = Tenancy::where('status', 'active')
            ->whereDate('lease_end', $target)
            ->get();

        foreach ($tenancies as $tenancy) {
            if ($this->fire($signal, tenancy: $tenancy, property: $tenancy->property)) {
                $count++;
            }
        }

        return $count;
    }

    protected function checkSlaBreached(): int
    {
        $signal = $this->signals['maintenance_sla_breached'] ?? null;
        if (! $signal) {
            return 0;
        }

        $count = 0;

        $requests = MaintenanceRequest::whereNotIn('status', ['resolved', 'closed'])->get();

        foreach ($requests as $request) {
            if (! $request->isSlaBreached()) {
                continue;
            }

            if (! $request->sla_breached) {
                $request->forceFill(['sla_breached' => true])->save();
            }

            if ($this->fire($signal, tenancy: $request->tenancy, property: $request->property, request: $request)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Cosine similarity > 0.85 <=> cosine distance < 0.15. Done as a
     * pgvector self-join in the database, not a PHP loop.
     */
    protected function checkRecurringIssue(): int
    {
        $signal = $this->signals['recurring_issue_at_property'] ?? null;
        if (! $signal) {
            return 0;
        }

        $rows = DB::select("
            SELECT DISTINCT a.property_id, a.id AS request_a, b.id AS request_b
            FROM maintenance_requests a
            JOIN maintenance_requests b
                ON a.property_id = b.property_id AND a.id < b.id
            WHERE a.created_at >= NOW() - INTERVAL '90 days'
              AND b.created_at >= NOW() - INTERVAL '90 days'
              AND a.embedding IS NOT NULL
              AND b.embedding IS NOT NULL
              AND (a.embedding <=> b.embedding) < 0.15
        ");

        $count = 0;
        $seenProperties = [];

        foreach ($rows as $row) {
            if (isset($seenProperties[$row->property_id])) {
                continue;
            }
            $seenProperties[$row->property_id] = true;

            $property = \App\Models\Property::find($row->property_id);
            if (! $property) {
                continue;
            }

            if ($this->fire(
                $signal,
                property: $property,
                note: "Requests #{$row->request_a} and #{$row->request_b} at this property look like the same issue (cosine similarity > 0.85)."
            )) {
                $count++;
            }
        }

        return $count;
    }

    protected function checkEmergencyOpen24h(): int
    {
        $signal = $this->signals['emergency_request_open_24h'] ?? null;
        if (! $signal) {
            return 0;
        }

        $count = 0;

        $requests = MaintenanceRequest::where('urgency', 'emergency')
            ->where('status', 'logged')
            ->where('created_at', '<=', now()->subHours(24))
            ->get();

        foreach ($requests as $request) {
            if ($this->fire($signal, tenancy: $request->tenancy, property: $request->property, request: $request)) {
                $count++;
            }
        }

        return $count;
    }

    protected function checkContractorReopened(): int
    {
        $signal = $this->signals['contractor_reopened_request'] ?? null;
        if (! $signal) {
            return 0;
        }

        $count = 0;

        $requests = MaintenanceRequest::whereNotNull('resolved_at')
            ->with('statusHistory')
            ->get()
            ->filter(fn (MaintenanceRequest $r) => $r->wasReopenedWithin(30));

        foreach ($requests as $request) {
            $contractorPoorResolutions = null;

            if ($request->contractor_id) {
                $priorCount = SignalEvent::where('signal_id', $signal->id)
                    ->whereHas('request', fn ($q) => $q->where('contractor_id', $request->contractor_id))
                    ->count();

                if ($priorCount + 1 > 3) {
                    $contractorPoorResolutions = "This contractor now has {$priorCount} previously logged reopened requests — review contractor quality.";
                }
            }

            if ($this->fire($signal, tenancy: $request->tenancy, property: $request->property, request: $request, note: $contractorPoorResolutions)) {
                $count++;
            }
        }

        return $count;
    }

    protected function checkExpiredTenancyNoAction(): int
    {
        $signal = $this->signals['expired_tenancy_no_action'] ?? null;
        if (! $signal) {
            return 0;
        }

        $count = 0;

        $tenancies = Tenancy::where('status', 'active')
            ->whereDate('lease_end', '<', Carbon::today())
            ->get();

        foreach ($tenancies as $tenancy) {
            if ($this->fire($signal, tenancy: $tenancy, property: $tenancy->property)) {
                $count++;
            }
        }

        return $count;
    }

    protected function fire(
        Signal $signal,
        ?Tenancy $tenancy = null,
        ?\App\Models\Property $property = null,
        ?MaintenanceRequest $request = null,
        ?string $note = null,
    ): bool {
        $exists = SignalEvent::where('signal_id', $signal->id)
            ->whereNull('resolved_at')
            ->when($tenancy, fn ($q) => $q->where('tenancy_id', $tenancy->id))
            ->when(! $tenancy, fn ($q) => $q->whereNull('tenancy_id'))
            ->when($property, fn ($q) => $q->where('property_id', $property->id))
            ->when(! $property, fn ($q) => $q->whereNull('property_id'))
            ->when($request, fn ($q) => $q->where('request_id', $request->id))
            ->when(! $request, fn ($q) => $q->whereNull('request_id'))
            ->exists();

        if ($exists) {
            return false;
        }

        SignalEvent::create([
            'signal_id' => $signal->id,
            'property_id' => $property?->id,
            'tenancy_id' => $tenancy?->id,
            'request_id' => $request?->id,
            'triggered_at' => now(),
            'note' => $note,
            'created_at' => now(),
        ]);

        return true;
    }
}
