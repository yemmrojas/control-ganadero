<?php

namespace App\Services\Math;

class SmaCalculator implements SmaCalculatorInterface
{
    /**
     * {@inheritdoc}
     *
     * Algoritmo:
     * 1. Extrae los precios de cierre (close) de cada vela.
     * 2. Calcula las series SMA corta y larga.
     * 3. Recorre ambas series buscando cruces entre ellas.
     *
     * Fórmula SMA:
     *   SMA_t = (1/n) * Σ(i=0..n-1) Close_(t-i)
     *
     * Cruce Ascendente (Golden Cross):
     *   SMA_corto[t-1] <= SMA_largo[t-1] AND SMA_corto[t] > SMA_largo[t]
     *
     * Cruce Descendente (Death Cross):
     *   SMA_corto[t-1] >= SMA_largo[t-1] AND SMA_corto[t] < SMA_largo[t]
     */
    public function detectCrossovers(array $klines, int $shortPeriod, int $longPeriod): array
    {
        // Extraer precios de cierre (índice 4) y openTime (índice 0)
        $closes = array_map(fn (array $k) => (float) $k[4], $klines);
        $times = array_map(fn (array $k) => (int) $k[0], $klines);

        $count = count($closes);

        // No es posible calcular cruces si no hay suficientes datos
        if ($count < $longPeriod) {
            return [];
        }

        // Calcular ambas series de SMA
        $shortSma = $this->calculateSmaSeries($closes, $shortPeriod);
        $longSma = $this->calculateSmaSeries($closes, $longPeriod);

        // Las series SMA tienen diferente longitud según el periodo.
        // Alineamos ambas al mismo índice de vela usando offsets.
        // shortSma[0] corresponde al índice (shortPeriod - 1) de $closes.
        // longSma[0] corresponde al índice (longPeriod - 1) de $closes.
        $crossovers = [];

        // Iterar desde el primer punto donde ambos SMAs existen
        // longSma comienza en el índice (longPeriod - 1) de $closes
        // Para comparar t y t-1, necesitamos al menos 2 puntos de longSma
        $longSmaCount = count($longSma);

        if ($longSmaCount < 2) {
            return [];
        }

        for ($i = 1; $i < $longSmaCount; $i++) {
            // Índice en el array $closes para este punto del longSma
            $closeIdx = ($longPeriod - 1) + $i;

            // Índice correspondiente en el shortSma
            $shortIdx = $closeIdx - ($shortPeriod - 1);
            $shortIdxPrev = $shortIdx - 1;

            // Validar que los índices del shortSma existen
            if ($shortIdxPrev < 0 || $shortIdx >= count($shortSma)) {
                continue;
            }

            $shortCurrent = $shortSma[$shortIdx];
            $shortPrevious = $shortSma[$shortIdxPrev];
            $longCurrent = $longSma[$i];
            $longPrevious = $longSma[$i - 1];

            // Detectar cruce ascendente (Golden Cross)
            if ($shortPrevious <= $longPrevious && $shortCurrent > $longCurrent) {
                $crossovers[] = [
                    'time' => $times[$closeIdx],
                    'direction' => 'ascending',
                    'short_sma' => round($shortCurrent, 8),
                    'long_sma' => round($longCurrent, 8),
                    'price' => $closes[$closeIdx],
                ];
            }

            // Detectar cruce descendente (Death Cross)
            if ($shortPrevious >= $longPrevious && $shortCurrent < $longCurrent) {
                $crossovers[] = [
                    'time' => $times[$closeIdx],
                    'direction' => 'descending',
                    'short_sma' => round($shortCurrent, 8),
                    'long_sma' => round($longCurrent, 8),
                    'price' => $closes[$closeIdx],
                ];
            }
        }

        return $crossovers;
    }

    /**
     * Calcula la serie de promedios móviles simples para un periodo dado.
     *
     * @param float[] $closes Array de precios de cierre
     * @param int     $period Longitud del periodo SMA
     * @return float[] Serie de valores SMA. El primer elemento corresponde
     *                 al índice ($period - 1) del array original.
     */
    private function calculateSmaSeries(array $closes, int $period): array
    {
        $sma = [];
        $count = count($closes);

        if ($count < $period) {
            return $sma;
        }

        // Calcular la primera ventana con suma completa
        $windowSum = 0.0;
        for ($i = 0; $i < $period; $i++) {
            $windowSum += $closes[$i];
        }
        $sma[] = $windowSum / $period;

        // Desplazar la ventana deslizante para el resto de puntos
        for ($i = $period; $i < $count; $i++) {
            $windowSum += $closes[$i] - $closes[$i - $period];
            $sma[] = $windowSum / $period;
        }

        return $sma;
    }
}
