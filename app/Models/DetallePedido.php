<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Productos incluidos en un pedido, con cantidad, precio unitario y subtotal.
 */
class DetallePedido extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'detalle_pedido';

    /** Llave primaria. */
    protected $primaryKey = 'id_detalle';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'id_pedido',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'notas',
    ];

    /**
     * Conversión automática de tipos de dato.
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Pedido al que pertenece el renglón (DETALLE_PEDIDO N : 1 PEDIDO).
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Producto del renglón (DETALLE_PEDIDO N : 1 PRODUCTO).
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
