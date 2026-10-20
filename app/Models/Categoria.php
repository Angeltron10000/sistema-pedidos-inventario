<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Agrupación de productos (Desayunos, Bebidas, Postres, etc.).
 */
class Categoria extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'categoria';

    /** Llave primaria. */
    protected $primaryKey = 'id_categoria';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'nombre',
    ];

    /**
     * Una categoría agrupa muchos productos (CATEGORIA 1 : N PRODUCTO).
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }
}
