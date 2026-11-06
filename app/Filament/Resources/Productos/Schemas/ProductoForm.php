<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id_categoria')
                    ->required()
                    ->numeric(),
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('descripcion'),
                TextInput::make('precio')
                    ->required()
                    ->numeric(),
                TextInput::make('existencia')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('stock_minimo')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
