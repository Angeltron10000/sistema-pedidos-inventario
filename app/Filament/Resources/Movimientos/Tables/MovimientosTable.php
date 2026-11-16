<?php

namespace App\Filament\Resources\Movimientos\Tables;

use App\Filament\Resources\Pedidos\PedidoResource;
use App\Models\MovimientoInventario;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tabla de movimientos de inventario: entradas, salidas y ajustes (RF-22, RN-22).
 * Se usa en la pantalla "Movimientos de inventario" y, sin la columna de producto,
 * dentro del detalle de cada producto.
 */
class MovimientosTable
{
    public const TIPOS = [
        'entrada' => 'Entrada',
        'salida'  => 'Salida',
        'ajuste'  => 'Ajuste',
    ];

    /** Cantidad con signo: + entra al inventario, − sale. */
    public static function cantidadConSigno(MovimientoInventario $movimiento): int
    {
        return $movimiento->tipo === 'salida' ? -abs($movimiento->cantidad) : (int) $movimiento->cantidad;
    }

    public static function configure(Table $table, bool $mostrarProducto = true): Table
    {
        return $table
            ->heading($mostrarProducto ? 'Movimientos de inventario' : 'Movimientos de este producto')
            ->description('Entradas (resurtido y cancelaciones), salidas (pedidos) y ajustes (merma o conteo).')
            ->columns([
                TextColumn::make('fecha_hora')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/y H:i')
                    ->sortable(),
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->visible($mostrarProducto),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::TIPOS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'salida'  => 'danger',
                        default   => 'warning',
                    }),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->state(fn (MovimientoInventario $record): int => self::cantidadConSigno($record))
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->weight(FontWeight::Bold),
                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('pedido.folio')
                    ->label('Pedido')
                    ->placeholder('—')
                    ->url(fn (MovimientoInventario $record): ?string => $record->id_pedido
                        ? PedidoResource::getUrl('view', ['record' => $record->id_pedido])
                        : null),
                TextColumn::make('usuario.usuario')
                    ->label('Usuario'),
            ])
            ->defaultSort('fecha_hora', 'desc')
            ->filters([
                SelectFilter::make('id_producto')
                    ->label('Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload()
                    ->visible($mostrarProducto),
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(self::TIPOS),
                Filter::make('fecha')
                    ->label('Fecha')
                    ->schema([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['desde'] ?? null, fn (Builder $q, $fecha) => $q->whereDate('fecha_hora', '>=', $fecha))
                        ->when($data['hasta'] ?? null, fn (Builder $q, $fecha) => $q->whereDate('fecha_hora', '<=', $fecha))),
                SelectFilter::make('id_usuario')
                    ->label('Usuario')
                    ->relationship('usuario', 'usuario'),
            ]);
    }
}
