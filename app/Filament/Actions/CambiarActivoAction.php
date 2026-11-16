<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * Botón "Desactivar / Activar" que reemplaza al botón "Eliminar".
 *
 * En lugar de borrar el registro (lo que haría perder el historial de pedidos),
 * solo cambia su campo "activo" (baja lógica, RN-21).
 * Se usa en Categorías, Productos, Clientes y Usuarios.
 */
class CambiarActivoAction
{
    public static function make(): Action
    {
        return Action::make('cambiarActivo')
            ->label(fn (Model $record): string => $record->activo ? 'Desactivar' : 'Activar')
            ->icon(fn (Model $record): string => $record->activo ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
            ->color(fn (Model $record): string => $record->activo ? 'danger' : 'success')
            // Solo aparece para quien tiene permiso (método desactivar() de cada Policy, RN-25)
            ->visible(fn (Model $record): bool => auth()->user()?->can('desactivar', $record) ?? false)
            ->requiresConfirmation()
            ->modalHeading(fn (Model $record): string => ($record->activo ? 'Desactivar ' : 'Activar ') . $record->nombre)
            ->modalDescription(fn (Model $record): string => $record->activo
                ? 'El registro no se borra: deja de aparecer para nuevos pedidos, pero se conserva su historial.'
                : 'El registro volverá a estar disponible.')
            ->modalSubmitActionLabel(fn (Model $record): string => $record->activo ? 'Sí, desactivar' : 'Sí, activar')
            ->action(function (Model $record): void {
                $record->update(['activo' => ! $record->activo]);

                Notification::make()
                    ->title($record->activo ? 'Registro activado' : 'Registro desactivado')
                    ->success()
                    ->send();
            });
    }
}
