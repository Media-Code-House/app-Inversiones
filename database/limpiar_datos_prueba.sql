-- =====================================================================================
-- SCRIPT: Limpiar datos de prueba antiguos
-- Descripción: Elimina todos los datos de prueba anteriores antes de la nueva prueba
-- Fecha: 2025-11-29
-- =====================================================================================

-- Buscar y eliminar datos de prueba anteriores

-- 1. Eliminar pagos relacionados con lotes de prueba antigua
DELETE FROM `pagos` 
WHERE `amortizacion_id` IN (
    SELECT id FROM `amortizaciones` 
    WHERE `lote_id` IN (
        SELECT id FROM `lotes` 
        WHERE `codigo_lote` = 'LOTE-TEST-001'
    )
);

-- 2. Eliminar amortizaciones del lote de prueba antigua
DELETE FROM `amortizaciones` 
WHERE `lote_id` IN (
    SELECT id FROM `lotes` 
    WHERE `codigo_lote` = 'LOTE-TEST-001'
);

-- 3. Eliminar el lote de prueba antigua
DELETE FROM `lotes` 
WHERE `codigo_lote` = 'LOTE-TEST-001';

-- 4. Eliminar proyectos de prueba antiguos
DELETE FROM `proyectos` 
WHERE `codigo` IN ('PRY-TEST', 'PRY-SIMPLE')
AND `nombre` LIKE '%prueba%' OR `nombre` LIKE '%Prueba%' OR `nombre` LIKE '%PRUEBA%';

-- 5. Eliminar cliente de prueba antiguo
DELETE FROM `clientes` 
WHERE `numero_documento` = '1234567890';

-- =====================================================================================
-- Verificación: Confirmar que fue limpiado
-- =====================================================================================

-- Verificar que no existen lotes con código de prueba:
-- SELECT COUNT(*) as lotes_prueba FROM lotes WHERE codigo_lote = 'LOTE-TEST-001';

-- Verificar que no existen clientes con ese documento:
-- SELECT COUNT(*) as clientes_prueba FROM clientes WHERE numero_documento = '1234567890';

-- =====================================================================================
