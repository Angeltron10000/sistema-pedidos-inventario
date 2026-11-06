<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el campo "activo" a la tabla categoria (RF-10).
 * Permite desactivar una categoría en lugar de borrarla (baja lógica, RN-21).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categoria', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('categoria', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
