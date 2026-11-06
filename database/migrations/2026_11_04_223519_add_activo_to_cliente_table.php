<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el campo "activo" a la tabla cliente.
 * Permite desactivar un cliente en lugar de borrarlo (baja lógica, RN-21),
 * conservando sus pedidos anteriores.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
