<?php

namespace App\Filament\Resources\Categorias\Tables;

use App\Filament\Actions\CambiarActivoAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Tabla (listado) de categorías.
 */
class CategoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Categorías del menú')
            ->description('Sistema de Pedidos · Equipo 11')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('productos_count')
                    ->label('Productos')
                    ->counts('productos')           // cuántos productos tiene la categoría
                    ->badge()
                    ->sortable(),
                IconColumn::make('activo')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->defaultSort('nombre')
            ->filters([
                TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                CambiarActivoAction::make(),        // en lugar de "Eliminar"
            ]);
    }
}

