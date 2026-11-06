<?php

namespace App\Filament\Resources\Pedidos\Tables;

use App\Filament\Actions\AccionesPedido;
use App\Models\Pedido;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tabla (listado) de pedidos.
 */
class PedidosTable
{
    /** Color de cada estado del pedido. */
    public static function colorEstado(?string $estado): string
    {
        return match ($estado) {
            'Recibido'       => 'info',
            'En preparación' => 'warning',
            'Listo'          => 'success',
            'Entregado'      => 'gray',
            'Cancelado'      => 'danger',
            default          => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Pedidos')
            ->description('Sistema de Pedidos · Equipo 11')
            ->columns([
                TextColumn::make('folio')
                    ->label('Folio')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),
                TextColumn::make('fecha_hora')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/y H:i')
                    ->sortable(),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('estado.nombre')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => self::colorEstado($state)),
                TextColumn::make('tipo_servicio')
                    ->label('Servicio')
                    ->formatStateUsing(fn (string $state): string => $state === 'llevar' ? 'Para llevar' : 'Comer aquí')
                    ->icon(fn (string $state): string => $state === 'llevar' ? 'heroicon-o-shopping-bag' : 'heroicon-o-building-storefront'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN', locale: 'es_MX')
                    ->sortable(),
                TextColumn::make('usuario.usuario')
                    ->label('Registró')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha_hora', 'desc')
            ->filters([
                SelectFilter::make('id_estado')
                    ->label('Estado')
                    ->relationship('estado', 'nombre')
                    ->multiple(),
                SelectFilter::make('tipo_servicio')
                    ->label('Servicio')
                    ->options([
                        'local'  => 'Comer aquí',
                        'llevar' => 'Para llevar',
                    ]),
                Filter::make('fecha')
                    ->label('Fecha')
                    ->schema([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['desde'] ?? null, fn (Builder $q, $fecha) => $q->whereDate('fecha_hora', '>=', $fecha))
                        ->when($data['hasta'] ?? null, fn (Builder $q, $fecha) => $q->whereDate('fecha_hora', '<=', $fecha))),
            ])
            ->recordActions([
                AccionesPedido::avanzar(),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (Pedido $record): bool => $record->sePuedeEditar()),
                    AccionesPedido::cancelar(),
                ]),
            ]);
    }
}
