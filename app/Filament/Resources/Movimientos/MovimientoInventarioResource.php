<?php

namespace App\Filament\Resources\Movimientos;

use App\Filament\Resources\Movimientos\Pages\ListMovimientos;
use App\Filament\Resources\Movimientos\Tables\MovimientosTable;
use App\Models\MovimientoInventario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Consulta de movimientos de inventario (RF-22). Solo lectura y solo para el Administrador:
 * los movimientos los genera el sistema al registrar pedidos, entradas y ajustes.
 */
class MovimientoInventarioResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;

    protected static ?string $slug = 'movimientos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $modelLabel = 'movimiento de inventario';

    protected static ?string $pluralModelLabel = 'movimientos de inventario';

    protected static ?string $navigationLabel = 'Movimientos de inventario';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 1;

    public static function getBreadcrumb(): string
    {
        return 'Movimientos de inventario';
    }

    public static function table(Table $table): Table
    {
        return MovimientosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientos::route('/'),
        ];
    }
}
