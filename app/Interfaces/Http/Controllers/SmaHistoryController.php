<?php

namespace App\Interfaces\Http\Controllers;

use App\Domain\Contracts\QueryRepositoryInterface;
use App\Infrastructure\Mappers\SmaQueryMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Controlador para historial de consultas SMA.
 * 
 * Responsabilidades:
 * - Recibir request HTTP
 * - Invocar Repository para obtener datos
 * - Mapear a DTO/Array (via Mapper)
 * - Serializar respuesta
 * 
 * Sin lógica de negocio ni queries directas a Eloquent.
 */
final class SmaHistoryController extends Controller
{
    public function __construct(
        private QueryRepositoryInterface $queryRepository
    ) {}

    /**
     * GET /api/sma-history
     *
     * Retorna el listado del historial de consultas realizadas,
     * ordenado por fecha de creación descendente (más recientes primero).
     */
    public function index(): JsonResponse
    {
        // Obtener queries del repositorio
        $queries = $this->queryRepository->getAllOrdered();

        // Mapear a arrays simples (sin crossovers para performance)
        $data = [];
        foreach ($queries as $query) {
            $data[] = SmaQueryMapper::toSummaryArray($query);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
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
        // Buscar query en el repositorio
        $query = $this->queryRepository->findById($id);

        // Si no existe, retornar 404
        if ($query === null) {
            return response()->json([
                'success' => false,
                'message' => 'Query not found',
            ], 404);
        }

        // Mapear a DTO completo (con crossovers)
        $result = SmaQueryMapper::toDTO($query);

        return response()->json([
            'success' => true,
            'data' => $result->toArray(),
        ]);
    }
}
