<?php

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SmaRequestData;
use App\Domain\Contracts\QueryRepositoryInterface;
use App\Infrastructure\Persistence\Models\SmaQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Implementación de QueryRepository usando Eloquent ORM.
 * 
 * Vive en Infrastructure porque depende de Eloquent y Laravel.
 * Encapsula toda la lógica de persistencia en base de datos.
 */
final class EloquentQueryRepository implements QueryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function save(SmaRequestData $requestData, array $crossovers): int
    {
        return DB::transaction(function () use ($requestData, $crossovers) {
            // Convertir DateTimeImmutable a Carbon para Eloquent
            $startDate = Carbon::instance(\DateTime::createFromImmutable($requestData->startDate));
            $endDate = Carbon::instance(\DateTime::createFromImmutable($requestData->endDate));
            
            // Crear el registro de consulta
            $query = SmaQuery::create([
                'market' => $requestData->market,
                'interval' => $requestData->interval,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'short_period' => $requestData->shortPeriod,
                'long_period' => $requestData->longPeriod,
                'crossovers_count' => count($crossovers),
            ]);

            // Persistir cada cruce detectado
            foreach ($crossovers as $crossover) {
                $query->crossovers()->create([
                    'crossover_time' => Carbon::createFromTimestampMs($crossover['time']),
                    'direction' => $crossover['direction'],
                    'short_sma_value' => $crossover['short_sma'],
                    'long_sma_value' => $crossover['long_sma'],
                    'price_at_crossover' => $crossover['price'],
                ]);
            }

            return $query->id;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?object
    {
        return SmaQuery::with('crossovers')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOrdered(): iterable
    {
        return SmaQuery::orderBy('created_at', 'desc')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function exists(int $id): bool
    {
        return SmaQuery::where('id', $id)->exists();
    }
}
