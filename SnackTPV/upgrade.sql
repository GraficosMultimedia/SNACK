USE `colibrip_snackmini`;

CREATE TABLE IF NOT EXISTS `caja_cortes` (
 `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 `fecha_apertura` DATETIME NULL,
 `fecha_corte` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `fondo_inicial` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 `ventas_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 `efectivo_ventas` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 `efectivo_esperado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 `efectivo_contado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 `diferencia` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 `cantidad_ventas` INT UNSIGNED NOT NULL DEFAULT 0,
 `usuario_id` INT UNSIGNED NULL,
 PRIMARY KEY (`id`), KEY `idx_corte_fecha` (`fecha_corte`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @db := DATABASE();
SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='productos' AND COLUMN_NAME='imagen')=0,
  'ALTER TABLE productos ADD COLUMN imagen VARCHAR(500) NULL AFTER descripcion', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='productos' AND COLUMN_NAME='permite_toppings')=0,
  'ALTER TABLE productos ADD COLUMN permite_toppings TINYINT(1) NOT NULL DEFAULT 0 AFTER precio', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='productos' AND COLUMN_NAME='max_toppings')=0,
  'ALTER TABLE productos ADD COLUMN max_toppings TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER permite_toppings', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='productos' AND COLUMN_NAME='toppings_gratis')=0,
  'ALTER TABLE productos ADD COLUMN toppings_gratis TINYINT UNSIGNED NOT NULL DEFAULT 2 AFTER max_toppings', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

/* Relación producto-topping: INT UNSIGNED coincide con productos.id y toppings.id. */
CREATE TABLE IF NOT EXISTS `producto_toppings` (
 `producto_id` INT UNSIGNED NOT NULL,
 `topping_id` INT UNSIGNED NOT NULL,
 PRIMARY KEY (`producto_id`,`topping_id`),
 KEY `idx_pt_topping` (`topping_id`),
 CONSTRAINT `fk_pt_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
 CONSTRAINT `fk_pt_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracion (clave,valor) VALUES ('direccion','Frontera Piedras Negras #55 a col. Progreso') ON DUPLICATE KEY UPDATE valor=VALUES(valor);
INSERT INTO configuracion (clave,valor) VALUES ('telefono','6271104930') ON DUPLICATE KEY UPDATE valor=VALUES(valor);
INSERT INTO configuracion (clave,valor) VALUES ('whatsapp','526271104930') ON DUPLICATE KEY UPDATE valor=VALUES(valor);
INSERT INTO configuracion (clave,valor) VALUES ('instagram','@snackliciosos') ON DUPLICATE KEY UPDATE valor=VALUES(valor);
INSERT INTO configuracion (clave,valor) VALUES ('mensaje_menu','¡Gracias por tu compra! ✨') ON DUPLICATE KEY UPDATE valor=VALUES(valor);

SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='venta_toppings' AND COLUMN_NAME='es_gratis')=0,
  'ALTER TABLE venta_toppings ADD COLUMN es_gratis TINYINT(1) NOT NULL DEFAULT 0 AFTER subtotal', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

/* La regla es fija: 1 topping se cobra; con 2 o más, los primeros 2 son gratis. */
UPDATE productos SET toppings_gratis=2 WHERE permite_toppings=1;
