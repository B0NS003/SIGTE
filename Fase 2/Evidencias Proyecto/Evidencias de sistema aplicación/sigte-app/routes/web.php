<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\EnfermeraController;
use App\Http\Controllers\FlujoController;
use App\Http\Controllers\InventarioController;
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
        Route::get('/panel/{rol}', [FlujoController::class, 'panel'])
            ->whereIn('rol', ['administradora', 'enfermera', 'operador', 'secretaria'])
            ->name('mockups.panel');
        Route::get('/operador/recepcion', [FlujoController::class, 'recepcion'])->name('mockups.recepcion');
        Route::get('/operador/avanzar', [FlujoController::class, 'avanzar'])->name('mockups.avanzar');
        Route::post('/operador/avanzar', [FlujoController::class, 'guardarAvance'])->name('mockups.avanzar.guardar');
        Route::post('/operador/retroceder', [FlujoController::class, 'guardarRetroceso'])->name('mockups.retroceder');
        Route::post('/operador/anotar', [FlujoController::class, 'guardarAnotacion'])->name('mockups.anotar');
        Route::get('/operador/actividad', [FlujoController::class, 'actividad'])->name('mockups.actividad');
        Route::get('/operador/entrega', [FlujoController::class, 'entrega'])->name('mockups.entrega');
        Route::post('/operador/entrega', [FlujoController::class, 'registrarEntrega'])->name('mockups.entrega.registrar');
        Route::get('/consulta/catalogo', [CatalogoController::class, 'catalogo'])->name('mockups.catalogo');
        Route::get('/admin/catalogo', [CatalogoController::class, 'catalogoAdmin'])->name('mockups.catalogo.admin');
        Route::get('/consulta/catalogo/{codigo}', [CatalogoController::class, 'ficha'])->name('mockups.ficha');
        Route::get('/consulta/inventario', [InventarioController::class, 'inventario'])->name('mockups.inventario');
        Route::get('/admin/usuarios', [AdminController::class, 'usuarios'])->name('mockups.usuarios');
        Route::get('/admin/reportes', [AdminController::class, 'reportes'])->name('mockups.reportes');
        Route::get('/admin/entregas', [AdminController::class, 'historialEntregas'])->name('mockups.historial_entregas');
        Route::get('/admin/custodia', [AdminController::class, 'custodia'])->name('mockups.custodia');
        Route::get('/admin/alertas', [AdminController::class, 'alertas'])->name('mockups.alertas');
        Route::get('/enfermera/cierre-turno', [EnfermeraController::class, 'cierreTurno'])->name('mockups.cierre_turno');

        Route::get('/secretaria/produccion', [SecretariaController::class, 'produccion'])->name('secretaria.produccion');
        Route::post('/secretaria/produccion', [SecretariaController::class, 'guardarProduccion'])->name('secretaria.produccion.guardar');
        Route::get('/secretaria/insumos', [SecretariaController::class, 'insumos'])->name('secretaria.insumos');
        Route::post('/secretaria/insumos/movimientos', [SecretariaController::class, 'guardarMovimiento'])->name('secretaria.insumos.movimiento');
        Route::get('/secretaria/reportes', [SecretariaController::class, 'reportes'])->name('secretaria.reportes');
        Route::get('/secretaria/reportes/exportar', [SecretariaController::class, 'exportarReportes'])->name('secretaria.reportes.exportar');
    });
});
