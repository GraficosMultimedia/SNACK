-- SnackTPV · métodos de pago efectivo / transferencia
-- Ejecutar una sola vez en la base de datos de producción.
SET @db = DATABASE();
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='ventas' AND COLUMN_NAME='metodo_pago')=0,
  "ALTER TABLE ventas ADD COLUMN metodo_pago ENUM('efectivo','transferencia') NOT NULL DEFAULT 'efectivo' AFTER cambio",
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='caja_cortes' AND COLUMN_NAME='transferencias')=0,
  "ALTER TABLE caja_cortes ADD COLUMN transferencias DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER efectivo_ventas",
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
