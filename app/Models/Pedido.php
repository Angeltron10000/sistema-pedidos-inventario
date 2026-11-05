<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Orden realizada por un cliente; tiene folio único, estado actual y total.
 */
class Pedido extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'pedido';

    /** Llave primaria. */
    protected $primaryKey = 'id_pedido';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'folio',
        'id_cliente',
        'id_usuario',
        'id_estado',
        'fecha_hora',
        'tipo_servicio',
        'total',
        'observaciones',
        'motivo_cancelacion',
    ];

    /**
     * Conversión automática de tipos de dato.
     */
    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Cliente que hizo el pedido (PEDIDO N : 1 CLIENTE).
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Empleado que registró el pedido (PEDIDO N : 1 USUARIO).
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Estado actual del pedido (PEDIDO N : 1 ESTADO_PEDIDO).
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado', 'id_estado');
    }

    /**
     * Productos incluidos en el pedido (PEDIDO 1 : N DETALLE_PEDIDO).
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Productos del pedido (relación N : M a través de DETALLE_PEDIDO).
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalle_pedido', 'id_pedido', 'id_producto')
            ->withPivot('cantidad', 'precio_unitario', 'subtotal', 'notas');
    }

    /**
     * Cambios de estado del pedido (PEDIDO 1 : N HISTORIAL_ESTADO).
     */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstado::class, 'id_pedido', 'id_pedido')->orderBy('fecha_hora');
    }

    /**
     * Salidas y reintegros generados por el pedido (PEDIDO 1 : N MOVIMIENTO_INVENTARIO, opcional).
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Estado al que pasa el pedido con el botón de avanzar (RN-08).
     * Recibido (1) → En preparación (2) → Listo (3) → Entregado (4).
     * Devuelve null si ya está Entregado o Cancelado.
     */
    public function siguienteEstado(): ?EstadoPedido
    {
        $orden = $this->estado?->orden ?? 0;

        if ($orden < 1 || $orden >= 4) {
            return null;
        }

        return EstadoPedido::where('orden', $orden + 1)->first();
    }

    /**
     * Solo se cancela un pedido que todavía no está listo (Recibido o En preparación).
     */
    public function sePuedeCancelar(): bool
    {
        return in_array($this->estado?->orden, [1, 2], true);
    }

    /**
     * Los datos generales del pedido solo se editan mientras está en "Recibido" (RN-11).
     */
    public function sePuedeEditar(): bool
    {
        return $this->estado?->orden === 1;
    }
}
