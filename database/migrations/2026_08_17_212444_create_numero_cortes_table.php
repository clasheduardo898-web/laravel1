<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/..._create_numero_cortes_table.php
        Schema::create('numero_cortes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corte_id')->constrained()->onDelete('cascade');
            $table->string('numero');
            $table->decimal('core_lb', 10, 3)->default(0);
            $table->enum('unidad_ancho', ['mm', 'pulgada'])->default('mm');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numero_cortes');
    }
};
