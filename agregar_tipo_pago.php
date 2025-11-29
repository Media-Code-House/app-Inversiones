<?php
/**
 * Migración: Agregar campo tipo_pago a tabla lotes
 * Fecha: 2025-11-29
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "===========================================\n";
    echo "MIGRACIÓN: Agregar campo tipo_pago a lotes\n";
    echo "===========================================\n\n";
    
    // Verificar si el campo ya existe
    $stmt = $pdo->query("DESCRIBE lotes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    if (in_array('tipo_pago', $columnNames)) {
        echo "✓ El campo 'tipo_pago' ya existe en la tabla lotes\n\n";
    } else {
        echo "Agregando campo 'tipo_pago'...\n";
        
        $sql = "ALTER TABLE lotes 
                ADD COLUMN tipo_pago ENUM('contado', 'amortizacion') DEFAULT 'contado' 
                COMMENT 'Tipo de pago: contado al 100% o con plan de amortización' 
                AFTER fecha_venta";
        
        $pdo->exec($sql);
        
        echo "✓ Campo 'tipo_pago' agregado exitosamente\n\n";
    }
    
    // Verificar estructura final
    echo "Estructura actual de campos relacionados con venta:\n";
    $stmt = $pdo->query("DESCRIBE lotes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $ventaFields = ['cliente_id', 'precio_venta', 'fecha_venta', 'tipo_pago', 'cuota_inicial', 'monto_financiado', 'tasa_interes', 'numero_cuotas'];
    
    foreach ($columns as $column) {
        if (in_array($column['Field'], $ventaFields)) {
            echo "  - {$column['Field']}: {$column['Type']}\n";
        }
    }
    
    echo "\n===========================================\n";
    echo "✓ MIGRACIÓN COMPLETADA\n";
    echo "===========================================\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}
