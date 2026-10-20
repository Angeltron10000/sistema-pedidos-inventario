<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla ESTADO_PEDIDO: catálogo de estados (Recibido, En preparación, Listo, Entregado, Cancelado).
     */
    public function up(): void
    {
        Schema::create('estado_pedido', function (Blueprint $table) {
            $table->increments('id_estado');                    // INT, llave primaria
            $table->string('nombre', 20);
            $table->integer('orden');                           // secuencia de los estados
        });
    }

    /**
     * Elimina la tabla estado_pedido (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_pedido');
    }
};
