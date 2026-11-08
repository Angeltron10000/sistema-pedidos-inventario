<?php

namespace App\Policies;

use App\Models\MovimientoInventario;
use App\Models\Usuario;

/**
 * Permisos de Movimientos de inventario (RF-22).
 * Solo el Administrador los consulta. Nadie los crea, edita ni borra desde esta
 * pantalla: los movimientos se generan solos (pedidos, entradas y ajustes) para
 * que el historial sea confiable (RN-22).
 */
class MovimientoInventarioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esAdministrador();
    }

    public function view(Usuario $usuario, MovimientoInventario $movimiento): bool
    {
        return $usuario->esAdministrador();
    }

    public function create(Usuario $usuario): bool
    {
        return false;
    }

    public function update(Usuario $usuario, MovimientoInventario $movimiento): bool
    {
        return false;
    }

    public function delete(Usuario $usuario, MovimientoInventario $movimiento): bool
    {
        return false;
    }

    public function deleteAny(Usuario $usuario): bool
    {
        return false;
    }
}
