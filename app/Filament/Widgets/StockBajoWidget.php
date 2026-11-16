<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\AccionesInventario;
use App\Models\Producto;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tabla del panel de inicio con los productos que hay que resurtir (RF-21, RF-38).
 * La ven el Administrador y el Cajero/Mesero.
 */
class StockBajoWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Producto::class) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Productos con stock bajo o agotados')
            ->description('Existencia menor o igual a la mínima. El Administrador puede registrar la entrada aquí mismo.')
            ->query(fn (): Builder => Producto::query()->stockBajo()->with('categoria'))
            ->defaultSort('existencia')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Producto')
                    ->weight('bold'),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge(),
                TextColumn::make('existencia')
                    ->label('Existencia')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning')
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'Agotado' : (string) $state),
                TextColumn::make('stock_minimo')
                    ->label('Mínimo'),
            ])
            ->recordActions([
                AccionesInventario::entrada(),
            ])
            ->emptyStateHeading('Todo en orden')
            ->emptyStateDescription('Ningún producto tiene stock bajo.')
            ->paginated([5, 10]);
    }
}
