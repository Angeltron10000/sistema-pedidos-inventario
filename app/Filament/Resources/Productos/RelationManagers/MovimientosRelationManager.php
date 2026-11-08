<?php

namespace App\Filament\Resources\Productos\RelationManagers;

use App\Filament\Resources\Movimientos\Tables\MovimientosTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Tabla de movimientos que aparece abajo del detalle (Ver) de cada producto (RF-22).
 * Solo la ve el Administrador.
 */
class MovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimientos';

    protected static ?string $title = 'Movimientos de inventario';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->esAdministrador() ?? false;
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return MovimientosTable::configure($table, mostrarProducto: false);
    }
}
