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
     */
    public function calculate(CalculateSmaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $requestData = new SmaRequestData(
            market: $validated['market'],
            interval: $validated['interval'],
            startDate: Carbon::parse($validated['start_date'])->utc(),
            endDate: Carbon::parse($validated['end_date'])->utc(),
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
