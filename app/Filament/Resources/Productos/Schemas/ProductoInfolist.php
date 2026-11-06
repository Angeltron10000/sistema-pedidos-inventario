<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Página de detalle (Ver) de un producto.
 */
class ProductoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Producto')
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre'),
                        TextEntry::make('categoria.nombre')
                            ->label('Categoría')
                            ->badge(),
                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Precio e inventario')
                    ->schema([
                        TextEntry::make('precio')
                            ->label('Precio')
                            ->money('MXN', locale: 'es_MX'),
                        TextEntry::make('existencia')
                            ->label('Existencia'),
                        TextEntry::make('stock_minimo')
                            ->label('Existencia mínima'),
                        IconEntry::make('activo')
                            ->label('Activo')
                            ->boolean(),
                    ])
                    ->columns(4),
            ]);
    }
}
