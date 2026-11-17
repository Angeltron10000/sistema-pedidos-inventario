<?php

namespace Tests\Feature;

use App\Filament\Resources\Categorias\Pages\CreateCategoria;
use App\Filament\Resources\Clientes\Pages\CreateCliente;
use App\Filament\Resources\Clientes\Pages\EditCliente;
use App\Filament\Resources\Clientes\Pages\ListClientes;
use App\Filament\Resources\Pedidos\Schemas\PedidoForm;
use App\Filament\Resources\Productos\Pages\CreateProducto;
use App\Filament\Resources\Productos\Pages\EditProducto;
use App\Filament\Resources\Productos\Pages\ListProductos;
use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\PedidoService;
use App\Support\Texto;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas de los ajustes que pidió la profesora después de la presentación (16-nov-2026):
 *   1. Nombres repetidos al copiar y pegar (ejercicio 15).
 *   2. Nombres iguales con distinta mayúscula / minúscula.
 *   3. Clientes: número CLI-0000, selector con nombre · teléfono · número, avisos sin bloquear.
 *   4. Rol "Cliente": consulta del pedido con folio + código y cancelación en "Recibido".
 * Se ejecutan con: php artisan test
 */
class AjustesProfesoraTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /** "Café americano" escrito como a veces llega al copiar y pegar: espacio duro y "é" en dos piezas. */
    private const CAFE_PEGADO = "Cafe\u{0301}\u{00A0}americano";

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(today()->setTime(12, 0));
    }

    private function entrarComo(string $usuario): Usuario
    {
        $u = Usuario::where('usuario', $usuario)->firstOrFail();
        $this->actingAs($u);

        return $u;
    }

    private function datosProducto(string $nombre): array
    {
        return [
            'nombre' => $nombre, 'id_categoria' => 1, 'precio' => 45,
            'existencia' => 10, 'stock_minimo' => 2, 'activo' => true,
        ];
    }

    private function pedidoNuevo(): Pedido
    {
        return app(PedidoService::class)->crear([
            'id_cliente'    => 2,
            'tipo_servicio' => 'llevar',
            'productos'     => [['id_producto' => 1, 'cantidad' => 2, 'notas' => null]],
        ], Usuario::where('usuario', 'jperez')->firstOrFail());
    }

    // ---------- 1 y 2. Nombres repetidos ----------

    public function test_texto_limpio_y_clave(): void
    {
        $this->assertSame('Café americano', Texto::limpiar("  Cafe\u{0301}\u{00A0}\u{00A0}americano\u{200B} "));
        $this->assertSame(Texto::clave('Café americano'), Texto::clave('CAFÉ AMERICANO'));
        $this->assertSame(Texto::clave('Café americano'), Texto::clave(self::CAFE_PEGADO));
        $this->assertSame('5512345678', Texto::soloDigitos('(55) 1234-5678'));
    }

    public function test_producto_copiado_y_pegado_se_detecta_como_repetido(): void        // ejercicio 15
    {
        $this->entrarComo('admin');

        Livewire::test(CreateProducto::class)
            ->fillForm($this->datosProducto(self::CAFE_PEGADO))
            ->call('create')
            ->assertHasFormErrors(['nombre']);

        $this->assertSame(1, Producto::where('nombre', 'Café americano')->count());
    }

    public function test_producto_con_otras_mayusculas_se_detecta_como_repetido(): void
    {
        $this->entrarComo('admin');

        foreach (['café americano', 'CAFÉ AMERICANO', '  Café   americano  '] as $nombre) {
            Livewire::test(CreateProducto::class)
                ->fillForm($this->datosProducto($nombre))
                ->call('create')
                ->assertHasFormErrors(['nombre']);
        }
    }

    public function test_producto_nuevo_se_guarda_con_el_nombre_limpio(): void
    {
        $this->entrarComo('admin');

        Livewire::test(CreateProducto::class)
            ->fillForm($this->datosProducto("Té\u{00A0}de  manzanilla "))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Producto::where('nombre', 'Té de manzanilla')->exists());
    }

    public function test_editar_un_producto_sin_cambiar_su_nombre_no_marca_error(): void
    {
        $this->entrarComo('admin');
        $cafe = Producto::where('nombre', 'Café americano')->firstOrFail();

        Livewire::test(EditProducto::class, ['record' => $cafe->getRouteKey()])
            ->fillForm(['nombre' => 'CAFÉ americano'])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_un_producto_desactivado_no_impide_usar_su_nombre(): void          // RN-17 (solo activos)
    {
        $this->entrarComo('admin');
        Producto::where('nombre', 'Café americano')->update(['activo' => false]);

        Livewire::test(CreateProducto::class)
            ->fillForm($this->datosProducto('café americano'))
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_no_se_reactiva_un_producto_si_ya_hay_otro_activo_con_ese_nombre(): void
    {
        $this->entrarComo('admin');
        $viejo = Producto::where('nombre', 'Café americano')->firstOrFail();
        $viejo->update(['activo' => false]);
        Producto::create($this->datosProducto('CAFÉ AMERICANO'));

        Livewire::test(ListProductos::class)
            ->callTableAction('cambiarActivo', $viejo);

        $this->assertFalse($viejo->fresh()->activo);
    }

    public function test_categoria_copiada_o_con_otras_mayusculas_se_detecta_como_repetida(): void   // ejercicio 12
    {
        $this->entrarComo('admin');
        $nombre = Categoria::firstOrFail()->nombre;

        foreach ([mb_strtoupper($nombre), str_replace(' ', "\u{00A0}", " {$nombre} ")] as $intento) {
            Livewire::test(CreateCategoria::class)
                ->fillForm(['nombre' => $intento, 'activo' => true])
                ->call('create')
                ->assertHasFormErrors(['nombre']);
        }
    }

    // ---------- 3. Clientes ----------

    public function test_numero_de_cliente(): void
    {
        $ana = Cliente::find(2);

        $this->assertSame('CLI-0002', $ana->numero);
        $this->assertSame('Ana Torres · 5512345678 · CLI-0002', $ana->etiqueta());
        $this->assertSame(7, Cliente::idDesdeNumero('cli-0007'));
        $this->assertSame(7, Cliente::idDesdeNumero('7'));
        $this->assertNull(Cliente::idDesdeNumero('Ana'));
    }

    public function test_se_puede_registrar_un_cliente_con_el_mismo_nombre(): void          // aviso, no bloqueo
    {
        $this->entrarComo('jperez');

        Livewire::test(CreateCliente::class)
            ->fillForm(['nombre' => 'ana torres', 'telefono' => '55 1234 5678', 'activo' => true])
            ->assertSee('Ya hay un cliente llamado Ana Torres (CLI-0002')
            ->assertSee('Este teléfono ya lo tiene Ana Torres (CLI-0002)')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, Cliente::all()->filter(fn ($c) => Texto::clave($c->nombre) === 'ana torres')->count());
    }

    public function test_sin_repetidos_no_hay_aviso_y_un_cliente_no_se_avisa_contra_si_mismo(): void
    {
        $this->entrarComo('jperez');

        Livewire::test(CreateCliente::class)
            ->fillForm(['nombre' => 'Persona Nueva', 'telefono' => '5599990000'])
            ->assertDontSee('Ya hay un cliente llamado')
            ->assertDontSee('Este teléfono ya lo tiene');

        Livewire::test(EditCliente::class, ['record' => 2])
            ->assertDontSee('Ya hay un cliente llamado')
            ->assertDontSee('Este teléfono ya lo tiene');
    }

    public function test_buscar_cliente_por_numero_nombre_o_telefono(): void
    {
        $this->assertSame([2 => 'Ana Torres · 5512345678 · CLI-0002'], PedidoForm::buscarClientes('CLI-0002'));
        $this->assertArrayHasKey(2, PedidoForm::buscarClientes('ana'));
        $this->assertArrayHasKey(2, PedidoForm::buscarClientes('5512'));
    }

    public function test_la_lista_de_clientes_busca_por_numero(): void
    {
        $this->entrarComo('jperez');

        Livewire::test(ListClientes::class)
            ->searchTable('CLI-0003')
            ->assertCanSeeTableRecords([Cliente::find(3)])
            ->assertCanNotSeeTableRecords([Cliente::find(2)]);
    }

    // ---------- 4. Rol "Cliente" y consulta del pedido ----------

    public function test_rol_cliente_y_su_usuario_existen_y_no_entran_al_panel(): void
    {
        $cuenta = Usuario::where('usuario', Usuario::USUARIO_CONSULTA_CLIENTE)->firstOrFail();

        $this->assertSame('Cliente', Rol::find(Rol::CLIENTE)->nombre);
        $this->assertTrue($cuenta->esCliente());
        $this->assertFalse($cuenta->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_el_admin_no_puede_modificar_ni_desactivar_al_usuario_cliente(): void
    {
        $this->entrarComo('admin');
        $cuenta = Usuario::where('usuario', Usuario::USUARIO_CONSULTA_CLIENTE)->firstOrFail();

        Livewire::test(ListUsuarios::class)
            ->assertTableActionHidden('cambiarActivo', $cuenta)
            ->assertTableActionHidden('edit', $cuenta);
    }

    public function test_cada_pedido_tiene_codigo_de_consulta(): void
    {
        $pedido = $this->pedidoNuevo();

        $this->assertMatchesRegularExpression('/^[A-HJKMNP-Z2-9]{4}$/', $pedido->codigo_consulta);
        $this->assertSame(0, Pedido::whereNull('codigo_consulta')->count());               // también los de prueba
    }

    public function test_el_comprobante_muestra_el_codigo(): void
    {
        $this->entrarComo('jperez');
        $pedido = $this->pedidoNuevo();

        $this->get(route('pedidos.comprobante', $pedido))
            ->assertOk()
            ->assertSee($pedido->codigo_consulta)
            ->assertSee('Código de consulta');
    }

    public function test_consulta_con_folio_y_codigo_correctos(): void
    {
        $pedido = $this->pedidoNuevo();
        $numero = (int) substr($pedido->folio, 4);

        // Folio solo con el número y el código en minúsculas: también funciona
        $this->post(route('consulta.buscar'), ['folio' => (string) $numero, 'codigo' => strtolower($pedido->codigo_consulta)])
            ->assertRedirect(route('consulta.pedido'));

        $this->get(route('consulta.pedido'))
            ->assertOk()
            ->assertSee($pedido->folio)
            ->assertSee('Recibido')
            ->assertSee('Cancelar mi pedido');

        $this->getJson(route('consulta.estado'))
            ->assertOk()
            ->assertJson(['id_estado' => $pedido->id_estado]);
    }

    public function test_con_codigo_equivocado_no_se_ve_el_pedido(): void
    {
        $pedido = $this->pedidoNuevo();
        $otro = $pedido->codigo_consulta === 'AAAA' ? 'BBBB' : 'AAAA';

        $this->from(route('consulta.formulario'))
            ->post(route('consulta.buscar'), ['folio' => $pedido->folio, 'codigo' => $otro])
            ->assertRedirect(route('consulta.formulario'))
            ->assertSessionHasErrors('codigo');

        $this->get(route('consulta.pedido'))->assertRedirect(route('consulta.formulario'));
        $this->getJson(route('consulta.estado'))->assertNotFound();
    }

    public function test_demasiados_intentos_piden_esperar(): void
    {
        $pedido = $this->pedidoNuevo();

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('consulta.buscar'), ['folio' => $pedido->folio, 'codigo' => 'ZZZZ']);
        }

        $this->post(route('consulta.buscar'), ['folio' => $pedido->folio, 'codigo' => $pedido->codigo_consulta])
            ->assertRedirect(route('consulta.formulario'))
            ->assertSessionHasErrors('codigo');
    }

    public function test_el_cliente_cancela_en_recibido_y_regresan_las_existencias(): void
    {
        $antes = Producto::find(1)->existencia;
        $pedido = $this->pedidoNuevo();

        $this->post(route('consulta.buscar'), ['folio' => $pedido->folio, 'codigo' => $pedido->codigo_consulta]);
        $this->post(route('consulta.cancelar'), ['motivo' => 'Ya no puedo esperar'])
            ->assertRedirect(route('consulta.pedido'));

        $pedido->refresh();
        $this->assertSame(0, $pedido->estado->orden);                                         // Cancelado
        $this->assertStringContainsString('Cancelado por el cliente', $pedido->motivo_cancelacion);
        $this->assertStringContainsString('Ya no puedo esperar', $pedido->motivo_cancelacion);
        $this->assertSame($antes, Producto::find(1)->existencia);                             // reintegro
        $this->assertTrue($pedido->historial->last()->usuario->esCliente());                  // quién canceló

        $this->get(route('consulta.pedido'))->assertSee('Pedido cancelado')->assertDontSee('Cancelar mi pedido');
    }

    public function test_el_cliente_no_cancela_si_ya_se_esta_preparando(): void
    {
        $pedido = $this->pedidoNuevo();
        app(PedidoService::class)->avanzarEstado($pedido, Usuario::where('usuario', 'rramirez')->firstOrFail());

        $this->post(route('consulta.buscar'), ['folio' => $pedido->folio, 'codigo' => $pedido->codigo_consulta]);

        $this->get(route('consulta.pedido'))
            ->assertSee('En preparación')
            ->assertDontSee('Cancelar mi pedido');

        $this->post(route('consulta.cancelar'))
            ->assertRedirect(route('consulta.pedido'))
            ->assertSessionHas('aviso_error');

        $this->assertSame(2, $pedido->fresh()->estado->orden);                               // sigue en preparación
    }

    public function test_la_consulta_solo_muestra_el_pedido_abierto(): void
    {
        $mio = $this->pedidoNuevo();
        $otro = $this->pedidoNuevo();

        $this->post(route('consulta.buscar'), ['folio' => $mio->folio, 'codigo' => $mio->codigo_consulta]);

        $this->get(route('consulta.pedido'))
            ->assertSee($mio->folio)
            ->assertDontSee($otro->folio);

        $this->post(route('consulta.salir'))->assertRedirect(route('consulta.formulario'));
        $this->get(route('consulta.pedido'))->assertRedirect(route('consulta.formulario'));
    }
}
