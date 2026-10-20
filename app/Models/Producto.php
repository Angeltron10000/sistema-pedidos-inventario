<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Artículos que vende el establecimiento, con precio, existencia y existencia mínima.
 */
class Producto extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'producto';

    /** Llave primaria. */
    protected $primaryKey = 'id_producto';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'id_categoria',
        'nombre',
        'descripcion',
        'precio',
        'existencia',
        'stock_minimo',
        'activo',
    ];

    /**
     * Productos que se pueden agregar a un pedido nuevo: activos y con existencia (RN-19).
     * Uso: Producto::disponibles()->get();
     */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('activo', true)->where('existencia', '>', 0);
    }

    /**
     * Productos con existencia menor o igual a su mínimo (alerta de stock, RN-20).
     * Uso: Producto::stockBajo()->get();
     */
    public function scopeStockBajo(Builder $query): Builder
    {
        return $query->where('activo', true)->whereColumn('existencia', '<=', 'stock_minimo');
    }

    /**
     * Conversión automática de tipos de dato.
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'existencia' => 'integer',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /**
     * Todo producto pertenece a una categoría (PRODUCTO N : 1 CATEGORIA).
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Renglones de pedidos en los que aparece el producto (PRODUCTO 1 : N DETALLE_PEDIDO).
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'id_producto', 'id_producto');
    }

    /**
     * Pedidos que incluyen el producto (relación N : M a través de DETALLE_PEDIDO).
     */
    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'detalle_pedido', 'id_producto', 'id_pedido')
            ->withPivot('cantidad', 'precio_unitario', 'subtotal', 'notas');
    }

    /**
     * Movimientos de inventario del producto (PRODUCTO 1 : N MOVIMIENTO_INVENTARIO).
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
    }
}
