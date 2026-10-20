<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla DETALLE_PEDIDO: productos incluidos en cada pedido.
     * Relaciones: PEDIDO 1:N y PRODUCTO 1:N con DETALLE_PEDIDO (resuelve la relación N:M).
     */
    public function up(): void
    {
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->increments('id_detalle');                   // INT, llave primaria
            $table->unsignedInteger('id_pedido');
            $table->unsignedInteger('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);          // precio congelado (RN-13)
            $table->decimal('subtotal', 10, 2);                 // cantidad × precio (RN-14)
            $table->string('notas', 100)->nullable();

            // Llaves foráneas
            $table->foreign('id_pedido', 'fk_detalle_pedido')->references('id_pedido')->on('pedido')
                  ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_producto', 'fk_detalle_producto')->references('id_producto')->on('producto')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        // Validaciones de las reglas de negocio (restricciones CHECK)
        DB::statement('ALTER TABLE detalle_pedido ADD CONSTRAINT chk_detalle_cantidad CHECK (cantidad >= 1)');
        DB::statement('ALTER TABLE detalle_pedido ADD CONSTRAINT chk_detalle_precio CHECK (precio_unitario > 0)');
        DB::statement('ALTER TABLE detalle_pedido ADD CONSTRAINT chk_detalle_subtotal CHECK (subtotal = cantidad * precio_unitario)');
    }

    /**
     * Elimina la tabla detalle_pedido (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
