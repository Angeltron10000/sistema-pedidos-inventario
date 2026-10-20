<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla ROL: tipos de usuario del sistema (Administrador, Cajero/Mesero, Cocina).
     */
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->increments('id_rol');                       // INT, llave primaria
            $table->string('nombre', 30);
            $table->string('descripcion', 100)->nullable();
        });
    }

    /**
     * Elimina la tabla rol (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
