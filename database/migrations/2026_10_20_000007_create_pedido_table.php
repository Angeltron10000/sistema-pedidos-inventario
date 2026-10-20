<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla PEDIDO: orden realizada por un cliente, con folio único, estado actual y total.
     * Relaciones: CLIENTE 1:N, USUARIO 1:N y ESTADO_PEDIDO 1:N con PEDIDO.
     */
    public function up(): void
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->increments('id_pedido');                    // INT, llave primaria
            $table->string('folio', 20)->unique('uq_pedido_folio');   // PED-#### (RN-01)
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_estado');
            $table->dateTime('fecha_hora')->useCurrent();
            $table->enum('tipo_servicio', ['local', 'llevar'])->default('local');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('observaciones', 200)->nullable();

            // Llaves foráneas
            $table->foreign('id_cliente', 'fk_pedido_cliente')->references('id_cliente')->on('cliente')
                  ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_usuario', 'fk_pedido_usuario')->references('id_usuario')->on('usuario')
                  ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_estado', 'fk_pedido_estado')->references('id_estado')->on('estado_pedido')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        // Validaciones de las reglas de negocio (restricciones CHECK)
        DB::statement('ALTER TABLE pedido ADD CONSTRAINT chk_pedido_total CHECK (total >= 0)');
    }

    /**
     * Elimina la tabla pedido (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido');
    }
};
