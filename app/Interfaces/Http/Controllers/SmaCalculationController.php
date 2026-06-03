<?php

namespace App\Interfaces\Http\Controllers;

use App\Application\DTOs\SmaRequestData;
use App\Application\UseCases\CalculateAndStoreCrossoversUseCase;
use App\Interfaces\Http\Requests\CalculateSmaRequest;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Controlador para cálculo de cruces de SMA.
 * 
 * Responsabilidades:
 * - Recibir request HTTP
 * - Validar entrada (delegado a FormRequest)
 * - Convertir datos de entrada a DTO
 * - Invocar caso de uso
 * - Serializar respuesta
 * 
 * Sin lógica de negocio ni acceso directo a BD.
 */
final class SmaCalculationController extends Controller
{
    public function __construct(
        private CalculateAndStoreCrossoversUseCase $calculateUseCase
    ) {}

    /**
     * POST /api/sma-crossover
     *
     * Recibe los parámetros de consulta validados, ejecuta el cálculo
     * de cruces de SMA y retorna el resultado como JSON.
     * 
     * Las fechas recibidas del frontend están en la zona horaria local del usuario.
     * Se convierten a UTC para consultar la API de Binance correctamente.
     */
    public function calculate(CalculateSmaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Obtener la zona horaria del usuario (por defecto UTC si no se envía)
        $userTimezone = $validated['timezone'] ?? 'UTC';

        // Parsear las fechas en la zona horaria del usuario y convertir a UTC
        $startDate = Carbon::parse($validated['start_date'], $userTimezone)->utc();
        $endDate = Carbon::parse($validated['end_date'], $userTimezone)->utc();

        // Convertir Carbon a DateTimeImmutable para el DTO
        $requestData = new SmaRequestData(
            market: $validated['market'],
            interval: $validated['interval'],
            startDate: DateTimeImmutable::createFromMutable($startDate),
            endDate: DateTimeImmutable::createFromMutable($endDate),
            shortPeriod: (int) $validated['short_period'],
            longPeriod: (int) $validated['long_period'],
        );

        // Ejecutar caso de uso
        $result = $this->calculateUseCase->execute($requestData);

        return response()->json([
            'success' => true,
            'data' => $result->toArray(),
        ]);
    }
}
