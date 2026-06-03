<?php

namespace App\Application\DTOs;

use DateTimeImmutable;

/**
 * DTO inmutable para datos de solicitud de cálculo de SMA.
 * 
 * Representa los parámetros de entrada para calcular cruces de SMA.
 * No tiene dependencias externas, solo tipos nativos de PHP.
 */
final readonly class SmaRequestData
{
    public function __construct(
        public string $market,
        public string $interval,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public int $shortPeriod,
        public int $longPeriod
    ) {}
}
