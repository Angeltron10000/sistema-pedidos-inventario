<?php

namespace App\Filament\Resources\Pedidos\Schemas;

use App\Models\Producto;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Formulario para registrar y editar pedidos.
 *
 * Al crear: se elige el cliente, el tipo de servicio y los productos con sus cantidades.
 * El folio, los precios, el total y el descuento de existencias los calcula el sistema
 * (ver App\Services\PedidoService).
 * Al editar: solo se cambian los datos generales; los productos ya no se modifican.
 */
class PedidoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Datos del pedido')
                    ->schema([
                        Select::make('id_cliente')
                            ->label('Cliente')
                            ->relationship(
                                name: 'cliente',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->where('activo', true)->orderBy('id_cliente'),
                            )
                            ->searchable()
                            ->preload()
                            ->default(1)                // 1 = "Público en general / Mostrador"
                            ->required(),
                        ToggleButtons::make('tipo_servicio')
                            ->label('Tipo de servicio')
                            ->options([
                                'local'  => 'Comer aquí',
                                'llevar' => 'Para llevar',
                            ])
                            ->icons([
                                'local'  => 'heroicon-o-building-storefront',
                                'llevar' => 'heroicon-o-shopping-bag',
                            ])
                            ->inline()
                            ->default('local')
                            ->required(),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Ejemplo: mesa 4, sin picante')
                            ->maxLength(200)
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Productos')
                    ->description('Solo aparecen los productos activos y con existencia. El precio se toma del menú.')
                    ->visibleOn('create')
                    ->schema([
                        Repeater::make('productos')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Producto')->markAsRequired(),
                                TableColumn::make('Cantidad')->markAsRequired(),
                                TableColumn::make('Notas'),
                            ])
                            ->schema([
                                Select::make('id_producto')
                                    ->label('Producto')
                                    ->options(fn () => Producto::disponibles()      // RN-19
                                        ->orderBy('nombre')
                                        ->get()
                                        ->mapWithKeys(fn (Producto $p) => [
                                            $p->id_producto => "{$p->nombre} — $" . number_format((float) $p->precio, 2) . " (quedan {$p->existencia})",
                                        ]))
                                    ->searchable()
                                    ->required()
                                    ->live(),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->integer()
                                    ->minValue(1)                                // RN-03
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->validationMessages([
                                        'min' => 'La cantidad debe ser al menos 1.',
                                    ]),
                                TextInput::make('notas')
                                    ->label('Notas')
                                    ->placeholder('Sin cebolla')
                                    ->maxLength(100),
                            ])
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar otro producto')
                            ->reorderable(false),
                        Text::make(fn (Get $get): string => 'Total estimado: $' . number_format(self::calcularTotal($get('productos') ?? []), 2) . ' MXN')
                            ->weight(FontWeight::Bold),
                    ]),
            ]);
    }

    /**
     * Suma cantidad × precio de cada renglón (solo para mostrar el total mientras se captura).
     */
    public static function calcularTotal(array $renglones): float
    {
        $ids = collect($renglones)->pluck('id_producto')->filter()->unique();
        $precios = Producto::whereIn('id_producto', $ids)->pluck('precio', 'id_producto');

        return collect($renglones)->sum(
            fn ($r) => ((int) ($r['cantidad'] ?? 0)) * (float) ($precios[$r['id_producto'] ?? 0] ?? 0)
        );
    }
}
