<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla PRODUCTO: artículos que vende el establecimiento. Relación CATEGORIA 1 : N PRODUCTO.
     */
    public function up(): void
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->increments('id_producto');                  // INT, llave primaria
            $table->unsignedInteger('id_categoria');
            $table->string('nombre', 80);
            $table->string('descripcion', 200)->nullable();
            $table->decimal('precio', 10, 2);
            $table->integer('existencia')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->boolean('activo')->default(true);

            // Llave foránea hacia CATEGORIA
            $table->foreign('id_categoria', 'fk_producto_categoria')->references('id_categoria')->on('categoria')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        // Validaciones de las reglas de negocio (restricciones CHECK)
        DB::statement('ALTER TABLE producto ADD CONSTRAINT chk_producto_precio CHECK (precio > 0)');
        DB::statement('ALTER TABLE producto ADD CONSTRAINT chk_producto_existencia CHECK (existencia >= 0)');
        DB::statement('ALTER TABLE producto ADD CONSTRAINT chk_producto_stock_minimo CHECK (stock_minimo >= 0)');
    }

    /**
     * Elimina la tabla producto (se usa al revertir la migración).
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
