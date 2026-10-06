<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MockupController;
use App\Http\Controllers\SecretariaController;
use Illuminate\Support\Facades\Route;


# si no esta autenticado, se redirige a la pagina de login
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

# si esta autenticado, se redirige a la pagina de panel
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('acceso')->group(function () {
    Route::get('/panel/{rol}', [MockupController::class, 'panel'])
        ->whereIn('rol', ['administradora', 'enfermera', 'operador', 'secretaria'])
        ->name('mockups.panel');
    Route::get('/operador/recepcion', [MockupController::class, 'recepcion'])->name('mockups.recepcion');
    Route::get('/operador/avanzar', [MockupController::class, 'avanzar'])->name('mockups.avanzar');
    Route::post('/operador/avanzar', [MockupController::class, 'guardarAvance'])->name('mockups.avanzar.guardar');
    Route::post('/operador/anotar', [MockupController::class, 'guardarAnotacion'])->name('mockups.anotar');
    Route::get('/operador/actividad', [MockupController::class, 'actividad'])->name('mockups.actividad');
    Route::get('/operador/entrega', [MockupController::class, 'entrega'])->name('mockups.entrega');
    Route::get('/consulta/catalogo', [MockupController::class, 'catalogo'])->name('mockups.catalogo');
    Route::get('/admin/catalogo', [MockupController::class, 'catalogoAdmin'])->name('mockups.catalogo.admin');
    Route::get('/consulta/catalogo/{codigo}', [MockupController::class, 'ficha'])->name('mockups.ficha');
    Route::get('/consulta/inventario', [MockupController::class, 'inventario'])->name('mockups.inventario');
    Route::get('/admin/usuarios', [MockupController::class, 'usuarios'])->name('mockups.usuarios');
    Route::get('/admin/reportes', [MockupController::class, 'reportes'])->name('mockups.reportes');
    Route::get('/admin/entregas', [MockupController::class, 'historialEntregas'])->name('mockups.historial_entregas');
    Route::get('/admin/custodia', [MockupController::class, 'custodia'])->name('mockups.custodia');
    Route::get('/admin/alertas', [MockupController::class, 'alertas'])->name('mockups.alertas');
    Route::get('/enfermera/cierre-turno', [MockupController::class, 'cierreTurno'])->name('mockups.cierre_turno');

    Route::get('/secretaria/produccion', [SecretariaController::class, 'produccion'])->name('secretaria.produccion');
    Route::post('/secretaria/produccion', [SecretariaController::class, 'guardarProduccion'])->name('secretaria.produccion.guardar');
    Route::get('/secretaria/insumos', [SecretariaController::class, 'insumos'])->name('secretaria.insumos');
    Route::post('/secretaria/insumos/movimientos', [SecretariaController::class, 'guardarMovimiento'])->name('secretaria.insumos.movimiento');
    Route::get('/secretaria/reportes', [SecretariaController::class, 'reportes'])->name('secretaria.reportes');
    Route::get('/secretaria/reportes/exportar', [SecretariaController::class, 'exportarReportes'])->name('secretaria.reportes.exportar');
    });
});
