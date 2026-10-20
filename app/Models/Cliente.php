<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Persona que realiza el pedido (registro opcional).
 */
class Cliente extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'cliente';

    /** Llave primaria. */
    protected $primaryKey = 'id_cliente';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'nombre',
        'telefono',
    ];

    /**
     * Un cliente puede realizar muchos pedidos (CLIENTE 1 : N PEDIDO).
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_cliente', 'id_cliente');
    }
}
