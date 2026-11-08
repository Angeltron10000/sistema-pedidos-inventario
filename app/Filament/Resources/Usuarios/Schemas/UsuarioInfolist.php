<?php

namespace App\Filament\Resources\Usuarios\Schemas;

use App\Filament\Resources\Usuarios\Tables\UsuariosTable;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

/**
 * Página de detalle (Ver) de un usuario.
 */
class UsuarioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Datos del empleado')
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre completo')
                            ->formatStateUsing(fn ($record): string => $record->getFilamentName())
                            ->weight(FontWeight::Bold),
                        TextEntry::make('usuario')
                            ->label('Usuario'),
                        TextEntry::make('rol.nombre')
                            ->label('Rol')
                            ->badge()
                            ->color(fn (?string $state): string => UsuariosTable::colorRol($state)),
                        IconEntry::make('activo')
                            ->label('Activo')
                            ->boolean(),
                        TextEntry::make('pedidos_count')
                            ->label('Pedidos registrados')
                            ->state(fn ($record): int => $record->pedidos()->count()),
                        TextEntry::make('cambios_count')
                            ->label('Cambios de estado realizados')
                            ->state(fn ($record): int => $record->historialEstados()->count()),
                    ])
                    ->columns(3),
            ]);
    }
}
