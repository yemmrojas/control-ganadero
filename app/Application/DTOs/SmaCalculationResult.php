<?php

namespace App\Application\DTOs;

/**
 * DTO inmutable para el resultado del cálculo de SMA.
 * 
 * Representa el resultado completo de una consulta de cruces de SMA,
 * incluyendo todos los cruces detectados.
 * 
 * Sin dependencias de Models ni ORM - Solo datos puros.
 */
final readonly class SmaCalculationResult
{
    /**
     * @param CrossoverDetail[] $crossovers
     */
    public function __construct(
        public int $id,
        public string $market,
        public string $interval,
        public string $startDate,
        public string $endDate,
        public int $shortPeriod,
        public int $longPeriod,
        public int $crossoversCount,
        public array $crossovers,
        public string $createdAt
    ) {}

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
