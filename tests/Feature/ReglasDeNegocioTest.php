<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\InventarioService;
use App\Services\PedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Pruebas automáticas de las reglas de negocio de la planeación.
 * Se ejecutan con: php artisan test
 * Usan una base de datos temporal en memoria (no tocan la base real).
 */
class ReglasDeNegocioTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;            // carga los datos de prueba (DatabaseSeeder)

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(today()->setTime(12, 0));       // mediodía: dentro del horario
    }

    private function usuario(string $usuario): Usuario
    {
        return Usuario::where('usuario', $usuario)->firstOrFail();
    }

    private function pedidoNuevo(array $productos = [[1, 2]], string $quien = 'jperez'): Pedido
    {
        return app(PedidoService::class)->crear([
            'id_cliente'    => 1,
            'tipo_servicio' => 'local',
            'productos'     => array_map(fn ($p) => ['id_producto' => $p[0], 'cantidad' => $p[1], 'notas' => null], $productos),
        ], $this->usuario($quien));
    }

    private function esperarError(callable $accion): void
    {
        try {
            $accion();
            $this->fail('Se esperaba un error de validación.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    // ---------- Registro de pedidos ----------

    public function test_crear_pedido_genera_folio_descuenta_existencia_y_guarda_historial(): void
    {
        $antes = Producto::find(1)->existencia;
        $pedido = $this->pedidoNuevo([[1, 2]]);

        $this->assertMatchesRegularExpression('/^PED-\d{4}$/', $pedido->folio);                 // RN-01
        $this->assertSame($antes - 2, Producto::find(1)->existencia);                            // RN-05
        $this->assertEquals(2 * Producto::find(1)->precio, $pedido->total);                       // RN-14, RN-15
        $this->assertSame('Recibido', $pedido->estado->nombre);                                   // RN-07
        $this->assertCount(1, $pedido->historial);                                                // RN-12
        $this->assertSame(1, $pedido->movimientos()->where('tipo', 'salida')->count());           // RN-22
    }

    public function test_no_se_puede_pedir_mas_de_lo_que_hay(): void                             // RN-04
    {
        $existencia = Producto::find(1)->existencia;
        $this->esperarError(fn () => $this->pedidoNuevo([[1, $existencia + 1]]));
    }

    public function test_cocina_no_puede_registrar_pedidos(): void                               // RN-25
    {
        $this->esperarError(fn () => $this->pedidoNuevo([[1, 1]], 'rramirez'));
    }

    public function test_fuera_de_horario_no_se_registran_pedidos(): void                        // RN-27
    {
        Configuracion::actual()->update(['hora_apertura' => '08:00', 'hora_cierre' => '22:00']);
        Carbon::setTestNow(today()->setTime(23, 30));
        $this->esperarError(fn () => $this->pedidoNuevo());

        Carbon::setTestNow(today()->setTime(21, 59));
        $this->assertNotNull($this->pedidoNuevo());
    }

    public function test_horario_que_cruza_medianoche(): void
    {
        $configuracion = Configuracion::actual();
        $configuracion->update(['hora_apertura' => '18:00', 'hora_cierre' => '02:00']);

        $this->assertTrue($configuracion->estaAbierto(today()->setTime(23, 0)));
        $this->assertTrue($configuracion->estaAbierto(today()->setTime(1, 30)));
        $this->assertFalse($configuracion->estaAbierto(today()->setTime(12, 0)));
    }

    // ---------- Estados y permisos ----------

    public function test_cada_rol_avanza_solo_los_estados_que_le_tocan(): void                   // RN-08, RN-25
    {
        $servicio = app(PedidoService::class);
        $pedido = $this->pedidoNuevo();

        // El cajero no pone a preparar; la cocina sí
        $this->esperarError(fn () => $servicio->avanzarEstado($pedido, $this->usuario('jperez')));
        $pedido = $servicio->avanzarEstado($pedido, $this->usuario('rramirez'));
        $this->assertSame('En preparación', $pedido->estado->nombre);

        $pedido = $servicio->avanzarEstado($pedido, $this->usuario('rramirez'));
        $this->assertSame('Listo', $pedido->estado->nombre);

        // La cocina no entrega; el cajero sí
        $this->esperarError(fn () => $servicio->avanzarEstado($pedido, $this->usuario('rramirez')));
        $pedido = $servicio->avanzarEstado($pedido, $this->usuario('jperez'));
        $this->assertSame('Entregado', $pedido->estado->nombre);

        // Entregado ya no avanza (RN-10)
        $this->esperarError(fn () => $servicio->avanzarEstado($pedido, $this->usuario('admin')));
        $this->assertCount(4, $pedido->historial()->get());                                      // RN-12
    }

    public function test_cancelar_regresa_existencias_y_solo_lo_hace_cajero_o_admin(): void     // RN-09
    {
        $servicio = app(PedidoService::class);
        $antes = Producto::find(1)->existencia;
        $pedido = $this->pedidoNuevo([[1, 3]]);

        $this->esperarError(fn () => $servicio->cancelar($pedido, 'Prueba', $this->usuario('rramirez')));

        $pedido = $servicio->cancelar($pedido, 'El cliente se fue', $this->usuario('jperez'));
        $this->assertSame('Cancelado', $pedido->estado->nombre);
        $this->assertSame($antes, Producto::find(1)->existencia);
        $this->assertSame('El cliente se fue', $pedido->motivo_cancelacion);
    }

    public function test_un_pedido_listo_no_se_puede_cancelar(): void
    {
        $servicio = app(PedidoService::class);
        $pedido = $this->pedidoNuevo();
        $servicio->avanzarEstado($pedido, $this->usuario('admin'));
        $pedido = $servicio->avanzarEstado($pedido->refresh(), $this->usuario('admin'));      // Listo

        $this->esperarError(fn () => $servicio->cancelar($pedido, 'Tarde', $this->usuario('admin')));
    }

    // ---------- Modificar pedido (RN-11) ----------

    public function test_modificar_pedido_ajusta_inventario_y_total(): void
    {
        $servicio = app(PedidoService::class);
        $existencia1 = Producto::find(1)->existencia;
        $existencia2 = Producto::find(2)->existencia;
        $pedido = $this->pedidoNuevo([[1, 3]]);

        // Precio del producto 1 cambia después: el pedido conserva el precio original (RN-13)
        $precioOriginal = Producto::find(1)->precio;
        Producto::find(1)->update(['precio' => 999]);

        // Se reduce el producto 1 a 1 pieza y se agrega 2 del producto 2
        $pedido = $servicio->actualizar($pedido, [
            'id_cliente' => 2, 'tipo_servicio' => 'llevar', 'observaciones' => 'Cambio',
            'productos'  => [
                ['id_producto' => 1, 'cantidad' => 1, 'notas' => null],
                ['id_producto' => 2, 'cantidad' => 2, 'notas' => 'Sin sal'],
            ],
        ], $this->usuario('jperez'));

        $this->assertSame($existencia1 - 1, Producto::find(1)->existencia);
        $this->assertSame($existencia2 - 2, Producto::find(2)->existencia);
        $this->assertEquals($precioOriginal, $pedido->detalles()->where('id_producto', 1)->value('precio_unitario'));
        $this->assertEquals($precioOriginal + 2 * Producto::find(2)->precio, $pedido->total);
        $this->assertSame('llevar', $pedido->tipo_servicio);

        // Se quita el producto 1 por completo
        $pedido = $servicio->actualizar($pedido, [
            'id_cliente' => 2, 'tipo_servicio' => 'llevar',
            'productos'  => [['id_producto' => 2, 'cantidad' => 2, 'notas' => null]],
        ], $this->usuario('jperez'));
        $this->assertSame($existencia1, Producto::find(1)->existencia);
        $this->assertSame(1, $pedido->detalles()->count());
    }

    public function test_no_se_modifica_un_pedido_que_ya_no_esta_en_recibido(): void            // RN-10, RN-11
    {
        $servicio = app(PedidoService::class);
        $pedido = $this->pedidoNuevo();
        $pedido = $servicio->avanzarEstado($pedido, $this->usuario('rramirez'));

        $this->esperarError(fn () => $servicio->actualizar($pedido, [
            'id_cliente' => 1, 'tipo_servicio' => 'local',
            'productos'  => [['id_producto' => 1, 'cantidad' => 5]],
        ], $this->usuario('admin')));
    }

    public function test_modificar_no_puede_dejar_el_pedido_vacio(): void                        // RN-02
    {
        $pedido = $this->pedidoNuevo();
        $this->esperarError(fn () => app(PedidoService::class)->actualizar($pedido, [
            'id_cliente' => 1, 'tipo_servicio' => 'local', 'productos' => [],
        ], $this->usuario('admin')));
    }

    // ---------- Inventario ----------

    public function test_entrada_de_inventario_suma_y_registra_movimiento(): void              // RF-16, RN-22
    {
        $producto = Producto::find(3);
        $antes = $producto->existencia;
        $movimiento = app(InventarioService::class)->registrarEntrada($producto, 10, 'Resurtido', $this->usuario('admin'));

        $this->assertSame($antes + 10, $producto->fresh()->existencia);
        $this->assertSame('entrada', $movimiento->tipo);
        $this->assertSame(10, $movimiento->cantidad);
    }

    public function test_ajuste_solo_admin_con_motivo_y_sin_negativos(): void                   // RF-17, RN-23, RN-06
    {
        $servicio = app(InventarioService::class);
        $producto = Producto::find(3);
        $antes = $producto->existencia;

        $this->esperarError(fn () => $servicio->ajustar($producto, $antes - 1, 'Merma', null, $this->usuario('jperez')));
        $this->esperarError(fn () => $servicio->ajustar($producto, -1, 'Merma', null, $this->usuario('admin')));
        $this->esperarError(fn () => $servicio->ajustar($producto, $antes, 'Merma', null, $this->usuario('admin')));
        $this->esperarError(fn () => $servicio->ajustar($producto, $antes - 1, '', null, $this->usuario('admin')));

        $movimiento = $servicio->ajustar($producto, $antes - 2, 'Merma', 'Se cayeron', $this->usuario('admin'));
        $this->assertSame($antes - 2, $producto->fresh()->existencia);
        $this->assertSame(-2, $movimiento->cantidad);
        $this->assertSame('Merma: Se cayeron', $movimiento->motivo);
    }

    public function test_stock_bajo_detecta_productos_en_el_minimo(): void                     // RN-20
    {
        $producto = Producto::find(3);
        $producto->update(['existencia' => $producto->stock_minimo]);

        $this->assertTrue(Producto::stockBajo()->whereKey(3)->exists());
    }
}
