<?php

namespace App\DTOs;

use App\Models\SmaQuery;

class SmaCalculationResult
{
    /**
     * @param CrossoverDetail[] $crossovers
     */
    public function __construct(
        public readonly int $id,
        public readonly string $market,
        public readonly string $interval,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $shortPeriod,
        public readonly int $longPeriod,
        public readonly int $crossoversCount,
        public readonly array $crossovers,
        public readonly string $createdAt
    ) {}

    public static function fromModel(SmaQuery $model): self
    {
        $model->load('crossovers');

        $crossoverDetails = $model->crossovers->map(function ($crossover) {
            return new CrossoverDetail(
                crossoverTime: $crossover->crossover_time->toIso8601String(),
                direction: $crossover->direction,
                shortSmaValue: (float) $crossover->short_sma_value,
                longSmaValue: (float) $crossover->long_sma_value,
                priceAtCrossover: (float) $crossover->price_at_crossover,
            );
        })->all();

        return new self(
            id: $model->id,
            market: $model->market,
            interval: $model->interval,
            startDate: $model->start_date->toIso8601String(),
            endDate: $model->end_date->toIso8601String(),
            shortPeriod: $model->short_period,
            longPeriod: $model->long_period,
            crossoversCount: $model->crossovers_count,
            crossovers: $crossoverDetails,
            createdAt: $model->created_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'market' => $this->market,
            'interval' => $this->interval,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'short_period' => $this->shortPeriod,
            'long_period' => $this->longPeriod,
            'crossovers_count' => $this->crossoversCount,
            'crossovers' => array_map(fn (CrossoverDetail $c) => $c->toArray(), $this->crossovers),
            'created_at' => $this->createdAt,
        ];
    }
}
