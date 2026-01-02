<?php
/**
 * Generar Comisiones Faltantes
 * Inserta registros de comisiones para lotes vendidos que no tienen comisión registrada
 */

require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== GENERANDO COMISIONES FALTANTES ===\n\n";

// 1. Verificar lotes vendidos sin comisión
$lotesSinComision = $db->fetchAll("
    SELECT 
        l.id,
        l.codigo_lote,
        l.vendedor_id,
        l.precio_venta,
        l.fecha_venta,
        u.nombre as vendedor_nombre
    FROM lotes l
    LEFT JOIN users u ON l.vendedor_id = u.id
    WHERE l.estado = 'vendido'
    AND l.vendedor_id IS NOT NULL
    AND l.precio_venta > 0
    AND l.fecha_venta IS NOT NULL
    AND NOT EXISTS (
        SELECT 1 FROM comisiones c WHERE c.lote_id = l.id
    )
");

if (empty($lotesSinComision)) {
    echo "✅ No hay lotes pendientes de generar comisión\n";
    
    // Mostrar comisiones existentes
    $comisionesExistentes = $db->fetchAll("SELECT COUNT(*) as total FROM comisiones");
    echo "Total de comisiones en sistema: " . $comisionesExistentes[0]['total'] . "\n";
    exit(0);
}

echo "📋 Se encontraron " . count($lotesSinComision) . " lotes sin comisión:\n\n";

foreach ($lotesSinComision as $lote) {
    echo "- Lote: {$lote['codigo_lote']} | Vendedor: {$lote['vendedor_nombre']} | Precio: \${$lote['precio_venta']}\n";
}

echo "\n¿Deseas generar las comisiones? (s/n): ";
$respuesta = trim(fgets(STDIN));

if (strtolower($respuesta) !== 's') {
    echo "Operación cancelada\n";
    exit(0);
}

// 2. Generar comisiones
echo "\n🔄 Generando comisiones...\n";

$sql = "INSERT INTO comisiones (
            vendedor_id, 
            lote_id, 
            valor_venta, 
            porcentaje_comision, 
            valor_comision, 
            estado, 
            fecha_venta, 
            created_at
        )
        SELECT 
            l.vendedor_id,
            l.id,
            l.precio_venta,
            3.0,
            ROUND(l.precio_venta * 0.03, 2),
            'pendiente',
            l.fecha_venta,
            NOW()
        FROM lotes l
        WHERE l.estado = 'vendido'
        AND l.vendedor_id IS NOT NULL
        AND l.precio_venta > 0
        AND l.fecha_venta IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM comisiones c WHERE c.lote_id = l.id
        )";

try {
    $rowsAffected = $db->execute($sql);
    
    echo "✅ Se generaron {$rowsAffected} comisiones exitosamente\n\n";
    
    // Mostrar comisiones generadas
    echo "Comisiones generadas:\n";
    echo str_repeat("-", 80) . "\n";
    
    $comisionesGeneradas = $db->fetchAll("
        SELECT 
            c.id,
            c.valor_venta,
            c.valor_comision,
            c.fecha_venta,
            l.codigo_lote,
            u.nombre as vendedor_nombre
        FROM comisiones c
        INNER JOIN lotes l ON c.lote_id = l.id
        INNER JOIN users u ON c.vendedor_id = u.id
        ORDER BY c.id DESC
        LIMIT " . $rowsAffected . "
    ");
    
    foreach ($comisionesGeneradas as $com) {
        echo "ID {$com['id']}: {$com['codigo_lote']} - {$com['vendedor_nombre']}\n";
        echo "  Venta: \${$com['valor_venta']} | Comisión: \${$com['valor_comision']} (3%)\n";
        echo "  Fecha: {$com['fecha_venta']}\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Proceso completado\n";
