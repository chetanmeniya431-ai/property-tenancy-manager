<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'tenant_name',
        'tenant_email',
        'tenant_phone',
        'lease_start',
        'lease_end',
        'monthly_rent',
        'payment_due_day',
        'deposit_amount',
        'lease_file_path',
        'lease_original_filename',
        'lease_uploaded_at',
        'lease_embedded_at',
        'lease_processing_error',
        'status',
        'end_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'lease_start' => 'date',
            'lease_end' => 'date',
            'lease_uploaded_at' => 'datetime',
            'lease_embedded_at' => 'datetime',
            'monthly_rent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public const STATUSES = [
        'active' => 'Active',
        'expired' => 'Expired',
        'notice_given' => 'Notice given',
        'ended' => 'Ended',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tenantUser(): HasMany
    {
        return $this->hasMany(User::class, 'tenancy_id');
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class)->orderByDesc('payment_date');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function leaseChunks(): HasMany
    {
        return $this->hasMany(LeaseChunk::class);
    }

    public function signalEvents(): HasMany
    {
        return $this->hasMany(SignalEvent::class);
    }

    public function hasLease(): bool
    {
        return ! empty($this->lease_file_path);
    }

    public function leaseIsEmbedded(): bool
    {
        return ! empty($this->lease_embedded_at);
    }

    public function leaseFailed(): bool
    {
        return ! empty($this->lease_processing_error);
    }

    public function nextRentDueDate(): \Carbon\Carbon
    {
        $today = now();
        $due = $today->copy()->day(min($this->payment_due_day, $today->daysInMonth));

        if ($due->isBefore($today->copy()->startOfDay())) {
            $next = $today->copy()->addMonthNoOverflow()->startOfMonth();
            $due = $next->day(min($this->payment_due_day, $next->daysInMonth));
        }

        return $due;
    }
}
