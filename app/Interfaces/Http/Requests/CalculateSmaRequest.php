<?php

namespace App\Interfaces\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validación de solicitud de cálculo de SMA.
 * 
 * Vive en Interfaces porque es específico del framework HTTP.
 * Valida reglas de negocio y restricciones técnicas.
 */
final class CalculateSmaRequest extends FormRequest
{
    /**
     * Mercados permitidos según el documento de especificaciones.
     */
    private const ALLOWED_MARKETS = ['BTCUSDT', 'ETHUSDT', 'XRPUSDT'];

    /**
     * Intervalos permitidos por la API de Binance.
     */
    private const ALLOWED_INTERVALS = [
        '1m', '3m', '5m', '15m', '30m',
        '1h', '2h', '4h', '6h', '8h', '12h',
        '1d', '3d', '1w',
    ];

    /**
     * Límites de rango máximo en días por intervalo (estrategia adaptativa).
     * Protege contra rate-limiting de Binance y asegura rendimiento.
     */
    private const MAX_RANGE_DAYS = [
        '1m' => 7,
        '3m' => 7,
        '5m' => 14,
        '15m' => 30,
        '30m' => 60,
        '1h' => 180,
        '2h' => 180,
        '4h' => 365,
        '6h' => 365,
        '8h' => 365,
        '12h' => 365,
        '1d' => 1095,
        '3d' => 1095,
        '1w' => 1095,
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'market' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_MARKETS)],
            'interval' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_INTERVALS)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'short_period' => ['required', 'integer', 'min:1'],
            'long_period' => ['required', 'integer', 'min:2', 'gt:short_period'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ];
    }

    /**
     * Validaciones adicionales después de las reglas estándar.
     * Implementa la validación adaptativa del rango de fechas.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $interval = $this->input('interval');
            $startDate = Carbon::parse($this->input('start_date'));
            $endDate = Carbon::parse($this->input('end_date'));

            $maxDays = self::MAX_RANGE_DAYS[$interval] ?? 7;
            $actualDays = $startDate->diffInDays($endDate);

            if ($actualDays > $maxDays) {
                $validator->errors()->add(
                    'date_range',
                    "El rango de fechas seleccionado ({$actualDays} días) excede el máximo permitido de {$maxDays} días para el intervalo '{$interval}'."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'market.required' => 'El mercado es obligatorio.',
            'market.in' => 'El mercado debe ser uno de: ' . implode(', ', self::ALLOWED_MARKETS) . '.',
            'interval.required' => 'El intervalo es obligatorio.',
            'interval.in' => 'El intervalo seleccionado no es válido.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date' => 'La fecha de inicio no tiene un formato válido.',
            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date' => 'La fecha de fin no tiene un formato válido.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'short_period.required' => 'El periodo SMA corto es obligatorio.',
            'short_period.integer' => 'El periodo SMA corto debe ser un número entero.',
            'short_period.min' => 'El periodo SMA corto debe ser al menos 1.',
            'long_period.required' => 'El periodo SMA largo es obligatorio.',
            'long_period.integer' => 'El periodo SMA largo debe ser un número entero.',
            'long_period.min' => 'El periodo SMA largo debe ser al menos 2.',
            'long_period.gt' => 'El periodo SMA largo debe ser mayor que el periodo corto.',
        ];
    }
}
