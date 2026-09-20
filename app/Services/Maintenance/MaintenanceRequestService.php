<?php

namespace App\Services\Maintenance;

use App\Jobs\EmbedMaintenanceRequest;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceStatusHistory;
use App\Models\User;
use App\Notifications\ContractorAssignedNotification;
use App\Services\Lease\LeaseAssistantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceRequestService
{
    public function __construct(protected LeaseAssistantService $leaseAssistant) {}

    /**
     * @param  array{property_id:int, tenancy_id:?int, category:string, urgency:string, description:string, attachments:?array, reported_by_name:string, reported_by_user_id:?int}  $data
     */
    public function create(array $data, User $actor): MaintenanceRequest
    {
        $request = DB::transaction(function () use ($data) {
            $request = MaintenanceRequest::create([
                ...$data,
                'status' => 'logged',
                'sla_hours' => MaintenanceRequest::SLA_HOURS[$data['urgency']],
            ]);

            MaintenanceStatusHistory::create([
                'request_id' => $request->id,
                'old_status' => null,
                'new_status' => 'logged',
                'changed_by' => $data['reported_by_user_id'] ?? $request->reported_by_user_id,
                'created_at' => now(),
            ]);

            return $request;
        });

        EmbedMaintenanceRequest::dispatch($request->id);

        return $request;
    }

    /**
     * The one-line "under this lease..." obligation note shown right after
     * logging. Informational only — never blocks request creation if the
     * lease hasn't been ingested yet or Ollama is unavailable.
     */
    public function obligationNote(MaintenanceRequest $request): ?string
    {
        if (! $request->tenancy || ! $request->tenancy->leaseIsEmbedded()) {
            return null;
        }

        $label = MaintenanceRequest::CATEGORIES[$request->category] ?? $request->category;

        try {
            return $this->leaseAssistant->obligationNote($request->tenancy, $label);
        } catch (\Throwable $e) {
            Log::warning('Lease obligation note failed: '.$e->getMessage());

            return null;
        }
    }

    public function assignContractor(MaintenanceRequest $request, User $contractor, User $actor): MaintenanceRequest
    {
        return $this->transition($request, 'assigned', $actor, function () use ($request, $contractor) {
            $request->contractor_id = $contractor->id;
        }, afterSave: function () use ($request, $contractor) {
            $contractor->notify(new ContractorAssignedNotification($request));
        });
    }

    public function startProgress(MaintenanceRequest $request, User $actor): MaintenanceRequest
    {
        return $this->transition($request, 'in_progress', $actor);
    }

    public function markResolved(MaintenanceRequest $request, User $actor): MaintenanceRequest
    {
        return $this->transition($request, 'resolved', $actor, function () use ($request) {
            $request->resolved_at = now();
        });
    }

    public function close(MaintenanceRequest $request, User $actor, ?string $resolutionNote = null, ?float $cost = null): MaintenanceRequest
    {
        return $this->transition($request, 'closed', $actor, function () use ($request, $resolutionNote, $cost) {
            $request->closed_at = now();
            $request->resolution_note = $resolutionNote;
            $request->cost = $cost;
        });
    }

    /**
     * Reopen a resolved/closed request (e.g. the tenant reports the same
     * issue again). Feeds the "Contractor Reopened Request" signal.
     */
    public function reopen(MaintenanceRequest $request, User $actor, ?string $note = null): MaintenanceRequest
    {
        return $this->transition($request, 'in_progress', $actor, note: $note);
    }

    protected function transition(
        MaintenanceRequest $request,
        string $newStatus,
        User $actor,
        ?\Closure $mutate = null,
        ?\Closure $afterSave = null,
        ?string $note = null,
    ): MaintenanceRequest {
        return DB::transaction(function () use ($request, $newStatus, $actor, $mutate, $afterSave, $note) {
            $oldStatus = $request->status;

            if ($mutate) {
                $mutate($request);
            }

            $request->status = $newStatus;
            $request->save();

            MaintenanceStatusHistory::create([
                'request_id' => $request->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $actor->id,
                'note' => $note,
                'created_at' => now(),
            ]);

            if ($afterSave) {
                $afterSave();
            }

            return $request;
        });
    }
}
