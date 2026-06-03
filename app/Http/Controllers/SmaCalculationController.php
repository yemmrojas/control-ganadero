<?php

namespace App\Http\Controllers;

use App\Actions\CalculateAndStoreCrossoversAction;
use App\DTOs\SmaRequestData;
use App\Http\Requests\CalculateSmaRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SmaCalculationController extends Controller
{
    public function __construct(
        private CalculateAndStoreCrossoversAction $calculateAction
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

        $requestData = new SmaRequestData(
            market: $validated['market'],
            interval: $validated['interval'],
            startDate: $startDate,
            endDate: $endDate,
            shortPeriod: (int) $validated['short_period'],
            longPeriod: (int) $validated['long_period'],
        );

        $result = $this->calculateAction->execute($requestData);

        return response()->json([
            'success' => true,
            'data' => $result->toArray(),
        ]);
    }
}
