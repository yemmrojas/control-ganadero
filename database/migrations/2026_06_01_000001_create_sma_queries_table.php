<?php

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
