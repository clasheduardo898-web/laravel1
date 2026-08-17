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
        // database/migrations/..._create_rollos_cortados_table.php
        Schema::create('rollos_cortados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('numero_corte_id')->constrained()->onDelete('cascade');
            $table->decimal('ancho_mm', 10, 2);
            $table->decimal('core_lb', 10, 3)->default(0);
            $table->decimal('peso_lb', 10, 3);
            $table->decimal('peso_neto_lb', 10, 3)->default(0);
            $table->decimal('peso_kg', 10, 3)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollo_cortados');
    }
};
