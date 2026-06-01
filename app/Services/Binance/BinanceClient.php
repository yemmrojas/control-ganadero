<?php

namespace App\Services\Binance;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class BinanceClient implements BinanceClientInterface
{
    /**
     * URL base de la API pública de Binance Spot.
     */
    private const BASE_URL = 'https://api.binance.com';

    /**
     * Límite máximo de velas por petición impuesto por Binance.
     */
    private const KLINES_LIMIT = 1000;

    /**
     * {@inheritdoc}
     *
     * Realiza peticiones paginadas iterativas cuando el rango de fechas
     * requiere más de 1000 velas en total.
     */
    public function getKlines(string $symbol, string $interval, Carbon $startTime, Carbon $endTime): array
    {
        $allKlines = [];
        $currentStartTime = $startTime->getTimestampMs();
        $endTimeMs = $endTime->getTimestampMs();

        while ($currentStartTime < $endTimeMs) {
            $response = Http::get(self::BASE_URL . '/api/v3/klines', [
                'symbol' => $symbol,
                'interval' => $interval,
                'startTime' => $currentStartTime,
                'endTime' => $endTimeMs,
                'limit' => self::KLINES_LIMIT,
            ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $klines = $response->json();

            // Si la respuesta está vacía, no hay más datos disponibles
            if (empty($klines)) {
                break;
            }

            $allKlines = array_merge($allKlines, $klines);

            // Avanzar el cursor de paginación al siguiente milisegundo
            // después del closeTime de la última vela recibida
            $lastKline = end($klines);
            $lastCloseTime = $lastKline[6]; // closeTime es el índice 6
            $currentStartTime = $lastCloseTime + 1;

            // Si recibimos menos velas que el límite, ya no hay más páginas
            if (count($klines) < self::KLINES_LIMIT) {
                break;
            }
        }

        return $allKlines;
    }
}
