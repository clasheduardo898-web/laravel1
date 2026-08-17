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
        // database/migrations/..._create_cortes_table.php
        Schema::create('cortes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('operario');
            $table->string('tipo_papel');
            $table->foreignId('tipo_papel_id')->nullable()->constrained('tipos_papel')->nullOnDelete();
            $table->decimal('rollo_largo_mm', 10, 2);
            $table->foreignId('largo_master_id')->nullable()->constrained('largos_master')->nullOnDelete();
            $table->decimal('rollo_peso_kg', 10, 2);
            $table->decimal('merma_kg', 10, 3)->default(0);
            $table->enum('estado', ['borrador', 'finalizado'])->default('borrador');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes');
    }
};
