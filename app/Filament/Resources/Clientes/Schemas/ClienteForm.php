<?php

namespace App\Filament\Resources\Clientes\Schemas;

use App\Models\Cliente;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 * Formulario para crear y editar clientes.
 *
 * Nombre y teléfono SÍ se pueden repetir (dos personas pueden llamarse igual, y varias
 * personas de una misma casa pueden dar el mismo teléfono). Por eso el sistema no
 * bloquea: solo muestra un aviso amarillo para no registrar dos veces a la misma persona.
 * Para distinguir a los clientes está su número de cliente (CLI-0007).
 *
 * Los campos "nombre" y "teléfono" también se usan en el botón + del pedido (PedidoForm).
 */
class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)                            // secciones a todo lo ancho
            ->components([
                Section::make('Datos del cliente')
                    ->description(fn (?Cliente $record): string => $record
                        ? "Número de cliente: {$record->numero}. El teléfono es opcional; sirve para avisar cuando el pedido está listo."
                        : 'El número de cliente (CLI-0000) se asigna solo al guardar. El teléfono es opcional; sirve para avisar cuando el pedido está listo.')
                    ->schema([
                        self::campoNombre(),
                        self::campoTelefono(),
                        Toggle::make('activo')
                            ->label('Cliente activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Campo "Nombre completo" con aviso si ya hay otro cliente que se llama igual.
     */
    public static function campoNombre(): TextInput
    {
        return TextInput::make('nombre')
            ->label('Nombre completo')
            ->placeholder('Ejemplo: María González')
            ->required()
            ->maxLength(80)
            ->live(onBlur: true)                    // el aviso se revisa al salir del campo
            ->belowContent(function (?string $state, ?Model $record): ?Text {
                $mismos = Cliente::conMismoNombre($state, self::idClienteEnEdicion($record));

                if ($mismos->isEmpty()) {
                    return null;
                }

                $lista = $mismos->take(3)
                    ->map(fn (Cliente $c): string => $c->numero . ($c->telefono ? ", tel. {$c->telefono}" : ', sin teléfono'))
                    ->implode('; ');

                $texto = $mismos->count() === 1
                    ? "Ya hay un cliente llamado {$mismos->first()->nombre} ({$lista})."
                    : "Ya hay {$mismos->count()} clientes llamados {$mismos->first()->nombre} ({$lista}).";

                return self::aviso($texto . ' ¿Es otra persona? Si es así, puedes guardarlo de todos modos.');
            });
    }

    /**
     * Campo "Teléfono" con aviso si ya hay otro cliente con ese mismo teléfono.
     */
    public static function campoTelefono(): TextInput
    {
        return TextInput::make('telefono')
            ->label('Teléfono')
            ->tel()
            ->placeholder('10 dígitos')
            ->maxLength(15)
            ->regex('/^[0-9 ()+-]{7,15}$/')
            ->validationMessages([
                'regex' => 'Escribe solo números (puede llevar espacios, guiones o paréntesis).',
            ])
            ->live(onBlur: true)                    // el aviso se revisa al salir del campo
            ->belowContent(function (?string $state, ?Model $record): ?Text {
                $mismos = Cliente::conMismoTelefono($state, self::idClienteEnEdicion($record));

                if ($mismos->isEmpty()) {
                    return null;
                }

                $quienes = $mismos->take(3)
                    ->map(fn (Cliente $c): string => "{$c->nombre} ({$c->numero})")
                    ->implode(', ');

                return self::aviso("Este teléfono ya lo tiene {$quienes}. ¿Es la misma persona? Si no, puedes guardarlo de todos modos.");
            });
    }

    /**
     * Texto amarillo con ícono de advertencia, debajo del campo.
     */
    private static function aviso(string $texto): Text
    {
        return Text::make($texto)
            ->color('warning')
            ->icon('heroicon-m-exclamation-triangle');
    }

    /**
     * Si se está editando un cliente, su id (para no avisar contra sí mismo).
     * En el botón + del pedido no hay cliente en edición.
     */
    private static function idClienteEnEdicion(?Model $record): ?int
    {
        return $record instanceof Cliente ? (int) $record->id_cliente : null;
    }
}
