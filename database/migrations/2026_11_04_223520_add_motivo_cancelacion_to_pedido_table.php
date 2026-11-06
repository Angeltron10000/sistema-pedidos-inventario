<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el campo "motivo_cancelacion" a la tabla pedido (RF-31).
 * Se llena solo cuando el pedido se cancela.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->string('motivo_cancelacion', 200)->nullable()->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('motivo_cancelacion');
        });
    }
};
