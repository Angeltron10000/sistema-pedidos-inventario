<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla CONFIGURACION: datos generales del establecimiento.
 * Por ahora guarda el horario de servicio (RN-27): los pedidos solo se registran
 * entre la hora de apertura y la de cierre. Tiene un solo renglón.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->increments('id_configuracion');             // INT, llave primaria
            $table->string('nombre_negocio', 80)->default('Sistema de Pedidos');
            $table->time('hora_apertura')->default('08:00:00');
            $table->time('hora_cierre')->default('22:00:00');
        });

        // Renglón único con el horario inicial: 8:00 a 22:00
        DB::table('configuracion')->insert([
            'nombre_negocio' => 'Sistema de Pedidos',
            'hora_apertura'  => '08:00:00',
            'hora_cierre'    => '22:00:00',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
