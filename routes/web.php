<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ruta fallback que sirve la SPA de Vue.
| Cualquier URL que no coincida con /api/* será capturada aquí
| y delegada a Vue Router en el navegador del cliente.
|
*/

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api).*');
