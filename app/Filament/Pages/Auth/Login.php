<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as LoginBase;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

/**
 * Pantalla de inicio de sesión personalizada.
 *
 * Filament, por defecto, pide correo electrónico y usa la tabla "users".
 * Nuestro sistema usa la tabla "usuario" del diagrama E-R, donde cada empleado
 * entra con su nombre de usuario (por ejemplo: admin, jperez) y su contraseña.
 */
class Login extends LoginBase
{
    /**
     * Campos del formulario: usuario y contraseña.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUsuarioFormComponent(),
                $this->getPasswordFormComponent(),
            ]);
    }

    /**
     * Campo "Usuario" en lugar del campo "Correo electrónico".
     */
    protected function getUsuarioFormComponent(): Component
    {
        return TextInput::make('usuario')
            ->label('Usuario')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    /**
     * Datos con los que se busca al empleado en la tabla "usuario".
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'usuario' => $data['usuario'],
            'password' => $data['password'],
        ];
    }

    /**
     * Mensaje cuando el usuario o la contraseña son incorrectos,
     * o cuando el usuario está inactivo (RN-24).
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.usuario' => 'El usuario o la contraseña son incorrectos, o el usuario está inactivo.',
        ]);
    }
}
