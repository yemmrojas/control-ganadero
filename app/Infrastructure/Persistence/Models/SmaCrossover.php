<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para cruces de SMA.
 * 
 * Representa un punto de cruce entre SMAs corta y larga.
 * Vive en Infrastructure/Persistence porque es específico de Eloquent.
 */
class SmaCrossover extends Model
{
    protected $fillable = [
        'sma_query_id',
        'crossover_time',
        'direction',
        'short_sma_value',
        'long_sma_value',
        'price_at_crossover',
    ];

    protected function casts(): array
    {
        return [
            'crossover_time' => 'datetime',
        ];
    }

    public function smaQuery(): BelongsTo
    {
        return $this->belongsTo(SmaQuery::class, 'sma_query_id');
    }
}
