-- ============================================================
-- Script para asignar vendedores a lotes vendidos existentes
-- ============================================================
-- Este script actualiza los lotes vendidos que no tienen
-- vendedor asignado, asignándoles un vendedor de prueba
-- ============================================================

-- PASO 1: Verificar lotes sin vendedor asignado
SELECT 
    l.id,
    l.codigo_lote,
    p.nombre as proyecto,
    l.precio_venta,
    l.fecha_venta,
    l.vendedor_id
FROM lotes l
INNER JOIN proyectos p ON l.proyecto_id = p.id
WHERE l.estado = 'vendido' 
AND l.vendedor_id IS NULL;

-- PASO 2: Verificar vendedores disponibles
SELECT 
    u.id,
    u.nombre,
    u.email,
    u.rol,
    u.activo
FROM users u
WHERE u.rol IN ('administrador', 'vendedor') 
AND u.activo = 1
ORDER BY u.nombre;

-- PASO 3: Actualizar lotes vendidos sin vendedor
-- IMPORTANTE: Reemplaza el ID '1' con el ID del vendedor que desees asignar
-- Puedes ver los IDs disponibles en el PASO 2

-- Opción A: Asignar a todos los lotes el mismo vendedor
-- UPDATE lotes 
-- SET vendedor_id = 1  -- Reemplaza '1' con el ID del vendedor
-- WHERE estado = 'vendido' 
-- AND vendedor_id IS NULL;

-- Opción B: Asignar vendedores de forma aleatoria (si tienes varios)
-- Descomenta y ajusta según tus necesidades:
-- UPDATE lotes l
-- SET vendedor_id = (
--     SELECT id FROM users 
--     WHERE rol IN ('administrador', 'vendedor') 
--     AND activo = 1 
--     ORDER BY RAND() 
--     LIMIT 1
-- )
-- WHERE l.estado = 'vendido' 
-- AND l.vendedor_id IS NULL;

-- PASO 4: Verificar la actualización
SELECT 
    l.id,
    l.codigo_lote,
    p.nombre as proyecto,
    u.nombre as vendedor,
    l.precio_venta,
    l.fecha_venta,
    l.vendedor_id
FROM lotes l
INNER JOIN proyectos p ON l.proyecto_id = p.id
LEFT JOIN users u ON l.vendedor_id = u.id
WHERE l.estado = 'vendido'
ORDER BY l.fecha_venta DESC;

-- PASO 5: Verificar el reporte de ventas por vendedor
SELECT 
    u.id,
    u.nombre as vendedor_nombre,
    u.email as vendedor_email,
    COUNT(l.id) as total_lotes_vendidos,
    SUM(COALESCE(l.precio_venta, l.precio_lista)) as total_ventas,
    SUM(COALESCE(l.precio_venta, l.precio_lista) * 0.03) as total_comisiones,
    MIN(l.fecha_venta) as primera_venta,
    MAX(l.fecha_venta) as ultima_venta
FROM users u
LEFT JOIN lotes l ON u.id = l.vendedor_id AND l.estado = 'vendido'
WHERE u.rol IN ('administrador', 'vendedor')
GROUP BY u.id
HAVING total_lotes_vendidos > 0
ORDER BY total_ventas DESC;
