<?php

namespace App\Filament\Resources\Productos\Tables;

use App\Filament\Actions\CambiarActivoAction;
use App\Models\Producto;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Tabla (listado) de productos.
 */
class ProductosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Productos del menú')
            ->description('Sistema de Pedidos · Equipo 11')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Producto $record): ?string => Str::limit((string) $record->descripcion, 45)),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->sortable(),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('MXN', locale: 'es_MX')
                    ->sortable(),
                TextColumn::make('existencia')
                    ->label('Existencia')
                    ->badge()
                    // Rojo = agotado, amarillo = stock bajo (RN-20), verde = suficiente
                    ->color(fn (Producto $record): string => match (true) {
                        $record->existencia <= 0 => 'danger',
                        $record->existencia <= $record->stock_minimo => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (Producto $record): string => match (true) {
                        $record->existencia <= 0 => 'Agotado',
                        $record->existencia <= $record->stock_minimo => "{$record->existencia} · stock bajo",
                        default => (string) $record->existencia,
                    })
                    ->sortable(),
                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('nombre')
            ->filters([
                SelectFilter::make('id_categoria')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
                TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                Filter::make('stock_bajo')
                    ->label('Solo con stock bajo o agotados')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereColumn('existencia', '<=', 'stock_minimo')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                CambiarActivoAction::make(),        // en lugar de "Eliminar"
            ]);
    }
}
