<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla HISTORIAL_ESTADO: cada cambio de estado de un pedido (quién y cuándo).
     * Relaciones: PEDIDO 1:N, ESTADO_PEDIDO 1:N y USUARIO 1:N con HISTORIAL_ESTADO.
     */
    public function up(): void
    {
        Schema::create('historial_estado', function (Blueprint $table) {
            $table->increments('id_historial');                 // INT, llave primaria
            $table->unsignedInteger('id_pedido');
            $table->unsignedInteger('id_estado');
            $table->unsignedInteger('id_usuario');
            $table->dateTime('fecha_hora')->useCurrent();

            // Llaves foráneas
            $table->foreign('id_pedido', 'fk_historial_pedido')->references('id_pedido')->on('pedido')
                  ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_estado', 'fk_historial_estado')->references('id_estado')->on('estado_pedido')
                  ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_usuario', 'fk_historial_usuario')->references('id_usuario')->on('usuario')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Elimina la tabla historial_estado (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estado');
    }
};
