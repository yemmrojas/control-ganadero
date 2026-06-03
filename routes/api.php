<?php

use App\Interfaces\Http\Controllers\SmaCalculationController;
use App\Interfaces\Http\Controllers\SmaHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoints REST para la SPA de cálculo de cruces de SMA.
| Prefijo automático: /api
|
*/

// Endpoint de cálculo de cruces SMA
Route::post('/sma-crossover', [SmaCalculationController::class, 'calculate']);

// Endpoints de historial de consultas
Route::get('/sma-history', [SmaHistoryController::class, 'index']);
Route::get('/sma-history/{id}', [SmaHistoryController::class, 'show'])->whereNumber('id');
