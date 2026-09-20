<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class LeaseChunk extends Model
{
    use HasNeighbors;

    public $timestamps = false;

    protected $fillable = [
        'tenancy_id',
        'chunk_index',
        'chunk_text',
        'embedding',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
            'created_at' => 'datetime',
        ];
    }

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }
}
