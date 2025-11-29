-- =====================================================
-- FIX TRIGGER ERROR: estado_pago -> estado
-- =====================================================
-- Este script corrige el trigger que está causando el error
-- "Unknown column 'estado_pago' in 'NEW'"
-- =====================================================

-- 1. Eliminar el trigger defectuoso
DROP TRIGGER IF EXISTS `before_amortizacion_update`;

-- 2. Recrear el trigger con el nombre correcto de columna
DELIMITER $$
CREATE TRIGGER `before_amortizacion_update` 
BEFORE UPDATE ON `amortizaciones` 
FOR EACH ROW 
BEGIN
    -- Actualizar días de mora cuando el estado cambia a mora
    IF NEW.estado = 'mora' THEN
        SET NEW.dias_mora = DATEDIFF(CURDATE(), NEW.fecha_vencimiento);
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- Verificar que el trigger se creó correctamente
-- =====================================================
-- Ejecutar después de aplicar el fix:
-- SHOW TRIGGERS WHERE `Table` = 'amortizaciones';
