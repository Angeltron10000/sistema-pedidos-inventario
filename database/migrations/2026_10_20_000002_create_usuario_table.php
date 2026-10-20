<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla USUARIO: empleados que usan el sistema. Relación ROL 1 : N USUARIO.
     */
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('id_usuario');                   // INT, llave primaria
            $table->unsignedInteger('id_rol');
            $table->string('nombre', 60);
            $table->string('apellidos', 80);
            $table->string('usuario', 30)->unique('uq_usuario_usuario');
            $table->string('contrasena_hash', 255);
            $table->boolean('activo')->default(true);

            // Llave foránea hacia ROL
            $table->foreign('id_rol', 'fk_usuario_rol')->references('id_rol')->on('rol')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Elimina la tabla usuario (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
