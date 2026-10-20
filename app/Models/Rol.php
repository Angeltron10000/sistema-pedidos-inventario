<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tipos de usuario del sistema (Administrador, Cajero/Mesero, Cocina).
 */
class Rol extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'rol';

    /** Llave primaria. */
    protected $primaryKey = 'id_rol';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Un rol se asigna a muchos usuarios (ROL 1 : N USUARIO).
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }
}
