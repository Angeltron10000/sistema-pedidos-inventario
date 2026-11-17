<?php

namespace App\Models;

use App\Support\Texto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Busca otra categoría (activa o no) con el mismo nombre.
     * Compara sin importar mayúsculas, espacios especiales ni caracteres invisibles.
     *
     * @param  int|null  $ignorarId  la categoría que se está editando (no cuenta contra sí misma)
     */
    public static function otraConNombre(?string $nombre, ?int $ignorarId = null): ?self
    {
        $clave = Texto::clave($nombre);

        return static::query()
            ->when($ignorarId, fn (Builder $q) => $q->where('id_categoria', '!=', $ignorarId))
            ->get(['id_categoria', 'nombre'])
            ->first(fn (Categoria $categoria): bool => Texto::clave($categoria->nombre) === $clave);
    }

    /**
     * Conversión automática de tipos de dato.
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Una categoría agrupa muchos productos (CATEGORIA 1 : N PRODUCTO).
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }
}
