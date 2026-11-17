<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Consulta del pedido por el cliente (ajuste pedido por la profesora, 16-nov-2026).
 *
 * 1) PEDIDO.codigo_consulta: código corto al azar (por ejemplo K7P2) que se imprime
 *    en el comprobante. El cliente escribe su folio + este código para ver su pedido.
 *    Solo con el folio no basta, porque los folios van en orden y se podrían adivinar.
 *
 * 2) Rol "Cliente" (id_rol = 4) y su usuario "consulta-cliente".
 *    Los clientes NO tienen cuenta ni contraseña: entran a la página de consulta
 *    con folio y código. Este usuario sirve para dejar anotado en el historial y en
 *    los movimientos de inventario que fue el cliente quien canceló su pedido.
 *    Este usuario no puede entrar al panel.
 *
 * En una base nueva (php artisan migrate:fresh --seed) el rol y el usuario los crean
 * RolSeeder y UsuarioSeeder; aquí solo se agregan si la base ya tenía datos.
 */
return new class extends Migration
{
    /** Letras y números sin los que se confunden (0/O, 1/I/L). */
    private const CARACTERES = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->string('codigo_consulta', 4)->nullable()->after('folio');
        });

        // Pedidos que ya existían: se les da su código
        foreach (DB::table('pedido')->pluck('id_pedido') as $id) {
            DB::table('pedido')->where('id_pedido', $id)->update(['codigo_consulta' => $this->codigo()]);
        }

        // Base con datos: se agregan el rol "Cliente" y su usuario
        if (DB::table('rol')->exists()) {
            if (! DB::table('rol')->where('nombre', 'Cliente')->exists()) {
                $libre = ! DB::table('rol')->where('id_rol', 4)->exists();

                DB::table('rol')->insert(array_filter([
                    'id_rol'      => $libre ? 4 : null,
                    'nombre'      => 'Cliente',
                    'descripcion' => 'Consulta su pedido con folio y código; no entra al panel',
                ], fn ($valor) => $valor !== null));
            }

            if (! DB::table('usuario')->where('usuario', 'consulta-cliente')->exists()) {
                DB::table('usuario')->insert([
                    'id_rol'          => DB::table('rol')->where('nombre', 'Cliente')->value('id_rol'),
                    'nombre'          => 'Cliente',
                    'apellidos'       => '(consulta en línea)',
                    'usuario'         => 'consulta-cliente',
                    'contrasena_hash' => Hash::make(Str::random(40)),
                    'activo'          => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('codigo_consulta');
        });
    }

    private function codigo(): string
    {
        $codigo = '';
        for ($i = 0; $i < 4; $i++) {
            $codigo .= self::CARACTERES[random_int(0, strlen(self::CARACTERES) - 1)];
        }

        return $codigo;
    }
};
