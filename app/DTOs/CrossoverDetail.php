<?php

namespace App\DTOs;

class CrossoverDetail
{
    public function __construct(
        public readonly string $crossoverTime,
        public readonly string $direction,
        public readonly float $shortSmaValue,
        public readonly float $longSmaValue,
        public readonly float $priceAtCrossover
    ) {}

    public function toArray(): array
    {
        return [
            'crossover_time' => $this->crossoverTime,
            'direction' => $this->direction,
            'short_sma_value' => $this->shortSmaValue,
            'long_sma_value' => $this->longSmaValue,
            'price_at_crossover' => $this->priceAtCrossover,
        ];
    }
}
