<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entradas, salidas y ajustes de existencias de cada producto.
 * En los ajustes la cantidad lleva signo: negativa = merma, positiva = sobrante.
 */
class MovimientoInventario extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'movimiento_inventario';

    /** Llave primaria. */
    protected $primaryKey = 'id_movimiento';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'id_producto',
        'id_usuario',
        'id_pedido',
        'tipo',
        'cantidad',
        'fecha_hora',
        'motivo',
    ];

    /**
     * Conversión automática de tipos de dato.
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'fecha_hora' => 'datetime',
        ];
    }

    /**
     * Producto afectado (MOVIMIENTO_INVENTARIO N : 1 PRODUCTO).
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * Usuario que registró el movimiento (MOVIMIENTO_INVENTARIO N : 1 USUARIO).
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Pedido que generó el movimiento; puede ser nulo (MOVIMIENTO_INVENTARIO N : 1 PEDIDO).
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
