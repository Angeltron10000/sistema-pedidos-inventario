<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla CATEGORIA: agrupación de productos.
     */
    public function up(): void
    {
        Schema::create('categoria', function (Blueprint $table) {
            $table->increments('id_categoria');                 // INT, llave primaria
            $table->string('nombre', 50);
        });
    }

    /**
     * Elimina la tabla categoria (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria');
    }
};
