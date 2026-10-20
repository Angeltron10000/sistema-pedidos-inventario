# Planeación del proyecto

**UNAM · Facultad de Contaduría y Administración · Informática**
**Implementación de Sistemas** — Quinto semestre · Grupo 1592

## Proyecto 2 — Sistema de pedidos e inventario para un local de comida

**Equipo N.º 11** — Integrantes (en orden alfabético por apellido):

| N.º | Apellido paterno | Apellido materno | Nombre(s)     |
|:---:|------------------|------------------|---------------|
| 1   | Chávez           | Rodríguez        | Cédric Ulises |
| 2   | Solano           | Reveles          | Ángel Cédric  |
| 3   | Soriano          | Aguilar          | José Ángel    |

> Versión en Markdown del documento *Planeación de los proyectos por equipo*. [← Volver al README](../README.md)

## Contenido

- [1. Definición de la problemática](#1-definición-de-la-problemática)
- [2. Necesidades a cubrir](#2-necesidades-a-cubrir)
- [3. Nombre del proyecto](#3-nombre-del-proyecto)
- [4. Objetivos](#4-objetivos)
- [5. Lista de alcances](#5-lista-de-alcances)
- [6. Reglas de negocio](#6-reglas-de-negocio)
- [7. Requisitos funcionales](#7-requisitos-funcionales)
- [8. Diagrama Entidad – Relación](#8-diagrama-entidad--relación)
- [9. Panel administrativo](#9-panel-administrativo)

---

## 1. Definición de la problemática

El establecimiento de comida, actualmente, el registro de los productos, de las existencias y de los pedidos de los clientes se realiza de forma manual (en libreta, notas o de memoria), lo que dificulta el control de la operación diaria.

Al no contar con una herramienta que concentre esta información, el personal no sabe con certeza qué productos están disponibles en un momento dado, por lo que pueden aceptarse pedidos de productos agotados. Las existencias no se actualizan al momento de vender, y las diferencias entre lo que se registra y lo que realmente hay en almacén solo se descubren cuando el producto ya se terminó.

Tampoco existe un mecanismo para identificar y dar seguimiento a cada pedido. Sin un número de pedido ni un estado definido, es común que se confundan órdenes, que se preparen dos veces o que se olviden, y que el cliente no sepa si su pedido fue recibido, si se está preparando o si ya está listo. Además, al capturar cantidades a mano se cometen errores en los totales cobrados y en las cantidades descontadas del inventario.

Por último, el encargado no dispone de información confiable para tomar decisiones: no sabe cuántos pedidos se atendieron en el día, cuáles son los productos más vendidos ni qué productos debe resurtir.

### Problemas identificados

- Desconocimiento de los productos disponibles y de sus existencias reales.
- Venta de productos agotados por falta de actualización del inventario.
- Errores al registrar las cantidades solicitadas y al calcular el total del pedido.
- Pedidos sin un identificador único, lo que provoca confusiones, duplicados u olvidos.
- Falta de seguimiento del estado de los pedidos (recibido, en preparación, listo, entregado).
- Ausencia de información para consultar ventas, pedidos pendientes y productos por resurtir.

## 2. Necesidades a cubrir

A partir de la problemática, el establecimiento necesita:

- **Control de productos:** registrar, consultar, modificar y dar de baja los productos que ofrece, organizados por categoría, con su precio y descripción.
- **Control de existencias:** conocer en todo momento la cantidad disponible de cada producto y registrar entradas (resurtido) y ajustes de inventario.
- **Actualización automática del inventario:** que las existencias disminuyan al registrar un pedido y se restablezcan si éste se cancela.
- **Registro de pedidos:** capturar los productos y cantidades que solicita el cliente, validando que haya existencias suficientes y calculando el total sin errores.
- **Identificación de pedidos:** generar automáticamente un número (folio) único para cada pedido.
- **Seguimiento de pedidos:** manejar los estados recibido, en preparación, listo y entregado, registrando quién y cuándo realizó cada cambio.
- **Alertas:** avisar cuando un producto llegue a su existencia mínima o se agote.
- **Consultas y reportes:** obtener información de pedidos por estado y por fecha, ventas del día, productos más vendidos y estado del inventario.
- **Seguridad:** que cada empleado acceda con su usuario y contraseña, y solo a las funciones que corresponden a su rol.

## 3. Nombre del proyecto

**Proyecto 2 — Sistema de pedidos e inventario para un local de comida**

## 4. Objetivos

### 4.1 Objetivo general

Desarrollar un sistema que permita organizar y controlar el proceso de pedidos del establecimiento de comida, desde el registro de los productos y sus existencias hasta la entrega del pedido al cliente, generando un número de identificación para cada pedido, dando seguimiento a sus estados y manteniendo actualizado el inventario, con el fin de reducir errores y facilitar la toma de decisiones.

### 4.2 Objetivos específicos

1. Implementar un módulo de administración de productos y categorías que permita registrar, consultar, modificar y desactivar productos.
2. Implementar el control de existencias, con registro de entradas, salidas y ajustes, y alertas de existencia mínima.
3. Desarrollar el registro de pedidos con validación de existencias y cálculo automático de subtotales y total.
4. Generar automáticamente un folio único y consecutivo para cada pedido.
5. Implementar el seguimiento de estados del pedido (recibido, en preparación, listo y entregado) con historial de cambios.
6. Actualizar automáticamente el inventario al registrar o cancelar un pedido.
7. Ofrecer consultas y reportes de pedidos, ventas e inventario en un panel administrativo.
8. Controlar el acceso al sistema mediante usuarios y roles.

## 5. Lista de alcances

### 5.1 El sistema sí incluirá

1. Inicio de sesión con usuario y contraseña, y control de acceso por rol (Administrador, Cajero/Mesero, Cocina).
2. Administración (alta, consulta, modificación y baja lógica) de categorías y productos.
3. Control de existencias por producto: entradas por resurtido, ajustes de inventario y existencia mínima.
4. Descuento automático de existencias al registrar un pedido y reintegro al cancelarlo.
5. Registro de pedidos con uno o varios productos, cantidades, notas y tipo de servicio (en local / para llevar).
6. Generación automática de un folio único para cada pedido.
7. Seguimiento de pedidos por estados: Recibido → En preparación → Listo → Entregado (y Cancelado).
8. Tablero de cocina para visualizar y avanzar el estado de los pedidos.
9. Búsqueda de pedidos por folio, estado, fecha o cliente.
10. Registro básico de clientes (nombre y teléfono) de forma opcional.
11. Panel administrativo con indicadores: pedidos del día, pendientes, listos y productos con stock bajo.
12. Reportes de ventas por día o periodo, productos más vendidos, inventario actual y movimientos de inventario.
13. Administración de usuarios y asignación de roles.
14. Historial de cambios de estado de cada pedido (usuario, fecha y hora).

### 5.2 Fuera del alcance

- Cobro con tarjeta, pagos en línea o facturación electrónica (CFDI).
- Pedidos en línea por parte del cliente (aplicación móvil o página web pública).
- Servicio de entrega a domicilio y rastreo de repartidores.
- Control de recetas o insumos (materia prima) por producto.
- Manejo de varias sucursales.

## 6. Reglas de negocio

Tipos: Restricción (limita lo que se puede hacer), Cálculo (fórmula o valor derivado), Inferencia (acción que el sistema deduce o ejecuta a partir de una condición) y Hecho (dato o política del negocio).

| ID    | Regla                   | Descripción                                                                                                                                                          | Tipo    |
|-----------|-----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------|
| RN-01 | Folio único                 | Cada pedido recibe automáticamente un folio único y consecutivo con el formato PED-#### (p. ej. PED-0048). El folio no puede modificarse ni repetirse.                   | Cálculo     |
| RN-02 | Contenido mínimo del pedido | Un pedido debe contener al menos un producto con cantidad mayor a cero.                                                                                                  | Restricción |
| RN-03 | Cantidad válida             | La cantidad de cada producto en un pedido debe ser un número entero mayor o igual a 1. | Restricción |
| RN-04 | Validación de existencias   | No se puede registrar un pedido si la cantidad solicitada de algún producto es mayor que su existencia disponible.                                                       | Restricción |
| RN-05 | Descuento de inventario     | Al registrar un pedido (estado Recibido), la existencia de cada producto se reduce en la cantidad solicitada y se genera un movimiento de tipo "salida".                 | Inferencia  |
| RN-06 | Existencia no negativa      | La existencia de un producto nunca puede ser menor que cero.                                                                                                             | Restricción |
| RN-07 | Estado inicial              | Todo pedido se crea con el estado "Recibido".                                                                                                                            | Hecho       |
| RN-08 | Secuencia de estados        | Los estados avanzan en orden: Recibido → En preparación → Listo → Entregado. No se puede saltar ni regresar a un estado anterior.                                        | Restricción |
| RN-09 | Cancelación                 | Un pedido solo puede cancelarse mientras está en estado "Recibido" o "En preparación"; al cancelarse, las existencias se reintegran con un movimiento de tipo "entrada". | Restricción |
| RN-10 | Pedido cerrado              | Un pedido en estado "Entregado" o "Cancelado" ya no puede modificarse.                                                                                                   | Restricción |
| RN-11 | Modificación de pedido      | Solo se pueden agregar o quitar productos de un pedido mientras está en estado "Recibido"; el inventario se ajusta según el cambio.                                      | Restricción |
| RN-12 | Historial de estados        | Cada cambio de estado se registra con el usuario que lo realizó y la fecha y hora.                                                                                       | Hecho       |
| RN-13 | Precio congelado            | El precio unitario se guarda en el detalle al momento del pedido; un cambio posterior de precio del producto no afecta pedidos ya registrados.                           | Hecho       |
| RN-14 | Subtotal                    | Subtotal = cantidad × precio unitario.                                                                                                                                   | Cálculo     |
| RN-15 | Total del pedido            | Total = suma de los subtotales de todos los productos del pedido.                                                                                                        | Cálculo     |
| RN-16 | Precio válido               | El precio de un producto debe ser mayor que cero.                                                                                                                        | Restricción |
| RN-17 | Nombre de producto único    | No pueden existir dos productos activos con el mismo nombre.                                                                                                             | Restricción |
| RN-18 | Categoría obligatoria       | Todo producto debe pertenecer a una categoría.                                                                                                                           | Restricción |
| RN-19 | Producto no disponible      | Un producto inactivo o con existencia 0 no se muestra para agregarse a nuevos pedidos.                                                                                   | Inferencia  |
| RN-20 | Alerta de stock             | Cuando la existencia de un producto es menor o igual a su existencia mínima, el sistema muestra una alerta.                                                              | Inferencia  |
| RN-21 | Baja lógica                 | Los productos, categorías y usuarios no se eliminan físicamente si tienen registros relacionados; solo se desactivan.                                                    | Restricción |
| RN-22 | Movimientos de inventario   | Toda entrada, salida o ajuste de inventario queda registrado con usuario, fecha, cantidad y motivo.                                                                      | Hecho       |
| RN-23 | Ajuste con motivo           | Un ajuste manual de inventario requiere indicar un motivo (merma, conteo físico, etc.) y solo lo puede hacer el Administrador.                                           | Restricción |
| RN-24 | Acceso autenticado          | Solo usuarios registrados y activos pueden ingresar al sistema.                                                                                                          | Restricción |
| RN-25 | Permisos por rol            | Administrador: acceso total. Cajero/Mesero: registrar y entregar pedidos, consultar productos. Cocina: consultar pedidos y cambiarlos a "En preparación" y "Listo".      | Restricción |
| RN-26 | Cliente opcional            | El registro del cliente es opcional; si no se captura, el pedido se asigna a "Público en general / Mostrador".                                                           | Hecho       |
| RN-27 | Horario de operación        | Los pedidos solo se registran dentro del horario de servicio del establecimiento: \[hora de apertura\] a \[hora de cierre\].                                             | Restricción |

## 7. Requisitos funcionales

Prioridad: Alta (indispensable para operar), Media (importante, puede entregarse después), Baja (deseable).

| ID    | Requisito                         | Descripción                                                                                             | Módulo | Prioridad |
|-----------|---------------------------------------|-------------------------------------------------------------------------------------------------------------|------------|---------------|
| RF-01 | Iniciar sesión                        | Permitir el acceso al sistema mediante usuario y contraseña.                                                | Acceso     | Alta          |
| RF-02 | Cerrar sesión                         | Permitir al usuario terminar su sesión de forma segura.                                                     | Acceso     | Alta          |
| RF-03 | Controlar acceso por rol              | Mostrar solo las opciones permitidas según el rol del usuario.                                              | Acceso     | Alta          |
| RF-04 | Registrar usuario                     | Dar de alta empleados con nombre, apellidos, usuario, contraseña y rol.                                     | Usuarios   | Alta          |
| RF-05 | Consultar usuarios                    | Listar y buscar usuarios por nombre o rol.                                                                  | Usuarios   | Media         |
| RF-06 | Modificar usuario                     | Editar los datos y el rol de un usuario.                                                                    | Usuarios   | Media         |
| RF-07 | Desactivar usuario                    | Impedir el acceso a un usuario sin eliminar su historial.                                                   | Usuarios   | Media         |
| RF-08 | Cambiar contraseña                    | Permitir al usuario o al administrador cambiar la contraseña.                                               | Usuarios   | Baja          |
| RF-09 | Registrar categoría                   | Crear categorías de productos (p. ej. \[categoría 1\], \[categoría 2\], bebidas, postres).                  | Categorías | Alta          |
| RF-10 | Modificar / desactivar categoría      | Editar el nombre de una categoría o desactivarla.                                                           | Categorías | Media         |
| RF-11 | Registrar producto                    | Dar de alta un producto con nombre, descripción, categoría, precio, existencia inicial y existencia mínima. | Productos  | Alta          |
| RF-12 | Consultar productos                   | Listar productos con su precio, existencia y estado; filtrar por categoría y buscar por nombre.             | Productos  | Alta          |
| RF-13 | Modificar producto                    | Editar los datos de un producto (nombre, descripción, precio, categoría, existencia mínima).                | Productos  | Alta          |
| RF-14 | Desactivar producto                   | Dar de baja lógica un producto para que no aparezca en nuevos pedidos.                                      | Productos  | Media         |
| RF-15 | Validar datos de producto             | Validar precio mayor a cero, nombre único y categoría obligatoria.                                          | Productos  | Alta          |
| RF-16 | Registrar entrada de inventario       | Aumentar la existencia de un producto por resurtido, indicando cantidad y motivo.                           | Inventario | Alta          |
| RF-17 | Registrar ajuste de inventario        | Corregir la existencia (merma, conteo físico) indicando el motivo.                                          | Inventario | Media         |
| RF-18 | Descontar existencias automáticamente | Reducir la existencia de cada producto al registrar un pedido.                                              | Inventario | Alta          |
| RF-19 | Reintegrar existencias                | Regresar las existencias al cancelar o modificar un pedido.                                                 | Inventario | Alta          |
| RF-20 | Consultar existencias                 | Mostrar la existencia actual de todos los productos.                                                        | Inventario | Alta          |
| RF-21 | Alertar stock bajo                    | Mostrar alertas de productos con existencia menor o igual a la mínima o agotados.                           | Inventario | Alta          |
| RF-22 | Consultar movimientos                 | Consultar el historial de entradas, salidas y ajustes por producto y por fecha.                             | Inventario | Media         |
| RF-23 | Registrar cliente                     | Registrar opcionalmente nombre y teléfono del cliente.                                                      | Clientes   | Baja          |
| RF-24 | Buscar cliente                        | Buscar un cliente por nombre o teléfono al registrar un pedido.                                             | Clientes   | Baja          |
| RF-25 | Registrar pedido                      | Crear un pedido agregando productos disponibles, cantidades, notas y tipo de servicio.                      | Pedidos    | Alta          |
| RF-26 | Validar existencias en pedido         | Impedir agregar una cantidad mayor a la existencia disponible e informar al usuario.                        | Pedidos    | Alta          |
| RF-27 | Calcular totales                      | Calcular automáticamente el subtotal de cada producto y el total del pedido.                                | Pedidos    | Alta          |
| RF-28 | Generar folio                         | Asignar automáticamente un folio único y consecutivo al guardar el pedido.                                  | Pedidos    | Alta          |
| RF-29 | Mostrar comprobante                   | Mostrar (o imprimir) un resumen del pedido con folio, productos, cantidades y total.                        | Pedidos    | Media         |
| RF-30 | Modificar pedido                      | Agregar o quitar productos mientras el pedido esté en estado "Recibido".                                    | Pedidos    | Media         |
| RF-31 | Cancelar pedido                       | Cancelar un pedido en estado "Recibido" o "En preparación" registrando el motivo.                           | Pedidos    | Alta          |
| RF-32 | Cambiar estado del pedido             | Avanzar el pedido al siguiente estado respetando la secuencia definida.                                     | Pedidos    | Alta          |
| RF-33 | Registrar historial de estados        | Guardar usuario, fecha y hora de cada cambio de estado.                                                     | Pedidos    | Alta          |
| RF-34 | Consultar pedido por folio            | Buscar un pedido por su folio y mostrar su detalle y estado actual.                                         | Pedidos    | Alta          |
| RF-35 | Listar pedidos                        | Listar pedidos filtrando por estado, fecha, cliente o tipo de servicio.                                     | Pedidos    | Alta          |
| RF-36 | Tablero de cocina                     | Mostrar los pedidos activos agrupados por estado para su preparación.                                       | Pedidos    | Alta          |
| RF-37 | Mostrar tiempo de espera              | Indicar el tiempo transcurrido desde que se recibió cada pedido.                                            | Pedidos    | Baja          |
| RF-38 | Panel de indicadores                  | Mostrar pedidos del día, pedidos pendientes, listos para entregar y productos con stock bajo.               | Panel      | Alta          |
| RF-39 | Reporte de ventas                     | Generar el total vendido y el número de pedidos por día o por periodo.                                      | Reportes   | Media         |
| RF-40 | Productos más vendidos                | Mostrar los productos con más unidades vendidas en un periodo.                                              | Reportes   | Media         |
| RF-41 | Reporte de inventario                 | Generar el listado de existencias actuales y productos por resurtir.                                        | Reportes   | Media         |
| RF-42 | Reporte de pedidos por estado         | Mostrar la cantidad de pedidos en cada estado, incluidos los cancelados.                                    | Reportes   | Media         |
| RF-43 | Exportar reportes                     | Exportar los reportes a PDF o Excel.                                                                        | Reportes   | Baja          |

## 8. Diagrama Entidad – Relación

El siguiente diagrama muestra las entidades del sistema, sus atributos (🔑 = llave primaria, FK = llave foránea) y la cardinalidad de sus relaciones.

![Diagrama entidad-relación](img/diagrama-entidad-relacion.png)

### 8.1 Descripción de entidades

| Entidad               | Descripción                                                                     |
|---------------------------|-------------------------------------------------------------------------------------|
| ROL                   | Tipos de usuario del sistema (Administrador, Cajero/Mesero, Cocina).                |
| USUARIO               | Empleados que utilizan el sistema; cada uno tiene un rol.                           |
| CLIENTE               | Persona que realiza el pedido (registro opcional).                                  |
| CATEGORIA             | Agrupación de productos (p. ej. \[categoría 1\], bebidas, postres).                 |
| PRODUCTO              | Artículos que vende el establecimiento, con precio, existencia y existencia mínima. |
| PEDIDO                | Orden realizada por un cliente; tiene folio único, estado actual y total.           |
| DETALLE_PEDIDO        | Productos incluidos en un pedido, con cantidad, precio unitario y subtotal.         |
| ESTADO_PEDIDO         | Catálogo de estados: Recibido, En preparación, Listo, Entregado, Cancelado.         |
| HISTORIAL_ESTADO      | Registro de cada cambio de estado de un pedido (quién y cuándo).                    |
| MOVIMIENTO_INVENTARIO | Entradas, salidas y ajustes de existencias de cada producto.                        |

### 8.2 Relaciones

| Relación                               | Cardinalidad | Descripción                                                                                  |
|--------------------------------------------|------------------|--------------------------------------------------------------------------------------------------|
| ROL – USUARIO                              | 1 : N            | Un rol se asigna a muchos usuarios; un usuario tiene un solo rol.                                |
| CLIENTE – PEDIDO                           | 1 : N            | Un cliente puede realizar muchos pedidos.                                                        |
| USUARIO – PEDIDO                           | 1 : N            | Un usuario registra muchos pedidos.                                                              |
| ESTADO_PEDIDO – PEDIDO                     | 1 : N            | Un estado lo pueden tener muchos pedidos; cada pedido tiene un estado actual.                    |
| PEDIDO – DETALLE_PEDIDO                    | 1 : N            | Un pedido contiene uno o más productos.                                                          |
| PRODUCTO – DETALLE_PEDIDO                  | 1 : N            | Un producto puede aparecer en muchos pedidos (resuelve la relación N:M entre PEDIDO y PRODUCTO). |
| CATEGORIA – PRODUCTO                       | 1 : N            | Una categoría agrupa muchos productos.                                                           |
| PEDIDO – HISTORIAL_ESTADO                  | 1 : N            | Un pedido tiene varios registros de cambio de estado.                                            |
| ESTADO_PEDIDO / USUARIO – HISTORIAL_ESTADO | 1 : N            | Cada registro del historial indica el estado asignado y el usuario que lo cambió.                |
| PRODUCTO – MOVIMIENTO_INVENTARIO           | 1 : N            | Un producto tiene muchos movimientos de inventario.                                              |
| USUARIO – MOVIMIENTO_INVENTARIO            | 1 : N            | Un usuario registra muchos movimientos.                                                          |
| PEDIDO – MOVIMIENTO_INVENTARIO             | 1 : N (opcional) | Las salidas y reintegros generados por un pedido quedan ligados a él.                            |

## 9. Panel administrativo

El panel administrativo es la interfaz principal del sistema. Desde un menú lateral, cada usuario accede únicamente a los módulos que le permite su rol. La pantalla de inicio concentra los indicadores del día para que el encargado conozca de un vistazo el estado de los pedidos y de las existencias.

![Diagrama de jerarquía del panel administrativo](img/panel-administrativo.png)

*Diagrama de jerarquía del panel administrativo.*
