<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Datos generales del establecimiento (un solo renglón): nombre y horario de servicio.
 */
class Configuracion extends Model
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'configuracion';

    /** Llave primaria. */
    protected $primaryKey = 'id_configuracion';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'nombre_negocio',
        'hora_apertura',
        'hora_cierre',
    ];

    /**
     * Devuelve el único renglón de configuración (si no existe, lo crea con 8:00 a 22:00).
     * Uso: Configuracion::actual()->hora_apertura
     */
    public static function actual(): self
    {
        return static::query()->first() ?? static::create([
            'nombre_negocio' => 'Sistema de Pedidos',
            'hora_apertura'  => '08:00:00',
            'hora_cierre'    => '22:00:00',
        ]);
    }

    /**
     * ¿El establecimiento está abierto en este momento? (RN-27)
     * - Si apertura y cierre son iguales, se considera abierto todo el día.
     * - Si el cierre es menor que la apertura (ej. 18:00 a 02:00), el horario pasa de medianoche.
     */
    public function estaAbierto(?Carbon $momento = null): bool
    {
        $hora = ($momento ?? now())->format('H:i');
        $apertura = substr((string) $this->hora_apertura, 0, 5);
        $cierre = substr((string) $this->hora_cierre, 0, 5);

        if ($apertura === $cierre) {
            return true;
        }

        if ($apertura < $cierre) {
            return $hora >= $apertura && $hora < $cierre;
        }

        return $hora >= $apertura || $hora < $cierre;      // horario que cruza la medianoche
    }

    /** Texto para mostrar el horario. Ejemplo: "08:00 a 22:00". */
    public function textoHorario(): string
    {
        return substr((string) $this->hora_apertura, 0, 5) . ' a ' . substr((string) $this->hora_cierre, 0, 5);
    }
}
