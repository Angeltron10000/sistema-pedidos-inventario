-- =====================================================================
--  PROYECTO 2 · Equipo 11
--  Sistema de pedidos e inventario para un local de comida
--  Script de creación de la base de datos para XAMPP (MariaDB/MySQL)
--  VERSIÓN CON DATOS DE PRUEBA (sistema_pedidos_datos.sql)
--
--  Basado en el Diagrama Entidad–Relación (sección 8, página 8)
--  del documento "Planeación de los proyectos por equipo".
--
--  Compatible con XAMPP (MariaDB) y con MySQL 8 / MySQL Workbench.
--
--  CÓMO USARLO EN XAMPP (Ubuntu):
--    Opción A (phpMyAdmin): abre http://localhost/phpmyadmin,
--      pestaña "Importar", elige este archivo y pulsa "Continuar".
--    Opción B (terminal):
--      sudo /opt/lampp/bin/mysql -u root < sistema_pedidos_datos.sql
--
--    Opción C (MySQL Workbench): File > Open SQL Script, elige este
--      archivo y pulsa el rayo "Execute all" (no Ctrl + Enter).
--
--  ATENCIÓN: si la base "sistema_pedidos" ya existe, se BORRA y se
--  vuelve a crear desde cero.
-- =====================================================================

-- Indica que este archivo está en UTF-8 para que los acentos y la ñ
-- se guarden correctamente, sin importar el programa que lo ejecute.
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS sistema_pedidos;
CREATE DATABASE sistema_pedidos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sistema_pedidos;

-- ---------------------------------------------------------------------
-- 1. ROL: tipos de usuario (Administrador, Cajero/Mesero, Cocina)
-- ---------------------------------------------------------------------
CREATE TABLE rol (
  id_rol       INT          NOT NULL AUTO_INCREMENT,
  nombre       VARCHAR(30)  NOT NULL,
  descripcion  VARCHAR(100) NULL,
  PRIMARY KEY (id_rol)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. USUARIO: empleados que usan el sistema (cada uno tiene un rol)
--    Relación ROL 1 : N USUARIO
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
  id_usuario       INT          NOT NULL AUTO_INCREMENT,
  id_rol           INT          NOT NULL,
  nombre           VARCHAR(60)  NOT NULL,
  apellidos        VARCHAR(80)  NOT NULL,
  usuario          VARCHAR(30)  NOT NULL,
  contrasena_hash  VARCHAR(255) NOT NULL,
  activo           BOOLEAN      NOT NULL DEFAULT TRUE,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuario_usuario (usuario),
  CONSTRAINT fk_usuario_rol
    FOREIGN KEY (id_rol) REFERENCES rol (id_rol)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. CLIENTE: persona que hace el pedido (registro opcional)
-- ---------------------------------------------------------------------
CREATE TABLE cliente (
  id_cliente  INT         NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(80) NOT NULL,
  telefono    VARCHAR(15) NULL,
  PRIMARY KEY (id_cliente)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. ESTADO_PEDIDO: catálogo de estados
--    (Recibido, En preparación, Listo, Entregado, Cancelado)
--    "orden" indica la secuencia en que avanzan los estados.
-- ---------------------------------------------------------------------
CREATE TABLE estado_pedido (
  id_estado  INT         NOT NULL AUTO_INCREMENT,
  nombre     VARCHAR(20) NOT NULL,
  orden      INT         NOT NULL,
  PRIMARY KEY (id_estado)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. CATEGORIA: agrupación de productos
-- ---------------------------------------------------------------------
CREATE TABLE categoria (
  id_categoria  INT         NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_categoria)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. PRODUCTO: artículos que vende el establecimiento
--    Relación CATEGORIA 1 : N PRODUCTO
--    Validaciones: RN-16 (precio > 0) y RN-06 (existencia >= 0)
-- ---------------------------------------------------------------------
CREATE TABLE producto (
  id_producto   INT           NOT NULL AUTO_INCREMENT,
  id_categoria  INT           NOT NULL,
  nombre        VARCHAR(80)   NOT NULL,
  descripcion   VARCHAR(200)  NULL,
  precio        DECIMAL(10,2) NOT NULL,
  existencia    INT           NOT NULL DEFAULT 0,
  stock_minimo  INT           NOT NULL DEFAULT 0,
  activo        BOOLEAN       NOT NULL DEFAULT TRUE,
  PRIMARY KEY (id_producto),
  CONSTRAINT fk_producto_categoria
    FOREIGN KEY (id_categoria) REFERENCES categoria (id_categoria)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_producto_precio       CHECK (precio > 0),
  CONSTRAINT chk_producto_existencia   CHECK (existencia >= 0),
  CONSTRAINT chk_producto_stock_minimo CHECK (stock_minimo >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. PEDIDO: orden realizada por un cliente
--    Relaciones: CLIENTE 1:N PEDIDO, USUARIO 1:N PEDIDO,
--                ESTADO_PEDIDO 1:N PEDIDO (estado actual)
--    RN-01: folio único con formato PED-#### (lo genera el sistema)
-- ---------------------------------------------------------------------
CREATE TABLE pedido (
  id_pedido      INT                    NOT NULL AUTO_INCREMENT,
  folio          VARCHAR(20)            NOT NULL,
  id_cliente     INT                    NOT NULL,
  id_usuario     INT                    NOT NULL,
  id_estado      INT                    NOT NULL,
  fecha_hora     DATETIME               NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tipo_servicio  ENUM('local','llevar') NOT NULL DEFAULT 'local',
  total          DECIMAL(10,2)          NOT NULL DEFAULT 0.00,
  observaciones  VARCHAR(200)           NULL,
  PRIMARY KEY (id_pedido),
  UNIQUE KEY uq_pedido_folio (folio),
  CONSTRAINT fk_pedido_cliente
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pedido_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pedido_estado
    FOREIGN KEY (id_estado) REFERENCES estado_pedido (id_estado)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_pedido_total CHECK (total >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. DETALLE_PEDIDO: productos incluidos en cada pedido
--    Relaciones: PEDIDO 1:N DETALLE_PEDIDO, PRODUCTO 1:N DETALLE_PEDIDO
--    RN-03: cantidad >= 1 · RN-13: precio_unitario se guarda "congelado"
--    RN-14: subtotal = cantidad × precio_unitario
-- ---------------------------------------------------------------------
CREATE TABLE detalle_pedido (
  id_detalle       INT           NOT NULL AUTO_INCREMENT,
  id_pedido        INT           NOT NULL,
  id_producto      INT           NOT NULL,
  cantidad         INT           NOT NULL,
  precio_unitario  DECIMAL(10,2) NOT NULL,
  subtotal         DECIMAL(10,2) NOT NULL,
  notas            VARCHAR(100)  NULL,
  PRIMARY KEY (id_detalle),
  CONSTRAINT fk_detalle_pedido
    FOREIGN KEY (id_pedido) REFERENCES pedido (id_pedido)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto
    FOREIGN KEY (id_producto) REFERENCES producto (id_producto)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_detalle_cantidad CHECK (cantidad >= 1),
  CONSTRAINT chk_detalle_precio   CHECK (precio_unitario > 0),
  CONSTRAINT chk_detalle_subtotal CHECK (subtotal = cantidad * precio_unitario)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. HISTORIAL_ESTADO: cada cambio de estado de un pedido (quién y cuándo)
--    Relaciones: PEDIDO 1:N, ESTADO_PEDIDO 1:N, USUARIO 1:N
-- ---------------------------------------------------------------------
CREATE TABLE historial_estado (
  id_historial  INT      NOT NULL AUTO_INCREMENT,
  id_pedido     INT      NOT NULL,
  id_estado     INT      NOT NULL,
  id_usuario    INT      NOT NULL,
  fecha_hora    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_historial),
  CONSTRAINT fk_historial_pedido
    FOREIGN KEY (id_pedido) REFERENCES pedido (id_pedido)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_historial_estado
    FOREIGN KEY (id_estado) REFERENCES estado_pedido (id_estado)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_historial_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. MOVIMIENTO_INVENTARIO: entradas, salidas y ajustes de existencias
--     Relaciones: PRODUCTO 1:N, USUARIO 1:N,
--                 PEDIDO 1:N (opcional → id_pedido puede ser NULL)
-- ---------------------------------------------------------------------
CREATE TABLE movimiento_inventario (
  id_movimiento  INT                                 NOT NULL AUTO_INCREMENT,
  id_producto    INT                                 NOT NULL,
  id_usuario     INT                                 NOT NULL,
  id_pedido      INT                                 NULL,
  tipo           ENUM('entrada','salida','ajuste')   NOT NULL,
  cantidad       INT                                 NOT NULL,
  fecha_hora     DATETIME                            NOT NULL DEFAULT CURRENT_TIMESTAMP,
  motivo         VARCHAR(120)                        NULL,
  PRIMARY KEY (id_movimiento),
  CONSTRAINT fk_movimiento_producto
    FOREIGN KEY (id_producto) REFERENCES producto (id_producto)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_movimiento_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_movimiento_pedido
    FOREIGN KEY (id_pedido) REFERENCES pedido (id_pedido)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
--  DATOS INICIALES DE LOS CATÁLOGOS
-- =====================================================================

-- Roles (RN-25)
INSERT INTO rol (nombre, descripcion) VALUES
  ('Administrador', 'Acceso total al sistema'),
  ('Cajero/Mesero', 'Registra y entrega pedidos; consulta productos'),
  ('Cocina',        'Consulta pedidos y los cambia a En preparación y Listo');

-- Estados del pedido (RN-07, RN-08). "orden" = secuencia; 0 = Cancelado
INSERT INTO estado_pedido (nombre, orden) VALUES
  ('Recibido',       1),
  ('En preparación', 2),
  ('Listo',          3),
  ('Entregado',      4),
  ('Cancelado',      0);

-- Cliente por defecto cuando no se captura uno (RN-26)
INSERT INTO cliente (nombre, telefono) VALUES
  ('Público en general / Mostrador', NULL);


-- =====================================================================
--  DATOS DE PRUEBA
--  Negocio de ejemplo: restaurante familiar de menú general
--  (desayunos, entradas, sopas, platos fuertes, bebidas y postres).
--
--  Contraseña de TODOS los usuarios de prueba: Prueba123
--  (guardada cifrada con bcrypt, compatible con password_verify() de PHP)
--
--  Las fechas se calculan a partir del día en que se ejecuta el script:
--  hay pedidos de los últimos 6 días y pedidos de "hoy" (de las últimas
--  horas), para que los reportes y el panel siempre muestren datos.
--
--  CASOS QUE CUBREN ESTOS DATOS
--   · Usuarios de los 3 roles y 1 usuario desactivado (RF-07, RN-24)
--   · Clientes con y sin teléfono, y pedidos a "Público en general" (RN-26)
--   · Productos con stock normal, con stock bajo (Molletes, Malteada),
--     agotado (Queso fundido) e inactivo (Flan napolitano) (RN-19, RN-20)
--   · Pedidos en TODOS los estados: Recibido, En preparación, Listo,
--     Entregado y Cancelado (RN-07, RN-08)
--   · Cancelación desde "Recibido" y desde "En preparación", con
--     reintegro de existencias (RN-09, RF-19)
--   · Pedido modificado en estado "Recibido" (se quitó un producto) (RN-11)
--   · Pedidos "en local" y "para llevar", con notas y observaciones
--   · Historial completo de cambios de estado con usuario y hora (RN-12)
--   · Movimientos de inventario: inventario inicial, resurtidos,
--     salidas por pedido, reintegros y ajustes por merma/conteo (RN-22)
--   · Existencias de cada producto = suma exacta de sus movimientos
--
--  NOTA sobre "ajuste": la cantidad lleva signo
--     negativa = disminuye la existencia (merma)
--     positiva = aumenta la existencia (sobrante en conteo físico)
-- =====================================================================

-- Usuarios (contraseña: Prueba123)
INSERT INTO usuario (id_usuario, id_rol, nombre, apellidos, usuario, contrasena_hash, activo) VALUES
  (1, 1, 'Laura', 'Méndez Ortiz', 'admin', '$2y$10$d7ptESRbN7mZDWVzFRqVVeFdnHLCqzP4ddNgchNWS7uBKon6AMtai', TRUE),
  (2, 2, 'Juan', 'Pérez Gómez', 'jperez', '$2y$10$.YYStmtk2iiFLnqMdtKxl.eo4tp4ZVGzu4mAJ2Jihj2EFALW568z2', TRUE),
  (3, 2, 'María', 'López Sánchez', 'mlopez', '$2y$10$KnyGwpEHM4GI2YN97lv/U.9qq2L/wy4jPVe3BONFBVHr1GgU21UAS', TRUE),
  (4, 3, 'Ricardo', 'Ramírez Cruz', 'rramirez', '$2y$10$LH8cDeY6loTuGy90b.2Mpum/kcOfIknpIxP0PhHC/P97ci63AhC.S', TRUE),
  (5, 3, 'Andrea', 'García Flores', 'agarcia', '$2y$10$xHVIPXFWXxaPS/ZXuy0fXeZqKfLpGS9lFfvQ9rySMorFeMxGA95xu', TRUE),
  (6, 2, 'Roberto', 'Torres Vega', 'rtorres', '$2y$10$v95PQUT8G/Jn1q9U0bW6zO85..0.jyoj.vcTmDMKh7L7wSa/dGgfC', FALSE);

-- Clientes (el 1 "Público en general / Mostrador" ya se insertó arriba)
INSERT INTO cliente (id_cliente, nombre, telefono) VALUES
  (2, 'Ana Torres', '5512345678'),
  (3, 'Carlos Hernández', '5587654321'),
  (4, 'Sofía Martínez', NULL),
  (5, 'Miguel Díaz', '5544332211');

-- Categorías
INSERT INTO categoria (id_categoria, nombre) VALUES
  (1, 'Desayunos'),
  (2, 'Entradas'),
  (3, 'Sopas'),
  (4, 'Platos fuertes'),
  (5, 'Bebidas'),
  (6, 'Postres');

-- Productos (la existencia ya refleja todos los movimientos de inventario)
INSERT INTO producto (id_producto, id_categoria, nombre, descripcion, precio, existencia, stock_minimo, activo) VALUES
  (1, 1, 'Chilaquiles verdes con pollo', 'Totopos en salsa verde con pollo, crema y queso', 129.00, 25, 5, TRUE),
  (2, 1, 'Huevos rancheros', 'Dos huevos estrellados sobre tortilla con salsa roja', 115.00, 22, 5, TRUE),
  (3, 1, 'Hot cakes con tocino', 'Tres hot cakes con mantequilla, miel y tocino', 105.00, 17, 5, TRUE),
  (4, 1, 'Molletes', 'Bolillo con frijoles, queso gratinado y pico de gallo', 89.00, 4, 4, TRUE),
  (5, 2, 'Guacamole con totopos', 'Guacamole preparado al momento', 95.00, 19, 5, TRUE),
  (6, 2, 'Queso fundido', 'Queso gratinado con chorizo y tortillas de harina', 110.00, 0, 3, TRUE),
  (7, 3, 'Sopa de tortilla', 'Caldo de jitomate con tortilla frita, aguacate y queso', 85.00, 27, 6, TRUE),
  (8, 3, 'Crema de elote', 'Crema de elote natural con granos', 79.00, 20, 5, TRUE),
  (9, 4, 'Enchiladas suizas', 'Tres enchiladas de pollo en salsa verde con queso gratinado', 149.00, 26, 5, TRUE),
  (10, 4, 'Club sándwich', 'Sándwich de tres pisos con papas a la francesa', 145.00, 22, 5, TRUE),
  (11, 4, 'Hamburguesa clásica', 'Carne de res, queso, lechuga y jitomate con papas', 159.00, 18, 5, TRUE),
  (12, 4, 'Arrachera con guarnición', 'Arrachera de 250 g con frijoles, guacamole y cebollitas', 245.00, 12, 4, TRUE),
  (13, 4, 'Pechuga a la plancha', 'Pechuga de pollo con verduras al vapor', 169.00, 17, 4, TRUE),
  (14, 5, 'Café americano', 'Taza de café de grano', 45.00, 140, 20, TRUE),
  (15, 5, 'Jugo de naranja', 'Jugo natural de 400 ml', 55.00, 35, 10, TRUE),
  (16, 5, 'Limonada natural', 'Limonada natural o mineral de 500 ml', 49.00, 49, 10, TRUE),
  (17, 5, 'Refresco', 'Refresco de lata 355 ml', 40.00, 44, 12, TRUE),
  (18, 5, 'Malteada de vainilla', 'Malteada de 400 ml', 75.00, 5, 5, TRUE),
  (19, 6, 'Pastel de chocolate', 'Rebanada de pastel de chocolate', 85.00, 10, 4, TRUE),
  (20, 6, 'Pay de queso', 'Rebanada de pay de queso con zarzamora', 79.00, 11, 3, TRUE),
  (21, 6, 'Flan napolitano', 'Producto descontinuado (baja lógica)', 65.00, 10, 3, FALSE);

-- Pedidos (folio consecutivo PED-####; total = suma de subtotales)
INSERT INTO pedido (id_pedido, folio, id_cliente, id_usuario, id_estado, fecha_hora, tipo_servicio, total, observaciones) VALUES
  (1, 'PED-0001', 1, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '09:10:00'), 'local', 348.00, NULL),
  (2, 'PED-0002', 2, 6, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '14:30:00'), 'llevar', 198.00, NULL),
  (3, 'PED-0003', 1, 3, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00'), 'local', 740.00, 'Mesa 7'),
  (4, 'PED-0004', 3, 2, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '20:00:00'), 'llevar', 234.00, 'Cancelado: el cliente ya no quiso esperar'),
  (5, 'PED-0005', 4, 6, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00'), 'local', 420.00, 'Mesa 3'),
  (6, 'PED-0006', 1, 3, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00'), 'local', 673.00, 'Mesa 12'),
  (7, 'PED-0007', 2, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '19:15:00'), 'llevar', 369.00, NULL),
  (8, 'PED-0008', 5, 3, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00'), 'local', 594.00, 'Cancelado: el cliente tuvo que retirarse'),
  (9, 'PED-0009', 1, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00'), 'local', 615.00, 'Mesa 5'),
  (10, 'PED-0010', 3, 3, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00'), 'local', 379.00, 'Mesa 2'),
  (11, 'PED-0011', 1, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00'), 'llevar', 483.00, NULL),
  (12, 'PED-0012', 1, 2, 4, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 150 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.1667) SECOND), 'local', 368.00, 'Mesa 4'),
  (13, 'PED-0013', 4, 3, 4, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 130 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.2778) SECOND), 'llevar', 198.00, NULL),
  (14, 'PED-0014', 2, 2, 3, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 90 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.5) SECOND), 'local', 505.00, 'Mesa 9'),
  (15, 'PED-0015', 1, 3, 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 60 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.6667) SECOND), 'local', 190.00, 'Mesa 1'),
  (16, 'PED-0016', 5, 2, 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 45 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.75) SECOND), 'llevar', 244.00, NULL),
  (17, 'PED-0017', 1, 3, 5, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 35 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8056) SECOND), 'local', 125.00, 'Cancelado: pedido capturado por error'),
  (18, 'PED-0018', 3, 2, 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 20 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8889) SECOND), 'local', 300.00, 'Modificado: se quitaron los molletes a petición del cliente'),
  (19, 'PED-0019', 1, 3, 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 8 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.9556) SECOND), 'llevar', 170.00, NULL);

-- Detalle de los pedidos (precio_unitario congelado; subtotal = cantidad × precio)
INSERT INTO detalle_pedido (id_detalle, id_pedido, id_producto, cantidad, precio_unitario, subtotal, notas) VALUES
  (1, 1, 1, 2, 129.00, 258.00, NULL),
  (2, 1, 14, 2, 45.00, 90.00, NULL),
  (3, 2, 9, 1, 149.00, 149.00, NULL),
  (4, 2, 16, 1, 49.00, 49.00, 'Mineral'),
  (5, 3, 12, 2, 245.00, 490.00, 'Término medio'),
  (6, 3, 17, 2, 40.00, 80.00, NULL),
  (7, 3, 19, 2, 85.00, 170.00, NULL),
  (8, 4, 11, 1, 159.00, 159.00, NULL),
  (9, 4, 18, 1, 75.00, 75.00, NULL),
  (10, 5, 2, 1, 115.00, 115.00, NULL),
  (11, 5, 3, 1, 105.00, 105.00, NULL),
  (12, 5, 15, 2, 55.00, 110.00, NULL),
  (13, 5, 14, 2, 45.00, 90.00, NULL),
  (14, 6, 7, 3, 85.00, 255.00, NULL),
  (15, 6, 9, 2, 149.00, 298.00, 'Sin crema'),
  (16, 6, 17, 3, 40.00, 120.00, NULL),
  (17, 7, 10, 2, 145.00, 290.00, NULL),
  (18, 7, 20, 1, 79.00, 79.00, NULL),
  (19, 8, 13, 2, 169.00, 338.00, NULL),
  (20, 8, 8, 2, 79.00, 158.00, NULL),
  (21, 8, 16, 2, 49.00, 98.00, NULL),
  (22, 9, 6, 4, 110.00, 440.00, NULL),
  (23, 9, 5, 1, 95.00, 95.00, NULL),
  (24, 9, 17, 2, 40.00, 80.00, NULL),
  (25, 10, 1, 1, 129.00, 129.00, NULL),
  (26, 10, 2, 1, 115.00, 115.00, 'Huevos bien cocidos'),
  (27, 10, 14, 3, 45.00, 135.00, NULL),
  (28, 11, 11, 2, 159.00, 318.00, 'Una sin cebolla'),
  (29, 11, 17, 2, 40.00, 80.00, NULL),
  (30, 11, 19, 1, 85.00, 85.00, NULL),
  (31, 12, 1, 2, 129.00, 258.00, NULL),
  (32, 12, 15, 2, 55.00, 110.00, NULL),
  (33, 13, 9, 1, 149.00, 149.00, NULL),
  (34, 13, 16, 1, 49.00, 49.00, NULL),
  (35, 14, 6, 2, 110.00, 220.00, NULL),
  (36, 14, 12, 1, 245.00, 245.00, 'Bien cocida'),
  (37, 14, 17, 1, 40.00, 40.00, NULL),
  (38, 15, 10, 1, 145.00, 145.00, 'Sin mayonesa'),
  (39, 15, 14, 1, 45.00, 45.00, NULL),
  (40, 16, 13, 1, 169.00, 169.00, NULL),
  (41, 16, 18, 1, 75.00, 75.00, NULL),
  (42, 17, 7, 1, 85.00, 85.00, NULL),
  (43, 17, 17, 1, 40.00, 40.00, NULL),
  (44, 18, 3, 2, 105.00, 210.00, NULL),
  (45, 18, 14, 2, 45.00, 90.00, NULL),
  (46, 19, 2, 1, 115.00, 115.00, NULL),
  (47, 19, 15, 1, 55.00, 55.00, NULL);

-- Historial de cambios de estado (1 Recibido, 2 En preparación, 3 Listo, 4 Entregado, 5 Cancelado)
INSERT INTO historial_estado (id_pedido, id_estado, id_usuario, fecha_hora) VALUES
  (1, 1, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '09:10:00')),
  (1, 2, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '09:10:00') + INTERVAL 5 MINUTE),
  (1, 3, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '09:10:00') + INTERVAL 20 MINUTE),
  (1, 4, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '09:10:00') + INTERVAL 30 MINUTE),
  (2, 1, 6, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '14:30:00')),
  (2, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '14:30:00') + INTERVAL 5 MINUTE),
  (2, 3, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '14:30:00') + INTERVAL 20 MINUTE),
  (2, 4, 6, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '14:30:00') + INTERVAL 30 MINUTE),
  (3, 1, 3, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00')),
  (3, 2, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00') + INTERVAL 5 MINUTE),
  (3, 3, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00') + INTERVAL 20 MINUTE),
  (3, 4, 3, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00') + INTERVAL 30 MINUTE),
  (4, 1, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '20:00:00')),
  (4, 5, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '20:00:00') + INTERVAL 10 MINUTE),
  (5, 1, 6, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00')),
  (5, 2, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00') + INTERVAL 5 MINUTE),
  (5, 3, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00') + INTERVAL 20 MINUTE),
  (5, 4, 6, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00') + INTERVAL 30 MINUTE),
  (6, 1, 3, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00')),
  (6, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00') + INTERVAL 5 MINUTE),
  (6, 3, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00') + INTERVAL 20 MINUTE),
  (6, 4, 3, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00') + INTERVAL 30 MINUTE),
  (7, 1, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '19:15:00')),
  (7, 2, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '19:15:00') + INTERVAL 5 MINUTE),
  (7, 3, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '19:15:00') + INTERVAL 20 MINUTE),
  (7, 4, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '19:15:00') + INTERVAL 30 MINUTE),
  (8, 1, 3, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00')),
  (8, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00') + INTERVAL 5 MINUTE),
  (8, 5, 1, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00') + INTERVAL 15 MINUTE),
  (9, 1, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00')),
  (9, 2, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00') + INTERVAL 5 MINUTE),
  (9, 3, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00') + INTERVAL 20 MINUTE),
  (9, 4, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00') + INTERVAL 30 MINUTE),
  (10, 1, 3, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00')),
  (10, 2, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00') + INTERVAL 5 MINUTE),
  (10, 3, 4, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00') + INTERVAL 20 MINUTE),
  (10, 4, 3, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00') + INTERVAL 30 MINUTE),
  (11, 1, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00')),
  (11, 2, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00') + INTERVAL 5 MINUTE),
  (11, 3, 5, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00') + INTERVAL 20 MINUTE),
  (11, 4, 2, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00') + INTERVAL 30 MINUTE),
  (12, 1, 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 150 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.1667) SECOND)),
  (12, 2, 4, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 145 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.1944) SECOND)),
  (12, 3, 4, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 130 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.2778) SECOND)),
  (12, 4, 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 120 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.3333) SECOND)),
  (13, 1, 3, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 130 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.2778) SECOND)),
  (13, 2, 5, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 125 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.3056) SECOND)),
  (13, 3, 5, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 110 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.3889) SECOND)),
  (13, 4, 3, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 100 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.4444) SECOND)),
  (14, 1, 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 90 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.5) SECOND)),
  (14, 2, 4, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 85 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.5278) SECOND)),
  (14, 3, 4, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 70 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.6111) SECOND)),
  (15, 1, 3, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 60 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.6667) SECOND)),
  (15, 2, 5, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 55 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.6944) SECOND)),
  (16, 1, 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 45 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.75) SECOND)),
  (16, 2, 4, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 40 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.7778) SECOND)),
  (17, 1, 3, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 35 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8056) SECOND)),
  (17, 5, 3, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 25 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8611) SECOND)),
  (18, 1, 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 20 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8889) SECOND)),
  (19, 1, 3, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 8 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.9556) SECOND));

-- Movimientos de inventario
INSERT INTO movimiento_inventario (id_producto, id_usuario, id_pedido, tipo, cantidad, fecha_hora, motivo) VALUES
  (1, 1, NULL, 'entrada', 30, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (2, 1, NULL, 'entrada', 25, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (3, 1, NULL, 'entrada', 20, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (4, 1, NULL, 'entrada', 6, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (5, 1, NULL, 'entrada', 20, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (6, 1, NULL, 'entrada', 6, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (7, 1, NULL, 'entrada', 30, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (8, 1, NULL, 'entrada', 20, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (9, 1, NULL, 'entrada', 30, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (10, 1, NULL, 'entrada', 25, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (11, 1, NULL, 'entrada', 20, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (12, 1, NULL, 'entrada', 15, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (13, 1, NULL, 'entrada', 18, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (14, 1, NULL, 'entrada', 100, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (15, 1, NULL, 'entrada', 40, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (16, 1, NULL, 'entrada', 50, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (17, 1, NULL, 'entrada', 30, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (18, 1, NULL, 'entrada', 6, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (19, 1, NULL, 'entrada', 15, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (20, 1, NULL, 'entrada', 12, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (21, 1, NULL, 'entrada', 10, TIMESTAMP(CURRENT_DATE - INTERVAL 10 DAY, '08:00:00'), 'Inventario inicial'),
  (1, 2, 1, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '09:10:00'), 'Salida por pedido PED-0001'),
  (14, 2, 1, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '09:10:00'), 'Salida por pedido PED-0001'),
  (9, 6, 2, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '14:30:00'), 'Salida por pedido PED-0002'),
  (16, 6, 2, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 6 DAY, '14:30:00'), 'Salida por pedido PED-0002'),
  (12, 3, 3, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00'), 'Salida por pedido PED-0003'),
  (17, 3, 3, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00'), 'Salida por pedido PED-0003'),
  (19, 3, 3, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '13:45:00'), 'Salida por pedido PED-0003'),
  (11, 2, 4, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '20:00:00'), 'Salida por pedido PED-0004'),
  (18, 2, 4, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '20:00:00'), 'Salida por pedido PED-0004'),
  (11, 2, 4, 'entrada', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '20:00:00') + INTERVAL 10 MINUTE, 'Reintegro por cancelación de PED-0004'),
  (18, 2, 4, 'entrada', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 5 DAY, '20:00:00') + INTERVAL 10 MINUTE, 'Reintegro por cancelación de PED-0004'),
  (2, 6, 5, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00'), 'Salida por pedido PED-0005'),
  (3, 6, 5, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00'), 'Salida por pedido PED-0005'),
  (15, 6, 5, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00'), 'Salida por pedido PED-0005'),
  (14, 6, 5, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 4 DAY, '09:30:00'), 'Salida por pedido PED-0005'),
  (17, 1, NULL, 'entrada', 24, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '07:30:00'), 'Resurtido de proveedor'),
  (14, 1, NULL, 'entrada', 50, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '07:35:00'), 'Resurtido de proveedor'),
  (7, 3, 6, 'salida', 3, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00'), 'Salida por pedido PED-0006'),
  (9, 3, 6, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00'), 'Salida por pedido PED-0006'),
  (17, 3, 6, 'salida', 3, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '14:00:00'), 'Salida por pedido PED-0006'),
  (10, 2, 7, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '19:15:00'), 'Salida por pedido PED-0007'),
  (20, 2, 7, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 3 DAY, '19:15:00'), 'Salida por pedido PED-0007'),
  (13, 3, 8, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00'), 'Salida por pedido PED-0008'),
  (8, 3, 8, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00'), 'Salida por pedido PED-0008'),
  (16, 3, 8, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00'), 'Salida por pedido PED-0008'),
  (13, 1, 8, 'entrada', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00') + INTERVAL 15 MINUTE, 'Reintegro por cancelación de PED-0008'),
  (8, 1, 8, 'entrada', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00') + INTERVAL 15 MINUTE, 'Reintegro por cancelación de PED-0008'),
  (16, 1, 8, 'entrada', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '13:20:00') + INTERVAL 15 MINUTE, 'Reintegro por cancelación de PED-0008'),
  (6, 2, 9, 'salida', 4, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00'), 'Salida por pedido PED-0009'),
  (5, 2, 9, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00'), 'Salida por pedido PED-0009'),
  (17, 2, 9, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '15:10:00'), 'Salida por pedido PED-0009'),
  (19, 1, NULL, 'ajuste', -2, TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY, '22:00:00'), 'Merma: rebanadas dañadas'),
  (1, 3, 10, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00'), 'Salida por pedido PED-0010'),
  (2, 3, 10, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00'), 'Salida por pedido PED-0010'),
  (14, 3, 10, 'salida', 3, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '08:45:00'), 'Salida por pedido PED-0010'),
  (11, 2, 11, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00'), 'Salida por pedido PED-0011'),
  (17, 2, 11, 'salida', 2, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00'), 'Salida por pedido PED-0011'),
  (19, 2, 11, 'salida', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '14:20:00'), 'Salida por pedido PED-0011'),
  (4, 1, NULL, 'ajuste', -2, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '22:00:00'), 'Merma: pan caducado'),
  (16, 1, NULL, 'ajuste', 1, TIMESTAMP(CURRENT_DATE - INTERVAL 1 DAY, '22:10:00'), 'Conteo físico: sobrante'),
  (1, 2, 12, 'salida', 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 150 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.1667) SECOND), 'Salida por pedido PED-0012'),
  (15, 2, 12, 'salida', 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 150 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.1667) SECOND), 'Salida por pedido PED-0012'),
  (9, 3, 13, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 130 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.2778) SECOND), 'Salida por pedido PED-0013'),
  (16, 3, 13, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 130 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.2778) SECOND), 'Salida por pedido PED-0013'),
  (6, 2, 14, 'salida', 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 90 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.5) SECOND), 'Salida por pedido PED-0014'),
  (12, 2, 14, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 90 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.5) SECOND), 'Salida por pedido PED-0014'),
  (17, 2, 14, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 90 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.5) SECOND), 'Salida por pedido PED-0014'),
  (10, 3, 15, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 60 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.6667) SECOND), 'Salida por pedido PED-0015'),
  (14, 3, 15, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 60 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.6667) SECOND), 'Salida por pedido PED-0015'),
  (13, 2, 16, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 45 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.75) SECOND), 'Salida por pedido PED-0016'),
  (18, 2, 16, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 45 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.75) SECOND), 'Salida por pedido PED-0016'),
  (7, 3, 17, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 35 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8056) SECOND), 'Salida por pedido PED-0017'),
  (17, 3, 17, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 35 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8056) SECOND), 'Salida por pedido PED-0017'),
  (7, 3, 17, 'entrada', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 25 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8611) SECOND), 'Reintegro por cancelación de PED-0017'),
  (17, 3, 17, 'entrada', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 25 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8611) SECOND), 'Reintegro por cancelación de PED-0017'),
  (3, 2, 18, 'salida', 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 20 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8889) SECOND), 'Salida por pedido PED-0018'),
  (14, 2, 18, 'salida', 2, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 20 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8889) SECOND), 'Salida por pedido PED-0018'),
  (4, 2, 18, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 20 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.8889) SECOND), 'Salida por pedido PED-0018'),
  (4, 2, 18, 'entrada', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 17 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.9056) SECOND), 'Reintegro por modificación de PED-0018 (se quitó el producto)'),
  (2, 3, 19, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 8 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.9556) SECOND), 'Salida por pedido PED-0019'),
  (15, 3, 19, 'salida', 1, IF(CURTIME() >= '03:00:00', NOW() - INTERVAL 8 MINUTE, TIMESTAMP(CURRENT_DATE) + INTERVAL ROUND(TIME_TO_SEC(CURTIME()) * 0.9556) SECOND), 'Salida por pedido PED-0019');

-- Fin del script
