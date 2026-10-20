-- =====================================================================
--  PROYECTO 2 · Equipo 11
--  Sistema de pedidos e inventario para un local de comida
--  Script de creación de la base de datos para XAMPP (MariaDB/MySQL)
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
--      sudo /opt/lampp/bin/mysql -u root < sistema_pedidos.sql
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

-- Fin del script
