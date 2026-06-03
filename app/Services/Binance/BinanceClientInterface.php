<?php

namespace App\Services\Binance;

use Carbon\Carbon;

interface BinanceClientInterface
{
    /**
     * Obtiene el listado de velas (Klines) para un mercado, intervalo y rango de fechas.
     * Realiza peticiones paginadas iterativamente si el rango excede las 1000 velas.
     *
     * @param string $symbol   Símbolo del mercado (ej: BTCUSDT)
     * @param string $interval Intervalo de tiempo (ej: 30m, 1h, 1d)
     * @param Carbon $startTime Fecha de inicio en UTC
     * @param Carbon $endTime   Fecha de fin en UTC
     * @return array Array de velas, cada vela es: [openTime, open, high, low, close, volume, closeTime, ...]
     */
    public function getKlines(string $symbol, string $interval, Carbon $startTime, Carbon $endTime): array;
}
