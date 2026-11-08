<?php

namespace App\Filament\Pages;

use App\Models\Configuracion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Página "Configuración" (solo Administrador): nombre del negocio y horario de servicio.
 * Fuera del horario el sistema no deja registrar pedidos (RN-27).
 */
class ConfiguracionNegocio extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del negocio';

    protected static ?string $slug = 'configuracion';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 3;

    /** Datos del formulario. */
    public ?array $data = [];

    /** Solo el Administrador entra a esta página (RN-25). */
    public static function canAccess(): bool
    {
        return auth()->user()?->esAdministrador() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(Configuracion::actual()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Datos del negocio')
                    ->schema([
                        TextInput::make('nombre_negocio')
                            ->label('Nombre del negocio')
                            ->helperText('Aparece en el comprobante de cada pedido.')
                            ->required()
                            ->maxLength(80),
                    ]),
                Section::make('Horario de servicio (RN-27)')
                    ->description('Fuera de este horario no se pueden registrar pedidos. Si la apertura y el cierre son iguales, el negocio se considera abierto todo el día.')
                    ->schema([
                        TimePicker::make('hora_apertura')
                            ->label('Hora de apertura')
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('hora_cierre')
                            ->label('Hora de cierre')
                            ->seconds(false)
                            ->required(),
                        Text::make(function (): string {
                            $configuracion = Configuracion::actual();

                            return 'Horario guardado: ' . $configuracion->textoHorario()
                                . ' · En este momento (' . now()->format('H:i') . ') el negocio está '
                                . ($configuracion->estaAbierto() ? 'ABIERTO.' : 'CERRADO.');
                        })->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    /** Coloca el formulario y el botón Guardar en la página. */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Guardar')
                            ->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $datos = $this->form->getState();

        Configuracion::actual()->update($datos);

        Notification::make()
            ->title('Configuración guardada')
            ->body('Horario de servicio: ' . Configuracion::actual()->textoHorario())
            ->success()
            ->send();
    }
}
