<?php

namespace App\Filament\Resources\Pedidos\Schemas;

use App\Filament\Resources\Pedidos\Tables\PedidosTable;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

/**
 * Página de detalle (Ver) de un pedido: datos generales, productos e historial de estados.
 */
class PedidoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Datos del pedido')
                    ->schema([
                        TextEntry::make('folio')
                            ->label('Folio')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('estado.nombre')
                            ->label('Estado actual')
                            ->badge()
                            ->color(fn (?string $state): string => PedidosTable::colorEstado($state)),
                        TextEntry::make('fecha_hora')
                            ->label('Fecha y hora')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('total')
                            ->label('Total')
                            ->money('MXN', locale: 'es_MX')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('cliente.nombre')
                            ->label('Cliente'),
                        TextEntry::make('tipo_servicio')
                            ->label('Servicio')
                            ->formatStateUsing(fn (string $state): string => $state === 'llevar' ? 'Para llevar' : 'Comer aquí'),
                        TextEntry::make('usuario.nombre')
                            ->label('Registró')
                            ->formatStateUsing(fn ($record): string => $record->usuario->getFilamentName()),
                        TextEntry::make('espera')
                            ->label('Tiempo de espera')
                            ->state(fn ($record): string => $record->tiempoDeEspera())          // RF-37
                            ->icon('heroicon-o-clock'),
                        TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Sin observaciones'),
                        TextEntry::make('motivo_cancelacion')
                            ->label('Motivo de la cancelación')
                            ->color('danger')
                            ->visible(fn ($record): bool => filled($record->motivo_cancelacion))
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Section::make('Productos')
                    ->schema([
                        RepeatableEntry::make('detalles')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Producto'),
                                TableColumn::make('Cantidad'),
                                TableColumn::make('Precio unitario'),
                                TableColumn::make('Subtotal'),
                                TableColumn::make('Notas'),
                            ])
                            ->schema([
                                TextEntry::make('producto.nombre'),
                                TextEntry::make('cantidad'),
                                TextEntry::make('precio_unitario')->money('MXN', locale: 'es_MX'),
                                TextEntry::make('subtotal')->money('MXN', locale: 'es_MX'),
                                TextEntry::make('notas')->placeholder('—'),
                            ]),
                    ]),

                Section::make('Historial de estados')
                    ->description('Quién cambió el estado del pedido y cuándo.')
                    ->schema([
                        RepeatableEntry::make('historial')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Estado'),
                                TableColumn::make('Fecha y hora'),
                                TableColumn::make('Usuario'),
                            ])
                            ->schema([
                                TextEntry::make('estado.nombre')
                                    ->badge()
                                    ->color(fn (?string $state): string => PedidosTable::colorEstado($state)),
                                TextEntry::make('fecha_hora')->dateTime('d/m/Y H:i'),
                                TextEntry::make('usuario.usuario'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
