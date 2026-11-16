<?php

namespace App\Filament\Resources\Productos;

use App\Filament\Resources\Productos\Pages\CreateProducto;
use App\Filament\Resources\Productos\Pages\EditProducto;
use App\Filament\Resources\Productos\Pages\ListProductos;
use App\Filament\Resources\Productos\Pages\ViewProducto;
use App\Filament\Resources\Productos\Schemas\ProductoForm;
use App\Filament\Resources\Productos\Schemas\ProductoInfolist;
use App\Filament\Resources\Productos\Tables\ProductosTable;
use App\Filament\Resources\Productos\RelationManagers\MovimientosRelationManager;
use App\Models\Producto;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    /** Textos en español que muestra Filament (menú, títulos y botones). */
    protected static ?string $modelLabel = 'producto';

    protected static ?string $pluralModelLabel = 'productos';

    /** Grupo y orden dentro del menú lateral. */
    protected static string|UnitEnum|null $navigationGroup = 'Catálogos';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ProductoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovimientosRelationManager::class,      // movimientos del producto (RF-22)
        ];
    }

    /**
     * Número rojo junto a "Productos" en el menú: cuántos productos activos
     * tienen stock bajo o están agotados (alerta de stock, RF-21, RN-20).
     */
    public static function getNavigationBadge(): ?string
    {
        $cantidad = Producto::stockBajo()->count();

        return $cantidad > 0 ? (string) $cantidad : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Productos con stock bajo o agotados';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductos::route('/'),
            'create' => CreateProducto::route('/create'),
            'view' => ViewProducto::route('/{record}'),
            'edit' => EditProducto::route('/{record}/edit'),
        ];
    }
}
