<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Página de detalle (Ver) de una categoría.
 */
class CategoriaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Categoría')
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre'),
                        IconEntry::make('activo')
                            ->label('Activa')
                            ->boolean(),
                        TextEntry::make('productos.nombre')
                            ->label('Productos de esta categoría')
                            ->badge()
                            ->placeholder('Sin productos')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
