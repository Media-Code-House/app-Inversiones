-- =====================================================================================
-- SCRIPT DE PRUEBA: Datos para validar Sistema de Saldo a Favor Global
-- Descripción: Crea un cliente con mora que será compensada por saldo a favor
-- Fecha: 2025-11-29
-- =====================================================================================

-- 1. Insertar Cliente (si no existe, crear uno nuevo)
INSERT IGNORE INTO `clientes` (
    `tipo_documento`, `numero_documento`, `nombre`, `email`, `telefono`, 
    `ciudad`, `created_at`
) VALUES (
    'CC', '1234567890', 'Cliente Prueba Mora', 'prueba@test.com', '3001234567',
    'Medellín', NOW()
);

-- Obtener el ID del cliente
SET @cliente_id = (SELECT id FROM clientes WHERE numero_documento = '1234567890' LIMIT 1);

-- 2. Insertar Proyecto (si no existe, crear uno nuevo)
INSERT IGNORE INTO `proyectos` (
    `codigo`, `nombre`, `ubicacion`, `descripcion`, `estado`, `fecha_inicio`
) VALUES (
    'PRY-TEST', 'Proyecto Prueba Saldo a Favor', 'Medellín',
    'Proyecto de prueba para validar sistema de Saldo a Favor Global',
    'activo', '2025-01-01'
);

-- Obtener el ID del proyecto
SET @proyecto_id = (SELECT id FROM proyectos WHERE codigo = 'PRY-TEST' LIMIT 1);

-- 3. Crear un LOTE con cuotas mensuales (24 meses para prueba rápida)
-- Características:
--   - Monto: $20.000.000
--   - Plazo: 24 meses (2 años)
--   - Tasa: 12% anual
--   - Cuota aprox: $1.977.085
--   - El cliente pagó de más en Cuota 1 → Saldo a Favor

INSERT INTO `lotes` (
    `proyecto_id`, `codigo_lote`, `manzana`, `area_m2`, `precio_lista`,
    `monto_financiado`, `tasa_interes`, `numero_cuotas`, 
    `fecha_inicio_amortizacion`, `estado`, `cliente_id`
) VALUES (
    @proyecto_id, 'LOTE-TEST-001', 'M-01', 150.00, 20000000.00,
    20000000.00, 12.00, 24, '2025-02-15', 'vendido', @cliente_id
);

SET @lote_id = LAST_INSERT_ID();

-- 4. Limpiar cuotas anteriores si existen
DELETE FROM `amortizaciones` WHERE `lote_id` = @lote_id;

-- 5. Crear 24 cuotas de amortización (Plan Francés - cuota fija mensual)
-- Estructura: Mes 1-24, Cuota: $1.977.085, Tasa: 12% anual

-- Las 24 cuotas se crean con amortización francés (cuota fija)
-- Cada cuota vence el día 15 de cada mes
INSERT INTO `amortizaciones` (
    `lote_id`, `numero_cuota`, `fecha_vencimiento`, 
    `valor_cuota`, `capital`, `interes`, `saldo`, `valor_pagado`, 
    `estado`, `created_at`
) VALUES
    (@lote_id, 1, '2025-02-15', 1977085.83, 1644418.16, 332667.67, 18355581.84, 0.00, 'pendiente', NOW()),
    (@lote_id, 2, '2025-03-15', 1977085.83, 1659611.10, 317474.73, 16695970.74, 0.00, 'pendiente', NOW()),
    (@lote_id, 3, '2025-04-15', 1977085.83, 1675170.11, 301915.72, 15020800.63, 0.00, 'pendiente', NOW()),
    (@lote_id, 4, '2025-05-15', 1977085.83, 1691101.41, 285984.42, 13329699.22, 0.00, 'pendiente', NOW()),
    (@lote_id, 5, '2025-06-15', 1977085.83, 1707412.43, 269673.40, 11622286.79, 0.00, 'pendiente', NOW()),
    (@lote_id, 6, '2025-07-15', 1977085.83, 1724010.56, 253075.27, 9898276.23, 0.00, 'pendiente', NOW()),
    (@lote_id, 7, '2025-08-15', 1977085.83, 1740903.30, 236182.53, 8157372.93, 0.00, 'pendiente', NOW()),
    (@lote_id, 8, '2025-09-15', 1977085.83, 1758097.33, 219013.50, 6399275.60, 0.00, 'pendiente', NOW()),
    (@lote_id, 9, '2025-10-15', 1977085.83, 1775600.40, 201485.43, 4623675.20, 0.00, 'pendiente', NOW()),
    (@lote_id, 10, '2025-11-15', 1977085.83, 1793420.40, 183665.43, 2830254.80, 0.00, 'pendiente', NOW()),
    (@lote_id, 11, '2025-12-15', 1977085.83, 1811566.21, 165519.62, 1018688.59, 0.00, 'pendiente', NOW()),
    (@lote_id, 12, '2026-01-15', 1977085.83, 1829456.24, 147629.59, -810767.65, 0.00, 'pendiente', NOW()),
    (@lote_id, 13, '2026-02-15', 1977085.83, 1812500.00, 164585.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 14, '2026-03-15', 1977085.83, 1830000.00, 147085.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 15, '2026-04-15', 1977085.83, 1847500.00, 129585.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 16, '2026-05-15', 1977085.83, 1865000.00, 112085.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 17, '2026-06-15', 1977085.83, 1882500.00, 94585.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 18, '2026-07-15', 1977085.83, 1900000.00, 77085.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 19, '2026-08-15', 1977085.83, 1917500.00, 59585.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 20, '2026-09-15', 1977085.83, 1935000.00, 42085.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 21, '2026-10-15', 1977085.83, 1952500.00, 24585.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 22, '2026-11-15', 1977085.83, 1970000.00, 7085.83, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 23, '2026-12-15', 1977085.83, 1977085.83, 0.00, 0.00, 0.00, 'pendiente', NOW()),
    (@lote_id, 24, '2027-01-15', 1977085.83, 1977085.83, 0.00, 0.00, 0.00, 'pendiente', NOW());

-- 5. ESCENARIO REALISTA DE MORA Y SALDO A FAVOR
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- El cliente pagó en Febrero (Cuota 1): $2.500.000 (+ $522.914 de excedente)
-- El cliente NO pagó en Marzo (Cuota 2) → MORA
-- El cliente NO pagó en Abril (Cuota 3) → MORA
-- El cliente pagó en Mayo (Cuota 4): $1.977.085
-- 
-- Resultado: Cliente está en MORA en Cuotas 2 y 3
-- Pero tiene $522.914 en saldo_a_favor que puede compensar la mora
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

-- Paso 1: Obtener IDs de las cuotas
SET @cuota_1_id = (SELECT id FROM amortizaciones WHERE lote_id = @lote_id AND numero_cuota = 1 LIMIT 1);
SET @cuota_4_id = (SELECT id FROM amortizaciones WHERE lote_id = @lote_id AND numero_cuota = 4 LIMIT 1);

-- Paso 2: Registrar pago de Cuota 1 (Febrero) - PAGO EXCEDENTE
INSERT INTO `pagos` (
    `amortizacion_id`, `fecha_pago`, `valor_pagado`, `metodo_pago`,
    `numero_recibo`, `observaciones`, `created_at`
) VALUES (
    @cuota_1_id, '2025-02-10', 2500000.00, 'transferencia',
    'TRF-2025-02-001', 'Pago Cuota 1 - Exceso de $522.914', NOW()
);

-- Paso 3: Actualizar cuota 1 como PAGADA
UPDATE `amortizaciones` 
SET `estado` = 'pagada', `valor_pagado` = 2500000.00, `fecha_pago` = '2025-02-10'
WHERE `id` = @cuota_1_id;

-- Paso 4: Registrar el EXCEDENTE en saldo_a_favor del lote
-- (2.500.000 - 1.977.085,83 = 522.914,17)
UPDATE `lotes` 
SET `saldo_a_favor` = 522914.17
WHERE `id` = @lote_id;

-- Paso 5: Cuota 2 (Marzo) - SIN PAGAR → MORA (estado='pendiente' por defecto)
-- Se deja como está (estado = 'pendiente', valor_pagado = 0)

-- Paso 6: Cuota 3 (Abril) - SIN PAGAR → MORA (estado='pendiente' por defecto)
-- Se deja como está (estado = 'pendiente', valor_pagado = 0)

-- Paso 7: Registrar pago de Cuota 4 (Mayo) - PAGO NORMAL
INSERT INTO `pagos` (
    `amortizacion_id`, `fecha_pago`, `valor_pagado`, `metodo_pago`,
    `numero_recibo`, `observaciones`, `created_at`
) VALUES (
    @cuota_4_id, '2025-05-10', 1977085.83, 'transferencia',
    'TRF-2025-05-001', 'Pago Normal Cuota 4', NOW()
);

-- Paso 8: Actualizar cuota 4 como PAGADA
UPDATE `amortizaciones` 
SET `estado` = 'pagada', `valor_pagado` = 1977085.83, `fecha_pago` = '2025-05-10'
WHERE `id` = @cuota_4_id;

-- =====================================================================================
-- ESTADO ACTUAL DESPUÉS DE ESTE SCRIPT
-- =====================================================================================
-- 
-- LOTE ID: @lote_id
-- Cliente: Cliente Prueba Mora (Doc: 1234567890)
-- 
-- CUOTAS PAGADAS:
--   • Cuota 1 (Feb 15): $2.500.000 ✅ PAGADA (Exceso $522.914)
--   • Cuota 4 (May 15): $1.977.085 ✅ PAGADA
--
-- CUOTAS EN MORA (NO PAGADAS):
--   • Cuota 2 (Mar 15): $1.977.085 ⚠️  MORA (30+ días)
--   • Cuota 3 (Apr 15): $1.977.085 ⚠️  MORA (30+ días)
--
-- CUOTAS PENDIENTES (FUTURAS):
--   • Cuota 5 en adelante...
--
-- SALDO A FAVOR DISPONIBLE: $522.914
--
-- PRÓXIMO PASO EN LA UI:
--   1. Accede a: /lotes/amortizacion/show/@lote_id
--   2. Verás el botón AZUL: "Aplicar Saldo a Favor ($522.914)"
--   3. Haz click → Confirma → Se ejecuta reajuste
--   4. Cuota 2 se marca como PAGADA (compensada completamente)
--   5. Cuota 3 queda con saldo parcial ($1.454.171)
--   6. Cliente sale PARCIALMENTE de MORA
--   7. Botón desaparece (saldo = 0)
--
-- =====================================================================================

-- Script de Verificación Final
-- Ejecuta estas queries para validar el estado:

-- A) Ver el LOTE creado:
-- SELECT id, codigo_lote, cliente_id, saldo_a_favor, numero_cuotas 
-- FROM lotes WHERE codigo_lote = 'LOTE-TEST-001';

-- B) Ver el CLIENTE:
-- SELECT id, tipo_documento, numero_documento, nombre 
-- FROM clientes WHERE numero_documento = '1234567890';

-- C) Ver el PLAN DE AMORTIZACIÓN (primeras 5 cuotas):
-- SELECT numero_cuota, fecha_vencimiento, valor_cuota, estado, valor_pagado 
-- FROM amortizaciones 
-- WHERE lote_id IN (SELECT id FROM lotes WHERE codigo_lote = 'LOTE-TEST-001')
-- ORDER BY numero_cuota ASC 
-- LIMIT 5;

-- D) Ver los PAGOS registrados:
-- SELECT a.numero_cuota, p.fecha_pago, p.valor_pagado, p.metodo_pago, p.observaciones 
-- FROM pagos p
-- JOIN amortizaciones a ON p.amortizacion_id = a.id
-- WHERE a.lote_id IN (SELECT id FROM lotes WHERE codigo_lote = 'LOTE-TEST-001')
-- ORDER BY p.fecha_pago ASC;

-- E) Ver SALDO A FAVOR actual:
-- SELECT saldo_a_favor FROM lotes WHERE codigo_lote = 'LOTE-TEST-001';

-- =====================================================================================
-- NOTAS IMPORTANTES
-- =====================================================================================
-- 
-- 1. Si no existe la columna 'saldo_a_favor', ejecutar primero:
--    mysql -u root -p inversiones < database/migration_saldo_a_favor.sql
--
-- 2. Este script genera:
--    - 1 Cliente de prueba
--    - 1 Proyecto de prueba
--    - 1 Lote con 24 cuotas
--    - 2 Pagos registrados
--    - Saldo a favor: $522.914
--
-- 3. Para obtener el LOTE ID ejecuta:
--    SELECT id FROM lotes WHERE codigo_lote = 'LOTE-TEST-001';
--
--    Luego accede a: /lotes/amortizacion/show/{ID}
--
-- ======================================================================================
