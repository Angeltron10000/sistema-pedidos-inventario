<?php

namespace App\Filament\Resources\Pedidos\Schemas;

use App\Filament\Resources\Clientes\Schemas\ClienteForm;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Producto;
use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

/**
 * Formulario para registrar y modificar pedidos.
 *
 * Al crear: se elige el cliente, el tipo de servicio y los productos con sus cantidades.
 * Al modificar (solo en "Recibido", RN-11): se pueden cambiar los datos y agregar,
 * quitar o cambiar la cantidad de productos; el inventario se ajusta solo.
 * El folio, los precios, el total y las existencias los calcula el sistema
 * (ver App\Services\PedidoService).
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
                            // Muestra nombre, teléfono y número de cliente: "Ana Torres · 5512345678 · CLI-0002".
                            // Así se distinguen dos clientes que se llaman igual (RF-24)
                            ->getOptionLabelFromRecordUsing(fn (Cliente $cliente): string => $cliente->etiqueta())
                            // Busca por nombre, teléfono o número de cliente (CLI-0002 o solo 2)
                            ->searchable()
                            ->getSearchResultsUsing(fn (?string $search): array => self::buscarClientes($search))
                            ->preload()
                            ->default(1)                // 1 = "Público en general / Mostrador" (RN-26)
                            ->helperText('Escribe el nombre, el teléfono o el número de cliente (CLI-0002). Si es nuevo, usa el botón + para registrarlo.')
                            // Botón + para registrar un cliente nuevo sin salir del pedido (RF-23)
                            // (mismos campos y avisos de nombre o teléfono repetido que en Clientes)
                            ->createOptionForm([
                                ClienteForm::campoNombre(),
                                ClienteForm::campoTelefono(),
                            ])
                            ->createOptionModalHeading('Registrar cliente nuevo')
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
                    ->description(fn (string $operation): string => $operation === 'create'
                        ? 'Solo aparecen los productos activos y con existencia. El precio se toma del menú. Horario de servicio: ' . Configuracion::actual()->textoHorario() . '.'
                        : 'Puedes agregar, quitar o cambiar cantidades. Si quitas o reduces un producto, sus piezas regresan al inventario. Los productos que ya estaban conservan su precio.')
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
                                    ->options(fn ($livewire) => self::opcionesDeProductos(self::pedidoEnEdicion($livewire)))
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
                                    ])
                                    // RF-26: no se puede pedir más de lo que hay en existencia
                                    ->rules([
                                        fn (Get $get, $livewire): Closure => function (string $attribute, $value, Closure $fail) use ($get, $livewire): void {
                                            $producto = Producto::find($get('id_producto'));
                                            if (! $producto) {
                                                return;
                                            }
                                            $maximo = $producto->existencia
                                                + self::cantidadYaEnPedido(self::pedidoEnEdicion($livewire), $producto->id_producto);
                                            if ((int) $value > $maximo) {
                                                $fail("Solo hay {$maximo} de {$producto->nombre}.");
                                            }
                                        },
                                    ]),
                                TextInput::make('notas')
                                    ->label('Notas')
                                    ->placeholder('Sin cebolla')
                                    ->maxLength(100),
                            ])
                            ->minItems(1)                                        // RN-02
                            ->defaultItems(1)
                            ->addActionLabel('Agregar otro producto')
                            ->reorderable(false),
                        Text::make(fn (Get $get, $livewire): string => 'Total estimado: $'
                            . number_format(self::calcularTotal($get('productos') ?? [], self::pedidoEnEdicion($livewire)), 2) . ' MXN')
                            ->weight(FontWeight::Bold),
                    ]),
            ]);
    }

    /** Si el formulario está en la página de Editar, devuelve el pedido; si no, null. */
    public static function pedidoEnEdicion($livewire): ?Pedido
    {
        return $livewire instanceof EditRecord ? $livewire->getRecord() : null;
    }

    /**
     * Búsqueda de clientes activos para el pedido: por nombre, teléfono o número de cliente.
     * "ana" encuentra a Ana Torres, "5512" busca por teléfono y "CLI-0002" (o "2") por número.
     *
     * @return array<int, string>  [id_cliente => "Ana Torres · 5512345678 · CLI-0002"]
     */
    public static function buscarClientes(?string $busqueda): array
    {
        $busqueda = trim((string) $busqueda);
        $id = Cliente::idDesdeNumero($busqueda);

        return Cliente::query()
            ->where('activo', true)
            ->where(function (Builder $q) use ($busqueda, $id): void {
                $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('telefono', 'like', "%{$busqueda}%");

                if ($id) {
                    $q->orWhere('id_cliente', $id);
                }
            })
            ->orderBy('nombre')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Cliente $cliente): array => [$cliente->id_cliente => $cliente->etiqueta()])
            ->all();
    }

    /**
     * Productos que se pueden elegir: activos y con existencia (RN-19).
     * Al modificar un pedido también aparecen los que ya tenía, aunque se hayan agotado.
     */
    public static function opcionesDeProductos(?Pedido $pedido): array
    {
        $idsDelPedido = $pedido?->detalles()->pluck('id_producto')->all() ?? [];

        return Producto::query()
            ->where(fn (Builder $q) => $q
                ->where(fn (Builder $d) => $d->where('activo', true)->where('existencia', '>', 0))
                ->orWhereIn('id_producto', $idsDelPedido))
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn (Producto $p) => [
                $p->id_producto => "{$p->nombre} — $" . number_format((float) $p->precio, 2) . " (quedan {$p->existencia})",
            ])
            ->all();
    }

    /** Piezas de un producto que el pedido ya tiene apartadas (0 si es un pedido nuevo). */
    public static function cantidadYaEnPedido(?Pedido $pedido, int $idProducto): int
    {
        return (int) ($pedido?->detalles()->where('id_producto', $idProducto)->sum('cantidad') ?? 0);
    }

    /**
     * Suma cantidad × precio de cada renglón (solo para mostrar el total mientras se captura).
     * En un pedido existente, los productos que ya tenía usan su precio congelado (RN-13).
     */
    public static function calcularTotal(array $renglones, ?Pedido $pedido = null): float
    {
        $ids = collect($renglones)->pluck('id_producto')->filter()->unique();
        $precios = Producto::whereIn('id_producto', $ids)->pluck('precio', 'id_producto')->all();

        if ($pedido) {
            $precios = array_replace($precios, $pedido->detalles()->pluck('precio_unitario', 'id_producto')->all());
        }

        return collect($renglones)->sum(
            fn ($r) => ((int) ($r['cantidad'] ?? 0)) * (float) ($precios[$r['id_producto'] ?? 0] ?? 0)
        );
    }
}
