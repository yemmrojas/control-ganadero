<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function query(): BelongsTo
    {
        return $this->belongsTo(SmaQuery::class, 'sma_query_id');
    }
}
