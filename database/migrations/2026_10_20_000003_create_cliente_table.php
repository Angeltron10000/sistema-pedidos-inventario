<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla CLIENTE: persona que realiza el pedido (registro opcional).
     */
    public function up(): void
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->increments('id_cliente');                   // INT, llave primaria
            $table->string('nombre', 80);
            $table->string('telefono', 15)->nullable();
        });
    }

    /**
     * Elimina la tabla cliente (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
