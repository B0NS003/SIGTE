<?php

use App\Http\Controllers\MockupController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('mockups.login'));
Route::get('/login', [MockupController::class, 'login'])->name('mockups.login');
Route::get('/panel/{rol}', [MockupController::class, 'panel'])
    ->whereIn('rol', ['administradora', 'enfermera', 'operador'])
    ->name('mockups.panel');
Route::get('/operador/recepcion', [MockupController::class, 'recepcion'])->name('mockups.recepcion');
Route::get('/operador/avanzar', [MockupController::class, 'avanzar'])->name('mockups.avanzar');
Route::get('/operador/entrega', [MockupController::class, 'entrega'])->name('mockups.entrega');
Route::get('/consulta/catalogo', [MockupController::class, 'catalogo'])->name('mockups.catalogo');
Route::get('/consulta/inventario', [MockupController::class, 'inventario'])->name('mockups.inventario');
Route::get('/admin/usuarios', [MockupController::class, 'usuarios'])->name('mockups.usuarios');
Route::get('/admin/reportes', [MockupController::class, 'reportes'])->name('mockups.reportes');
Route::get('/admin/custodia', [MockupController::class, 'custodia'])->name('mockups.custodia');
Route::get('/admin/alertas', [MockupController::class, 'alertas'])->name('mockups.alertas');
Route::get('/enfermera/cierre-turno', [MockupController::class, 'cierreTurno'])->name('mockups.cierre_turno');
