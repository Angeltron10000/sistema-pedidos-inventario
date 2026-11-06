<?php

use Illuminate\Support\Facades\Route;

// La página principal lleva directo al panel administrativo de Filament.
Route::redirect('/', '/admin');
