<?php

namespace App\Domain\Contracts;

interface SmaCalculatorInterface
{
    /**
     * Calcula las SMA y detecta los puntos exactos de cruce.
     *
     * @param array $klines     Velas obtenidas desde Binance.
     *                          Cada vela: [openTime, open, high, low, close, volume, closeTime, ...]
     * @param int   $shortPeriod Periodo de la SMA corta.
     * @param int   $longPeriod  Periodo de la SMA larga.
     * @return array Lista de arrays asociativos con las claves:
     *               'time' (int ms), 'direction' (string), 'short_sma' (float),
     *               'long_sma' (float), 'price' (float)
     */
    public function detectCrossovers(array $klines, int $shortPeriod, int $longPeriod): array;
}
