<?php
/**
 * Archivo de prueba para diagnosticar errores en reportes
 */

// Configurar display de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== TEST DE REPORTES ===\n\n";

// Incluir configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

echo "1. Configuración cargada OK\n";

// Probar conexión a base de datos
try {
    $db = Database::getInstance();
    echo "2. Conexión a base de datos OK\n";
    
    // Probar query simple
    $result = $db->fetchAll("SELECT COUNT(*) as total FROM lotes WHERE estado = 'vendido'");
    echo "3. Query simple OK - Lotes vendidos: " . $result[0]['total'] . "\n";
    
    // Probar query compleja (Lotes Vendidos)
    $sql = "SELECT 
                l.id,
                l.codigo_lote,
                l.fecha_venta,
                l.precio_venta,
                p.nombre as proyecto_nombre,
                p.codigo as proyecto_codigo,
                c.nombre as cliente_nombre,
                c.numero_documento as cliente_documento,
                u.nombre as vendedor_nombre,
                (l.precio_venta * 0.03) as comision_vendedor
            FROM lotes l
            INNER JOIN proyectos p ON l.proyecto_id = p.id
            LEFT JOIN clientes c ON l.cliente_id = c.id
            LEFT JOIN users u ON l.vendedor_id = u.id
            WHERE l.estado = 'vendido'
            ORDER BY l.fecha_venta DESC";
    
    $lotes = $db->fetchAll($sql);
    echo "4. Query de Lotes Vendidos OK - Total: " . count($lotes) . " lotes\n";
    
    // Probar query de Ventas por Proyecto
    $sql = "SELECT 
                p.id,
                p.codigo,
                p.nombre,
                COUNT(l.id) as total_lotes,
                SUM(CASE WHEN l.estado = 'vendido' THEN 1 ELSE 0 END) as lotes_vendidos,
                SUM(CASE WHEN l.estado = 'disponible' THEN 1 ELSE 0 END) as lotes_disponibles,
                SUM(CASE WHEN l.estado = 'vendido' THEN l.precio_venta ELSE 0 END) as valor_ventas,
                ROUND((SUM(CASE WHEN l.estado = 'vendido' THEN 1 ELSE 0 END) / COUNT(l.id) * 100), 2) as porcentaje_vendido
            FROM proyectos p
            LEFT JOIN lotes l ON p.id = l.proyecto_id
            GROUP BY p.id
            ORDER BY valor_ventas DESC";
    
    $proyectos = $db->fetchAll($sql);
    echo "5. Query de Ventas por Proyecto OK - Total: " . count($proyectos) . " proyectos\n";
    
    echo "\n✅ TODOS LOS TESTS PASARON CORRECTAMENTE\n";
    echo "\nSi los reportes aún dan error 500, el problema está en:\n";
    echo "- La inicialización del controlador\n";
    echo "- Los modelos que se instancian\n";
    echo "- El sistema de permisos can()\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
