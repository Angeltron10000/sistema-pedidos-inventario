<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Orden realizada por un cliente; tiene folio único, código de consulta, estado actual y total.
 */
class Pedido extends Model
{
    /** Letras y números para el código de consulta, sin los que se confunden (0/O, 1/I/L). */
    public const CARACTERES_CODIGO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /** Nombre de la tabla en la base de datos. */
    protected $table = 'pedido';

    /** Llave primaria. */
    protected $primaryKey = 'id_pedido';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'folio',
        'codigo_consulta',
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
     * Cada pedido nuevo recibe su código de consulta al guardarse por primera vez.
     */
    protected static function booted(): void
    {
        static::creating(function (Pedido $pedido): void {
            if (blank($pedido->codigo_consulta)) {
                $pedido->codigo_consulta = self::generarCodigoConsulta();
            }
        });
    }

    /**
     * Código corto al azar, por ejemplo "K7P2". Se imprime en el comprobante y,
     * junto con el folio, permite al cliente consultar su pedido.
     */
    public static function generarCodigoConsulta(): string
    {
        $codigo = '';
        for ($i = 0; $i < 4; $i++) {
            $codigo .= self::CARACTERES_CODIGO[random_int(0, strlen(self::CARACTERES_CODIGO) - 1)];
        }

        return $codigo;
    }

    /**
     * Busca el pedido que corresponde a un folio y un código escritos por el cliente.
     * Acepta el folio como "PED-0021", "ped-21", "ped21" o "21", y el código en mayúsculas o minúsculas.
     * Si el folio o el código no coinciden, devuelve null (no se dice cuál de los dos está mal).
     */
    public static function buscarParaConsulta(?string $folio, ?string $codigo): ?self
    {
        $numero = preg_match('/^\s*(?:ped\s*-?\s*)?0*(\d{1,9})\s*$/i', (string) $folio, $partes) ? (int) $partes[1] : null;
        $codigo = strtoupper(preg_replace('/\s+/', '', (string) $codigo) ?? '');

        if (! $numero || strlen($codigo) !== 4) {
            return null;
        }

        $pedido = static::where('folio', sprintf('PED-%04d', $numero))->first();

        return $pedido && hash_equals((string) $pedido->codigo_consulta, $codigo) ? $pedido : null;
    }

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
     * El cliente solo puede cancelar su pedido desde la consulta mientras está en "Recibido",
     * es decir, antes de que la cocina empiece a prepararlo.
     */
    public function sePuedeCancelarPorCliente(): bool
    {
        return $this->estado?->orden === 1;
    }

    /**
     * El pedido (datos y productos) solo se modifica mientras está en "Recibido" (RN-10, RN-11).
     */
    public function sePuedeEditar(): bool
    {
        return $this->estado?->orden === 1;
    }

    /**
     * ¿Este usuario puede pasar el pedido al siguiente estado? (RN-08 y RN-25)
     * - Recibido → En preparación y En preparación → Listo: Cocina o Administrador.
     * - Listo → Entregado: Cajero/Mesero o Administrador.
     */
    public function puedeAvanzarPor(?Usuario $usuario): bool
    {
        if (! $usuario || ! $this->siguienteEstado()) {
            return false;
        }

        return match ($this->estado?->orden) {
            1, 2    => $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::COCINA),
            3       => $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO),
            default => false,
        };
    }

    /**
     * ¿Este usuario puede cancelar el pedido? Cajero/Mesero o Administrador,
     * y solo en "Recibido" o "En preparación" (RN-09, RN-25).
     */
    public function puedeCancelarPor(?Usuario $usuario): bool
    {
        return $usuario !== null
            && $this->sePuedeCancelar()
            && $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO);
    }

    /**
     * ¿El pedido sigue abierto? (Recibido, En preparación o Listo)
     */
    public function estaActivo(): bool
    {
        return in_array($this->estado?->orden, [1, 2, 3], true);
    }

    /**
     * Tiempo de espera desde que se recibió el pedido (RF-37).
     * Mientras está activo se cuenta hasta este momento; si ya se entregó o canceló,
     * hasta su último cambio de estado. Ejemplo: "25 min" o "1 h 10 min".
     */
    public function tiempoDeEspera(): string
    {
        $fin = $this->estaActivo()
            ? now()
            : ($this->historial->last()?->fecha_hora ?? now());

        $minutos = max(0, (int) $this->fecha_hora->diffInMinutes($fin));

        if ($minutos < 60) {
            return "{$minutos} min";
        }

        return intdiv($minutos, 60) . ' h ' . ($minutos % 60) . ' min';
    }

    /**
     * Minutos que lleva esperando un pedido activo (para marcar en rojo los que tardan).
     */
    public function minutosDeEspera(): int
    {
        return max(0, (int) $this->fecha_hora->diffInMinutes(now()));
    }
}
