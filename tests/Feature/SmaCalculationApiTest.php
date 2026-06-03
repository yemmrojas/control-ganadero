<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Models\SmaQuery;

uses(RefreshDatabase::class);

describe('SMA Calculation API', function () {
    it('validates required fields', function () {
        $response = $this->postJson('/api/sma-crossover', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['market', 'interval', 'start_date', 'end_date', 'short_period', 'long_period']);
    });

    it('validates market is in allowed list', function () {
        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'INVALID',
            'interval' => '30m',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-22 00:00:00',
            'short_period' => 50,
            'long_period' => 200,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['market']);
    });

    it('validates interval is in allowed list', function () {
        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'BTCUSDT',
            'interval' => 'invalid',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-22 00:00:00',
            'short_period' => 50,
            'long_period' => 200,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interval']);
    });

    it('validates end_date is after start_date', function () {
        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'start_date' => '2024-10-22 00:00:00',
            'end_date' => '2024-10-21 00:00:00',
            'short_period' => 50,
            'long_period' => 200,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    });

    it('validates long_period is greater than short_period', function () {
        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-22 00:00:00',
            'short_period' => 200,
            'long_period' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['long_period']);
    });

    it('validates date range does not exceed maximum for interval', function () {
        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'BTCUSDT',
            'interval' => '1m',
            'start_date' => '2024-01-01 00:00:00',
            'end_date' => '2024-12-31 23:59:59', // Más de 7 días para intervalo 1m
            'short_period' => 50,
            'long_period' => 200,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_range']);
    });

    it('successfully calculates crossovers with mocked Binance API', function () {
        // Mock de la respuesta de Binance con datos simulados
        Http::fake([
            'api.binance.com/*' => Http::response([
                [1729468800000, '62000', '62500', '61800', '62340.50', '100', 1729470600000],
                [1729470600000, '62340', '62400', '62200', '62350.20', '100', 1729472400000],
                [1729472400000, '62350', '62450', '62300', '62400.00', '100', 1729474200000],
                [1729474200000, '62400', '62500', '62350', '62450.00', '100', 1729476000000],
                [1729476000000, '62450', '62550', '62400', '62500.00', '100', 1729477800000],
            ], 200),
        ]);

        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-21 06:00:00',
            'short_period' => 2,
            'long_period' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'market',
                    'interval',
                    'start_date',
                    'end_date',
                    'short_period',
                    'long_period',
                    'crossovers_count',
                    'crossovers',
                    'created_at',
                ],
            ]);

        // Verificar que se guardó en la base de datos
        $this->assertDatabaseHas('sma_queries', [
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'short_period' => 2,
            'long_period' => 3,
        ]);
    });

    it('correctly handles user timezone when parsing dates', function () {
        // Mock de Binance API
        Http::fake([
            'api.binance.com/*' => Http::response([
                [1729468800000, '62000', '62500', '61800', '62340.50', '100', 1729470600000],
            ], 200),
        ]);

        // Usuario en US/Eastern (UTC-5) selecciona 2024-10-21 00:00
        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-21 06:00:00',
            'short_period' => 2,
            'long_period' => 3,
            'timezone' => 'America/New_York', // US/Eastern
        ]);

        $response->assertStatus(200);

        // Verificar que las fechas se guardaron correctamente en UTC
        $query = SmaQuery::latest()->first();
        
        // 2024-10-21 00:00 en America/New_York = 2024-10-21 04:00 UTC (durante DST)
        // o 2024-10-21 05:00 UTC (fuera de DST)
        expect($query->start_date->timezone->getName())->toBe('UTC');
        expect($query->end_date->timezone->getName())->toBe('UTC');
    });

    it('defaults to UTC when no timezone is provided', function () {
        Http::fake([
            'api.binance.com/*' => Http::response([
                [1729468800000, '62000', '62500', '61800', '62340.50', '100', 1729470600000],
            ], 200),
        ]);

        $response = $this->postJson('/api/sma-crossover', [
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-21 06:00:00',
            'short_period' => 2,
            'long_period' => 3,
            // Sin timezone
        ]);

        $response->assertStatus(200);

        $query = SmaQuery::latest()->first();
        expect($query->start_date->timezone->getName())->toBe('UTC');
    });
});

describe('SMA History API', function () {
    it('returns empty list when no queries exist', function () {
        $response = $this->getJson('/api/sma-history');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    });

    it('returns list of queries', function () {
        // Crear una consulta de prueba
        $query = SmaQuery::create([
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-22 00:00:00',
            'short_period' => 50,
            'long_period' => 200,
            'crossovers_count' => 2,
        ]);

        $response = $this->getJson('/api/sma-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'market',
                        'interval',
                        'start_date',
                        'end_date',
                        'short_period',
                        'long_period',
                        'crossovers_count',
                        'created_at',
                    ],
                ],
            ]);
    });

    it('returns query details with crossovers', function () {
        // Crear una consulta con cruces
        $query = SmaQuery::create([
            'market' => 'BTCUSDT',
            'interval' => '30m',
            'start_date' => '2024-10-21 00:00:00',
            'end_date' => '2024-10-22 00:00:00',
            'short_period' => 50,
            'long_period' => 200,
            'crossovers_count' => 1,
        ]);

        $query->crossovers()->create([
            'crossover_time' => '2024-10-21 18:00:00',
            'direction' => 'ascending',
            'short_sma_value' => 62340.50,
            'long_sma_value' => 62350.20,
            'price_at_crossover' => 62345.00,
        ]);

        $response = $this->getJson("/api/sma-history/{$query->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'market',
                    'interval',
                    'crossovers' => [
                        '*' => [
                            'crossover_time',
                            'direction',
                            'short_sma_value',
                            'long_sma_value',
                            'price_at_crossover',
                        ],
                    ],
                ],
            ]);
    });

    it('returns 404 for non-existent query', function () {
        $response = $this->getJson('/api/sma-history/99999');

        $response->assertStatus(404);
    });
});
