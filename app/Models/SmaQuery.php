<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmaQuery extends Model
{
    protected $fillable = [
        'market',
        'interval',
        'start_date',
        'end_date',
        'short_period',
        'long_period',
        'crossovers_count',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function crossovers(): HasMany
    {
        return $this->hasMany(SmaCrossover::class, 'sma_query_id');
    }
}
