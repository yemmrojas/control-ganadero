<?php

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
