<?php

namespace App\Filament\Resources\Usuarios\Schemas;

use App\Models\Usuario;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

/**
 * Formulario para dar de alta y editar usuarios (RF-04, RF-06).
 * La contraseña se guarda cifrada en la columna contrasena_hash.
 */
class UsuarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Datos del empleado')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre(s)')
                            ->placeholder('Ejemplo: Laura')
                            ->required()
                            ->maxLength(60),
                        TextInput::make('apellidos')
                            ->label('Apellidos')
                            ->placeholder('Ejemplo: Méndez Ortiz')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('usuario')
                            ->label('Usuario (para iniciar sesión)')
                            ->placeholder('Ejemplo: lmendez')
                            ->required()
                            ->maxLength(30)
                            ->alphaDash()                       // solo letras, números, - y _
                            ->unique(ignoreRecord: true)        // no se puede repetir
                            ->validationMessages([
                                'unique'     => 'Ya existe un usuario con ese nombre de usuario.',
                                'alpha_dash' => 'Usa solo letras, números, guiones o guion bajo (sin espacios).',
                            ]),
                        Select::make('id_rol')
                            ->label('Rol')
                            ->relationship('rol', 'nombre')
                            ->required()
                            ->helperText('Administrador: todo. Cajero/Mesero: pedidos y clientes. Cocina: preparar pedidos.')
                            // El administrador no puede quitarse su propio rol
                            ->disabled(fn (?Usuario $record): bool => $record?->id_usuario === auth()->id()),
                        Toggle::make('activo')
                            ->label('Usuario activo (puede entrar al sistema)')
                            ->default(true)
                            // El administrador no puede desactivarse a sí mismo
                            ->disabled(fn (?Usuario $record): bool => $record?->id_usuario === auth()->id()),
                    ])
                    ->columns(2),

                Section::make('Contraseña')
                    ->description(fn (string $operation): string => $operation === 'create'
                        ? 'Mínimo 8 caracteres.'
                        : 'Déjala vacía si no quieres cambiarla.')
                    ->schema([
                        TextInput::make('contrasena_hash')
                            ->label(fn (string $operation): string => $operation === 'create' ? 'Contraseña' : 'Nueva contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(50)
                            ->formatStateUsing(fn (): ?string => null)          // nunca se muestra la contraseña guardada
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))   // se guarda cifrada
                            ->dehydrated(fn (?string $state): bool => filled($state)),               // vacía = no cambiar
                        TextInput::make('contrasena_confirmacion')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => filled($get('contrasena_hash')))
                            ->same('contrasena_hash')
                            ->dehydrated(false)                                  // no es una columna de la tabla
                            ->validationMessages([
                                'same' => 'Las contraseñas no coinciden.',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
