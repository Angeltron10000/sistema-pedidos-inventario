<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Página de detalle (Ver) de un cliente.
 */
class ClienteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Cliente')
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre'),
                        TextEntry::make('telefono')
                            ->label('Teléfono')
                            ->placeholder('Sin teléfono'),
                        IconEntry::make('activo')
                            ->label('Activo')
                            ->boolean(),
                        TextEntry::make('pedidos.folio')
                            ->label('Pedidos realizados')
                            ->badge()
                            ->placeholder('Sin pedidos')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
