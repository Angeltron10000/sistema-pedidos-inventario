<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de cada cambio de estado de un pedido (quién y cuándo).
 */
class HistorialEstado extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'historial_estado';

    /** Llave primaria. */
    protected $primaryKey = 'id_historial';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'id_pedido',
        'id_estado',
        'id_usuario',
        'fecha_hora',
    ];

    /**
     * Conversión automática de tipos de dato.
     */
    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    /**
     * Pedido que cambió de estado (HISTORIAL_ESTADO N : 1 PEDIDO).
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Estado que se asignó (HISTORIAL_ESTADO N : 1 ESTADO_PEDIDO).
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado', 'id_estado');
    }

    /**
     * Usuario que hizo el cambio (HISTORIAL_ESTADO N : 1 USUARIO).
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
