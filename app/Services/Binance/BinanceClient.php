<?php

namespace App\Services\Binance;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     * Timeout para requests HTTP en segundos.
     */
    private const REQUEST_TIMEOUT = 30;

    /**
     * Número de reintentos en caso de fallo.
     */
    private const MAX_RETRIES = 3;

    /**
     * Delay entre reintentos en milisegundos.
     */
    private const RETRY_DELAY_MS = 1000;

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
        $requestCount = 0;

        Log::info('Binance API: Starting klines fetch', [
            'symbol' => $symbol,
            'interval' => $interval,
            'start' => $startTime->toIso8601String(),
            'end' => $endTime->toIso8601String(),
        ]);

        while ($currentStartTime < $endTimeMs) {
            $requestCount++;

            try {
                $response = Http::timeout(self::REQUEST_TIMEOUT)
                    ->retry(self::MAX_RETRIES, self::RETRY_DELAY_MS)
                    ->get(self::BASE_URL . '/api/v3/klines', [
                        'symbol' => $symbol,
                        'interval' => $interval,
                        'startTime' => $currentStartTime,
                        'endTime' => $endTimeMs,
                        'limit' => self::KLINES_LIMIT,
                    ]);

                if ($response->failed()) {
                    $this->handleBinanceError($response, $symbol);
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
            } catch (RequestException $e) {
                Log::error('Binance API: Request failed', [
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'request_count' => $requestCount,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        Log::info('Binance API: Completed klines fetch', [
            'symbol' => $symbol,
            'requests_made' => $requestCount,
            'total_klines' => count($allKlines),
        ]);

        return $allKlines;
    }

    /**
     * Maneja errores específicos de la API de Binance.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $symbol
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \InvalidArgumentException
     */
    private function handleBinanceError($response, string $symbol): void
    {
        $errorData = $response->json();
        $errorCode = $errorData['code'] ?? null;
        $errorMsg = $errorData['msg'] ?? 'Unknown error';

        Log::warning('Binance API: Error response', [
            'code' => $errorCode,
            'message' => $errorMsg,
            'symbol' => $symbol,
        ]);

        // Manejar errores específicos de Binance
        switch ($errorCode) {
            case -1121: // Invalid symbol
                throw new \InvalidArgumentException("Invalid symbol: {$symbol}. Error: {$errorMsg}");

            case -1100: // Illegal characters in parameter
                throw new \InvalidArgumentException("Illegal characters in parameter. Error: {$errorMsg}");

            case -1003: // Too many requests (rate limit)
                throw new \RuntimeException("Binance rate limit exceeded. Please try again later. Error: {$errorMsg}");

            default:
                throw new RequestException($response);
        }
    }
}
