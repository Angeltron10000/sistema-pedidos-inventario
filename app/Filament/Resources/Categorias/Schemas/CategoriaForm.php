<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulario para crear y editar categorías.
 */
class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Datos de la categoría')
                    ->description('Las categorías agrupan los productos del menú (Desayunos, Bebidas, Postres, etc.).')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre de la categoría')
                            ->placeholder('Ejemplo: Desayunos')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Ya existe una categoría con ese nombre.',
                            ]),
                        Toggle::make('activo')
                            ->label('Categoría activa')
                            ->helperText('Si se desactiva, sus productos ya no se pueden elegir al registrar productos nuevos.')
                            ->default(true),
                    ])
                    ->columns(1),
            ]);
    }
}