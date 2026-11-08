<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

/**
 * Formulario para crear y editar productos.
 * Las validaciones repiten las reglas de la base de datos (precio > 0, existencia >= 0),
 * así también se cumplen en SQLite, donde no hay restricciones CHECK.
 */
class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Datos del producto')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del producto')
                            ->placeholder('Ejemplo: Chilaquiles verdes')
                            ->required()
                            ->maxLength(80)
                            // RN-17: no puede haber dos productos activos con el mismo nombre
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('activo', true),
                            )
                            ->validationMessages([
                                'unique' => 'Ya existe un producto activo con ese nombre.',
                            ]),
                        Select::make('id_categoria')
                            ->label('Categoría')
                            ->relationship(
                                name: 'categoria',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->where('activo', true)->orderBy('nombre'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Ingredientes o presentación del platillo')
                            ->maxLength(200)
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Precio e inventario')
                    ->description('La existencia se descuenta sola con cada pedido y se regresa si el pedido se cancela.')
                    ->schema([
                        TextInput::make('precio')
                            ->label('Precio de venta')
                            ->prefix('$')
                            ->suffix('MXN')
                            ->numeric()
                            ->required()
                            ->rule('gt:0')                                      // RN-16
                            ->validationMessages([
                                'gt' => 'El precio debe ser mayor que 0.',
                            ]),
                        TextInput::make('existencia')
                            ->label(fn (string $operation): string => $operation === 'create'
                                ? 'Existencia inicial (piezas)'
                                : 'Existencia (piezas disponibles)')
                            ->integer()
                            ->required()
                            ->minValue(0)                                      // RN-06
                            ->default(0)
                            // Al editar ya no se escribe a mano: se usan los botones "Registrar entrada"
                            // o "Ajustar existencia", que guardan el movimiento con su motivo (RN-22, RN-23)
                            ->disabledOn('edit')
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Para cambiarla usa los botones "Registrar entrada" o "Ajustar existencia".'
                                : null)
                            ->validationMessages([
                                'min' => 'La existencia no puede ser negativa.',
                            ]),
                        TextInput::make('stock_minimo')
                            ->label('Existencia mínima')
                            ->helperText('Cuando la existencia llegue a este número se marca como "stock bajo".')
                            ->integer()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('activo')
                            ->label('Producto activo (se puede vender)')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }
}