-- ============================================================
-- MIGRACIÓN: Sistema de Pago Inicial Diferido (Plan de Enganche)
-- Fecha: 2025-12-02
-- Descripción: Añade soporte para diferir el pago inicial en cuotas
--              antes de generar el plan de amortización principal
-- ============================================================

-- --------------------------------------------------------
-- 1. CREAR TABLA: pagos_iniciales
-- --------------------------------------------------------
-- Almacena el plan de diferido para la inicial del lote

CREATE TABLE IF NOT EXISTS `pagos_iniciales` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lote_id` int(10) UNSIGNED NOT NULL,
  `monto_inicial_total_requerido` decimal(15,2) NOT NULL COMMENT 'Total de inicial que debe pagarse',
  `monto_pagado_hoy` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Abono inicial del primer día',
  `monto_pendiente_diferir` decimal(15,2) NOT NULL COMMENT 'Saldo que se diferirá en cuotas',
  `plazo_meses` int(3) NOT NULL COMMENT 'Número de meses para pagar el saldo',
  `cuota_mensual` decimal(15,2) NOT NULL COMMENT 'Valor de cada cuota mensual',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio del plan',
  `estado` enum('pendiente','en_curso','pagado_total','cancelado') NOT NULL DEFAULT 'en_curso',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lote_id` (`lote_id`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `fk_pagos_iniciales_lote` FOREIGN KEY (`lote_id`) 
    REFERENCES `lotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Plan de pago diferido para la inicial de un lote';

-- --------------------------------------------------------
-- 2. CREAR TABLA: pagos_iniciales_detalle
-- --------------------------------------------------------
-- Registro detallado de cada pago realizado contra el plan inicial

CREATE TABLE IF NOT EXISTS `pagos_iniciales_detalle` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_inicial_id` int(10) UNSIGNED NOT NULL,
  `fecha_pago` date NOT NULL,
  `valor_pagado` decimal(15,2) NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','cheque','tarjeta') NOT NULL,
  `numero_recibo` varchar(50) DEFAULT NULL,
  `saldo_pendiente_despues` decimal(15,2) NOT NULL COMMENT 'Saldo restante después de este pago',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_plan_inicial_id` (`plan_inicial_id`),
  KEY `idx_fecha_pago` (`fecha_pago`),
  CONSTRAINT `fk_pagos_iniciales_detalle_plan` FOREIGN KEY (`plan_inicial_id`) 
    REFERENCES `pagos_iniciales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detalle de pagos realizados contra el plan inicial';

-- --------------------------------------------------------
-- 3. ACTUALIZAR TABLA: lotes
-- --------------------------------------------------------
-- Añadir campo de control para vincular lote con su plan inicial activo

ALTER TABLE `lotes` 
ADD COLUMN `plan_inicial_id` int(10) UNSIGNED DEFAULT NULL 
  COMMENT 'FK al plan de pago inicial activo (si existe)' 
  AFTER `saldo_a_favor`;

-- Añadir índice y constraint
ALTER TABLE `lotes`
ADD KEY `idx_plan_inicial_id` (`plan_inicial_id`),
ADD CONSTRAINT `fk_lotes_plan_inicial` FOREIGN KEY (`plan_inicial_id`) 
  REFERENCES `pagos_iniciales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------
-- 4. TRIGGER: Transición automática de estado al completar inicial
-- --------------------------------------------------------
-- Cuando el plan inicial llega a estado 'pagado_total', 
-- el lote cambia de 'reservado' a 'vendido' automáticamente

DELIMITER $$

CREATE TRIGGER `after_plan_inicial_completado` 
AFTER UPDATE ON `pagos_iniciales` 
FOR EACH ROW 
BEGIN
    -- Si el plan cambió a 'pagado_total'
    IF NEW.estado = 'pagado_total' AND OLD.estado != 'pagado_total' THEN
        -- Actualizar el lote asociado: cambiar de 'reservado' a 'vendido'
        UPDATE lotes 
        SET estado = 'vendido',
            plan_inicial_id = NULL  -- Limpiar la referencia ya que el plan está completo
        WHERE id = NEW.lote_id 
          AND estado = 'reservado';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- 5. VISTA: Resumen de planes iniciales por lote
-- --------------------------------------------------------
-- Facilita consultas sobre el estado de los planes de pago inicial

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
    
    -- Calcular pagos realizados
    COALESCE(SUM(pid.valor_pagado), 0) AS total_pagado_plan,
    (pi.monto_inicial_total_requerido - COALESCE(SUM(pid.valor_pagado), 0)) AS saldo_real_pendiente,
    
    -- Cuotas pagadas
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

-- --------------------------------------------------------
-- 6. COMENTARIOS Y DOCUMENTACIÓN
-- --------------------------------------------------------

/*
FLUJO DE ESTADOS DEL LOTE CON PAGO INICIAL DIFERIDO:
=====================================================

1. DISPONIBLE → RESERVADO (con plan_inicial_id):
   - Se crea un lote y se vende con pago inicial diferido
   - Se genera registro en pagos_iniciales
   - El lote queda en estado 'reservado' hasta completar la inicial

2. RESERVADO → VENDIDO (automático):
   - Cuando el plan inicial llega a estado 'pagado_total'
   - El trigger 'after_plan_inicial_completado' cambia el lote a 'vendido'
   - Se limpia el campo plan_inicial_id del lote
   - AHORA SÍ se puede crear el plan de amortización principal

ESTADOS DEL PLAN INICIAL:
========================
- 'pendiente': Plan creado pero sin pagos aún
- 'en_curso': Plan activo con pagos en proceso
- 'pagado_total': Plan completado, lote listo para amortización principal
- 'cancelado': Plan cancelado (cliente desistió)

VALIDACIÓN CRÍTICA:
==================
- NO se puede crear plan de amortización principal si el lote tiene plan_inicial_id activo
- El LoteController debe validar que el estado sea 'vendido' Y que plan_inicial_id sea NULL
- El monto_financiado del plan principal será: precio_lista - monto_inicial_total_requerido
*/

-- ============================================================
-- FIN DE MIGRACIÓN
-- ============================================================
-- Ejecutar este script en la base de datos de producción
-- Verificar que no haya errores antes de continuar
-- ============================================================
