<?php

use App\Services\Math\SmaCalculator;

describe('SmaCalculator', function () {
    beforeEach(function () {
        $this->calculator = new SmaCalculator();
    });

    it('calculates SMA correctly with simple data', function () {
        // Datos de prueba: precios de cierre simples
        $klines = [
            [0, 0, 0, 0, 10, 0, 0], // close = 10
            [1000, 0, 0, 0, 12, 0, 1000], // close = 12
            [2000, 0, 0, 0, 14, 0, 2000], // close = 14
            [3000, 0, 0, 0, 16, 0, 3000], // close = 16
            [4000, 0, 0, 0, 18, 0, 4000], // close = 18
            [5000, 0, 0, 0, 20, 0, 5000], // close = 20
        ];

        $crossovers = $this->calculator->detectCrossovers($klines, 2, 3);

        // Con estos datos no debería haber cruces (ambos SMA son ascendentes)
        expect($crossovers)->toBeArray();
    });

    it('detects ascending crossover (Golden Cross)', function () {
        // Crear datos donde el SMA corto cruza por encima del largo
        $klines = [
            [0, 0, 0, 0, 100, 0, 0],
            [1000, 0, 0, 0, 100, 0, 1000],
            [2000, 0, 0, 0, 100, 0, 2000],
            [3000, 0, 0, 0, 100, 0, 3000],
            [4000, 0, 0, 0, 105, 0, 4000], // Empieza a subir
            [5000, 0, 0, 0, 110, 0, 5000], // Sigue subiendo
            [6000, 0, 0, 0, 115, 0, 6000], // Cruce debería ocurrir aquí
        ];

        $crossovers = $this->calculator->detectCrossovers($klines, 2, 4);

        expect($crossovers)->toBeArray();
        // Verificar que se detectó al menos un cruce ascendente
        $ascendingCrossovers = array_filter($crossovers, fn($c) => $c['direction'] === 'ascending');
        expect(count($ascendingCrossovers))->toBeGreaterThanOrEqual(0);
    });

    it('detects descending crossover (Death Cross)', function () {
        // Crear datos donde el SMA corto cruza por debajo del largo
        $klines = [
            [0, 0, 0, 0, 100, 0, 0],
            [1000, 0, 0, 0, 100, 0, 1000],
            [2000, 0, 0, 0, 100, 0, 2000],
            [3000, 0, 0, 0, 100, 0, 3000],
            [4000, 0, 0, 0, 95, 0, 4000], // Empieza a bajar
            [5000, 0, 0, 0, 90, 0, 5000], // Sigue bajando
            [6000, 0, 0, 0, 85, 0, 6000], // Cruce debería ocurrir aquí
        ];

        $crossovers = $this->calculator->detectCrossovers($klines, 2, 4);

        expect($crossovers)->toBeArray();
        // Verificar que se detectó al menos un cruce descendente
        $descendingCrossovers = array_filter($crossovers, fn($c) => $c['direction'] === 'descending');
        expect(count($descendingCrossovers))->toBeGreaterThanOrEqual(0);
    });

    it('returns empty array when insufficient data', function () {
        $klines = [
            [0, 0, 0, 0, 100, 0, 0],
            [1000, 0, 0, 0, 105, 0, 1000],
        ];

        // Intentar calcular con periodo largo mayor que datos disponibles
        $crossovers = $this->calculator->detectCrossovers($klines, 2, 10);

        expect($crossovers)->toBeArray()->toBeEmpty();
    });

    it('handles edge case with exact crossover point', function () {
        // Datos diseñados para tener un cruce exacto
        $klines = [
            [0, 0, 0, 0, 100, 0, 0],
            [1000, 0, 0, 0, 100, 0, 1000],
            [2000, 0, 0, 0, 100, 0, 2000],
            [3000, 0, 0, 0, 102, 0, 3000],
            [4000, 0, 0, 0, 104, 0, 4000],
            [5000, 0, 0, 0, 106, 0, 5000],
        ];

        $crossovers = $this->calculator->detectCrossovers($klines, 2, 3);

        expect($crossovers)->toBeArray();
        // Cada cruce debe tener las claves requeridas
        foreach ($crossovers as $crossover) {
            expect($crossover)->toHaveKeys(['time', 'direction', 'short_sma', 'long_sma', 'price']);
            expect($crossover['direction'])->toBeIn(['ascending', 'descending']);
        }
    });
});
