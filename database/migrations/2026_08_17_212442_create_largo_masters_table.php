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
        // database/migrations/..._create_largos_master_table.php (debe ir DESPUÉS de tipos_papel)
        Schema::create('largos_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_papel_id')->constrained('tipos_papel')->onDelete('cascade');
            $table->decimal('largo_mm', 10, 2);
            $table->decimal('valor_original', 10, 2);
            $table->enum('unidad_medida', ['mm', 'pulgada'])->default('mm');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('largo_masters');
    }
};
