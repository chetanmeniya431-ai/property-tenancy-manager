<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class MaintenanceRequest extends Model
{
    use HasFactory, HasNeighbors;

    protected $fillable = [
        'property_id',
        'tenancy_id',
        'category',
        'urgency',
        'description',
        'attachments',
        'reported_by_name',
        'reported_by_user_id',
        'status',
        'contractor_id',
        'resolved_at',
        'closed_at',
        'resolution_note',
        'cost',
        'sla_hours',
        'sla_breached',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'cost' => 'decimal:2',
            'sla_breached' => 'boolean',
            'embedding' => Vector::class,
        ];
    }

    public const CATEGORIES = [
        'plumbing' => 'Plumbing',
        'electrical' => 'Electrical',
        'structural' => 'Structural',
        'heating_cooling' => 'Heating & Cooling',
        'appliances' => 'Appliances',
        'pest_control' => 'Pest Control',
        'cleaning' => 'Cleaning',
        'other' => 'Other',
    ];

    public const URGENCIES = [
        'emergency' => 'Emergency',
        'urgent' => 'Urgent',
        'routine' => 'Routine',
    ];

    public const SLA_HOURS = [
        'emergency' => 24,
        'urgent' => 72,
        'routine' => 336,
    ];

    public const STATUSES = [
        'logged' => 'Logged',
        'assigned' => 'Assigned to Contractor',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(MaintenanceStatusHistory::class, 'request_id')->orderBy('created_at');
    }

    public function signalEvents(): HasMany
    {
        return $this->hasMany(SignalEvent::class, 'request_id');
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['resolved', 'closed']);
    }

    public function slaDeadline(): \Carbon\Carbon
    {
        return $this->created_at->copy()->addHours($this->sla_hours);
    }

    public function isSlaBreached(): bool
    {
        if ($this->resolved_at) {
            return $this->resolved_at->greaterThan($this->slaDeadline());
        }

        return $this->isOpen() && now()->greaterThan($this->slaDeadline());
    }

    /**
     * True if, at any point in this request's history, it left the
     * "resolved" status again within $days of having been marked resolved
     * — regardless of what the *current* resolved_at happens to be (a
     * request can be reopened and resolved again more than once).
     */
    public function wasReopenedWithin(int $days): bool
    {
        $lastResolvedAt = null;

        foreach ($this->statusHistory as $entry) {
            if ($entry->new_status === 'resolved') {
                $lastResolvedAt = $entry->created_at;

                continue;
            }

            if ($entry->old_status === 'resolved' && $entry->new_status !== 'closed' && $lastResolvedAt !== null) {
                if ($entry->created_at->lessThanOrEqualTo($lastResolvedAt->copy()->addDays($days))) {
                    return true;
                }
            }
        }

        return false;
    }
}
