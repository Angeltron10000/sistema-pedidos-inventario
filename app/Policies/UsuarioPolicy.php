<?php

namespace App\Policies;

use App\Models\Usuario;

/**
 * Permisos de Usuarios (RF-04 a RF-07, RN-25).
 * Solo el Administrador da de alta, consulta, modifica y desactiva usuarios.
 */
class UsuarioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->esAdministrador();
    }

    public function view(Usuario $usuario, Usuario $registro): bool
    {
        return $usuario->esAdministrador();
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->esAdministrador();
    }

    public function update(Usuario $usuario, Usuario $registro): bool
    {
        return $usuario->esAdministrador();
    }

    /**
     * Desactivar / Activar. El administrador no puede desactivarse a sí mismo
     * (si no, nadie podría volver a entrar como administrador).
     */
    public function desactivar(Usuario $usuario, Usuario $registro): bool
    {
        return $usuario->esAdministrador() && $usuario->id_usuario !== $registro->id_usuario;
    }

    public function delete(Usuario $usuario, Usuario $registro): bool
    {
        return false;
    }

    public function deleteAny(Usuario $usuario): bool
    {
        return false;
    }
}
