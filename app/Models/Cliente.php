<?php

namespace App\Models;

use App\Support\Texto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
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
        'activo',
    ];

    /**
     * El nombre se guarda "limpio": sin espacios especiales ni caracteres invisibles
     * que trae a veces el texto copiado y pegado (ver App\Support\Texto).
     * A diferencia de productos y categorías, el nombre SÍ se puede repetir:
     * dos personas distintas pueden llamarse igual. Para distinguirlas está su número de cliente.
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(set: fn (?string $valor): string => Texto::limpiar($valor));
    }

    /**
     * Número de cliente que se ve en pantalla, hecho con su id_cliente.
     * Ejemplo: el cliente con id_cliente = 7 es "CLI-0007".
     */
    protected function numero(): Attribute
    {
        return Attribute::get(fn (): string => self::formatoNumero((int) $this->id_cliente));
    }

    /**
     * Texto con el que se elige al cliente en un pedido: "Ana Torres · 5512345678 · CLI-0002".
     */
    public function etiqueta(): string
    {
        return collect([$this->nombre, $this->telefono, $this->numero])->filter()->implode(' · ');
    }

    /**
     * Convierte un id en número de cliente: 7 -> "CLI-0007".
     */
    public static function formatoNumero(int $id): string
    {
        return sprintf('CLI-%04d', $id);
    }

    /**
     * Lee un número de cliente escrito por una persona y devuelve su id.
     * Acepta "CLI-0007", "cli-7", "cli7" o "7". Devuelve null si no es un número de cliente.
     */
    public static function idDesdeNumero(?string $texto): ?int
    {
        if (preg_match('/^\s*(?:cli\s*-?\s*)?0*(\d{1,9})\s*$/i', (string) $texto, $partes)) {
            return (int) $partes[1];
        }

        return null;
    }

    /**
     * Otros clientes que se llaman igual (sin importar mayúsculas ni espacios especiales).
     * Solo sirve para AVISAR: el nombre sí se puede repetir.
     *
     * @param  int|null  $ignorarId  el cliente que se está editando
     */
    public static function conMismoNombre(?string $nombre, ?int $ignorarId = null): Collection
    {
        $clave = Texto::clave($nombre);

        if ($clave === '') {
            return new Collection;
        }

        return static::query()
            ->when($ignorarId, fn (Builder $q) => $q->where('id_cliente', '!=', $ignorarId))
            ->orderBy('id_cliente')
            ->get()
            ->filter(fn (Cliente $cliente): bool => Texto::clave($cliente->nombre) === $clave)
            ->values();
    }

    /**
     * Otros clientes con el mismo teléfono (se comparan solo los números:
     * "55 1234-5678" y "5512345678" son el mismo). Solo sirve para AVISAR:
     * varias personas de una misma casa pueden dar el mismo teléfono.
     *
     * @param  int|null  $ignorarId  el cliente que se está editando
     */
    public static function conMismoTelefono(?string $telefono, ?int $ignorarId = null): Collection
    {
        $digitos = Texto::soloDigitos($telefono);

        if (strlen($digitos) < 7) {
            return new Collection;
        }

        return static::query()
            ->whereNotNull('telefono')
            ->when($ignorarId, fn (Builder $q) => $q->where('id_cliente', '!=', $ignorarId))
            ->orderBy('id_cliente')
            ->get()
            ->filter(fn (Cliente $cliente): bool => Texto::soloDigitos($cliente->telefono) === $digitos)
            ->values();
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
     * Un cliente puede realizar muchos pedidos (CLIENTE 1 : N PEDIDO).
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_cliente', 'id_cliente');
    }
}
