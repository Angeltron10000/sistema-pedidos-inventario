<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Empleados que utilizan el sistema; cada uno tiene un rol.
 * Extiende de Authenticatable para poder usarse en el inicio de sesión.
 * Implementa FilamentUser y HasName para entrar al panel de Filament.
 */
class Usuario extends Authenticatable implements FilamentUser, HasName
{
    /** Nombre de la tabla en la base de datos. */
    protected $table = 'usuario';

    /** Llave primaria. */
    protected $primaryKey = 'id_usuario';

    /** La tabla no tiene columnas created_at / updated_at. */
    public $timestamps = false;

    /** Campos que se pueden llenar con create() o fill(). */
    protected $fillable = [
        'id_rol',
        'nombre',
        'apellidos',
        'usuario',
        'contrasena_hash',
        'activo',
    ];

    /** Campos que nunca se muestran al convertir el modelo a arreglo o JSON. */
    protected $hidden = [
        'contrasena_hash',
    ];

    /**
     * Indica a Laravel en qué columna está la contraseña cifrada.
     */
    public function getAuthPassword(): string
    {
        return $this->contrasena_hash;
    }

    /**
     * Nombre de la columna de la contraseña (Laravel la usa si necesita volver a cifrarla).
     */
    public function getAuthPasswordName(): string
    {
        return 'contrasena_hash';
    }

    /**
     * Solo los usuarios activos pueden entrar al panel de Filament (RN-24).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->activo;
    }

    /**
     * Nombre que Filament muestra en la esquina superior (menú del usuario).
     */
    public function getFilamentName(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }

    /**
     * Esta tabla no usa la columna remember_token.
     */
    public function getRememberTokenName(): ?string
    {
        return null;
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
     * ¿El usuario tiene alguno de estos roles? (RN-25)
     * Uso: $usuario->tieneRol(Rol::ADMINISTRADOR, Rol::CAJERO)
     */
    public function tieneRol(int ...$roles): bool
    {
        return in_array((int) $this->id_rol, $roles, true);
    }

    public function esAdministrador(): bool
    {
        return $this->tieneRol(Rol::ADMINISTRADOR);
    }

    public function esCajero(): bool
    {
        return $this->tieneRol(Rol::CAJERO);
    }

    public function esCocina(): bool
    {
        return $this->tieneRol(Rol::COCINA);
    }

    /**
     * Cada usuario tiene un solo rol (USUARIO N : 1 ROL).
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * Pedidos que registró el usuario (USUARIO 1 : N PEDIDO).
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Cambios de estado que realizó el usuario (USUARIO 1 : N HISTORIAL_ESTADO).
     */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstado::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Movimientos de inventario que registró el usuario (USUARIO 1 : N MOVIMIENTO_INVENTARIO).
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_usuario', 'id_usuario');
    }
}
