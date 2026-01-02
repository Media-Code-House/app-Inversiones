-- ============================================================
-- Script para generar comisiones faltantes de lotes vendidos
-- ============================================================
-- Este script identifica lotes vendidos con vendedor asignado
-- que no tienen comisión asociada y las genera automáticamente
-- ============================================================

-- PASO 1: Verificar lotes vendidos con vendedor pero SIN comisión
SELECT 
    l.id,
    l.codigo_lote,
    p.nombre as proyecto,
    l.vendedor_id,
    u.nombre as vendedor_nombre,
    u.email as vendedor_email,
    COALESCE(l.precio_venta, l.precio_lista) as valor_venta,
    (COALESCE(l.precio_venta, l.precio_lista) * 0.03) as comision_3_porciento,
    l.fecha_venta,
    l.estado
FROM lotes l
INNER JOIN proyectos p ON l.proyecto_id = p.id
INNER JOIN users u ON l.vendedor_id = u.id
LEFT JOIN comisiones c ON l.id = c.lote_id
WHERE l.estado = 'vendido' 
AND l.vendedor_id IS NOT NULL
AND c.id IS NULL
ORDER BY l.fecha_venta DESC;

-- PASO 2: Verificar vendedores activos
SELECT 
    u.id,
    u.nombre,
    u.email,
    u.rol,
    u.activo,
    v.id as vendedor_record_id,
    v.codigo_vendedor,
    CONCAT(v.nombres, ' ', v.apellidos) as nombre_completo_vendedor
FROM users u
LEFT JOIN vendedores v ON u.id = v.user_id
WHERE u.rol IN ('administrador', 'vendedor') 
AND u.activo = 1
ORDER BY u.nombre;

-- PASO 3: Ver comisiones existentes (si hay)
SELECT 
    c.id,
    c.lote_id,
    l.codigo_lote,
    c.vendedor_id,
    u.nombre as vendedor,
    c.valor_venta,
    c.porcentaje_comision,
    c.valor_comision,
    c.estado,
    c.fecha_venta,
    c.created_at
FROM comisiones c
INNER JOIN lotes l ON c.lote_id = l.id
INNER JOIN users u ON c.vendedor_id = u.id
ORDER BY c.created_at DESC
LIMIT 20;

-- PASO 4: GENERAR COMISIONES FALTANTES
-- IMPORTANTE: Este INSERT generará comisiones para todos los lotes vendidos
-- que tengan vendedor asignado pero no tengan comisión creada

INSERT INTO comisiones (
    lote_id, 
    vendedor_id, 
    valor_venta, 
    porcentaje_comision, 
    valor_comision, 
    estado, 
    fecha_venta,
    observaciones
)
SELECT 
    l.id,
    l.vendedor_id,  -- Este campo apunta a users.id
    COALESCE(l.precio_venta, l.precio_lista) as valor_venta,
    3.00 as porcentaje,
    ROUND(COALESCE(l.precio_venta, l.precio_lista) * 0.03, 2) as valor_comision,
    'pendiente' as estado,
    COALESCE(l.fecha_venta, CURDATE()) as fecha_venta,
    'Comisión generada automáticamente por script de corrección' as observaciones
FROM lotes l
LEFT JOIN comisiones c ON l.id = c.lote_id
WHERE l.estado = 'vendido' 
AND l.vendedor_id IS NOT NULL
AND c.id IS NULL;

-- PASO 5: Verificar comisiones recién creadas
SELECT 
    c.id,
    c.lote_id,
    l.codigo_lote,
    p.nombre as proyecto,
    c.vendedor_id,
    u.nombre as vendedor,
    u.email,
    c.valor_venta,
    c.porcentaje_comision,
    c.valor_comision,
    c.estado,
    c.fecha_venta,
    c.observaciones,
    c.created_at
FROM comisiones c
INNER JOIN lotes l ON c.lote_id = l.id
INNER JOIN proyectos p ON l.proyecto_id = p.id
INNER JOIN users u ON c.vendedor_id = u.id
WHERE c.observaciones LIKE '%script de corrección%'
ORDER BY c.created_at DESC;

-- PASO 6: Resumen por vendedor después de generar comisiones
SELECT 
    u.id as user_id,
    u.nombre as vendedor,
    u.email,
    COUNT(DISTINCT l.id) as total_lotes_vendidos,
    COALESCE(SUM(COALESCE(l.precio_venta, l.precio_lista)), 0) as total_valor_vendido,
    COUNT(DISTINCT c.id) as total_comisiones,
    COALESCE(SUM(c.valor_comision), 0) as total_valor_comisiones,
    COALESCE(SUM(CASE WHEN c.estado = 'pendiente' THEN c.valor_comision ELSE 0 END), 0) as comisiones_pendientes,
    COALESCE(SUM(CASE WHEN c.estado = 'pagada' THEN c.valor_comision ELSE 0 END), 0) as comisiones_pagadas
FROM users u
LEFT JOIN lotes l ON u.id = l.vendedor_id AND l.estado = 'vendido'
LEFT JOIN comisiones c ON u.id = c.vendedor_id
WHERE u.rol IN ('administrador', 'vendedor')
AND u.activo = 1
GROUP BY u.id, u.nombre, u.email
HAVING total_lotes_vendidos > 0
ORDER BY total_valor_vendido DESC;

-- PASO 7: Verificar que el trigger funciona para futuras ventas
-- Este paso es solo informativo, el trigger ya existe en la BD

SHOW TRIGGERS LIKE 'lotes';

-- ============================================================
-- NOTAS IMPORTANTES:
-- ============================================================
-- 1. El PASO 4 es el que genera las comisiones faltantes
-- 2. Verifica primero con el PASO 1 cuántas comisiones se generarán
-- 3. Todas las comisiones se crean con estado 'pendiente'
-- 4. El porcentaje usado es 3% por defecto
-- 5. El trigger after_lote_vendido manejará automáticamente
--    las ventas futuras
-- ============================================================

-- OPCIONAL: Si quieres borrar las comisiones de prueba generadas
-- DELETE FROM comisiones WHERE observaciones LIKE '%script de corrección%';
