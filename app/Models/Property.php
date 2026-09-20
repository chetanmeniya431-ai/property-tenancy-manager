<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_line1',
        'address_line2',
        'city',
        'postcode',
        'property_type',
        'bedrooms',
        'floor_area_sqm',
        'owner_id',
        'notes',
    ];

    public const TYPES = [
        'residential_flat' => 'Residential flat',
        'residential_house' => 'Residential house',
        'commercial_unit' => 'Commercial unit',
        'hmo' => 'HMO',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    public function activeTenancy(): HasMany
    {
        return $this->tenancies()->where('status', 'active');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function signalEvents(): HasMany
    {
        return $this->hasMany(SignalEvent::class);
    }

    public function fullAddress(): string
    {
        return trim(implode(', ', array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->postcode,
        ])));
    }
}
