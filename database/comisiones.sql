-- =====================================================
-- TABLA DE COMISIONES DE VENDEDORES
-- =====================================================

CREATE TABLE IF NOT EXISTS `comisiones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lote_id` int(10) UNSIGNED NOT NULL COMMENT 'Lote vendido',
  `vendedor_id` int(10) UNSIGNED NOT NULL COMMENT 'Vendedor que realizó la venta',
  `valor_venta` decimal(15,2) NOT NULL COMMENT 'Valor total de la venta',
  `porcentaje_comision` decimal(5,2) NOT NULL COMMENT 'Porcentaje de comisión aplicado',
  `valor_comision` decimal(15,2) NOT NULL COMMENT 'Valor de la comisión calculada',
  `estado` enum('pendiente','pagada','cancelada') DEFAULT 'pendiente',
  `fecha_venta` date NOT NULL COMMENT 'Fecha de la venta',
  `fecha_pago_comision` date DEFAULT NULL COMMENT 'Fecha en que se pagó la comisión',
  `metodo_pago` enum('efectivo','transferencia','cheque','otro') DEFAULT NULL,
  `referencia_pago` varchar(100) DEFAULT NULL COMMENT 'Número de comprobante o referencia',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lote` (`lote_id`),
  KEY `idx_vendedor` (`vendedor_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_venta` (`fecha_venta`),
  CONSTRAINT `comisiones_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comisiones_ibfk_2` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONFIGURACIÓN DE PORCENTAJES DE COMISIÓN POR VENDEDOR
-- =====================================================

CREATE TABLE IF NOT EXISTS `configuracion_comisiones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendedor_id` int(10) UNSIGNED NOT NULL,
  `porcentaje_comision` decimal(5,2) NOT NULL DEFAULT 3.00 COMMENT 'Porcentaje de comisión por defecto',
  `activo` tinyint(1) DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_vendedor` (`vendedor_id`),
  CONSTRAINT `configuracion_comisiones_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRIGGER: Crear comisión automáticamente al vender lote
-- =====================================================

DELIMITER $$
CREATE TRIGGER `after_lote_vendido` 
AFTER UPDATE ON `lotes` 
FOR EACH ROW 
BEGIN
    -- Si el lote cambió a vendido Y tiene vendedor asignado
    IF NEW.estado = 'vendido' AND OLD.estado != 'vendido' AND NEW.vendedor_id IS NOT NULL THEN
        -- Obtener el porcentaje de comisión configurado para el vendedor
        SET @porcentaje = (
            SELECT porcentaje_comision 
            FROM configuracion_comisiones 
            WHERE vendedor_id = NEW.vendedor_id AND activo = 1 
            LIMIT 1
        );
        
        -- Si no tiene configuración, usar 3% por defecto
        IF @porcentaje IS NULL THEN
            SET @porcentaje = 3.00;
        END IF;
        
        -- Calcular valor de la venta (usar precio_venta si existe, sino precio_lista)
        SET @valor_venta = COALESCE(NEW.precio_venta, NEW.precio_lista);
        
        -- Calcular comisión
        SET @valor_comision = (@valor_venta * @porcentaje / 100);
        
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
            NEW.vendedor_id,
            @valor_venta,
            @porcentaje,
            @valor_comision,
            'pendiente',
            COALESCE(NEW.fecha_venta, CURDATE())
        );
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- Insertar configuración por defecto para vendedores existentes
-- =====================================================

INSERT INTO configuracion_comisiones (vendedor_id, porcentaje_comision, activo, observaciones)
SELECT 
    id,
    3.00 as porcentaje_comision,
    1 as activo,
    'Configuración inicial - 3% por defecto' as observaciones
FROM users 
WHERE rol IN ('administrador', 'vendedor') 
AND activo = 1
AND id NOT IN (SELECT vendedor_id FROM configuracion_comisiones);
