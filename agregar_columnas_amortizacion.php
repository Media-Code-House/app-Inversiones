<?php
/**
 * Script para agregar columnas de amortización a la tabla lotes
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

try {
    echo "Agregando columnas de amortización a tabla lotes...\n\n";
    
    // Verificar si las columnas ya existen
    $checkSql = "SHOW COLUMNS FROM lotes LIKE 'cuota_inicial'";
    $exists = $db->query($checkSql);
    
    if (empty($exists)) {
        echo "✓ Agregando columna: cuota_inicial\n";
        $db->query("ALTER TABLE lotes ADD COLUMN cuota_inicial DECIMAL(15,2) NULL DEFAULT NULL AFTER precio_venta");
    } else {
        echo "○ La columna 'cuota_inicial' ya existe\n";
    }
    
    // monto_financiado
    $checkSql = "SHOW COLUMNS FROM lotes LIKE 'monto_financiado'";
    $exists = $db->query($checkSql);
    
    if (empty($exists)) {
        echo "✓ Agregando columna: monto_financiado\n";
        $db->query("ALTER TABLE lotes ADD COLUMN monto_financiado DECIMAL(15,2) NULL DEFAULT NULL AFTER cuota_inicial");
    } else {
        echo "○ La columna 'monto_financiado' ya existe\n";
    }
    
    // tasa_interes
    $checkSql = "SHOW COLUMNS FROM lotes LIKE 'tasa_interes'";
    $exists = $db->query($checkSql);
    
    if (empty($exists)) {
        echo "✓ Agregando columna: tasa_interes\n";
        $db->query("ALTER TABLE lotes ADD COLUMN tasa_interes DECIMAL(5,2) NULL DEFAULT NULL AFTER monto_financiado");
    } else {
        echo "○ La columna 'tasa_interes' ya existe\n";
    }
    
    // numero_cuotas
    $checkSql = "SHOW COLUMNS FROM lotes LIKE 'numero_cuotas'";
    $exists = $db->query($checkSql);
    
    if (empty($exists)) {
        echo "✓ Agregando columna: numero_cuotas\n";
        $db->query("ALTER TABLE lotes ADD COLUMN numero_cuotas INT NULL DEFAULT NULL AFTER tasa_interes");
    } else {
        echo "○ La columna 'numero_cuotas' ya existe\n";
    }
    
    // fecha_inicio_amortizacion
    $checkSql = "SHOW COLUMNS FROM lotes LIKE 'fecha_inicio_amortizacion'";
    $exists = $db->query($checkSql);
    
    if (empty($exists)) {
        echo "✓ Agregando columna: fecha_inicio_amortizacion\n";
        $db->query("ALTER TABLE lotes ADD COLUMN fecha_inicio_amortizacion DATE NULL DEFAULT NULL AFTER numero_cuotas");
    } else {
        echo "○ La columna 'fecha_inicio_amortizacion' ya existe\n";
    }
    
    echo "\n✅ Proceso completado exitosamente!\n";
    echo "\nColumnas agregadas:\n";
    echo "  - cuota_inicial (DECIMAL 15,2)\n";
    echo "  - monto_financiado (DECIMAL 15,2)\n";
    echo "  - tasa_interes (DECIMAL 5,2)\n";
    echo "  - numero_cuotas (INT)\n";
    echo "  - fecha_inicio_amortizacion (DATE)\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
