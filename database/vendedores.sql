-- =====================================================
-- MÓDULO DE GESTIÓN DE VENDEDORES Y COMISIONES
-- =====================================================
-- Este archivo crea todas las tablas necesarias para:
-- - Gestión de vendedores
-- - Registro y seguimiento de comisiones
-- - Configuración de porcentajes de comisión
-- - Historial de pagos de comisiones
-- =====================================================

-- =====================================================
-- TABLA: vendedores
-- Información completa de vendedores
-- =====================================================

CREATE TABLE IF NOT EXISTS `vendedores` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Relación con tabla users',
  `codigo_vendedor` varchar(20) NOT NULL COMMENT 'Código único del vendedor',
  `tipo_documento` enum('CC','NIT','CE','pasaporte') DEFAULT 'CC',
  `numero_documento` varchar(50) NOT NULL COMMENT 'Documento de identidad',
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL COMMENT 'Fecha de ingreso a la empresa',
  `fecha_salida` date DEFAULT NULL COMMENT 'Fecha de retiro (si aplica)',
  `tipo_contrato` enum('indefinido','fijo','prestacion_servicios','freelance') DEFAULT 'indefinido',
  `porcentaje_comision_default` decimal(5,2) NOT NULL DEFAULT 3.00 COMMENT 'Porcentaje por defecto',
  `banco` varchar(100) DEFAULT NULL COMMENT 'Banco para pagos',
  `tipo_cuenta` enum('ahorros','corriente') DEFAULT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido') DEFAULT 'activo',
  `observaciones` text DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL COMMENT 'Ruta de la foto del vendedor',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user` (`user_id`),
  UNIQUE KEY `uk_codigo` (`codigo_vendedor`),
  UNIQUE KEY `uk_documento` (`numero_documento`),
  KEY `idx_email` (`email`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `vendedores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: comisiones
-- Registro de comisiones por ventas
-- =====================================================

CREATE TABLE IF NOT EXISTS `comisiones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lote_id` int(10) UNSIGNED NOT NULL COMMENT 'Lote vendido',
  `vendedor_id` int(10) UNSIGNED NOT NULL COMMENT 'Vendedor que realizó la venta',
  `valor_venta` decimal(15,2) NOT NULL COMMENT 'Valor total de la venta del lote',
  `porcentaje_comision` decimal(5,2) NOT NULL COMMENT 'Porcentaje aplicado en esta venta',
  `valor_comision` decimal(15,2) NOT NULL COMMENT 'Valor calculado de la comisión',
  `estado` enum('pendiente','pagada','pagada_parcial','cancelada') DEFAULT 'pendiente',
  `fecha_venta` date NOT NULL COMMENT 'Fecha de la venta del lote',
  `fecha_calculo` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de cálculo de la comisión',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lote` (`lote_id`),
  KEY `idx_vendedor` (`vendedor_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_venta` (`fecha_venta`),
  CONSTRAINT `comisiones_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comisiones_ibfk_2` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pagos_comisiones
-- Historial de pagos realizados a vendedores
-- =====================================================

CREATE TABLE IF NOT EXISTS `pagos_comisiones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `comision_id` int(10) UNSIGNED NOT NULL COMMENT 'Comisión que se está pagando',
  `vendedor_id` int(10) UNSIGNED NOT NULL COMMENT 'Vendedor receptor del pago',
  `valor_pagado` decimal(15,2) NOT NULL COMMENT 'Valor del pago realizado',
  `fecha_pago` date NOT NULL COMMENT 'Fecha del pago',
  `metodo_pago` enum('efectivo','transferencia','cheque','consignacion','otro') NOT NULL DEFAULT 'transferencia',
  `numero_comprobante` varchar(100) DEFAULT NULL COMMENT 'Número de comprobante/transacción',
  `banco` varchar(100) DEFAULT NULL COMMENT 'Banco utilizado para el pago',
  `referencia` varchar(255) DEFAULT NULL COMMENT 'Referencia adicional del pago',
  `observaciones` text DEFAULT NULL,
  `usuario_registro_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Usuario que registró el pago',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comision` (`comision_id`),
  KEY `idx_vendedor` (`vendedor_id`),
  KEY `idx_fecha_pago` (`fecha_pago`),
  KEY `idx_usuario_registro` (`usuario_registro_id`),
  CONSTRAINT `pagos_comisiones_ibfk_1` FOREIGN KEY (`comision_id`) REFERENCES `comisiones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pagos_comisiones_ibfk_2` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pagos_comisiones_ibfk_3` FOREIGN KEY (`usuario_registro_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: metas_vendedores
-- Metas mensuales/trimestrales/anuales para vendedores
-- =====================================================

CREATE TABLE IF NOT EXISTS `metas_vendedores` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendedor_id` int(10) UNSIGNED NOT NULL,
  `periodo_tipo` enum('mensual','trimestral','semestral','anual') NOT NULL DEFAULT 'mensual',
  `periodo_inicio` date NOT NULL COMMENT 'Fecha inicio del periodo',
  `periodo_fin` date NOT NULL COMMENT 'Fecha fin del periodo',
  `meta_ventas` int(11) NOT NULL DEFAULT 0 COMMENT 'Número de lotes a vender',
  `meta_valor` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor en pesos a vender',
  `ventas_realizadas` int(11) DEFAULT 0 COMMENT 'Lotes vendidos en el periodo',
  `valor_vendido` decimal(15,2) DEFAULT 0.00 COMMENT 'Valor vendido en el periodo',
  `porcentaje_cumplimiento` decimal(5,2) GENERATED ALWAYS AS (
    CASE 
      WHEN `meta_ventas` > 0 THEN (`ventas_realizadas` / `meta_ventas` * 100)
      ELSE 0 
    END
  ) STORED COMMENT 'Cumplimiento calculado automáticamente',
  `estado` enum('activa','completada','vencida','cancelada') DEFAULT 'activa',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vendedor` (`vendedor_id`),
  KEY `idx_periodo` (`periodo_inicio`, `periodo_fin`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `metas_vendedores_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: historial_comisiones
-- Historial de cambios en porcentajes de comisión
-- =====================================================

CREATE TABLE IF NOT EXISTS `historial_comisiones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendedor_id` int(10) UNSIGNED NOT NULL,
  `porcentaje_anterior` decimal(5,2) NOT NULL,
  `porcentaje_nuevo` decimal(5,2) NOT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_modifico_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vendedor` (`vendedor_id`),
  KEY `idx_fecha` (`fecha_cambio`),
  CONSTRAINT `historial_comisiones_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historial_comisiones_ibfk_2` FOREIGN KEY (`usuario_modifico_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VISTA: vista_vendedores_resumen
-- Resumen completo de cada vendedor con sus estadísticas
-- =====================================================

CREATE OR REPLACE VIEW `vista_vendedores_resumen` AS
SELECT 
    v.id,
    v.codigo_vendedor,
    v.user_id,
    CONCAT(v.nombres, ' ', v.apellidos) as nombre_completo,
    v.email,
    v.telefono,
    v.celular,
    v.fecha_ingreso,
    v.porcentaje_comision_default,
    v.estado,
    u.rol,
    
    -- Estadísticas de ventas
    COUNT(DISTINCT l.id) as total_lotes_vendidos,
    COALESCE(SUM(l.precio_venta), 0) as valor_total_vendido,
    
    -- Estadísticas de comisiones
    COUNT(DISTINCT c.id) as total_comisiones,
    COALESCE(SUM(c.valor_comision), 0) as total_comisiones_generadas,
    COALESCE(SUM(CASE WHEN c.estado = 'pendiente' THEN c.valor_comision ELSE 0 END), 0) as comisiones_pendientes,
    COALESCE(SUM(CASE WHEN c.estado = 'pagada' THEN c.valor_comision ELSE 0 END), 0) as comisiones_pagadas,
    
    -- Estadísticas de pagos
    COUNT(DISTINCT pc.id) as total_pagos_recibidos,
    COALESCE(SUM(pc.valor_pagado), 0) as total_dinero_recibido,
    
    -- Última venta
    MAX(l.fecha_venta) as fecha_ultima_venta,
    
    -- Última comisión
    MAX(c.fecha_venta) as fecha_ultima_comision
    
FROM vendedores v
INNER JOIN users u ON v.user_id = u.id
LEFT JOIN lotes l ON v.id = l.vendedor_id AND l.estado = 'vendido'
LEFT JOIN comisiones c ON v.id = c.vendedor_id
LEFT JOIN pagos_comisiones pc ON v.id = pc.vendedor_id
WHERE v.estado = 'activo'
GROUP BY v.id, v.codigo_vendedor, v.user_id, v.nombres, v.apellidos, 
         v.email, v.telefono, v.celular, v.fecha_ingreso, 
         v.porcentaje_comision_default, v.estado, u.rol;

-- =====================================================
-- TRIGGER: after_lote_vendido
-- Crea automáticamente la comisión cuando se vende un lote
-- =====================================================

DELIMITER $$
CREATE TRIGGER `after_lote_vendido` 
AFTER UPDATE ON `lotes` 
FOR EACH ROW 
BEGIN
    DECLARE v_vendedor_id INT;
    DECLARE v_porcentaje DECIMAL(5,2);
    DECLARE v_valor_venta DECIMAL(15,2);
    DECLARE v_valor_comision DECIMAL(15,2);
    
    -- Si el lote cambió a vendido Y tiene vendedor asignado (user_id)
    IF NEW.estado = 'vendido' AND OLD.estado != 'vendido' AND NEW.vendedor_id IS NOT NULL THEN
        
        -- Obtener el ID del vendedor desde la tabla vendedores usando el user_id
        SELECT id, porcentaje_comision_default 
        INTO v_vendedor_id, v_porcentaje
        FROM vendedores 
        WHERE user_id = NEW.vendedor_id 
        AND estado = 'activo'
        LIMIT 1;
        
        -- Solo proceder si encontramos un vendedor activo
        IF v_vendedor_id IS NOT NULL THEN
            
            -- Calcular valor de la venta (usar precio_venta si existe, sino precio_lista)
            SET v_valor_venta = COALESCE(NEW.precio_venta, NEW.precio_lista);
            
            -- Calcular comisión
            SET v_valor_comision = (v_valor_venta * v_porcentaje / 100);
            
            -- Insertar registro de comisión
            INSERT INTO comisiones (
                lote_id, 
                vendedor_id, 
                valor_venta, 
                porcentaje_comision, 
                valor_comision, 
                estado, 
                fecha_venta
            ) VALUES (
                NEW.id,
                v_vendedor_id,
                v_valor_venta,
                v_porcentaje,
                v_valor_comision,
                'pendiente',
                COALESCE(NEW.fecha_venta, CURDATE())
            );
        END IF;
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- TRIGGER: after_pago_comision_insert
-- Actualiza el estado de la comisión después de un pago
-- =====================================================

DELIMITER $$
CREATE TRIGGER `after_pago_comision_insert`
AFTER INSERT ON `pagos_comisiones`
FOR EACH ROW
BEGIN
    DECLARE v_total_pagado DECIMAL(15,2);
    DECLARE v_valor_comision DECIMAL(15,2);
    
    -- Obtener el valor de la comisión
    SELECT valor_comision INTO v_valor_comision
    FROM comisiones
    WHERE id = NEW.comision_id;
    
    -- Calcular total pagado para esta comisión
    SELECT COALESCE(SUM(valor_pagado), 0) INTO v_total_pagado
    FROM pagos_comisiones
    WHERE comision_id = NEW.comision_id;
    
    -- Actualizar estado de la comisión según lo pagado
    IF v_total_pagado >= v_valor_comision THEN
        UPDATE comisiones 
        SET estado = 'pagada' 
        WHERE id = NEW.comision_id;
    ELSEIF v_total_pagado > 0 THEN
        UPDATE comisiones 
        SET estado = 'pagada_parcial' 
        WHERE id = NEW.comision_id;
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- TRIGGER: before_vendedor_update_comision
-- Registra cambios en el porcentaje de comisión
-- =====================================================

DELIMITER $$
CREATE TRIGGER `before_vendedor_update_comision`
BEFORE UPDATE ON `vendedores`
FOR EACH ROW
BEGIN
    -- Si cambió el porcentaje de comisión, guardar en historial
    IF OLD.porcentaje_comision_default != NEW.porcentaje_comision_default THEN
        INSERT INTO historial_comisiones (
            vendedor_id,
            porcentaje_anterior,
            porcentaje_nuevo,
            fecha_cambio
        ) VALUES (
            NEW.id,
            OLD.porcentaje_comision_default,
            NEW.porcentaje_comision_default,
            NOW()
        );
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- Migrar vendedores existentes desde users a vendedores
-- (Solo si no existen registros en vendedores)

INSERT INTO vendedores (
    user_id,
    codigo_vendedor,
    numero_documento,
    nombres,
    apellidos,
    email,
    porcentaje_comision_default,
    fecha_ingreso,
    estado
)
SELECT 
    u.id,
    CONCAT('VEND-', LPAD(u.id, 4, '0')) as codigo_vendedor,
    CONCAT('DOC-', u.id) as numero_documento,
    SUBSTRING_INDEX(u.nombre, ' ', 1) as nombres,
    SUBSTRING_INDEX(u.nombre, ' ', -1) as apellidos,
    u.email,
    3.00 as porcentaje_comision_default,
    COALESCE(u.created_at, CURDATE()) as fecha_ingreso,
    CASE WHEN u.activo = 1 THEN 'activo' ELSE 'inactivo' END as estado
FROM users u
WHERE u.rol IN ('administrador', 'vendedor')
AND NOT EXISTS (SELECT 1 FROM vendedores v WHERE v.user_id = u.id);

-- =====================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

-- Índice compuesto para búsquedas de comisiones por vendedor y estado
CREATE INDEX idx_vendedor_estado ON comisiones(vendedor_id, estado);

-- Índice para búsquedas de pagos por fecha
CREATE INDEX idx_fecha_metodo ON pagos_comisiones(fecha_pago, metodo_pago);

-- Índice para vendedores activos
CREATE INDEX idx_estado_fecha ON vendedores(estado, fecha_ingreso);

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
