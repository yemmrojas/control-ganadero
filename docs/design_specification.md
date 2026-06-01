# Documento de Diseño Técnico (SDD): Binance SMA Crossover SPA

Este documento profundiza en la fase de diseño de la aplicación. Detalla la estructura exacta de clases, métodos, firmas, esquema de base de datos, maquetación de interfaz de usuario (wireframes) y arquitectura de pruebas.

---

## 1. Diseño de Arquitectura y Clases (SOLID & Clean Architecture)

Ubicaremos nuestras clases respetando la estructura estándar de Laravel 13, creando namespaces adicionales cuando sea necesario para aislar la lógica de negocio.

### Estructura de Directorios

```
app/
├── Actions/
│   └── CalculateAndStoreCrossoversAction.php
├── DTOs/
│   ├── CrossoverDetail.php
│   ├── SmaCalculationResult.php
│   └── SmaRequestData.php
├── Http/
│   ├── Controllers/
│   │   ├── SmaCalculationController.php
│   │   └── SmaHistoryController.php
│   └── Requests/
│       └── CalculateSmaRequest.php
├── Models/
│   ├── SmaCrossover.php
│   └── SmaQuery.php
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    ├── Binance/
    │   ├── BinanceClient.php
    │   └── BinanceClientInterface.php
    └── Math/
        ├── SmaCalculator.php
        └── SmaCalculatorInterface.php
```

---

### Detalles de Clases y Firmas

#### `App\DTOs\SmaRequestData`
Estructura inmutable que transporta los parámetros de consulta limpios y validados.
```php
namespace App\DTOs;

use Carbon\Carbon;

class SmaRequestData
{
    public function __construct(
        public readonly string $market,
        public readonly string $interval,
        public readonly Carbon $startDate, // Almacenado en UTC
        public readonly Carbon $endDate,   // Almacenado en UTC
        public readonly int $shortPeriod,
        public readonly int $longPeriod
    ) {}
}
```

#### `App\Services\Binance\BinanceClientInterface`
Contrato para el cliente API de Binance. Facilita la inyección de mocks para tests.
```php
namespace App\Services\Binance;

use Carbon\Carbon;

interface BinanceClientInterface
{
    /**
     * Obtiene el listado de velas (Klines) para un mercado, intervalo y rango de fechas.
     * Realiza peticiones paginadas iterativamente si el rango excede las 1000 velas.
     * 
     * @return array Array de velas, cada vela es: [openTime, open, high, low, close, volume, closeTime, ...]
     */
    public function getKlines(string $symbol, string $interval, Carbon $startTime, Carbon $endTime): array;
}
```

#### `App\Services\Math\SmaCalculatorInterface`
Contrato para la lógica de cálculo matemático del SMA y detección de cruces.
```php
namespace App\Services\Math;

interface SmaCalculatorInterface
{
    /**
     * Calcula las SMA y detecta los puntos exactos de cruce.
     * 
     * @param array $klines Velas obtenidas desde Binance.
     * @param int $shortPeriod Periodo de la SMA corta.
     * @param int $longPeriod Periodo de la SMA larga.
     * @return array Lista de arrays asociativos representativos de los cruces de SMA.
     */
    public function detectCrossovers(array $klines, int $shortPeriod, int $longPeriod): array;
}
```

#### `App\Actions\CalculateAndStoreCrossoversAction`
Caso de uso orquestador (Single Responsibility Principle).
```php
namespace App\Actions;

use App\DTOs\SmaRequestData;
use App\DTOs\SmaCalculationResult;
use App\Services\Binance\BinanceClientInterface;
use App\Services\Math\SmaCalculatorInterface;

class CalculateAndStoreCrossoversAction
{
    public function __construct(
        private BinanceClientInterface $binanceClient,
        private SmaCalculatorInterface $smaCalculator
    ) {}

    public function execute(SmaRequestData $requestData): SmaCalculationResult
    {
        // 1. Obtener velas
        $klines = $this->binanceClient->getKlines(
            $requestData->market,
            $requestData->interval,
            $requestData->startDate,
            $requestData->endDate
        );

        // 2. Calcular SMA y cruces
        $crossovers = $this->smaCalculator->detectCrossovers(
            $klines,
            $requestData->shortPeriod,
            $requestData->longPeriod
        );

        // 3. Persistir en Base de Datos (Uso de Transacciones para atomicidad)
        $queryModel = \DB::transaction(function () use ($requestData, $crossovers) {
            $query = \App\Models\SmaQuery::create([
                'market' => $requestData->market,
                'interval' => $requestData->interval,
                'start_date' => $requestData->startDate,
                'end_date' => $requestData->endDate,
                'short_period' => $requestData->shortPeriod,
                'long_period' => $requestData->longPeriod,
                'crossovers_count' => count($crossovers),
            ]);

            foreach ($crossovers as $crossover) {
                $query->crossovers()->create([
                    'crossover_time' => Carbon::createFromTimestampMs($crossover['time']),
                    'direction' => $crossover['direction'], // 'ascending' o 'descending'
                    'short_sma_value' => $crossover['short_sma'],
                    'long_sma_value' => $crossover['long_sma'],
                    'price_at_crossover' => $crossover['price'],
                ]);
            }

            return $query;
        });

        // 4. Retornar DTO de Resultado
        return SmaCalculationResult::fromModel($queryModel);
    }
}
```

---

## 2. Diseño de la Base de Datos (Estructura Física)

### Migración `create_sma_queries_table`
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sma_queries', function (Blueprint $table) {
            $table->id();
            $table->string('market', 15);     // ej: BTCUSDT
            $table->string('interval', 5);     // ej: 30m
            $table->dateTime('start_date');     // UTC
            $table->dateTime('end_date');       // UTC
            $table->unsignedInteger('short_period');
            $table->unsignedInteger('long_period');
            $table->unsignedInteger('crossovers_count')->default(0);
            $table->timestamps();

            // Índices para búsquedas frecuentes y optimización
            $table->index(['market', 'interval']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sma_queries');
    }
};
```

### Migración `create_sma_crossovers_table`
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sma_crossovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sma_query_id')->constrained('sma_queries')->onDelete('cascade');
            $table->dateTime('crossover_time'); // UTC
            $table->enum('direction', ['ascending', 'descending']);
            $table->decimal('short_sma_value', 20, 8);
            $table->decimal('long_sma_value', 20, 8);
            $table->decimal('price_at_crossover', 20, 8);
            $table->timestamps();

            // Índices
            $table->index('sma_query_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sma_crossovers');
    }
};
```

---

## 3. Diseño de la Interfaz de Usuario (SPA Wireframes & Layout)

La SPA utilizará un diseño de tipo Dashboard moderno y premium. Implementará colores oscuros (Dark Mode) con contrastes HSL vibrantes (azul eléctrico y naranja neón para cruces) para generar un impacto estético "WOW".

### 3.1. Integración de Vue 3 con Laravel (SPA Decoplada por API)

Implementaremos la arquitectura de **SPA Desacoplada** mediante un entrypoint unificado en Blade, enrutamiento en el cliente con **Vue Router**, y peticiones asíncronas con **Axios**.

#### 1. Configuración de Vite (`vite.config.js`)
Configuraremos el compilador para soportar archivos `.vue`:
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
});
```

#### 2. Entrypoint de Laravel (Filtro de Rutas en `routes/web.php`)
Laravel capturará cualquier ruta web y la redirigirá a la vista única `app.blade.php`, permitiendo que Vue Router resuelva el camino en el cliente:
```php
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
```

#### 3. Entrypoint de Vue (`resources/js/app.js`)
El script principal inicializa e inyecta las dependencias del frontend:
```javascript
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import i18n from './i18n';

createApp(App)
    .use(router)
    .use(i18n)
    .mount('#app');
```

#### 4. Enrutamiento del Cliente (`resources/js/router/index.js`)
Definición de vistas reactivas en la SPA:
*   `/` -> Renderiza el componente `CalculatorView.vue` (Formulario y resultados).
*   `/history` -> Renderiza el componente `HistoryView.vue` (Tabla del historial de consultas).


### Maqueta de Navegación e Layout General
```
+-----------------------------------------------------------------------------------+
|  [GanadoControl Logo]    |  [Cálculo SMA]   [Historial]       | Idioma: [ES / EN] |
+-----------------------------------------------------------------------------------+
|                                                                                   |
|  CONTENIDO PRINCIPAL                                                              |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

### Pantalla 1: Calculadora de Cruces (Vista por Defecto)
```
+-----------------------------------------------------------------------------------+
|  NUEVO CÁLCULO DE CRUCES SMA                                                      |
+-----------------------------------------------------------------------------------+
|  Formulario de Configuración:                                                     |
|                                                                                   |
|  Mercado:                Intervalo:              SMA Corto:       SMA Largo:      |
|  [ BTCUSDT         v ]   [ 30m          v ]      [ 50      ]      [ 200     ]     |
|                                                                                   |
|  Fecha Desde:                    Fecha Hasta:                                     |
|  [ 2024-10-21 00:00        ]     [ 2024-10-26 23:59        ]                      |
|                                                                                   |
|                                                     [ BOTÓN: CALCULAR CRUCES ]    |
+-----------------------------------------------------------------------------------+
|  RESULTADOS                                                                       |
+-----------------------------------------------------------------------------------+
|  Resumen: Cruces Detectados: 2                                                    |
|                                                                                   |
|  Detalles de los Cruces:                                                          |
|  +--------------------+--------------+---------------+---------------+----------+ |
|  | Fecha y Hora (Loc) | Tipo Cruce   | Valor SMA (C) | Valor SMA (L) | Precio   | |
|  +--------------------+--------------+---------------+---------------+----------+ |
|  | 2024-10-21 18:00   | Descendente  | 62,340.50     | 62,350.20     | 62,345.0 | |
|  | 2024-10-24 18:00   | Ascendente   | 63,120.40     | 63,115.10     | 63,122.5 | |
|  +--------------------+--------------+---------------+---------------+----------+ |
+-----------------------------------------------------------------------------------+
```

### Pantalla 2: Historial de Consultas
```
+-----------------------------------------------------------------------------------+
|  HISTORIAL DE CONSULTAS REALIZADAS                                                |
+-----------------------------------------------------------------------------------+
|  +---------+-----------+-----------------------+----------+----------+----------+ |
|  | Mercado | Intervalo | Rango de Fechas (Loc) | SMA (C)  | SMA (L)  | Cruces   | |
|  +---------+-----------+-----------------------+----------+----------+----------+ |
|  | BTCUSDT | 30m       | 2024-10-21 - 24-10-26 | 50       | 200      |    2     | |
|  | ETHUSDT | 1h        | 2024-01-01 - 24-03-01 | 20       | 50       |   14     | |
|  +---------+-----------+-----------------------+----------+----------+----------+ |
|  * Hacer clic en una fila abre un modal con el detalle completo de los cruces.    |
+-----------------------------------------------------------------------------------+
```

---

## 4. Estructura de Traducción (i18n)

### Archivo `es.json` (Español)
```json
{
  "app_title": "Control Ganadero - SMA Crossover",
  "menu_calculator": "Calculadora",
  "menu_history": "Historial de Consultas",
  "form_market": "Mercado Cripto",
  "form_interval": "Intervalo de Tiempo",
  "form_start_date": "Fecha Desde",
  "form_end_date": "Fecha Hasta",
  "form_short_sma": "Longitud SMA Corto",
  "form_long_sma": "Longitud SMA Largo",
  "btn_calculate": "Calcular Cruces",
  "validation_error": "Por favor corrige los errores del formulario",
  "range_limit_error": "El rango de fechas seleccionado excede el máximo permitido para este intervalo.",
  "results_title": "Resultados de la Consulta",
  "total_crossovers": "Total de Cruces de SMA detectados: {count}",
  "table_date": "Fecha y Hora",
  "table_type": "Dirección del Cruce",
  "table_short_sma": "Valor SMA Corto",
  "table_long_sma": "Valor SMA Largo",
  "table_price": "Precio de Cierre",
  "crossover_ascending": "Ascendente (Golden Cross)",
  "crossover_descending": "Descendente (Death Cross)"
}
```

---

## 5. Diseño y Arquitectura de Pruebas

Para garantizar que los tests sean rápidos, reproducibles e infalibles, estructuraremos nuestras pruebas con **Pest**.

### 1. Test Unitario: `tests/Unit/SmaCalculatorTest.php`
Este test no tocará la red ni la base de datos.
*   **Caso de prueba 1**: Calcular SMA con un array plano de números (precios). Comprobar que el valor coincida con la fórmula matemática exacta.
*   **Caso de prueba 2**: Detectar un cruce ascendente (Golden Cross). Proveer una serie de precios cortos y largos donde el corto supere al largo e identificar el índice y marca de tiempo correctos.
*   **Caso de prueba 3**: Detectar un cruce descendente (Death Cross).

### 2. Test de Integración: `tests/Feature/SmaCalculationApiTest.php`
Este test verifica la API REST expuesta en `/api/sma-crossover`.
*   **Caso de prueba 1**: La validación falla si el rango de fecha excede el límite adaptativo.
*   **Caso de prueba 2**: Mockear la API de Binance usando `Http::fake()` y simular la llegada de 50 velas. Comprobar que los registros se guarden en la base de datos (`assertDatabaseHas`) en las tablas `sma_queries` y `sma_crossovers`.
*   **Caso de prueba 3 (Verificación de Aceptación)**: Simular la petición del ejemplo del PDF (BTCUSDT, 30m, 21 al 26 de Octubre de 2024, SMA 50/200). Mockear las velas reales descargadas de Binance para esas fechas y verificar que la respuesta del endpoint contenga exactamente los 2 cruces con las marcas de tiempo especificadas en el PDF.
