<?php
/**
 * Script de Diagnóstico: Verificar por qué no aparecen datos en vista de comisiones
 */

require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== DIAGNÓSTICO DE COMISIONES - VISTA ===\n\n";

// 1. Verificar lotes vendidos con vendedor
echo "1. LOTES VENDIDOS CON VENDEDOR:\n";
echo str_repeat("-", 80) . "\n";
$lotes = $db->fetchAll("
    SELECT 
        l.id,
        l.codigo_lote,
        l.estado,
        l.vendedor_id,
        l.precio_venta,
        l.fecha_venta,
        u.nombre as vendedor_nombre
    FROM lotes l
    LEFT JOIN users u ON l.vendedor_id = u.id
    WHERE l.estado = 'vendido'
    ORDER BY l.id DESC
    LIMIT 10
");

if (empty($lotes)) {
    echo "⚠️  NO HAY LOTES VENDIDOS\n\n";
} else {
    foreach ($lotes as $lote) {
        $vendedor = $lote['vendedor_id'] ? $lote['vendedor_nombre'] : '❌ SIN VENDEDOR';
        echo "Lote: {$lote['codigo_lote']} | Vendedor ID: {$lote['vendedor_id']} | Vendedor: {$vendedor}\n";
        echo "  Precio: \${$lote['precio_venta']} | Fecha: {$lote['fecha_venta']}\n";
    }
    echo "\n";
}

// 2. Verificar registros en tabla comisiones
echo "2. REGISTROS EN TABLA COMISIONES:\n";
echo str_repeat("-", 80) . "\n";
$comisiones = $db->fetchAll("
    SELECT 
        c.id,
        c.vendedor_id,
        c.lote_id,
        c.valor_venta,
        c.porcentaje_comision,
        c.valor_comision,
        c.estado,
        c.fecha_venta
    FROM comisiones c
    ORDER BY c.id DESC
    LIMIT 10
");

if (empty($comisiones)) {
    echo "❌ NO HAY COMISIONES REGISTRADAS\n\n";
} else {
    echo "✅ Total de comisiones en tabla: " . count($comisiones) . "\n";
    foreach ($comisiones as $com) {
        echo "ID: {$com['id']} | Vendedor ID: {$com['vendedor_id']} | Lote ID: {$com['lote_id']}\n";
        echo "  Venta: \${$com['valor_venta']} | Comisión: \${$com['valor_comision']} ({$com['porcentaje_comision']}%)\n";
        echo "  Estado: {$com['estado']} | Fecha: {$com['fecha_venta']}\n";
    }
    echo "\n";
}

// 3. Probar query de ComisionModel::getAll()
echo "3. CONSULTA ComisionModel::getAll() - SIN FILTROS:\n";
echo str_repeat("-", 80) . "\n";
$query = "
    SELECT 
        c.*,
        COALESCE(CONCAT(v.nombres, ' ', v.apellidos), u.nombre) as vendedor_nombre,
        p.nombre as proyecto_nombre,
        l.codigo_lote,
        cl.nombre as cliente_nombre
    FROM comisiones c
    INNER JOIN users u ON c.vendedor_id = u.id
    LEFT JOIN vendedores v ON u.id = v.user_id
    LEFT JOIN lotes l ON c.lote_id = l.id
    LEFT JOIN proyectos p ON l.proyecto_id = p.id
    LEFT JOIN clientes cl ON l.cliente_id = cl.id
    ORDER BY c.fecha_venta DESC
";

try {
    $resultados = $db->fetchAll($query);
    
    if (empty($resultados)) {
        echo "❌ LA CONSULTA NO RETORNA RESULTADOS\n";
        echo "\nPOSIBLES CAUSAS:\n";
        echo "- INNER JOIN con users falla porque vendedor_id no existe en users\n";
        echo "- No hay registros en tabla comisiones\n";
        echo "- Problema con las relaciones entre tablas\n\n";
    } else {
        echo "✅ La consulta retorna " . count($resultados) . " resultados:\n";
        foreach (array_slice($resultados, 0, 5) as $r) {
            echo "  - {$r['vendedor_nombre']} | {$r['codigo_lote']} | \${$r['valor_comision']}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR EN LA CONSULTA: " . $e->getMessage() . "\n\n";
}

// 4. Verificar relaciones vendedor_id
echo "4. VERIFICAR RELACIONES DE VENDEDOR_ID:\n";
echo str_repeat("-", 80) . "\n";
$relaciones = $db->fetchAll("
    SELECT 
        'comisiones' as tabla,
        COUNT(*) as total,
        COUNT(DISTINCT c.vendedor_id) as vendedores_unicos,
        SUM(CASE WHEN u.id IS NULL THEN 1 ELSE 0 END) as vendedores_invalidos
    FROM comisiones c
    LEFT JOIN users u ON c.vendedor_id = u.id
    
    UNION ALL
    
    SELECT 
        'lotes' as tabla,
        COUNT(*) as total,
        COUNT(DISTINCT l.vendedor_id) as vendedores_unicos,
        SUM(CASE WHEN u.id IS NULL THEN 1 ELSE 0 END) as vendedores_invalidos
    FROM lotes l
    LEFT JOIN users u ON l.vendedor_id = u.id
    WHERE l.estado = 'vendido'
");

foreach ($relaciones as $rel) {
    echo "Tabla: {$rel['tabla']}\n";
    echo "  Total registros: {$rel['total']}\n";
    echo "  Vendedores únicos: {$rel['vendedores_unicos']}\n";
    echo "  Vendedores inválidos: {$rel['vendedores_invalidos']}\n";
}
echo "\n";

// 5. Verificar usuarios con rol vendedor
echo "5. USUARIOS CON ROL VENDEDOR/ADMINISTRADOR:\n";
echo str_repeat("-", 80) . "\n";
$usuarios = $db->fetchAll("
    SELECT 
        u.id,
        u.nombre,
        u.rol,
        u.activo,
        v.id as vendedor_tabla_id,
        v.codigo_vendedor
    FROM users u
    LEFT JOIN vendedores v ON u.id = v.user_id
    WHERE u.rol IN ('vendedor', 'administrador')
    ORDER BY u.id
");

foreach ($usuarios as $user) {
    $tiene_registro = $user['vendedor_tabla_id'] ? '✅' : '❌';
    echo "{$tiene_registro} ID: {$user['id']} | {$user['nombre']} | Rol: {$user['rol']} | Activo: {$user['activo']}\n";
    if ($user['vendedor_tabla_id']) {
        echo "   Código vendedor: {$user['codigo_vendedor']}\n";
    }
}
echo "\n";

// 6. RECOMENDACIONES
echo "=== RECOMENDACIONES ===\n";
echo str_repeat("-", 80) . "\n";

if (empty($lotes)) {
    echo "❌ PROBLEMA 1: No hay lotes vendidos\n";
    echo "   SOLUCIÓN: Crear o editar un lote con estado 'vendido' y asignar vendedor\n\n";
}

if (empty($comisiones)) {
    echo "❌ PROBLEMA 2: No hay comisiones en la tabla\n";
    echo "   CAUSA: El trigger 'after_lote_vendido' no se ejecutó\n";
    echo "   SOLUCIÓN:\n";
    echo "   1. Verificar que el trigger existe: SHOW TRIGGERS LIKE 'lotes';\n";
    echo "   2. Ejecutar: generar_comisiones_faltantes.sql\n";
    echo "   3. O crear manualmente una venta con vendedor asignado\n\n";
}

$comisionesOrfanas = $db->fetch("
    SELECT COUNT(*) as total
    FROM comisiones c
    LEFT JOIN users u ON c.vendedor_id = u.id
    WHERE u.id IS NULL
")['total'] ?? 0;

if ($comisionesOrfanas > 0) {
    echo "❌ PROBLEMA 3: Hay {$comisionesOrfanas} comisiones con vendedor_id inválido\n";
    echo "   SOLUCIÓN: Ejecutar corregir_vendedor_id_lotes.sql\n\n";
}

echo "\n✅ Diagnóstico completado\n";
