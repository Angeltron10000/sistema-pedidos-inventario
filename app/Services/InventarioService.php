<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reglas del inventario.
 *
 * Toda entrada o ajuste cambia la existencia del producto Y deja un registro en
 * movimiento_inventario con usuario, fecha, cantidad y motivo (RN-22).
 * Las salidas por pedido las registra PedidoService.
 */
class InventarioService
{
    /** Motivos que se ofrecen al ajustar la existencia (RN-23). */
    public const MOTIVOS_AJUSTE = [
        'Merma'           => 'Merma (se echó a perder o se tiró)',
        'Conteo físico'   => 'Conteo físico (se contó y no coincide)',
        'Producto dañado' => 'Producto dañado',
        'Otro'            => 'Otro',
    ];

    /**
     * Entrada por resurtido: suma piezas a la existencia (RF-16).
     */
    public function registrarEntrada(Producto $producto, int $cantidad, ?string $motivo, Usuario $usuario): MovimientoInventario
    {
        if ($cantidad < 1) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad de la entrada debe ser al menos 1.',
            ]);
        }

        return DB::transaction(function () use ($producto, $cantidad, $motivo, $usuario) {
            $producto->increment('existencia', $cantidad);

            return MovimientoInventario::create([
                'id_producto' => $producto->id_producto,
                'id_usuario'  => $usuario->id_usuario,
                'tipo'        => 'entrada',
                'cantidad'    => $cantidad,
                'fecha_hora'  => now(),
                'motivo'      => filled($motivo) ? $motivo : 'Resurtido',
            ]);
        });
    }

    /**
     * Ajuste: se escribe cuántas piezas hay realmente y el sistema calcula la diferencia
     * (negativa = faltan, positiva = sobran). Requiere motivo y solo lo hace el
     * Administrador (RF-17, RN-23). La existencia nunca queda negativa (RN-06).
     */
    public function ajustar(Producto $producto, int $existenciaReal, string $motivo, ?string $detalle, Usuario $usuario): MovimientoInventario
    {
        if (! $usuario->esAdministrador()) {
            throw ValidationException::withMessages([
                'existencia_real' => 'Solo el Administrador puede ajustar el inventario.',
            ]);
        }

        if ($existenciaReal < 0) {
            throw ValidationException::withMessages([
                'existencia_real' => 'La existencia no puede ser negativa.',
            ]);
        }

        if (blank($motivo)) {
            throw ValidationException::withMessages([
                'motivo' => 'Indica el motivo del ajuste.',
            ]);
        }

        $diferencia = $existenciaReal - (int) $producto->existencia;

        if ($diferencia === 0) {
            throw ValidationException::withMessages([
                'existencia_real' => 'La existencia real es igual a la actual; no hay nada que ajustar.',
            ]);
        }

        return DB::transaction(function () use ($producto, $existenciaReal, $diferencia, $motivo, $detalle, $usuario) {
            $producto->update(['existencia' => $existenciaReal]);

            return MovimientoInventario::create([
                'id_producto' => $producto->id_producto,
                'id_usuario'  => $usuario->id_usuario,
                'tipo'        => 'ajuste',
                'cantidad'    => $diferencia,                     // con signo (+ sobrante / − faltante)
                'fecha_hora'  => now(),
                'motivo'      => mb_substr($motivo . (filled($detalle) ? ": {$detalle}" : ''), 0, 120),
            ]);
        });
    }
}
