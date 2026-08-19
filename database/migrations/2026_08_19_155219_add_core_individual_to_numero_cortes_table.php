<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('numero_cortes', function (Blueprint $table) {
            $table->boolean('core_individual')->default(false)->after('core_lb');
        });
    }

    public function down(): void
    {
        Schema::table('numero_cortes', function (Blueprint $table) {
            $table->dropColumn('core_individual');
        });
    }
    
};
