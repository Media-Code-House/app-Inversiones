<?php
/**
 * Test específico para Estado de Clientes
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== TEST ESTADO DE CLIENTES ===\n\n";

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance();
    echo "1. Conexión OK\n\n";
    
    // Query corregida
    $sql = "SELECT 
                COALESCE(c.id, 0) as id,
                COALESCE(c.nombre, 'Sin cliente asignado') as cliente_nombre,
                COALESCE(c.tipo_documento, '-') as tipo_documento,
                COALESCE(c.numero_documento, '-') as numero_documento,
                COALESCE(c.telefono, '') as telefono,
                COALESCE(c.email, '') as email,
                COUNT(DISTINCT l.id) as total_lotes_comprados,
                SUM(COALESCE(l.precio_venta, l.precio_lista)) as valor_total_compras,
                SUM(CASE WHEN a.estado = 'pendiente' THEN a.saldo ELSE 0 END) as saldo_pendiente_global,
                COUNT(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 THEN 1 END) as cuotas_vencidas,
                MAX(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 
                    THEN DATEDIFF(CURDATE(), a.fecha_vencimiento) ELSE 0 END) as dias_mora_maxima,
                CASE 
                    WHEN COUNT(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 30 THEN 1 END) > 0 THEN 'CRÍTICO'
                    WHEN COUNT(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 THEN 1 END) > 0 THEN 'EN MORA'
                    WHEN COUNT(CASE WHEN a.estado = 'pendiente' THEN 1 END) > 0 THEN 'AL DÍA'
                    ELSE 'PAGADO'
                END as estado_credito
            FROM lotes l
            LEFT JOIN clientes c ON l.cliente_id = c.id
            LEFT JOIN amortizaciones a ON l.id = a.lote_id
            WHERE l.estado = 'vendido'
            GROUP BY COALESCE(c.id, 0), c.nombre, c.tipo_documento, c.numero_documento, c.telefono, c.email
            ORDER BY saldo_pendiente_global DESC, dias_mora_maxima DESC";
    
    $clientes = $db->fetchAll($sql);
    
    echo "2. Query ejecutada OK\n";
    echo "   Total clientes encontrados: " . count($clientes) . "\n\n";
    
    if (count($clientes) > 0) {
        echo "3. ✅ DATOS ENCONTRADOS:\n\n";
        foreach ($clientes as $cliente) {
            echo "   Cliente: {$cliente['cliente_nombre']}\n";
            echo "   Lotes: {$cliente['total_lotes_comprados']}\n";
            echo "   Valor compras: $" . number_format($cliente['valor_total_compras'], 2) . "\n";
            echo "   Saldo pendiente: $" . number_format($cliente['saldo_pendiente_global'], 2) . "\n";
            echo "   Estado: {$cliente['estado_credito']}\n";
            echo "   Días mora: {$cliente['dias_mora_maxima']}\n";
            echo "   ---\n";
        }
    } else {
        echo "3. ⚠️ NO HAY DATOS\n";
        echo "   Esto significa que:\n";
        echo "   - No hay lotes con estado 'vendido', O\n";
        echo "   - Los lotes vendidos no tienen amortizaciones\n\n";
        
        // Verificar lotes vendidos
        $lotesVendidos = $db->fetchAll("SELECT COUNT(*) as total FROM lotes WHERE estado = 'vendido'");
        echo "   Lotes vendidos en BD: " . $lotesVendidos[0]['total'] . "\n";
        
        // Verificar amortizaciones
        $amortizaciones = $db->fetchAll("SELECT COUNT(*) as total FROM amortizaciones");
        echo "   Amortizaciones en BD: " . $amortizaciones[0]['total'] . "\n";
    }
    
    echo "\n✅ TEST COMPLETADO\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
