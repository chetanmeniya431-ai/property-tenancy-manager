<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Signal extends Model
{
    protected $fillable = [
        'name',
        'condition_key',
        'severity',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(SignalEvent::class);
    }
}
