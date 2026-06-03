<?php

namespace App\Application\UseCases;

use App\Application\DTOs\SmaCalculationResult;
use App\Application\DTOs\SmaRequestData;
use App\Domain\Contracts\BinanceClientInterface;
use App\Domain\Contracts\QueryRepositoryInterface;
use App\Domain\Contracts\SmaCalculatorInterface;
use App\Infrastructure\Mappers\SmaQueryMapper;

/**
 * Caso de uso: Calcular y almacenar cruces de SMA.
 * 
 * Orquesta el flujo completo sin conocer detalles de implementación:
 * 1. Obtiene datos de mercado (via BinanceClient)
 * 2. Calcula cruces (via SmaCalculator)
 * 3. Persiste resultados (via Repository)
 * 4. Retorna DTO (via Mapper)
 * 
 * Clean Architecture: solo orquestación, sin lógica de negocio ni persistencia.
 */
final class CalculateAndStoreCrossoversUseCase
{
    public function __construct(
        private BinanceClientInterface $binanceClient,
        private SmaCalculatorInterface $smaCalculator,
        private QueryRepositoryInterface $queryRepository
    ) {}

    /**
     * Ejecuta el caso de uso completo.
     * 
     * @param SmaRequestData $requestData Parámetros de la consulta
     * @return SmaCalculationResult DTO inmutable con los resultados
     */
    public function execute(SmaRequestData $requestData): SmaCalculationResult
    {
        // 1. Obtener velas históricas desde Binance
        $klines = $this->binanceClient->getKlines(
            $requestData->market,
            $requestData->interval,
            $requestData->startDate,
            $requestData->endDate
        );

        // 2. Calcular SMAs y detectar cruces (lógica de dominio)
        $crossovers = $this->smaCalculator->detectCrossovers(
            $klines,
            $requestData->shortPeriod,
            $requestData->longPeriod
        );

        // 3. Persistir consulta y cruces (delega al repositorio)
        $queryId = $this->queryRepository->save($requestData, $crossovers);

        // 4. Recuperar modelo persistido y mapear a DTO
        $queryModel = $this->queryRepository->findById($queryId);
        
        return SmaQueryMapper::toDTO($queryModel);
    }
}
