<?php

namespace App\Application\DTOs;

/**
 * DTO inmutable para detalle de un cruce de SMA.
 * 
 * Representa un punto de cruce entre las SMAs corta y larga.
 * Sin dependencias externas, solo tipos nativos de PHP.
 */
final readonly class CrossoverDetail
{
    public function __construct(
        public string $crossoverTime,
        public string $direction,
        public float $shortSmaValue,
        public float $longSmaValue,
        public float $priceAtCrossover
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
