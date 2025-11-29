-- =====================================================================================
-- SCRIPT DE PRUEBA: Datos SIMPLIFICADOS para validar Sistema de Saldo a Favor
-- Descripción: Una única cuota en mora + saldo a favor para compensar
-- Fecha: 2025-11-29
-- =====================================================================================

-- 1. Insertar Cliente
INSERT IGNORE INTO `clientes` (
    `tipo_documento`, `numero_documento`, `nombre`, `email`, `telefono`, 
    `ciudad`, `created_at`
) VALUES (
    'CC', '1111111111', 'Cliente Mora Simple', 'cliente@test.com', '3001111111',
    'Medellín', NOW()
);

SET @cliente_id = (SELECT id FROM clientes WHERE numero_documento = '1111111111' LIMIT 1);

-- 2. Insertar Proyecto
INSERT IGNORE INTO `proyectos` (
    `codigo`, `nombre`, `ubicacion`, `descripcion`, `estado`, `fecha_inicio`
) VALUES (
    'PRY-SIMPLE', 'Proyecto Simple Mora', 'Medellín',
    'Proyecto simple para prueba de saldo a favor',
    'activo', '2025-01-01'
);

SET @proyecto_id = (SELECT id FROM proyectos WHERE codigo = 'PRY-SIMPLE' LIMIT 1);

-- 3. Crear un LOTE simple (12 cuotas)
INSERT INTO `lotes` (
    `proyecto_id`, `codigo_lote`, `manzana`, `area_m2`, `precio_lista`,
    `monto_financiado`, `tasa_interes`, `numero_cuotas`, 
    `fecha_inicio_amortizacion`, `estado`, `cliente_id`, `saldo_a_favor`
) VALUES (
    @proyecto_id, 'LOTE-SIMPLE', 'M-01', 100.00, 12000000.00,
    12000000.00, 12.00, 12, '2025-10-15', 'vendido', @cliente_id, 0.00
);

SET @lote_id = LAST_INSERT_ID();

-- 4. Limpiar cuotas anteriores si existen
DELETE FROM `amortizaciones` WHERE `lote_id` = @lote_id;

-- 5. Crear 12 cuotas simples (cuota fija de $1.000.000 aprox)
INSERT INTO `amortizaciones` (
    `lote_id`, `numero_cuota`, `fecha_vencimiento`, 
    `valor_cuota`, `capital`, `interes`, `saldo`, `valor_pagado`, 
    `estado`, `created_at`
) VALUES
    (@lote_id, 1, '2025-11-15', 1000000.00, 900000.00, 100000.00, 11100000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 2, '2025-12-15', 1000000.00, 910000.00, 90000.00, 10190000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 3, '2026-01-15', 1000000.00, 920000.00, 80000.00, 9270000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 4, '2026-02-15', 1000000.00, 930000.00, 70000.00, 8340000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 5, '2026-03-15', 1000000.00, 940000.00, 60000.00, 7400000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 6, '2026-04-15', 1000000.00, 950000.00, 50000.00, 6450000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 7, '2026-05-15', 1000000.00, 960000.00, 40000.00, 5490000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 8, '2026-06-15', 1000000.00, 970000.00, 30000.00, 4520000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 9, '2026-07-15', 1000000.00, 980000.00, 20000.00, 3540000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 10, '2026-08-15', 1000000.00, 990000.00, 10000.00, 2550000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 11, '2026-09-15', 1000000.00, 1000000.00, 0.00, 1550000.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 12, '2026-10-15', 1000000.00, 1000000.00, 0.00, 550000.00, 0.00, 'pendiente', NOW());

-- =====================================================================================
-- ESCENARIO: UNA CUOTA EN MORA
-- ========================================================================================
-- Cuota 1: VENCIDA (15 de noviembre pasado) - EN MORA
-- Cuota 2 en adelante: PENDIENTES (futuras)
-- Saldo a Favor: $0.00 (TÚ LO AGREGARÁS después registrando un pago mayor)
-- ========================================================================================

-- =====================================================================================
-- ESTADO ACTUAL DESPUÉS DE ESTE SCRIPT
-- =====================================================================================
-- 
-- LOTE ID: @lote_id (LOTE-SIMPLE)
-- Cliente: Cliente Mora Simple (Doc: 1111111111)
-- Proyecto: Proyecto Simple Mora (PRY-SIMPLE)
-- 
-- ESTADO INICIAL:
--   • Cuota 1: VENCIDA (15 de noviembre 2025) ⚠️  EN MORA
--   • Cuota 2-12: PENDIENTES (futuras)
--   • Saldo a Favor: $0.00 (TÚ LO AGREGARÁS después)
--
-- =====================================================================================

-- Script de Verificación Final
-- Ejecuta estas queries para validar el estado:

-- A) Ver el LOTE creado:
-- SELECT id, codigo_lote, cliente_id, saldo_a_favor, numero_cuotas 
-- FROM lotes WHERE codigo_lote = 'LOTE-SIMPLE';

-- B) Ver el CLIENTE:
-- SELECT id, tipo_documento, numero_documento, nombre 
-- FROM clientes WHERE numero_documento = '1111111111';

-- C) Ver el PLAN DE AMORTIZACIÓN (primeras 3 cuotas):
-- SELECT numero_cuota, fecha_vencimiento, valor_cuota, estado, valor_pagado 
-- FROM amortizaciones 
-- WHERE lote_id IN (SELECT id FROM lotes WHERE codigo_lote = 'LOTE-SIMPLE')
-- ORDER BY numero_cuota ASC 
-- LIMIT 3;

-- D) Ver SALDO A FAVOR:
-- SELECT id, saldo_a_favor FROM lotes WHERE codigo_lote = 'LOTE-SIMPLE';

-- =====================================================================================
-- PRÓXIMOS PASOS
-- =====================================================================================
-- 
-- 1. Ejecutar este script para crear datos iniciales
--
-- 2. Obtener el LOTE ID:
--    SELECT id FROM lotes WHERE codigo_lote = 'LOTE-SIMPLE';
--
-- 3. En la UI (/lotes/amortizacion/show/{ID}):
--    - Verás Cuota 1 VENCIDA (en mora)
--    - Saldo a Favor: $0.00 (sin botón para aplicar)
--
-- 4. Luego registrar un PAGO CON EXCEDENTE en Cuota 1:
--    - Ej: Pagar $1.300.000 cuando cuota es $1.000.000
--    - Excedente: $300.000 → Se acumula en saldo_a_favor
--
-- 5. Después de registrar el pago:
--    - Cuota 1: PAGADA ✅
--    - Saldo a Favor: $300.000 ✓
--    - Botón [💰 Aplicar] aparecerá (visible si hay mora en otras cuotas)
--
-- 6. Luego puedes hacer click en el botón para compensar la mora
--
-- ======================================================================================
