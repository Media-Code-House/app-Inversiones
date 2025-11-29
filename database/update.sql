-- ==========================================
-- UPDATE.SQL - Módulo 4: CRUD de Lotes
-- ==========================================
-- Fecha: 2025-11-29
-- Propósito: Añadir campo vendedor_id a tabla lotes
-- Autor: Ingeniero Principal - Arquitecto de Datos
-- ==========================================

USE sistema_lotes;

-- ==========================================
-- 1. AGREGAR COLUMNA vendedor_id
-- ==========================================
-- Añade el campo vendedor_id a la tabla lotes
-- para rastrear qué usuario (vendedor) gestionó la venta

ALTER TABLE `lotes` 
ADD COLUMN `vendedor_id` INT UNSIGNED NULL AFTER `cliente_id`,
ADD COLUMN `ubicacion` VARCHAR(255) NULL AFTER `manzana`,
ADD COLUMN `descripcion` TEXT NULL AFTER `observaciones`;

-- ==========================================
-- 2. CREAR FOREIGN KEY vendedor_id → users.id
-- ==========================================
-- Establece la relación de integridad referencial
-- entre lotes y el usuario vendedor

ALTER TABLE `lotes`
ADD CONSTRAINT `fk_lotes_vendedor`
    FOREIGN KEY (`vendedor_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- ==========================================
-- 3. CREAR ÍNDICE para vendedor_id
-- ==========================================
-- Optimiza las consultas que filtren por vendedor

ALTER TABLE `lotes`
ADD INDEX `idx_vendedor` (`vendedor_id`);

-- ==========================================
-- 4. ACTUALIZAR LOTES VENDIDOS EXISTENTES
-- ==========================================
-- Asigna el primer usuario admin como vendedor por defecto
-- para los lotes que ya están vendidos

UPDATE `lotes` 
SET `vendedor_id` = (SELECT id FROM users WHERE rol = 'admin' LIMIT 1)
WHERE `estado` = 'vendido' AND `vendedor_id` IS NULL;

-- ==========================================
-- 5. VERIFICACIÓN DE CAMBIOS
-- ==========================================
-- Ejecuta estas consultas para verificar que los cambios se aplicaron correctamente

-- Ver estructura actualizada de la tabla lotes
DESCRIBE lotes;

-- Verificar FKs
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'sistema_lotes'
  AND TABLE_NAME = 'lotes'
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Verificar índices
SHOW INDEX FROM lotes;

-- Ver lotes con vendedor asignado
SELECT 
    l.id,
    l.codigo_lote,
    l.estado,
    l.cliente_id,
    l.vendedor_id,
    u.nombre as vendedor_nombre
FROM lotes l
LEFT JOIN users u ON l.vendedor_id = u.id
WHERE l.estado = 'vendido';

-- ==========================================
-- NOTAS DE IMPLEMENTACIÓN
-- ==========================================
-- 
-- 1. El campo vendedor_id es NULL por defecto para permitir
--    lotes que aún no han sido vendidos.
--
-- 2. ON DELETE SET NULL: Si se elimina un usuario vendedor,
--    el campo se establece en NULL (no se pierde el lote).
--
-- 3. ON UPDATE CASCADE: Si cambia el ID del usuario,
--    se actualiza automáticamente en lotes.
--
-- 4. Los campos ubicacion y descripcion añadidos para 
--    complementar la información del lote.
--
-- ==========================================
-- ROLLBACK (En caso de necesitar deshacer)
-- ==========================================
/*
-- Para revertir estos cambios:

ALTER TABLE `lotes` DROP FOREIGN KEY `fk_lotes_vendedor`;
ALTER TABLE `lotes` DROP INDEX `idx_vendedor`;
ALTER TABLE `lotes` DROP COLUMN `vendedor_id`;
ALTER TABLE `lotes` DROP COLUMN `ubicacion`;
ALTER TABLE `lotes` DROP COLUMN `descripcion`;
*/
