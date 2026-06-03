<?php

namespace App\Domain\Contracts;

use DateTimeImmutable;

/**
 * Contrato para cliente de API externa de Binance.
 * 
 * Define la interfaz para obtener datos históricos de mercado.
 * La implementación vive en Infrastructure.
 */
interface BinanceClientInterface
{
    /**
     * Obtiene el listado de velas (Klines) para un mercado, intervalo y rango de fechas.
     * Realiza peticiones paginadas iterativamente si el rango excede las 1000 velas.
     *
     * @param string $symbol   Símbolo del mercado (ej: BTCUSDT)
     * @param string $interval Intervalo de tiempo (ej: 30m, 1h, 1d)
     * @param DateTimeImmutable $startTime Fecha de inicio en UTC
     * @param DateTimeImmutable $endTime   Fecha de fin en UTC
     * @return array Array de velas, cada vela es: [openTime, open, high, low, close, volume, closeTime, ...]
     */
    public function getKlines(
        string $symbol,
        string $interval,
        DateTimeImmutable $startTime,
        DateTimeImmutable $endTime
    ): array;
}
