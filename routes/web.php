<?php

use App\Http\Controllers\ComprobanteController;
use Illuminate\Support\Facades\Route;

// La página principal lleva directo al panel administrativo de Filament.
Route::redirect('/', '/admin');

// Comprobante del pedido para imprimir (RF-29). Solo para usuarios que iniciaron sesión.
Route::get('/pedidos/{pedido}/comprobante', ComprobanteController::class)
    ->middleware('auth')
    ->name('pedidos.comprobante');
