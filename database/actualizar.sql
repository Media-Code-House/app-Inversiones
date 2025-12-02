-- ============================================================================
-- ACTUALIZACIÓN: Módulo de Pago Inicial Diferido
-- ============================================================================
-- Fecha: 2 de diciembre de 2025
-- Descripción: Crea las tablas, campos, triggers y vistas necesarias para 
--              el módulo de Pago Inicial Diferido
-- Ejecución: mysql -u usuario -p nombre_bd < database/actualizar.sql
-- ============================================================================

USE u418271893_inversiones;

-- ============================================================================
-- 1. TABLA: pagos_iniciales
-- ============================================================================
-- Almacena los planes de pago inicial diferidos para cada lote

CREATE TABLE IF NOT EXISTS `pagos_iniciales` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lote_id` int(10) UNSIGNED NOT NULL COMMENT 'FK al lote',
  `monto_inicial_total_requerido` decimal(15,2) NOT NULL COMMENT 'Monto total de la inicial (ej: $1.000.000)',
  `monto_pagado_hoy` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Abono inicial pagado el día de creación',
  `monto_pendiente_diferir` decimal(15,2) NOT NULL COMMENT 'Monto restante a financiar en cuotas',
  `plazo_meses` smallint(5) UNSIGNED NOT NULL COMMENT 'Plazo en meses para el pago inicial',
  `cuota_mensual` decimal(15,2) NOT NULL COMMENT 'Cuota mensual calculada (monto_pendiente / plazo)',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio del plan',
  `estado` enum('pendiente','en_curso','pagado_total','cancelado') DEFAULT 'en_curso',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lote_id` (`lote_id`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `fk_pagos_iniciales_lote` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Planes de pago inicial diferido';


-- ============================================================================
-- 2. TABLA: pagos_iniciales_detalle
-- ============================================================================
-- Almacena el historial de pagos realizados contra cada plan inicial

CREATE TABLE IF NOT EXISTS `pagos_iniciales_detalle` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_inicial_id` int(10) UNSIGNED NOT NULL COMMENT 'FK al plan de pago inicial',
  `fecha_pago` date NOT NULL COMMENT 'Fecha en que se realizó el pago',
  `valor_pagado` decimal(15,2) NOT NULL COMMENT 'Valor del pago realizado',
  `metodo_pago` enum('efectivo','transferencia','cheque','tarjeta') DEFAULT 'efectivo',
  `numero_recibo` varchar(100) DEFAULT NULL COMMENT 'Número de recibo o comprobante',
  `saldo_pendiente_despues` decimal(15,2) NOT NULL COMMENT 'Saldo pendiente después de este pago',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_plan_inicial_id` (`plan_inicial_id`),
  KEY `idx_fecha_pago` (`fecha_pago`),
  CONSTRAINT `fk_pagos_iniciales_detalle_plan` FOREIGN KEY (`plan_inicial_id`) REFERENCES `pagos_iniciales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de pagos del plan inicial';


-- ============================================================================
-- 3. AGREGAR CAMPO: lotes.plan_inicial_id
-- ============================================================================
-- Campo para vincular el lote con su plan de pago inicial activo

-- Verificar si el campo ya existe antes de agregarlo
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'lotes' 
  AND COLUMN_NAME = 'plan_inicial_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `lotes` ADD COLUMN `plan_inicial_id` int(10) UNSIGNED DEFAULT NULL COMMENT ''FK al plan de pago inicial activo (si existe)'' AFTER `saldo_a_favor`',
    'SELECT "Campo plan_inicial_id ya existe" AS Mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índice
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'lotes' 
  AND INDEX_NAME = 'idx_plan_inicial_id';

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `lotes` ADD KEY `idx_plan_inicial_id` (`plan_inicial_id`)',
    'SELECT "Índice idx_plan_inicial_id ya existe" AS Mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar foreign key
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'lotes' 
  AND CONSTRAINT_NAME = 'fk_lotes_plan_inicial';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `lotes` ADD CONSTRAINT `fk_lotes_plan_inicial` FOREIGN KEY (`plan_inicial_id`) REFERENCES `pagos_iniciales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "Foreign key fk_lotes_plan_inicial ya existe" AS Mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ============================================================================
-- 4. TRIGGER: after_plan_inicial_completado
-- ============================================================================
-- Trigger que se ejecuta automáticamente cuando un plan inicial es completado
-- Cambia el lote de 'reservado' a 'vendido' y limpia la referencia al plan

DROP TRIGGER IF EXISTS `after_plan_inicial_completado`;

DELIMITER $$

CREATE TRIGGER `after_plan_inicial_completado`
AFTER UPDATE ON `pagos_iniciales`
FOR EACH ROW
BEGIN
    -- Solo ejecutar si el estado cambió a 'pagado_total'
    IF NEW.estado = 'pagado_total' AND OLD.estado != 'pagado_total' THEN
        
        -- Actualizar el lote asociado
        UPDATE lotes 
        SET 
            estado = 'vendido',        -- Cambiar de 'reservado' a 'vendido'
            plan_inicial_id = NULL     -- Limpiar la referencia ya que el plan está completo
        WHERE id = NEW.lote_id 
          AND estado = 'reservado';    -- Solo si está en estado reservado
        
    END IF;
END$$

DELIMITER ;


-- ============================================================================
-- 5. VISTA: vista_planes_iniciales_resumen
-- ============================================================================
-- Vista que proporciona un resumen completo de cada plan inicial con totales

DROP VIEW IF EXISTS `vista_planes_iniciales_resumen`;

CREATE OR REPLACE VIEW `vista_planes_iniciales_resumen` AS
SELECT 
    pi.id AS plan_id,
    pi.lote_id,
    l.codigo_lote,
    p.nombre AS proyecto_nombre,
    c.nombre AS cliente_nombre,
    pi.monto_inicial_total_requerido,
    pi.monto_pagado_hoy,
    pi.monto_pendiente_diferir,
    pi.plazo_meses,
    pi.cuota_mensual,
    pi.fecha_inicio,
    pi.estado AS estado_plan,
    l.estado AS estado_lote,
    
    -- Totales calculados desde pagos_iniciales_detalle
    COALESCE(SUM(pid.valor_pagado), 0) AS total_pagado_plan,
    (pi.monto_inicial_total_requerido - COALESCE(SUM(pid.valor_pagado), 0)) AS saldo_real_pendiente,
    
    -- Cuotas pagadas y pendientes
    COUNT(pid.id) AS cuotas_pagadas,
    (pi.plazo_meses - COUNT(pid.id)) AS cuotas_pendientes,
    
    -- Fechas
    MAX(pid.fecha_pago) AS fecha_ultimo_pago,
    pi.created_at AS fecha_creacion_plan,
    pi.updated_at AS fecha_actualizacion_plan
    
FROM pagos_iniciales pi
INNER JOIN lotes l ON pi.lote_id = l.id
INNER JOIN proyectos p ON l.proyecto_id = p.id
LEFT JOIN clientes c ON l.cliente_id = c.id
LEFT JOIN pagos_iniciales_detalle pid ON pi.id = pid.plan_inicial_id
GROUP BY 
    pi.id, pi.lote_id, l.codigo_lote, p.nombre, c.nombre,
    pi.monto_inicial_total_requerido, pi.monto_pagado_hoy, 
    pi.monto_pendiente_diferir, pi.plazo_meses, pi.cuota_mensual,
    pi.fecha_inicio, pi.estado, l.estado, pi.created_at, pi.updated_at;


-- ============================================================================
-- FIN DE LA ACTUALIZACIÓN
-- ============================================================================

SELECT '✅ ACTUALIZACIÓN COMPLETADA EXITOSAMENTE' AS Resultado;

-- Verificar tablas creadas
SELECT 
    'pagos_iniciales' AS Tabla,
    COUNT(*) AS Registros
FROM pagos_iniciales
UNION ALL
SELECT 
    'pagos_iniciales_detalle' AS Tabla,
    COUNT(*) AS Registros
FROM pagos_iniciales_detalle;

-- Verificar campo agregado
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'lotes'
  AND COLUMN_NAME = 'plan_inicial_id';

-- Verificar trigger
SHOW TRIGGERS WHERE `Trigger` = 'after_plan_inicial_completado';

-- Verificar vista
SELECT 
    TABLE_NAME,
    TABLE_TYPE
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'vista_planes_iniciales_resumen';
