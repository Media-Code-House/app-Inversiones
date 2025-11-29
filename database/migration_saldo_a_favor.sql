-- Migration: Agregar columna saldo_a_favor a tabla lotes
-- Descripción: Implementa el sistema de Saldo a Favor Global para compensación de deudas
-- Fecha: 2025-11-29

ALTER TABLE `lotes` 
ADD COLUMN `saldo_a_favor` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Saldo acumulado de pagos excedentes para aplicar a cuotas futuras' AFTER `numero_cuotas`;

-- Comentario de la columna
ALTER TABLE `lotes` 
MODIFY COLUMN `saldo_a_favor` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Saldo acumulado de pagos excedentes disponible para reajustar mora y compensar cuotas futuras';

-- Crear índice para consultas rápidas de lotes con saldo_a_favor > 0
CREATE INDEX `idx_lotes_saldo_a_favor` ON `lotes` (`saldo_a_favor`, `estado`);

-- Verificación final: Confirmar que la columna se agregó correctamente
-- Ejecutar esta query después de la migration:
-- SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';
-- 
-- Resultado esperado:
-- | saldo_a_favor | decimal(15,2) | 0.00 |
