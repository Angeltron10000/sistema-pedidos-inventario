<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla MOVIMIENTO_INVENTARIO: entradas, salidas y ajustes de existencias.
     * Relaciones: PRODUCTO 1:N, USUARIO 1:N y PEDIDO 1:N (opcional) con MOVIMIENTO_INVENTARIO.
     */
    public function up(): void
    {
        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->increments('id_movimiento');                // INT, llave primaria
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_pedido')->nullable();
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->integer('cantidad');                        // en "ajuste" lleva signo (+/-)
            $table->dateTime('fecha_hora')->useCurrent();
            $table->string('motivo', 120)->nullable();

            // Llaves foráneas (id_pedido puede ser nulo)
            $table->foreign('id_producto', 'fk_movimiento_producto')->references('id_producto')->on('producto')
                  ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_usuario', 'fk_movimiento_usuario')->references('id_usuario')->on('usuario')
                  ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_pedido', 'fk_movimiento_pedido')->references('id_pedido')->on('pedido')
                  ->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Elimina la tabla movimiento_inventario (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
    }
};
