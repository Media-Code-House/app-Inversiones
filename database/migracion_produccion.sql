-- ================================================
-- MIGRACIÓN DE ESTRUCTURA DE BASE DE DATOS
-- Adapta la BD de producción a los modelos del Módulo 3
-- ================================================

USE u418271893_inversiones;

-- ================================================
-- 1. MODIFICAR TABLA USERS
-- ================================================
-- Cambiar rol_id por rol (ENUM)
ALTER TABLE `users` 
  DROP COLUMN IF EXISTS `rol_id`,
  ADD COLUMN `rol` ENUM('administrador', 'vendedor', 'consulta') DEFAULT 'consulta' AFTER `nombre`;

-- Renombrar is_active a activo
ALTER TABLE `users` 
  CHANGE COLUMN `is_active` `activo` TINYINT(1) DEFAULT 1 COMMENT 'Estado del usuario';

-- Renombrar password_hash a password
ALTER TABLE `users` 
  CHANGE COLUMN `password_hash` `password` VARCHAR(255) NOT NULL;

-- Renombrar reset_token_expires a reset_token_expira
ALTER TABLE `users` 
  CHANGE COLUMN `reset_token_expires` `reset_token_expira` DATETIME DEFAULT NULL COMMENT 'Fecha de expiración del token';

-- ================================================
-- 2. MODIFICAR TABLA CLIENTES
-- ================================================
-- Simplificar columnas para compatibilidad con ClienteModel
ALTER TABLE `clientes` 
  CHANGE COLUMN `numero_documento` `numero_documento` VARCHAR(50) NOT NULL COMMENT 'Documento de identidad';

ALTER TABLE `clientes` 
  CHANGE COLUMN `nombre_completo` `nombre` VARCHAR(200) NOT NULL;

-- Eliminar columnas no usadas en el modelo
ALTER TABLE `clientes` 
  DROP COLUMN IF EXISTS `departamento`,
  DROP COLUMN IF EXISTS `fecha_nacimiento`,
  DROP COLUMN IF EXISTS `estado_civil`,
  DROP COLUMN IF EXISTS `ocupacion`,
  DROP COLUMN IF EXISTS `is_active`;

-- ================================================
-- 3. MODIFICAR TABLA LOTES
-- ================================================
-- Eliminar columnas no usadas
ALTER TABLE `lotes` 
  DROP FOREIGN KEY IF EXISTS `lotes_ibfk_3`;

ALTER TABLE `lotes` 
  DROP COLUMN IF EXISTS `vendedor_id`;

-- Cambiar fecha_venta de datetime a date
ALTER TABLE `lotes` 
  MODIFY COLUMN `fecha_venta` DATE DEFAULT NULL;

-- ================================================
-- 4. MODIFICAR TABLA AMORTIZACIONES
-- ================================================
-- Agregar columnas faltantes
ALTER TABLE `amortizaciones` 
  ADD COLUMN IF NOT EXISTS `valor_pagado` DECIMAL(15,2) DEFAULT 0 AFTER `valor_cuota`,
  ADD COLUMN IF NOT EXISTS `fecha_pago` DATE DEFAULT NULL AFTER `fecha_vencimiento`,
  ADD COLUMN IF NOT EXISTS `estado` ENUM('pendiente', 'pagada', 'cancelada') DEFAULT 'pendiente' AFTER `fecha_pago`;

-- Eliminar cliente_id (se obtiene a través de lote)
ALTER TABLE `amortizaciones` 
  DROP FOREIGN KEY IF EXISTS `amortizaciones_ibfk_2`;

ALTER TABLE `amortizaciones` 
  DROP COLUMN IF EXISTS `cliente_id`;

-- Renombrar estado_pago a estado (si existe)
ALTER TABLE `amortizaciones` 
  DROP COLUMN IF EXISTS `estado_pago`;

-- Modificar saldo_pendiente para que sea calculado
ALTER TABLE `amortizaciones` 
  DROP COLUMN IF EXISTS `saldo_pendiente`;

ALTER TABLE `amortizaciones` 
  ADD COLUMN `saldo_pendiente` DECIMAL(15,2) GENERATED ALWAYS AS (valor_cuota - valor_pagado) STORED AFTER `valor_pagado`;

-- ================================================
-- 5. MODIFICAR TABLA PAGOS
-- ================================================
-- Eliminar columnas redundantes (lote_id y cliente_id se obtienen de amortizacion)
ALTER TABLE `pagos` 
  DROP FOREIGN KEY IF EXISTS `pagos_ibfk_2`,
  DROP FOREIGN KEY IF EXISTS `pagos_ibfk_3`,
  DROP FOREIGN KEY IF EXISTS `pagos_ibfk_4`;

ALTER TABLE `pagos` 
  DROP COLUMN IF EXISTS `lote_id`,
  DROP COLUMN IF EXISTS `cliente_id`,
  DROP COLUMN IF EXISTS `registrado_por`;

-- Cambiar fecha_pago de datetime a date
ALTER TABLE `pagos` 
  MODIFY COLUMN `fecha_pago` DATE NOT NULL;

-- ================================================
-- 6. MODIFICAR TABLA PROYECTOS
-- ================================================
-- Cambiar estado para coincidir con el modelo
ALTER TABLE `proyectos` 
  MODIFY COLUMN `estado` ENUM('activo', 'completado', 'pausado', 'cancelado') DEFAULT 'activo';

-- ================================================
-- 7. RECREAR VISTA
-- ================================================
DROP VIEW IF EXISTS `vista_proyectos_resumen`;

CREATE VIEW `vista_proyectos_resumen` AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.ubicacion,
    p.estado,
    p.total_lotes,
    COUNT(DISTINCT CASE WHEN l.estado = 'disponible' THEN l.id END) as lotes_disponibles,
    COUNT(DISTINCT CASE WHEN l.estado = 'vendido' THEN l.id END) as lotes_vendidos,
    COUNT(DISTINCT CASE WHEN l.estado = 'reservado' THEN l.id END) as lotes_reservados,
    COUNT(DISTINCT CASE WHEN l.estado = 'bloqueado' THEN l.id END) as lotes_bloqueados,
    SUM(CASE WHEN l.estado IN ('disponible', 'reservado') THEN l.precio_lista ELSE 0 END) as valor_inventario,
    SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) ELSE 0 END) as valor_ventas
FROM proyectos p
LEFT JOIN lotes l ON p.id = l.proyecto_id
GROUP BY p.id, p.codigo, p.nombre, p.ubicacion, p.estado, p.total_lotes;

-- ================================================
-- 8. RECREAR FOREIGN KEYS
-- ================================================
ALTER TABLE `amortizaciones`
  DROP FOREIGN KEY IF EXISTS `amortizaciones_ibfk_1`;

ALTER TABLE `amortizaciones`
  ADD CONSTRAINT `amortizaciones_ibfk_1` 
  FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE RESTRICT;

ALTER TABLE `pagos`
  DROP FOREIGN KEY IF EXISTS `pagos_ibfk_1`;

ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` 
  FOREIGN KEY (`amortizacion_id`) REFERENCES `amortizaciones` (`id`) ON DELETE RESTRICT;

ALTER TABLE `lotes`
  DROP FOREIGN KEY IF EXISTS `lotes_ibfk_1`,
  DROP FOREIGN KEY IF EXISTS `lotes_ibfk_2`;

ALTER TABLE `lotes`
  ADD CONSTRAINT `lotes_ibfk_1` 
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `lotes_ibfk_2` 
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT;

-- ================================================
-- 9. ACTUALIZAR DATOS DEL USUARIO ADMIN
-- ================================================
-- Actualizar el usuario existente para usar la nueva estructura
UPDATE `users` 
SET 
  `rol` = 'administrador',
  `activo` = 1
WHERE `id` = 1;

-- ================================================
-- 10. VERIFICACIÓN FINAL
-- ================================================
-- Ver estructura actualizada
SELECT 'Tablas actualizadas correctamente' as mensaje;

SHOW TABLES;

SELECT 
    'users' as tabla,
    COUNT(*) as registros
FROM users
UNION ALL
SELECT 'proyectos', COUNT(*) FROM proyectos
UNION ALL
SELECT 'clientes', COUNT(*) FROM clientes
UNION ALL
SELECT 'lotes', COUNT(*) FROM lotes
UNION ALL
SELECT 'amortizaciones', COUNT(*) FROM amortizaciones
UNION ALL
SELECT 'pagos', COUNT(*) FROM pagos;

-- ================================================
-- MIGRACIÓN COMPLETADA
-- ================================================
-- Ahora la BD de producción es compatible con los modelos del Módulo 3
