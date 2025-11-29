-- =====================================================
-- ACTUALIZACIÓN MÓDULO VENDEDORES - CORRECCIÓN DE ERRORES
-- =====================================================
-- Ejecutar en producción para corregir problemas existentes
-- =====================================================

-- PASO 1: Corregir vista vista_vendedores_resumen
-- =====================================================
DROP VIEW IF EXISTS `vista_vendedores_resumen`;

CREATE VIEW `vista_vendedores_resumen` AS
SELECT 
    v.id,
    v.codigo_vendedor,
    v.user_id,
    CONCAT(v.nombres, ' ', v.apellidos) AS nombre_completo,
    v.email,
    v.telefono,
    v.celular,
    v.fecha_ingreso,
    v.porcentaje_comision_default,
    v.estado,
    u.rol,
    
    -- Contar lotes vendidos por este vendedor (usando user_id)
    COUNT(DISTINCT l.id) AS total_lotes_vendidos,
    COALESCE(SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) END), 0) AS valor_total_vendido,
    
    -- Comisiones del vendedor
    COUNT(DISTINCT c.id) AS total_comisiones,
    COALESCE(SUM(c.valor_comision), 0) AS total_comisiones_generadas,
    COALESCE(SUM(CASE WHEN c.estado = 'pendiente' THEN c.valor_comision ELSE 0 END), 0) AS comisiones_pendientes,
    COALESCE(SUM(CASE WHEN c.estado = 'pagada' THEN c.valor_comision ELSE 0 END), 0) AS comisiones_pagadas,
    
    -- Pagos recibidos
    COUNT(DISTINCT pc.id) AS total_pagos_recibidos,
    COALESCE(SUM(pc.valor_pagado), 0) AS total_dinero_recibido,
    
    -- Fechas
    MAX(l.fecha_venta) AS fecha_ultima_venta,
    MAX(c.fecha_venta) AS fecha_ultima_comision
    
FROM vendedores v
INNER JOIN users u ON v.user_id = u.id
LEFT JOIN lotes l ON v.user_id = l.vendedor_id AND l.estado = 'vendido'
LEFT JOIN comisiones c ON v.id = c.vendedor_id
LEFT JOIN pagos_comisiones pc ON v.id = pc.vendedor_id

WHERE v.estado = 'activo'

GROUP BY 
    v.id, v.codigo_vendedor, v.user_id, v.nombres, v.apellidos,
    v.email, v.telefono, v.celular, v.fecha_ingreso,
    v.porcentaje_comision_default, v.estado, u.rol;

-- PASO 2: Verificar foreign key en lotes.vendedor_id
-- =====================================================
-- La tabla lotes ya tiene vendedor_id apuntando a users.id (correcto)
-- No necesita cambios

-- PASO 3: Agregar índice faltante en vendedores para optimización
-- =====================================================
ALTER TABLE `vendedores` 
DROP INDEX IF EXISTS `idx_estado_fecha`;

ALTER TABLE `vendedores` 
ADD INDEX `idx_estado_fecha` (`estado`, `fecha_ingreso`);

-- =====================================================
-- FIN - Base de datos actualizada correctamente
-- =====================================================
