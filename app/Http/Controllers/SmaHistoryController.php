<?php

namespace App\Http\Controllers;

use App\DTOs\SmaCalculationResult;
use App\Models\SmaQuery;
use Illuminate\Http\JsonResponse;

class SmaHistoryController extends Controller
{
    /**
     * GET /api/sma-history
     *
     * Retorna el listado del historial de consultas realizadas,
     * ordenado por fecha de creación descendente (más recientes primero).
     */
    public function index(): JsonResponse
    {
        $queries = SmaQuery::orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SmaQuery $query) => [
                'id' => $query->id,
                'market' => $query->market,
                'interval' => $query->interval,
                'start_date' => $query->start_date->toIso8601String(),
                'end_date' => $query->end_date->toIso8601String(),
                'short_period' => $query->short_period,
                'long_period' => $query->long_period,
                'crossovers_count' => $query->crossovers_count,
                'created_at' => $query->created_at->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $queries,
        ]);
    }

    /**
     * GET /api/sma-history/{id}
     *
     * Retorna el detalle completo de una consulta específica,
     * incluyendo todos sus cruces asociados.
     */
    public function show(int $id): JsonResponse
    {
        $query = SmaQuery::findOrFail($id);
        $result = SmaCalculationResult::fromModel($query);

        return response()->json([
            'success' => true,
            'data' => $result->toArray(),
        ]);
    }
}
