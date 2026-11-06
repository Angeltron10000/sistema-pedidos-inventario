<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulario para crear y editar clientes.
 */
class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Datos del cliente')
                    ->description('El teléfono es opcional; sirve para avisar cuando el pedido está listo.')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre completo')
                            ->placeholder('Ejemplo: María González')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->placeholder('10 dígitos')
                            ->maxLength(15)
                            ->regex('/^[0-9 ()+-]{7,15}$/')
                            ->validationMessages([
                                'regex' => 'Escribe solo números (puede llevar espacios, guiones o paréntesis).',
                            ]),
                        Toggle::make('activo')
                            ->label('Cliente activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
