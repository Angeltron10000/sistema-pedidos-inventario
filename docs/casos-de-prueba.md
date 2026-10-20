# Datos y casos de prueba

**Proyecto 2 — Sistema de pedidos e inventario para un local de comida** · Equipo N.º 11

> Este documento describe los datos de prueba que cargan los seeders de Laravel ([`database/seeders/`](../database/seeders/)) y el script [`database/sql/sistema_pedidos_datos.sql`](../database/sql/sistema_pedidos_datos.sql) y los casos de prueba que comprueban que la base de datos cumple con los [requerimientos](requerimientos.md). [← Volver al README](../README.md)

## Contenido

- [1. Datos de prueba](#1-datos-de-prueba)
- [2. Cómo ejecutar un caso de prueba](#2-cómo-ejecutar-un-caso-de-prueba)
- [3. Resumen de casos de prueba](#3-resumen-de-casos-de-prueba)
- [4. Detalle de los casos de prueba](#4-detalle-de-los-casos-de-prueba)

## 1. Datos de prueba

Los datos representan un **restaurante familiar de menú general** (desayunos, entradas, sopas, platos fuertes, bebidas y postres). Se diseñaron para que exista al menos un ejemplo de cada situación descrita en los requerimientos.

> [!NOTE]
> **Fechas automáticas:** las fechas se calculan a partir del día en que se ejecuta el script. Siempre habrá pedidos de "hoy" (de las últimas horas) y de los 6 días anteriores, para que el panel de indicadores y los reportes muestren información sin importar cuándo se instale.

### 1.1 Usuarios

Todos los usuarios de prueba tienen la contraseña **`Prueba123`**, guardada cifrada con bcrypt (compatible con `password_hash()` y `password_verify()` de PHP).

| Usuario | Nombre | Rol | Estado | Para qué sirve |
|---|---|---|:---:|---|
| `admin` | Laura Méndez Ortiz | Administrador | Activo | Acceso total, ajustes de inventario y cancelaciones. |
| `jperez` | Juan Pérez Gómez | Cajero/Mesero | Activo | Registrar y entregar pedidos. |
| `mlopez` | María López Sánchez | Cajero/Mesero | Activo | Registrar y entregar pedidos. |
| `rramirez` | Ricardo Ramírez Cruz | Cocina | Activo | Cambiar pedidos a "En preparación" y "Listo". |
| `agarcia` | Andrea García Flores | Cocina | Activo | Cambiar pedidos a "En preparación" y "Listo". |
| `rtorres` | Roberto Torres Vega | Cajero/Mesero | **Inactivo** | Probar que un usuario dado de baja no entra, pero su historial se conserva. |

### 1.2 Clientes

| ID | Nombre | Teléfono | Caso que representa |
|:---:|---|---|---|
| 1 | Público en general / Mostrador | — | Cliente por defecto cuando no se captura uno (RN-26). |
| 2 | Ana Torres | 5512345678 | Cliente frecuente con teléfono. |
| 3 | Carlos Hernández | 5587654321 | Cliente frecuente con teléfono. |
| 4 | Sofía Martínez | — | Cliente registrado sin teléfono (dato opcional). |
| 5 | Miguel Díaz | 5544332211 | Cliente con un pedido cancelado. |

### 1.3 Categorías y productos

Hay 6 categorías: Desayunos, Entradas, Sopas, Platos fuertes, Bebidas y Postres. La existencia mostrada es la que queda **después** de todos los pedidos y movimientos de prueba.

| ID | Producto | Categoría | Precio | Existencia | Mínimo | Situación |
|:---:|---|---|---:|:---:|:---:|---|
| 1 | Chilaquiles verdes con pollo | Desayunos | $129.00 | 25 | 5 | Normal |
| 2 | Huevos rancheros | Desayunos | $115.00 | 22 | 5 | Normal |
| 3 | Hot cakes con tocino | Desayunos | $105.00 | 17 | 5 | Normal |
| 4 | Molletes | Desayunos | $89.00 | 4 | 4 | ⚠️ **Stock bajo** |
| 5 | Guacamole con totopos | Entradas | $95.00 | 19 | 5 | Normal |
| 6 | Queso fundido | Entradas | $110.00 | 0 | 3 | ⛔ **Agotado** |
| 7 | Sopa de tortilla | Sopas | $85.00 | 27 | 6 | Normal |
| 8 | Crema de elote | Sopas | $79.00 | 20 | 5 | Normal |
| 9 | Enchiladas suizas | Platos fuertes | $149.00 | 26 | 5 | Normal |
| 10 | Club sándwich | Platos fuertes | $145.00 | 22 | 5 | Normal |
| 11 | Hamburguesa clásica | Platos fuertes | $159.00 | 18 | 5 | Normal |
| 12 | Arrachera con guarnición | Platos fuertes | $245.00 | 12 | 4 | Normal |
| 13 | Pechuga a la plancha | Platos fuertes | $169.00 | 17 | 4 | Normal |
| 14 | Café americano | Bebidas | $45.00 | 140 | 20 | Normal (resurtido) |
| 15 | Jugo de naranja | Bebidas | $55.00 | 35 | 10 | Normal |
| 16 | Limonada natural | Bebidas | $49.00 | 49 | 10 | Normal (ajuste +1) |
| 17 | Refresco | Bebidas | $40.00 | 44 | 12 | Normal (resurtido) |
| 18 | Malteada de vainilla | Bebidas | $75.00 | 5 | 5 | ⚠️ **Stock bajo** |
| 19 | Pastel de chocolate | Postres | $85.00 | 10 | 4 | Normal (merma −2) |
| 20 | Pay de queso | Postres | $79.00 | 11 | 3 | Normal |
| 21 | Flan napolitano | Postres | $65.00 | 10 | 3 | 🚫 **Inactivo** (baja lógica) |

### 1.4 Pedidos

19 pedidos con folios consecutivos del `PED-0001` al `PED-0019`, en todos los estados, en local y para llevar, con distintos clientes y usuarios.

| Folio | Día | Cliente | Servicio | Estado | Total | Caso que representa |
|---|---|---|:---:|---|---:|---|
| `PED-0001` | Hace 6 días | Público en general | Local | Entregado | $348.00 | Flujo completo de estados. |
| `PED-0002` | Hace 6 días | Ana Torres | Llevar | Entregado | $198.00 | Registrado por un usuario hoy inactivo. |
| `PED-0003` | Hace 5 días | Público en general | Local | Entregado | $740.00 | Notas por producto ("Término medio"). |
| `PED-0004` | Hace 5 días | Carlos Hernández | Llevar | Cancelado | $234.00 | Cancelado desde "Recibido". |
| `PED-0005` | Hace 4 días | Sofía Martínez | Local | Entregado | $420.00 | Cliente sin teléfono; 4 productos. |
| `PED-0006` | Hace 3 días | Público en general | Local | Entregado | $673.00 | Venta después de un resurtido. |
| `PED-0007` | Hace 3 días | Ana Torres | Llevar | Entregado | $369.00 | Pedido para llevar entregado. |
| `PED-0008` | Hace 2 días | Miguel Díaz | Local | Cancelado | $594.00 | Cancelado desde "En preparación". |
| `PED-0009` | Hace 2 días | Público en general | Local | Entregado | $615.00 | Venta grande de Queso fundido. |
| `PED-0010` | Ayer | Carlos Hernández | Local | Entregado | $379.00 | Notas para cocina. |
| `PED-0011` | Ayer | Público en general | Llevar | Entregado | $483.00 | Pedido para llevar entregado. |
| `PED-0012` | Hoy | Público en general | Local | Entregado | $368.00 | Entregado hoy (ventas del día). |
| `PED-0013` | Hoy | Sofía Martínez | Llevar | Entregado | $198.00 | Entregado hoy (ventas del día). |
| `PED-0014` | Hoy | Ana Torres | Local | Listo | $505.00 | Listo para entregar; agota el Queso fundido. |
| `PED-0015` | Hoy | Público en general | Local | En preparación | $190.00 | En cocina; nota "Sin mayonesa". |
| `PED-0016` | Hoy | Miguel Díaz | Llevar | En preparación | $244.00 | En cocina; para llevar. |
| `PED-0017` | Hoy | Público en general | Local | Cancelado | $125.00 | Cancelado hoy (capturado por error). |
| `PED-0018` | Hoy | Carlos Hernández | Local | Recibido | $300.00 | Modificado: se quitaron los Molletes. |
| `PED-0019` | Hoy | Público en general | Llevar | Recibido | $170.00 | Recién recibido. |

### 1.5 Historial de estados y movimientos de inventario

- **Historial de estados:** cada pedido tiene un registro por cada estado por el que pasó, con el usuario y la hora. "En preparación" y "Listo" los cambia un usuario de Cocina; "Recibido" y "Entregado", el cajero que registró el pedido.
- **Movimientos de inventario (82 en total):** 21 de inventario inicial, 2 resurtidos de proveedor, 48 salidas por pedido, 8 reintegros (por cancelación o modificación) y 3 ajustes.
- La existencia de cada producto es **exactamente** la suma de sus movimientos.

> [!IMPORTANT]
> **Convención para los ajustes:** en los movimientos de tipo `ajuste`, la cantidad lleva signo. Un número **negativo** disminuye la existencia (por ejemplo, −2 por merma) y uno **positivo** la aumenta (por ejemplo, +1 por un sobrante en el conteo físico). En las entradas y salidas la cantidad siempre es positiva.

## 2. Cómo ejecutar un caso de prueba

1. Carga los datos originales: con Laravel ejecuta `php artisan migrate:fresh --seed` (ver el [README](../README.md#6-ejecutar-las-migraciones-y-cargar-los-datos-de-prueba)), o importa `database/sql/sistema_pedidos_datos.sql` ([alternativa sin Laravel](../README.md#alternativa-sin-laravel-scripts-sql)). Ambos dejan exactamente los mismos datos.
2. En phpMyAdmin, haz clic en **sistema_pedidos** en el panel izquierdo y abre la pestaña **SQL**.
3. Copia la consulta del caso de prueba, pégala en el recuadro y pulsa **Continuar**.
4. Compara lo que aparece en pantalla con el **resultado esperado**.

> [!TIP]
> **¿Usas MySQL Workbench?** Haz doble clic en `sistema_pedidos` en el panel **Schemas** (para que quede en negritas), pega la consulta en una pestaña de consultas y ejecútala con el rayo ⚡ **Execute all**. Los resultados son los mismos; solo cambia el texto de algunos mensajes de error, que se indican en cada caso.

Hay dos tipos de casos:

- ✅ **Positivo:** la operación debe funcionar y mostrar la información esperada.
- ❌ **Negativo:** la operación debe ser **rechazada** por la base de datos. Si aparece el mensaje de error indicado, la prueba es **correcta**, porque significa que la base de datos impidió guardar datos incorrectos.

> [!WARNING]
> Los casos negativos no modifican los datos, porque la base de datos los rechaza. El único caso que sí cambia información es el **CP-28**, por eso está al final; después de ejecutarlo, vuelve a importar el script.

## 3. Resumen de casos de prueba

| ID | Caso de prueba | Requisitos / reglas | Tipo |
|:---:|---|---|---|
| [CP-01](#cp-01--creación-de-la-estructura) | Creación de la estructura | Diagrama E-R | ✅ Positivo |
| [CP-02](#cp-02--catálogos-iniciales) | Catálogos iniciales | RN-07, RN-08, RN-25 | ✅ Positivo |
| [CP-03](#cp-03--acceso-de-un-usuario-activo) | Acceso de un usuario activo | RF-01, RN-24 | ✅ Positivo |
| [CP-04](#cp-04--usuario-desactivado) | Usuario desactivado | RF-07, RN-24 | ❌ Negativo |
| [CP-05](#cp-05--usuarios-por-rol) | Usuarios por rol | RF-05, RN-25 | ✅ Positivo |
| [CP-06](#cp-06--productos-disponibles-para-un-nuevo-pedido) | Productos disponibles para un nuevo pedido | RN-19, RF-12 | ✅ Positivo |
| [CP-07](#cp-07--alerta-de-stock-bajo) | Alerta de stock bajo | RN-20, RF-21, RF-41 | ✅ Positivo |
| [CP-08](#cp-08--consultar-un-pedido-por-su-folio) | Consultar un pedido por su folio | RF-34, RF-29 | ✅ Positivo |
| [CP-09](#cp-09--pedidos-por-estado) | Pedidos por estado | RF-42, RN-07 | ✅ Positivo |
| [CP-10](#cp-10--tablero-de-cocina-y-tiempo-de-espera) | Tablero de cocina y tiempo de espera | RF-36, RF-37 | ✅ Positivo |
| [CP-11](#cp-11--historial-de-cambios-de-estado) | Historial de cambios de estado | RN-12, RF-33 | ✅ Positivo |
| [CP-12](#cp-12--cancelación-con-reintegro-de-existencias) | Cancelación con reintegro de existencias | RN-09, RF-19, RF-31 | ✅ Positivo |
| [CP-13](#cp-13--pedido-modificado-en-estado-recibido) | Pedido modificado en estado Recibido | RN-11, RF-30 | ✅ Positivo |
| [CP-14](#cp-14--cálculo-de-subtotales-y-total) | Cálculo de subtotales y total | RN-14, RN-15, RF-27 | ✅ Positivo |
| [CP-15](#cp-15--existencias-consistentes-con-los-movimientos) | Existencias consistentes con los movimientos | RN-05, RN-22, RF-20 | ✅ Positivo |
| [CP-16](#cp-16--movimientos-de-un-producto) | Movimientos de un producto | RF-22, RF-16 | ✅ Positivo |
| [CP-17](#cp-17--ventas-del-día) | Ventas del día | RF-38, RF-39 | ✅ Positivo |
| [CP-18](#cp-18--ventas-por-periodo) | Ventas por periodo | RF-39 | ✅ Positivo |
| [CP-19](#cp-19--productos-más-vendidos) | Productos más vendidos | RF-40 | ✅ Positivo |
| [CP-20](#cp-20--pedidos-por-cliente) | Pedidos por cliente | RF-24, RF-35, RN-26 | ✅ Positivo |
| [CP-21](#cp-21--folio-repetido) | Folio repetido | RN-01, RF-28 | ❌ Negativo |
| [CP-22](#cp-22--precio-inválido) | Precio inválido | RN-16, RF-15 | ❌ Negativo |
| [CP-23](#cp-23--existencia-negativa) | Existencia negativa | RN-06 | ❌ Negativo |
| [CP-24](#cp-24--cantidad-inválida-en-un-pedido) | Cantidad inválida en un pedido | RN-02, RN-03 | ❌ Negativo |
| [CP-25](#cp-25--subtotal-mal-calculado) | Subtotal mal calculado | RN-14 | ❌ Negativo |
| [CP-26](#cp-26--pedido-de-un-cliente-que-no-existe) | Pedido de un cliente que no existe | Integridad referencial | ❌ Negativo |
| [CP-27](#cp-27--borrado-de-un-producto-con-pedidos) | Borrado de un producto con pedidos | RN-21, RF-14 | ❌ Negativo |
| [CP-28](#cp-28--precio-congelado-en-pedidos-anteriores) | Precio congelado en pedidos anteriores | RN-13 | ✅ Positivo |

## 4. Detalle de los casos de prueba

### CP-01 · Creación de la estructura

- **Objetivo:** Comprobar que el script creó las 10 tablas del diagrama entidad-relación.
- **Relacionado con:** Diagrama E-R
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SHOW TABLES;
```

**Resultado esperado:** Aparecen 10 tablas: categoria, cliente, detalle_pedido, estado_pedido, historial_estado, movimiento_inventario, pedido, producto, rol y usuario.

### CP-02 · Catálogos iniciales

- **Objetivo:** Comprobar que existen los estados del pedido y los roles.
- **Relacionado con:** RN-07, RN-08, RN-25
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT * FROM estado_pedido ORDER BY orden;
SELECT * FROM rol;
```

**Resultado esperado:** 5 estados: Cancelado (orden 0), Recibido (1), En preparación (2), Listo (3) y Entregado (4). 3 roles: Administrador, Cajero/Mesero y Cocina.

### CP-03 · Acceso de un usuario activo

- **Objetivo:** Comprobar que un usuario activo puede iniciar sesión.
- **Relacionado con:** RF-01, RN-24
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT id_usuario, nombre, apellidos, id_rol, activo
FROM usuario
WHERE usuario = 'admin' AND activo = 1;
```

**Resultado esperado:** Regresa 1 fila: Laura Méndez Ortiz, rol 1 (Administrador). En la aplicación, el usuario admin con la contraseña Prueba123 debe entrar al sistema.

### CP-04 · Usuario desactivado

- **Objetivo:** Comprobar que un usuario desactivado no puede entrar, pero que su historial se conserva.
- **Relacionado con:** RF-07, RN-24
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
SELECT * FROM usuario WHERE usuario = 'rtorres' AND activo = 1;
SELECT COUNT(*) AS pedidos FROM pedido WHERE id_usuario = 6;
```

**Resultado esperado:** La primera consulta no regresa filas (no puede entrar). La segunda regresa 2: los pedidos que registró se conservan.

### CP-05 · Usuarios por rol

- **Objetivo:** Consultar cuántos usuarios hay de cada rol y cuántos están activos.
- **Relacionado con:** RF-05, RN-25
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT r.nombre AS rol, COUNT(*) AS usuarios, SUM(u.activo) AS activos
FROM usuario u JOIN rol r ON r.id_rol = u.id_rol
GROUP BY r.id_rol;
```

**Resultado esperado:** Administrador: 1 usuario (1 activo). Cajero/Mesero: 3 usuarios (2 activos). Cocina: 2 usuarios (2 activos).

### CP-06 · Productos disponibles para un nuevo pedido

- **Objetivo:** Mostrar solo los productos que se pueden agregar a un pedido nuevo (activos y con existencia).
- **Relacionado con:** RN-19, RF-12
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT id_producto, nombre, precio, existencia
FROM producto
WHERE activo = 1 AND existencia > 0;
```

**Resultado esperado:** Regresa 19 productos. No aparecen Queso fundido (agotado) ni Flan napolitano (desactivado).

### CP-07 · Alerta de stock bajo

- **Objetivo:** Detectar los productos que llegaron a su existencia mínima o se agotaron.
- **Relacionado con:** RN-20, RF-21, RF-41
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT nombre, existencia, stock_minimo
FROM producto
WHERE activo = 1 AND existencia <= stock_minimo;
```

**Resultado esperado:** Regresa 3 productos: Molletes (4 de mínimo 4), Queso fundido (0 de mínimo 3) y Malteada de vainilla (5 de mínimo 5).

### CP-08 · Consultar un pedido por su folio

- **Objetivo:** Buscar un pedido por su folio y ver su detalle y estado actual.
- **Relacionado con:** RF-34, RF-29
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT p.folio, e.nombre AS estado, c.nombre AS cliente, pr.nombre AS producto,
       d.cantidad, d.precio_unitario, d.subtotal, p.total
FROM pedido p
JOIN estado_pedido e ON e.id_estado = p.id_estado
JOIN cliente c ON c.id_cliente = p.id_cliente
JOIN detalle_pedido d ON d.id_pedido = p.id_pedido
JOIN producto pr ON pr.id_producto = d.id_producto
WHERE p.folio = 'PED-0014';
```

**Resultado esperado:** Regresa 3 filas del pedido PED-0014, en estado Listo, de la clienta Ana Torres: Queso fundido (2), Arrachera con guarnición (1) y Refresco (1). Total: $505.00.

### CP-09 · Pedidos por estado

- **Objetivo:** Contar cuántos pedidos hay en cada estado, incluidos los cancelados.
- **Relacionado con:** RF-42, RN-07
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT e.nombre AS estado, COUNT(p.id_pedido) AS pedidos
FROM estado_pedido e
LEFT JOIN pedido p ON p.id_estado = e.id_estado
GROUP BY e.id_estado;
```

**Resultado esperado:** Recibido: 2. En preparación: 2. Listo: 1. Entregado: 11. Cancelado: 3. (Total: 19 pedidos).

### CP-10 · Tablero de cocina y tiempo de espera

- **Objetivo:** Ver los pedidos activos agrupados por estado y cuántos minutos llevan esperando.
- **Relacionado con:** RF-36, RF-37
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT p.folio, e.nombre AS estado,
       TIMESTAMPDIFF(MINUTE, p.fecha_hora, NOW()) AS minutos_espera
FROM pedido p JOIN estado_pedido e ON e.id_estado = p.id_estado
WHERE p.id_estado IN (1, 2, 3)
ORDER BY e.orden, p.fecha_hora;
```

**Resultado esperado:** Regresa 5 pedidos: PED-0018 y PED-0019 (Recibido), PED-0015 y PED-0016 (En preparación) y PED-0014 (Listo). Los minutos de espera cambian según la hora en que se ejecute.

### CP-11 · Historial de cambios de estado

- **Objetivo:** Ver quién y cuándo cambió el estado de un pedido.
- **Relacionado con:** RN-12, RF-33
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT e.nombre AS estado, u.usuario, h.fecha_hora
FROM historial_estado h
JOIN estado_pedido e ON e.id_estado = h.id_estado
JOIN usuario u ON u.id_usuario = h.id_usuario
JOIN pedido p ON p.id_pedido = h.id_pedido
WHERE p.folio = 'PED-0008'
ORDER BY h.fecha_hora;
```

**Resultado esperado:** 3 cambios en orden: Recibido (mlopez), En preparación (rramirez) y Cancelado (admin), cada uno con su fecha y hora.

### CP-12 · Cancelación con reintegro de existencias

- **Objetivo:** Comprobar que al cancelar un pedido las existencias regresaron al inventario.
- **Relacionado con:** RN-09, RF-19, RF-31
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT pr.nombre, m.tipo, m.cantidad, m.motivo
FROM movimiento_inventario m
JOIN producto pr ON pr.id_producto = m.id_producto
WHERE m.id_pedido = 8
ORDER BY m.id_movimiento;
```

**Resultado esperado:** 6 movimientos: 3 salidas (Pechuga a la plancha 2, Crema de elote 2, Limonada natural 2) y 3 entradas con las mismas cantidades y el motivo "Reintegro por cancelación de PED-0008".

### CP-13 · Pedido modificado en estado Recibido

- **Objetivo:** Comprobar que al quitar un producto de un pedido se devolvió su existencia.
- **Relacionado con:** RN-11, RF-30
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT pr.nombre, m.tipo, m.cantidad, m.motivo
FROM movimiento_inventario m
JOIN producto pr ON pr.id_producto = m.id_producto
WHERE m.id_pedido = 18 AND pr.nombre = 'Molletes';
```

**Resultado esperado:** 2 movimientos de Molletes: una salida de 1 pieza y una entrada de 1 pieza con el motivo "Reintegro por modificación de PED-0018". Los Molletes ya no aparecen en el detalle del pedido.

### CP-14 · Cálculo de subtotales y total

- **Objetivo:** Comprobar que el total de cada pedido es igual a la suma de sus subtotales.
- **Relacionado con:** RN-14, RN-15, RF-27
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT p.folio, p.total, SUM(d.subtotal) AS suma_subtotales
FROM pedido p JOIN detalle_pedido d ON d.id_pedido = p.id_pedido
GROUP BY p.id_pedido
HAVING p.total <> SUM(d.subtotal);
```

**Resultado esperado:** No regresa filas: ningún pedido tiene diferencias.

### CP-15 · Existencias consistentes con los movimientos

- **Objetivo:** Comprobar que la existencia de cada producto coincide con la suma de sus movimientos de inventario.
- **Relacionado con:** RN-05, RN-22, RF-20
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT pr.nombre, pr.existencia,
       SUM(CASE WHEN m.tipo = 'salida' THEN -m.cantidad ELSE m.cantidad END) AS segun_movimientos
FROM producto pr JOIN movimiento_inventario m ON m.id_producto = pr.id_producto
GROUP BY pr.id_producto
HAVING pr.existencia <> segun_movimientos;
```

**Resultado esperado:** No regresa filas: todas las existencias coinciden.

### CP-16 · Movimientos de un producto

- **Objetivo:** Consultar el historial de entradas, salidas y ajustes de un producto.
- **Relacionado con:** RF-22, RF-16
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT m.fecha_hora, m.tipo, m.cantidad, m.motivo, u.usuario
FROM movimiento_inventario m
JOIN usuario u ON u.id_usuario = m.id_usuario
WHERE m.id_producto = 17
ORDER BY m.fecha_hora;
```

**Resultado esperado:** Regresa 9 movimientos del Refresco: inventario inicial (30), resurtido de proveedor (24), 6 salidas por pedido y 1 reintegro por la cancelación de PED-0017. Existencia final: 44.

### CP-17 · Ventas del día

- **Objetivo:** Obtener el número de pedidos y el total vendido hoy, sin contar los cancelados.
- **Relacionado con:** RF-38, RF-39
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT COUNT(*) AS pedidos, SUM(total) AS total_vendido
FROM pedido
WHERE DATE(fecha_hora) = CURRENT_DATE AND id_estado <> 5;
```

**Resultado esperado:** 7 pedidos y un total de $1,975.00.

### CP-18 · Ventas por periodo

- **Objetivo:** Obtener el total vendido por día en los últimos 7 días, sin contar los cancelados.
- **Relacionado con:** RF-39
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT DATE(fecha_hora) AS dia, COUNT(*) AS pedidos, SUM(total) AS total_vendido
FROM pedido
WHERE fecha_hora >= CURRENT_DATE - INTERVAL 6 DAY AND id_estado <> 5
GROUP BY DATE(fecha_hora)
ORDER BY dia;
```

**Resultado esperado:** 7 días con ventas. En total suman 16 pedidos y $6,200.00 (el último día, hoy, con 7 pedidos y $1,975.00).

### CP-19 · Productos más vendidos

- **Objetivo:** Mostrar los productos con más unidades vendidas, sin contar pedidos cancelados.
- **Relacionado con:** RF-40
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT pr.nombre, SUM(d.cantidad) AS unidades
FROM detalle_pedido d
JOIN pedido p ON p.id_pedido = d.id_pedido
JOIN producto pr ON pr.id_producto = d.id_producto
WHERE p.id_estado <> 5
GROUP BY pr.id_producto
ORDER BY unidades DESC, pr.nombre
LIMIT 5;
```

**Resultado esperado:** Café americano (10), Refresco (10), Queso fundido (6), Chilaquiles verdes con pollo (5) y Jugo de naranja (5).

### CP-20 · Pedidos por cliente

- **Objetivo:** Contar los pedidos de cada cliente, incluido el cliente general de mostrador.
- **Relacionado con:** RF-24, RF-35, RN-26
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
SELECT c.nombre, c.telefono, COUNT(p.id_pedido) AS pedidos
FROM cliente c LEFT JOIN pedido p ON p.id_cliente = c.id_cliente
GROUP BY c.id_cliente;
```

**Resultado esperado:** Público en general / Mostrador: 9. Ana Torres: 3. Carlos Hernández: 3. Sofía Martínez: 2 (sin teléfono). Miguel Díaz: 2.

### CP-21 · Folio repetido

- **Objetivo:** Comprobar que no se puede registrar dos veces el mismo folio.
- **Relacionado con:** RN-01, RF-28
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
INSERT INTO pedido (folio, id_cliente, id_usuario, id_estado)
VALUES ('PED-0001', 1, 2, 1);
```

**Resultado esperado:** La base de datos lo rechaza. El mensaje de error depende del servidor:

- **XAMPP (MariaDB):** `Duplicate entry 'PED-0001' for key 'uq_pedido_folio'`
- **MySQL 8 / Workbench:** `Duplicate entry 'PED-0001' for key 'pedido.uq_pedido_folio'`

### CP-22 · Precio inválido

- **Objetivo:** Comprobar que no se puede registrar un producto con precio de cero.
- **Relacionado con:** RN-16, RF-15
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
INSERT INTO producto (id_categoria, nombre, precio, existencia, stock_minimo)
VALUES (1, 'Producto de prueba', 0, 5, 1);
```

**Resultado esperado:** La base de datos lo rechaza. El mensaje de error depende del servidor:

- **XAMPP (MariaDB):** `` CONSTRAINT `chk_producto_precio` failed ``
- **MySQL 8 / Workbench:** `Check constraint 'chk_producto_precio' is violated.`

### CP-23 · Existencia negativa

- **Objetivo:** Comprobar que la existencia de un producto nunca puede quedar por debajo de cero.
- **Relacionado con:** RN-06
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
UPDATE producto SET existencia = -1 WHERE id_producto = 4;
```

**Resultado esperado:** La base de datos lo rechaza. El mensaje de error depende del servidor:

- **XAMPP (MariaDB):** `` CONSTRAINT `chk_producto_existencia` failed ``
- **MySQL 8 / Workbench:** `Check constraint 'chk_producto_existencia' is violated.`

### CP-24 · Cantidad inválida en un pedido

- **Objetivo:** Comprobar que no se puede pedir una cantidad menor a 1.
- **Relacionado con:** RN-02, RN-03
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
VALUES (19, 14, 0, 45.00, 0);
```

**Resultado esperado:** La base de datos lo rechaza. El mensaje de error depende del servidor:

- **XAMPP (MariaDB):** `` CONSTRAINT `chk_detalle_cantidad` failed ``
- **MySQL 8 / Workbench:** `Check constraint 'chk_detalle_cantidad' is violated.`

### CP-25 · Subtotal mal calculado

- **Objetivo:** Comprobar que el subtotal debe ser exactamente cantidad × precio unitario.
- **Relacionado con:** RN-14
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
VALUES (19, 14, 2, 45.00, 100.00);
```

**Resultado esperado:** La base de datos lo rechaza (2 × 45.00 = 90.00, no 100.00). El mensaje de error depende del servidor:

- **XAMPP (MariaDB):** `` CONSTRAINT `chk_detalle_subtotal` failed ``
- **MySQL 8 / Workbench:** `Check constraint 'chk_detalle_subtotal' is violated.`

### CP-26 · Pedido de un cliente que no existe

- **Objetivo:** Comprobar que un pedido no puede apuntar a un cliente que no está registrado.
- **Relacionado con:** Integridad referencial
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
INSERT INTO pedido (folio, id_cliente, id_usuario, id_estado)
VALUES ('PED-0099', 99, 2, 1);
```

**Resultado esperado:** La base de datos lo rechaza con el error (igual en MariaDB y en MySQL 8): Cannot add or update a child row: a foreign key constraint fails (fk_pedido_cliente).

### CP-27 · Borrado de un producto con pedidos

- **Objetivo:** Comprobar que un producto con pedidos no se puede borrar; solo se puede desactivar.
- **Relacionado con:** RN-21, RF-14
- **Tipo:** ❌ Negativo (debe ser rechazado)

```sql
DELETE FROM producto WHERE id_producto = 1;
```

**Resultado esperado:** La base de datos lo rechaza con el error (igual en MariaDB y en MySQL 8): Cannot delete or update a parent row: a foreign key constraint fails (fk_detalle_producto). Para darlo de baja se usa: `UPDATE producto SET activo = 0 WHERE id_producto = 1;`

### CP-28 · Precio congelado en pedidos anteriores

- **Objetivo:** Comprobar que si cambia el precio de un producto, los pedidos ya registrados conservan el precio anterior.
- **Relacionado con:** RN-13
- **Tipo:** ✅ Positivo (debe funcionar)

```sql
UPDATE producto SET precio = 139.00 WHERE id_producto = 1;
SELECT d.id_pedido, d.precio_unitario
FROM detalle_pedido d WHERE d.id_producto = 1;
```

**Resultado esperado:** El precio del producto cambia a $139.00, pero los 3 pedidos que ya tenían Chilaquiles verdes con pollo conservan $129.00. Importante: este caso sí modifica datos; al terminar, vuelve a importar sistema_pedidos_datos.sql.

---

[↑ Volver al inicio](#datos-y-casos-de-prueba) · [← Volver al README](../README.md)
