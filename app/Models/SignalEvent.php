<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignalEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'signal_id',
        'property_id',
        'tenancy_id',
        'request_id',
        'triggered_at',
        'resolved_at',
        'resolved_by',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function signal(): BelongsTo
    {
        return $this->belongsTo(Signal::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class, 'request_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return is_null($this->resolved_at);
    }
}
