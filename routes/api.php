<?php

use Illuminate\Support\Facades\Route;
use App\Models\LargoMaster;

Route::middleware(['auth'])->group(function () {
    Route::get('/api/tipos-papel/{tipoPapel}/largos', function (\App\Models\TipoPapel $tipoPapel) {
        return LargoMaster::where('tipo_papel_id', $tipoPapel->id)
            ->where('activo', true)
            ->get(['id', 'valor_original', 'unidad_medida', 'largo_mm']);
    });
});