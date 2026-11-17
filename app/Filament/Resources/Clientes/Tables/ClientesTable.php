<?php

namespace App\Filament\Resources\Clientes\Tables;

use App\Filament\Actions\CambiarActivoAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tabla (listado) de clientes.
 */
class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Clientes')
            ->description('Sistema de Pedidos · Equipo 11')
            ->columns([
                // Número de cliente (CLI-0007): distingue a dos clientes que se llaman igual
                TextColumn::make('id_cliente')
                    ->label('Número')
                    ->formatStateUsing(fn ($state): string => Cliente::formatoNumero((int) $state))
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $id = Cliente::idDesdeNumero($search);

                        return $id ? $query->where('id_cliente', $id) : $query->whereRaw('1 = 0');
                    })
                    ->color('gray'),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->placeholder('Sin teléfono')
                    ->icon('heroicon-o-phone'),
                TextColumn::make('pedidos_count')
                    ->label('Pedidos')
                    ->counts('pedidos')             // cuántos pedidos ha hecho
                    ->badge()
                    ->sortable(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('nombre')
            ->filters([
                TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                CambiarActivoAction::make(),        // en lugar de "Eliminar"
            ]);
    }
}
