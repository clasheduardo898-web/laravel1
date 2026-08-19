<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('numero_cortes', function (Blueprint $table) {
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('numero_cortes', function (Blueprint $table) {
            $table->dropForeign(['verificado_por']);
            $table->dropColumn(['verificado_por', 'verificado_en']);
        });
    }
};
