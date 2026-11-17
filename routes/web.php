<?php

use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\ConsultaPedidoController;
use Illuminate\Support\Facades\Route;

// La página principal lleva directo al panel administrativo de Filament.
Route::redirect('/', '/admin');

// Comprobante del pedido para imprimir (RF-29). Solo para usuarios que iniciaron sesión.
Route::get('/pedidos/{pedido}/comprobante', ComprobanteController::class)
    ->middleware('auth')
    ->name('pedidos.comprobante');

// Consulta del pedido para el CLIENTE (rol "Cliente"): sin cuenta, con folio + código de consulta.
// Se puede intentar 10 veces por minuto, para que nadie pueda probar códigos al azar.
Route::controller(ConsultaPedidoController::class)->prefix('consulta')->name('consulta.')->group(function () {
    Route::get('/', 'formulario')->name('formulario');
    Route::post('/', 'buscar')->middleware('throttle:10,1')->name('buscar');
    Route::get('/pedido', 'pedido')->name('pedido');
    Route::get('/pedido/estado', 'estado')->name('estado');
    Route::post('/pedido/cancelar', 'cancelar')->name('cancelar');
    Route::post('/salir', 'salir')->name('salir');
});
