-- ============================================================
-- Snackliciosos Mini TPV
-- Base de datos: colibrip_snackmini
-- Sistema: kiosko táctil + cobro en efectivo
-- ============================================================

CREATE DATABASE IF NOT EXISTS `colibrip_snackmini`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `colibrip_snackmini`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `venta_toppings`;
DROP TABLE IF EXISTS `venta_detalle`;
DROP TABLE IF EXISTS `ventas`;
DROP TABLE IF EXISTS `toppings`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `configuracion`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- CONFIGURACIÓN
-- ============================================================
CREATE TABLE `configuracion` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(100) NOT NULL,
  `valor` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_configuracion_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('nombre_negocio', 'Snackliciosos'),
('direccion', 'Frontera Piedras Negras #55 a col. Progreso'),
('telefono', '6271104930'),
('whatsapp', '526271104930'),
('instagram', '@snackliciosos'),
('mensaje_menu', '¡Gracias por tu preferencia! ✨'),
('mensaje_ticket', '¡Gracias por tu compra! ✨');

-- ============================================================
-- USUARIOS ADMINISTRADORES
-- ============================================================
CREATE TABLE `usuarios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario: admin
-- Contraseña inicial: admin123
-- IMPORTANTE: cambiarla después de la instalación.
INSERT INTO `usuarios` (`usuario`, `password`, `activo`) VALUES
('admin', '$2y$10$LzHgqWPp4Gin0CFSE4uKk.d2opJ/ssU9EuALIBOz8oQEl4VMPi0u6', 1);

-- ============================================================
-- CATEGORÍAS
-- ============================================================
CREATE TABLE `categorias` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `orden` INT NOT NULL DEFAULT 0,
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias` (`nombre`, `orden`, `activa`) VALUES
('Fresas con crema', 1, 1),
('Chicharrines caseros', 2, 1),
('Más antojitos', 3, 1);

-- ============================================================
-- PRODUCTOS
-- Se reutilizan los productos de la BD original, excepto
-- "Toppings extras", que ahora serán complementos reales.
-- ============================================================
CREATE TABLE `productos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` INT UNSIGNED NOT NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` VARCHAR(500) NULL,
  `imagen` VARCHAR(500) NULL,
  `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `permite_toppings` TINYINT(1) NOT NULL DEFAULT 0,
  `max_toppings` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `orden` INT NOT NULL DEFAULT 0,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_productos_categoria` (`categoria_id`),
  KEY `idx_productos_activo` (`activo`),
  CONSTRAINT `fk_productos_categoria`
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fresas con crema
INSERT INTO `productos`
(`categoria_id`,`nombre`,`descripcion`,`precio`,`orden`,`activo`) VALUES
(1,'Vaso chico','Fresas con crema en vaso chico',45.00,1,1),
(1,'Vaso mediano','Fresas con crema en vaso mediano',60.00,2,1),
(1,'Medio litro','Fresas con crema, presentación de medio litro',80.00,3,1),
(1,'Litro','Fresas con crema, presentación de un litro',150.00,4,1),
(1,'Rebanada de pay con fresas con crema','Rebanada de pay acompañada con fresas y crema',75.00,5,1),
(1,'Fresas especiales Ferrero','Nutella, Ferrero Roche, almendra',120.00,6,1),
(1,'Fresas especiales Kinder Delice','Nutella, Kinder Delice, almendra',120.00,7,1),
(1,'Fresas especiales Mazapán','',120.00,8,1),
(1,'Mini hotcakes','10 mini hotcakes. Acompañados de fruta y 1 jarabe',65.00,9,1),
(1,'Waffle grande','Waffle grande acompañado de fruta, jarabe y toppings',65.00,10,1);

-- Chicharrines
INSERT INTO `productos`
(`categoria_id`,`nombre`,`descripcion`,`precio`,`orden`,`activo`) VALUES
(2,'Plato','Chicharrines caseros preparados con verdura',25.00,1,1),
(2,'Charola','Chicharrines caseros preparados con verdura',55.00,2,1),
(2,'Bolsa','No disponible para envío a domicilio',20.00,3,1),
(2,'Con cueritos curtidos','Agregado de cueritos curtidos',10.00,4,1),
(2,'Chicharrines Bolsa salsa y crema','Chicharrines en bolsa con salsa y crema',12.00,5,1);

-- Más antojitos
INSERT INTO `productos`
(`categoria_id`,`nombre`,`descripcion`,`precio`,`orden`,`activo`) VALUES
(3,'Tostilocos','Repollo, tomate, pepino, cueritos, cacahuates, salsa y crema',50.00,1,1),
(3,'Pepihuates chico','Pepino, cacahuate, rielitos, cueritos y clamato preparado',45.00,2,1),
(3,'Nachos con queso','Nachos, Sabritas o papas caseras con queso',40.00,3,1),
(3,'Gomilocas','Gomitas preparadas con chamoy y chile',35.00,4,1),
(3,'Papas caseras preparadas litro','Papas caseras preparadas de un litro. Pepino, cacahuates, rielitos y salsas',50.00,5,1),
(3,'Vaso loco','Sabritas a elección, pepino, cacahuate, rielitos, cueritos, churritos, salsa soya y salsa Valentina',60.00,6,1),
(3,'Pepihuates 1/2 litro','Pepino, cacahuates, rielitos, cuerito y clamato preparado',65.00,7,1),
(3,'Papas caseras charola grande','Pepino, cacahuates y rielitos',110.00,8,1);

-- Productos que permiten toppings (configuración inicial).
UPDATE `productos` SET `permite_toppings`=1, `max_toppings`=2
WHERE `id` IN (1,2,3,4,9,10);

-- ============================================================
-- TOPPINGS / COMPLEMENTOS
-- NO son productos independientes.
-- Los precios son iniciales y pueden cambiarse desde ADMIN.
-- ============================================================
CREATE TABLE `toppings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `permite_toppings` TINYINT(1) NOT NULL DEFAULT 0,
  `max_toppings` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `orden` INT NOT NULL DEFAULT 0,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_toppings_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajustar estos precios desde el panel ADMIN según el precio real.
INSERT INTO `toppings` (`nombre`,`precio`,`orden`,`activo`) VALUES
('Oreo',10.00,1,1),
('Nuez',10.00,2,1),
('Bombones',10.00,3,1),
('Chispas',10.00,4,1),
('Lunetas',10.00,5,1),
('Almendra',15.00,6,1),
('Nutella',15.00,7,1),
('Granola',10.00,8,1),
('Lechera',10.00,9,1),
('Granillo de chocolate',10.00,10,1),
('Granillo de colores',10.00,11,1);
CREATE TABLE `producto_toppings` (
  `producto_id` INT UNSIGNED NOT NULL,
  `topping_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`producto_id`,`topping_id`),
  KEY `idx_pt_topping` (`topping_id`),
  CONSTRAINT `fk_pt_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pt_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `producto_toppings` (`producto_id`,`topping_id`)
SELECT p.id,t.id FROM productos p CROSS JOIN toppings t WHERE p.permite_toppings=1;


-- ============================================================
-- VENTAS
-- Solo efectivo.
-- ============================================================
CREATE TABLE `ventas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `folio` VARCHAR(30) NOT NULL,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `efectivo` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `cambio` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ventas_folio` (`folio`),
  KEY `idx_ventas_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DETALLE DE VENTA
-- Guardamos nombre y precio histórico para que una venta
-- antigua no cambie aunque después cambie el precio del menú.
-- ============================================================
CREATE TABLE `venta_detalle` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_id` BIGINT UNSIGNED NOT NULL,
  `producto_id` INT UNSIGNED NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `cantidad` INT UNSIGNED NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_detalle_venta` (`venta_id`),
  KEY `idx_detalle_producto` (`producto_id`),
  CONSTRAINT `fk_detalle_venta`
    FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TOPPINGS DE CADA PRODUCTO VENDIDO
-- También guarda precio histórico.
-- ============================================================
CREATE TABLE `venta_toppings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_detalle_id` BIGINT UNSIGNED NOT NULL,
  `topping_id` INT UNSIGNED NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `cantidad` INT UNSIGNED NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_venta_toppings_detalle` (`venta_detalle_id`),
  KEY `idx_venta_toppings_topping` (`topping_id`),
  CONSTRAINT `fk_venta_toppings_detalle`
    FOREIGN KEY (`venta_detalle_id`) REFERENCES `venta_detalle` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_venta_toppings_topping`
    FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FIN
-- ============================================================
