<?php

namespace App\Models;

use App\Support\Texto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * El nombre se guarda "limpio": sin espacios especiales ni caracteres invisibles
     * que trae a veces el texto copiado y pegado (ver App\Support\Texto).
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(set: fn (?string $valor): string => Texto::limpiar($valor));
    }

    /**
     * Busca otro producto ACTIVO con el mismo nombre (RN-17).
     * Compara sin importar mayúsculas, espacios especiales ni caracteres invisibles:
     * "Café americano", "CAFÉ AMERICANO" y un "Café americano" copiado y pegado son el mismo nombre.
     *
     * @param  int|null  $ignorarId  el producto que se está editando (no cuenta contra sí mismo)
     */
    public static function otroActivoConNombre(?string $nombre, ?int $ignorarId = null): ?self
    {
        $clave = Texto::clave($nombre);

        return static::query()
            ->where('activo', true)
            ->when($ignorarId, fn (Builder $q) => $q->where('id_producto', '!=', $ignorarId))
            ->get(['id_producto', 'nombre', 'activo'])
            ->first(fn (Producto $producto): bool => Texto::clave($producto->nombre) === $clave);
    }

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
