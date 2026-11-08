<?php

namespace App\Filament\Resources\Usuarios;

use App\Filament\Resources\Usuarios\Pages\CreateUsuario;
use App\Filament\Resources\Usuarios\Pages\EditUsuario;
use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\Resources\Usuarios\Pages\ViewUsuario;
use App\Filament\Resources\Usuarios\Schemas\UsuarioForm;
use App\Filament\Resources\Usuarios\Schemas\UsuarioInfolist;
use App\Filament\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\Usuario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Recurso de Usuarios (empleados que usan el sistema), RF-04 a RF-07.
 * Solo lo ve el Administrador (ver App\Policies\UsuarioPolicy).
 */
class UsuarioResource extends Resource
{
    protected static ?string $model = Usuario::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    /** Textos en español que muestra Filament (menú, títulos y botones). */
    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    /** Grupo y orden dentro del menú lateral. */
    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'usuario';

    public static function form(Schema $schema): Schema
    {
        return UsuarioForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UsuarioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuario::route('/create'),
            'view' => ViewUsuario::route('/{record}'),
            'edit' => EditUsuario::route('/{record}/edit'),
        ];
    }
}
