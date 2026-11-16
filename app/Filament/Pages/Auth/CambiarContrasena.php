<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

/**
 * Página "Cambiar contraseña" (RF-08).
 *
 * Cualquier usuario la abre desde el menú de su nombre (esquina superior derecha).
 * Se basa en la página de perfil de Filament, pero solo deja cambiar la contraseña,
 * pidiendo la contraseña actual para confirmar que es la misma persona.
 * (El administrador también puede cambiar la contraseña de cualquier usuario
 * desde Usuarios → Editar.)
 */
class CambiarContrasena extends EditProfile
{
    protected static ?string $slug = 'cambiar-contrasena';

    public static function getLabel(): string
    {
        return 'Cambiar contraseña';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'cambiar-contrasena';
    }

    /** Solo tres campos: nueva contraseña, confirmación y contraseña actual. */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getPasswordFormComponent()
                    ->label('Nueva contraseña')
                    ->required(),
                $this->getPasswordConfirmationFormComponent()
                    ->label('Confirmar nueva contraseña'),
                $this->getCurrentPasswordFormComponent()
                    ->label('Contraseña actual'),
            ]);
    }

    /**
     * La tabla "usuario" guarda la contraseña en la columna contrasena_hash
     * (no en "password", como espera Filament).
     */
    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        $record->update(['contrasena_hash' => $data['password']]);

        // Se actualiza la sesión para que el usuario no tenga que volver a iniciar sesión
        if (request()->hasSession()) {
            request()->session()->put('password_hash_' . Filament::getAuthGuard(), $data['password']);
        }

        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Contraseña actualizada';
    }
}
