<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de estados: Recibido, En preparación, Listo, Entregado y Cancelado.
 */
class EstadoPedido extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'estado_pedido';

    /** Llave primaria. */
    protected $primaryKey = 'id_estado';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'nombre',
        'orden',
    ];

    /**
     * Conversión automática de tipos de dato.
     */
    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    /**
     * Pedidos que se encuentran en este estado (ESTADO_PEDIDO 1 : N PEDIDO).
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_estado', 'id_estado');
    }

    /**
     * Registros del historial con este estado (ESTADO_PEDIDO 1 : N HISTORIAL_ESTADO).
     */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstado::class, 'id_estado', 'id_estado');
    }
}
