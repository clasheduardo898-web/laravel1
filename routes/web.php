<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CorteController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\OperarioController;
use App\Http\Controllers\TipoPapelController;
use App\Http\Controllers\UserController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::middleware('permission:cortes.crear|cortes.ver-propios|cortes.ver-todos')->group(function () {
        Route::get('/cortes', [CorteController::class, 'index']);
        Route::post('/cortes', [CorteController::class, 'store']);
        Route::put('/cortes/{corte}', [CorteController::class, 'update']);
        Route::delete('/cortes/{corte}', [CorteController::class, 'destroyBorrador']);
    });

    Route::middleware('permission:historial.ver')->group(function () {
        Route::get('/historial', [HistorialController::class, 'index']);
        Route::post('/historial/{corte}/verificar', [HistorialController::class, 'verificar']);
        Route::post('/historial/{corte}/revertir', [HistorialController::class, 'revertirVerificacion']);
        Route::delete('/historial/{corte}', [HistorialController::class, 'destroy']);
        Route::get('/historial/exportar', [HistorialController::class, 'exportar']);
    });

    Route::middleware('permission:informes.ver')->group(function () {
        Route::get('/informes', [InformeController::class, 'index']);
        Route::get('/informes/exportar', [InformeController::class, 'exportar']);
        Route::get('/informes/pdf', [InformeController::class, 'exportarPdf']);
    });

    Route::middleware('permission:catalogos.gestionar')->group(function () {
        Route::get('/operarios', [OperarioController::class, 'index']);
        Route::post('/operarios', [OperarioController::class, 'store']);
        Route::put('/operarios/{operario}', [OperarioController::class, 'update']);
        Route::delete('/operarios/{operario}', [OperarioController::class, 'destroy']);

        Route::get('/tipos-papel', [TipoPapelController::class, 'index']);
        Route::post('/tipos-papel', [TipoPapelController::class, 'store']);
        Route::post('/tipos-papel/{tipoPapel}/largos', [TipoPapelController::class, 'agregarLargo']);
        Route::put('/largos-master/{largo}', [TipoPapelController::class, 'actualizarLargo']);
        Route::post('/largos-master/{largo}/alternar', [TipoPapelController::class, 'alternarLargo']);
        Route::delete('/largos-master/{largo}', [TipoPapelController::class, 'eliminarLargo']);
    });

    Route::middleware('permission:usuarios.gestionar')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::put('/usuarios/{user}', [UserController::class, 'update']);
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy']);
    });

    Route::get('/cortes/{corte}/imprimir', [CorteController::class, 'imprimir'])
    ->middleware('permission:cortes.crear|cortes.ver-propios|cortes.ver-todos|historial.ver');
    });


   Route::get('/tipos-papel/{tipoPapel}/largos-json', function (\App\Models\TipoPapel $tipoPapel) {
       return \App\Models\LargoMaster::where('tipo_papel_id', $tipoPapel->id)
           ->where('activo', true)
           ->get(['id', 'valor_original', 'unidad_medida', 'largo_mm']);
   })->middleware('auth');

Route::get('/', fn () => redirect('/cortes'));