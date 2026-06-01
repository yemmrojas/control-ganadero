<?php

namespace App\Actions;

use App\DTOs\SmaCalculationResult;
use App\DTOs\SmaRequestData;
use App\Models\SmaQuery;
use App\Services\Binance\BinanceClientInterface;
use App\Services\Math\SmaCalculatorInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalculateAndStoreCrossoversAction
{
    public function __construct(
        private BinanceClientInterface $binanceClient,
        private SmaCalculatorInterface $smaCalculator
    ) {}

    /**
     * Ejecuta el caso de uso completo:
     * 1. Obtiene las velas históricas desde Binance.
     * 2. Calcula las SMAs y detecta los cruces.
     * 3. Persiste la consulta y los cruces en la base de datos.
     * 4. Retorna un DTO con el resultado completo.
     */
    public function execute(SmaRequestData $requestData): SmaCalculationResult
    {
        // 1. Obtener velas desde Binance
        $klines = $this->binanceClient->getKlines(
            $requestData->market,
            $requestData->interval,
            $requestData->startDate,
            $requestData->endDate
        );

        // 2. Calcular SMA y detectar cruces
        $crossovers = $this->smaCalculator->detectCrossovers(
            $klines,
            $requestData->shortPeriod,
            $requestData->longPeriod
        );

        // 3. Persistir en Base de Datos con transacción atómica
        $queryModel = DB::transaction(function () use ($requestData, $crossovers) {
            $query = SmaQuery::create([
                'market' => $requestData->market,
                'interval' => $requestData->interval,
                'start_date' => $requestData->startDate,
                'end_date' => $requestData->endDate,
                'short_period' => $requestData->shortPeriod,
                'long_period' => $requestData->longPeriod,
                'crossovers_count' => count($crossovers),
            ]);

            foreach ($crossovers as $crossover) {
                $query->crossovers()->create([
                    'crossover_time' => Carbon::createFromTimestampMs($crossover['time']),
                    'direction' => $crossover['direction'],
                    'short_sma_value' => $crossover['short_sma'],
                    'long_sma_value' => $crossover['long_sma'],
                    'price_at_crossover' => $crossover['price'],
                ]);
            }

            return $query;
        });

        // 4. Retornar DTO de resultado
        return SmaCalculationResult::fromModel($queryModel);
    }
}
