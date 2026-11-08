<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\CambiarContrasena;
use App\Filament\Pages\ConfiguracionNegocio;
use App\Filament\Pages\Reportes;
use App\Filament\Pages\TableroCocina;
use App\Filament\Resources\Pedidos\Pages\CreatePedido;
use App\Filament\Resources\Pedidos\Pages\EditPedido;
use App\Filament\Resources\Pedidos\Pages\ViewPedido;
use App\Filament\Resources\Productos\Pages\EditProducto;
use App\Filament\Resources\Productos\Pages\ListProductos;
use App\Filament\Resources\Usuarios\Pages\CreateUsuario;
use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas de las pantallas de Filament: se llenan los formularios y se presionan
 * los botones igual que lo haría un usuario. Se ejecutan con: php artisan test
 */
class PantallasTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(today()->setTime(12, 0));
        Repeater::fake();                                    // claves predecibles en la tabla de productos
    }

    private function entrarComo(string $usuario): Usuario
    {
        $u = Usuario::where('usuario', $usuario)->firstOrFail();
        $this->actingAs($u);

        return $u;
    }

    public function test_registrar_pedido_desde_el_formulario(): void
    {
        $this->entrarComo('jperez');
        $antes = Pedido::count();

        Livewire::test(CreatePedido::class)
            ->fillForm([
                'id_cliente'    => 2,
                'tipo_servicio' => 'llevar',
                'productos'     => [
                    ['id_producto' => 1, 'cantidad' => 2, 'notas' => 'Sin crema'],
                    ['id_producto' => 2, 'cantidad' => 1, 'notas' => null],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($antes + 1, Pedido::count());
        $this->assertSame(2, Pedido::latest('id_pedido')->first()->detalles()->count());
    }

    public function test_el_formulario_no_deja_pedir_mas_de_la_existencia(): void            // RF-26
    {
        $this->entrarComo('jperez');
        $existencia = Producto::find(1)->existencia;

        Livewire::test(CreatePedido::class)
            ->fillForm([
                'id_cliente' => 1,
                'productos'  => [['id_producto' => 1, 'cantidad' => $existencia + 5]],
            ])
            ->call('create')
            ->assertHasFormErrors(['productos.0.cantidad']);
    }

    public function test_modificar_pedido_desde_el_formulario(): void                          // RF-30
    {
        $this->entrarComo('jperez');
        $pedido = Pedido::whereHas('estado', fn ($q) => $q->where('orden', 1))->firstOrFail();
        $detalle = $pedido->detalles()->first();

        Livewire::test(EditPedido::class, ['record' => $pedido->getRouteKey()])
            ->assertFormSet(['productos' => $pedido->detalles->map(fn ($d) => [
                'id_producto' => $d->id_producto, 'cantidad' => $d->cantidad, 'notas' => $d->notas,
            ])->all()])
            ->fillForm([
                'productos' => [['id_producto' => $detalle->id_producto, 'cantidad' => $detalle->cantidad + 1, 'notas' => null]],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(1, $pedido->detalles()->count());
        $this->assertSame($detalle->cantidad + 1, $pedido->detalles()->first()->cantidad);
    }

    public function test_cocina_ve_el_boton_de_avanzar_pero_no_el_de_cancelar(): void        // RN-25
    {
        $this->entrarComo('rramirez');
        $pedido = Pedido::whereHas('estado', fn ($q) => $q->where('orden', 1))->firstOrFail();

        Livewire::test(ViewPedido::class, ['record' => $pedido->getRouteKey()])
            ->assertActionVisible('avanzarEstado')
            ->assertActionHidden('cancelarPedido')
            ->assertActionHidden('edit');
    }

    public function test_tablero_de_cocina_avanza_pedidos(): void                               // RF-36
    {
        $this->entrarComo('rramirez');
        $pedido = Pedido::whereHas('estado', fn ($q) => $q->where('orden', 1))->firstOrFail();

        Livewire::test(TableroCocina::class)
            ->assertSee($pedido->folio)
            ->call('avanzar', $pedido->id_pedido);

        $this->assertSame('En preparación', $pedido->fresh()->estado->nombre);
    }

    public function test_registrar_entrada_y_ajuste_desde_productos(): void                     // RF-16, RF-17
    {
        $this->entrarComo('admin');
        $producto = Producto::find(4);
        $antes = $producto->existencia;

        Livewire::test(ListProductos::class)
            ->callTableAction('registrarEntrada', $producto, data: ['cantidad' => 7, 'motivo' => 'Resurtido'])
            ->assertHasNoTableActionErrors();
        $this->assertSame($antes + 7, $producto->fresh()->existencia);

        Livewire::test(ListProductos::class)
            ->callTableAction('ajustarExistencia', $producto, data: ['existencia_real' => 3, 'motivo' => 'Conteo físico'])
            ->assertHasNoTableActionErrors();
        $this->assertSame(3, $producto->fresh()->existencia);
    }

    public function test_el_cajero_no_ve_los_botones_de_inventario(): void                      // RN-23
    {
        $this->entrarComo('jperez');

        Livewire::test(ListProductos::class)
            ->assertTableActionHidden('registrarEntrada', Producto::find(1))
            ->assertTableActionHidden('ajustarExistencia', Producto::find(1));
    }

    public function test_editar_producto_no_cambia_la_existencia(): void
    {
        $this->entrarComo('admin');
        $producto = Producto::find(5);
        $antes = $producto->existencia;

        Livewire::test(EditProducto::class, ['record' => $producto->getRouteKey()])
            ->fillForm(['existencia' => $antes + 100, 'precio' => 77])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($antes, $producto->fresh()->existencia);
        $this->assertEquals(77, $producto->fresh()->precio);
    }

    public function test_crear_usuario_guarda_la_contrasena_cifrada(): void                     // RF-04
    {
        $this->entrarComo('admin');

        Livewire::test(CreateUsuario::class)
            ->fillForm([
                'nombre' => 'Pedro', 'apellidos' => 'Ruiz Luna', 'usuario' => 'pruiz', 'id_rol' => 3,
                'activo' => true, 'contrasena_hash' => 'Cocina2026', 'contrasena_confirmacion' => 'Cocina2026',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $nuevo = Usuario::where('usuario', 'pruiz')->firstOrFail();
        $this->assertTrue(Hash::check('Cocina2026', $nuevo->contrasena_hash));
    }

    public function test_no_se_repite_el_nombre_de_usuario(): void
    {
        $this->entrarComo('admin');

        Livewire::test(CreateUsuario::class)
            ->fillForm([
                'nombre' => 'Otro', 'apellidos' => 'X', 'usuario' => 'jperez', 'id_rol' => 2,
                'contrasena_hash' => 'Prueba123', 'contrasena_confirmacion' => 'Prueba123',
            ])
            ->call('create')
            ->assertHasFormErrors(['usuario' => 'unique']);
    }

    public function test_el_admin_no_puede_desactivarse_a_si_mismo(): void
    {
        $admin = $this->entrarComo('admin');

        Livewire::test(ListUsuarios::class)
            ->assertTableActionHidden('cambiarActivo', $admin)
            ->assertTableActionVisible('cambiarActivo', Usuario::where('usuario', 'jperez')->first());
    }

    public function test_cambiar_mi_contrasena(): void                                          // RF-08
    {
        $usuario = $this->entrarComo('mlopez');

        Livewire::test(CambiarContrasena::class)
            ->fillForm([
                'password' => 'NuevaClave2026',
                'passwordConfirmation' => 'NuevaClave2026',
                'currentPassword' => 'Prueba123',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('NuevaClave2026', $usuario->fresh()->contrasena_hash));
    }

    public function test_configurar_horario(): void                                             // RN-27
    {
        $this->entrarComo('admin');

        Livewire::test(ConfiguracionNegocio::class)
            ->fillForm(['nombre_negocio' => 'Fonda Equipo 11', 'hora_apertura' => '09:00', 'hora_cierre' => '18:00'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('09:00 a 18:00', Configuracion::actual()->textoHorario());
    }

    public function test_reportes_muestran_datos(): void                                        // RF-39 a RF-42
    {
        $this->entrarComo('admin');

        Livewire::test(Reportes::class)
            ->assertSee('Total vendido')
            ->assertSee('Productos más vendidos')
            ->call('periodo', 'semana')
            ->set('soloPorResurtir', true)
            ->assertSee('Inventario actual');
    }

    public function test_comprobante_requiere_iniciar_sesion(): void                           // RF-29
    {
        $pedido = Pedido::first();
        $this->get("/pedidos/{$pedido->id_pedido}/comprobante")->assertRedirect('/admin/login');

        $this->entrarComo('jperez');
        $this->get("/pedidos/{$pedido->id_pedido}/comprobante")->assertOk()->assertSee($pedido->folio);
    }
}
