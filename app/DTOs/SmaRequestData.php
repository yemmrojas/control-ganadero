<?php

namespace App\DTOs;

use Carbon\Carbon;

class SmaRequestData
{
    public function __construct(
        public readonly string $market,
        public readonly string $interval,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
        public readonly int $shortPeriod,
        public readonly int $longPeriod
    ) {}
}
