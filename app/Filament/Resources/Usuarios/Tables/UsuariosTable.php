<?php

namespace App\Filament\Resources\Usuarios\Tables;

use App\Filament\Actions\CambiarActivoAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Tabla (listado) de usuarios: buscar por nombre o usuario y filtrar por rol (RF-05).
 */
class UsuariosTable
{
    /** Color de cada rol. */
    public static function colorRol(?string $rol): string
    {
        return match ($rol) {
            'Administrador' => 'danger',
            'Cajero/Mesero' => 'info',
            'Cocina'        => 'warning',
            default         => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Usuarios del sistema')
            ->description('Sistema de Pedidos · Equipo 11')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record): string => $record->getFilamentName())
                    ->searchable(['nombre', 'apellidos'])
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('usuario')
                    ->label('Usuario')
                    ->searchable()
                    ->icon('heroicon-o-user'),
                TextColumn::make('rol.nombre')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (?string $state): string => self::colorRol($state)),
                TextColumn::make('pedidos_count')
                    ->label('Pedidos registrados')
                    ->counts('pedidos')
                    ->sortable(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('nombre')
            ->filters([
                SelectFilter::make('id_rol')
                    ->label('Rol')
                    ->relationship('rol', 'nombre'),
                TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                CambiarActivoAction::make(),        // en lugar de "Eliminar" (RF-07)
            ]);
    }
}
