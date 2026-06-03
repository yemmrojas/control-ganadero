<?php

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\CrossoverDetail;
use App\Application\DTOs\SmaCalculationResult;
use App\Infrastructure\Persistence\Models\SmaQuery;

/**
 * Mapper que transforma Eloquent Models a DTOs de Application.
 * 
 * Vive en la capa de Infrastructure porque conoce tanto Models (Infrastructure)
 * como DTOs (Application). Esto mantiene los DTOs libres de dependencias.
 */
final class SmaQueryMapper
{
    /**
     * Convierte un modelo SmaQuery a DTO SmaCalculationResult.
     * 
     * @param SmaQuery $model Modelo Eloquent con datos persistidos
     * @return SmaCalculationResult DTO inmutable con los datos
     */
    public static function toDTO(SmaQuery $model): SmaCalculationResult
    {
        // Eager load de crossovers si no están cargados
        if (!$model->relationLoaded('crossovers')) {
            $model->load('crossovers');
        }

        // Mapear crossovers a DTOs
        $crossoverDetails = $model->crossovers->map(function ($crossover) {
            return new CrossoverDetail(
                crossoverTime: $crossover->crossover_time->toIso8601String(),
                direction: $crossover->direction,
                shortSmaValue: (float) $crossover->short_sma_value,
                longSmaValue: (float) $crossover->long_sma_value,
                priceAtCrossover: (float) $crossover->price_at_crossover,
            );
        })->all();

        return new SmaCalculationResult(
            id: $model->id,
            market: $model->market,
            interval: $model->interval,
            startDate: $model->start_date->toIso8601String(),
            endDate: $model->end_date->toIso8601String(),
            shortPeriod: $model->short_period,
            longPeriod: $model->long_period,
            crossoversCount: $model->crossovers_count,
            crossovers: $crossoverDetails,
            createdAt: $model->created_at->toIso8601String(),
        );
    }

    /**
     * Convierte una colección de modelos SmaQuery a array de DTOs.
     * 
     * @param iterable<SmaQuery> $models Colección de modelos
     * @return SmaCalculationResult[] Array de DTOs
     */
    public static function collectionToDTO(iterable $models): array
    {
        $dtos = [];
        foreach ($models as $model) {
            $dtos[] = self::toDTO($model);
        }
        return $dtos;
    }

    /**
     * Convierte un modelo SmaQuery a array simple (sin crossovers).
     * Útil para listados donde no se necesita el detalle completo.
     * 
     * @param SmaQuery $model Modelo Eloquent
     * @return array Array asociativo con datos básicos
     */
    public static function toSummaryArray(SmaQuery $model): array
    {
        return [
            'id' => $model->id,
            'market' => $model->market,
            'interval' => $model->interval,
            'start_date' => $model->start_date->toIso8601String(),
            'end_date' => $model->end_date->toIso8601String(),
            'short_period' => $model->short_period,
            'long_period' => $model->long_period,
            'crossovers_count' => $model->crossovers_count,
            'created_at' => $model->created_at->toIso8601String(),
        ];
    }
}
