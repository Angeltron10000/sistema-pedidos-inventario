<?php

namespace App\Filament\Actions;

use App\Models\Producto;
use App\Services\InventarioService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Botones de inventario que aparecen en Productos (solo para el Administrador):
 * - "Registrar entrada": cuando llega mercancía (RF-16).
 * - "Ajustar existencia": merma o conteo físico, con motivo (RF-17, RN-23).
 */
class AccionesInventario
{
    public static function entrada(): Action
    {
        return Action::make('registrarEntrada')
            ->label('Registrar entrada')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->visible(fn (Producto $record): bool => auth()->user()?->can('registrarEntrada', $record) ?? false)
            ->modalHeading(fn (Producto $record): string => "Entrada de inventario: {$record->nombre}")
            ->modalDescription(fn (Producto $record): string => "Existencia actual: {$record->existencia} piezas.")
            ->schema([
                TextInput::make('cantidad')
                    ->label('Piezas que llegaron')
                    ->integer()
                    ->minValue(1)
                    ->required()
                    ->validationMessages([
                        'min' => 'La cantidad debe ser al menos 1.',
                    ]),
                TextInput::make('motivo')
                    ->label('Motivo')
                    ->default('Resurtido')
                    ->maxLength(120),
            ])
            ->modalSubmitActionLabel('Registrar entrada')
            ->action(function (Producto $record, array $data): void {
                app(InventarioService::class)->registrarEntrada(
                    $record, (int) $data['cantidad'], $data['motivo'] ?? null, auth()->user()
                );
                $record->refresh();

                Notification::make()
                    ->title("Entrada registrada: +{$data['cantidad']} {$record->nombre}")
                    ->body("Nueva existencia: {$record->existencia} piezas.")
                    ->success()
                    ->send();
            });
    }

    public static function ajuste(): Action
    {
        return Action::make('ajustarExistencia')
            ->label('Ajustar existencia')
            ->icon('heroicon-o-adjustments-horizontal')
            ->color('warning')
            ->visible(fn (Producto $record): bool => auth()->user()?->can('ajustar', $record) ?? false)
            ->modalHeading(fn (Producto $record): string => "Ajuste de inventario: {$record->nombre}")
            ->modalDescription(fn (Producto $record): string => "Existencia en el sistema: {$record->existencia} piezas. Escribe cuántas hay realmente.")
            ->schema(fn (Producto $record): array => [
                TextInput::make('existencia_real')
                    ->label('Existencia real (piezas que hay)')
                    ->integer()
                    ->minValue(0)                                    // RN-06
                    ->required()
                    ->live(onBlur: true)
                    ->validationMessages([
                        'min' => 'La existencia no puede ser negativa.',
                    ]),
                Text::make(function (Get $get) use ($record): string {
                    if (! is_numeric($get('existencia_real'))) {
                        return 'Diferencia: —';
                    }
                    $diferencia = (int) $get('existencia_real') - (int) $record->existencia;

                    return 'Diferencia: ' . ($diferencia > 0 ? "+{$diferencia}" : $diferencia) . ' piezas';
                }),
                Select::make('motivo')
                    ->label('Motivo')
                    ->options(InventarioService::MOTIVOS_AJUSTE)
                    ->required(),                                   // RN-23: el ajuste requiere motivo
                TextInput::make('detalle')
                    ->label('Detalle (opcional)')
                    ->placeholder('Ejemplo: se cayeron 2 botellas')
                    ->maxLength(80),
            ])
            ->modalSubmitActionLabel('Guardar ajuste')
            ->action(function (Producto $record, array $data): void {
                $movimiento = app(InventarioService::class)->ajustar(
                    $record, (int) $data['existencia_real'], $data['motivo'], $data['detalle'] ?? null, auth()->user()
                );
                $record->refresh();

                Notification::make()
                    ->title("Ajuste guardado (" . ($movimiento->cantidad > 0 ? "+" : "") . "{$movimiento->cantidad} piezas)")
                    ->body("{$record->nombre}: nueva existencia {$record->existencia} piezas.")
                    ->success()
                    ->send();
            });
    }
}
