<?php
/**
 * Migración: Agregar campos para método francés a tabla amortizaciones
 * Fecha: 2025-11-29
 * 
 * Agrega los campos necesarios para el sistema de amortización con método francés:
 * - capital: Monto del capital en cada cuota
 * - interes: Monto del interés en cada cuota
 * - saldo: Saldo del capital pendiente después de cada cuota
 * 
 * También agrega campos de amortización a la tabla lotes
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "===========================================\n";
    echo "MIGRACIÓN: Método Francés - Amortizaciones\n";
    echo "===========================================\n\n";
    
    // 1. Agregar campos a tabla amortizaciones
    echo "1. Agregando campos a tabla amortizaciones...\n";
    
    $alterAmortizaciones = [
        "ALTER TABLE amortizaciones ADD COLUMN IF NOT EXISTS capital DECIMAL(15,2) DEFAULT 0 AFTER valor_cuota",
        "ALTER TABLE amortizaciones ADD COLUMN IF NOT EXISTS interes DECIMAL(15,2) DEFAULT 0 AFTER capital",
        "ALTER TABLE amortizaciones ADD COLUMN IF NOT EXISTS saldo DECIMAL(15,2) DEFAULT 0 AFTER interes"
    ];
    
    foreach ($alterAmortizaciones as $sql) {
        try {
            $pdo->exec($sql);
            echo "   ✓ " . substr($sql, 0, 60) . "...\n";
        } catch (PDOException $e) {
            // Si el campo ya existe, continuar
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "   - Campo ya existe, saltando...\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "\n2. Agregando campos a tabla lotes...\n";
    
    $alterLotes = [
        "ALTER TABLE lotes ADD COLUMN IF NOT EXISTS cuota_inicial DECIMAL(15,2) NULL AFTER precio_venta",
        "ALTER TABLE lotes ADD COLUMN IF NOT EXISTS monto_financiado DECIMAL(15,2) NULL AFTER cuota_inicial",
        "ALTER TABLE lotes ADD COLUMN IF NOT EXISTS tasa_interes DECIMAL(5,2) NULL COMMENT 'Tasa anual %' AFTER monto_financiado",
        "ALTER TABLE lotes ADD COLUMN IF NOT EXISTS numero_cuotas INT NULL AFTER tasa_interes",
        "ALTER TABLE lotes ADD COLUMN IF NOT EXISTS fecha_inicio_amortizacion DATE NULL AFTER numero_cuotas"
    ];
    
    foreach ($alterLotes as $sql) {
        try {
            $pdo->exec($sql);
            echo "   ✓ " . substr($sql, 0, 60) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "   - Campo ya existe, saltando...\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "\n3. Verificando estructura de tabla amortizaciones...\n";
    $stmt = $pdo->query("DESCRIBE amortizaciones");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredFields = ['capital', 'interes', 'saldo', 'valor_cuota', 'valor_pagado', 'saldo_pendiente'];
    $foundFields = array_column($columns, 'Field');
    
    foreach ($requiredFields as $field) {
        if (in_array($field, $foundFields)) {
            echo "   ✓ Campo '$field' existe\n";
        } else {
            echo "   ✗ Campo '$field' NO existe\n";
        }
    }
    
    echo "\n4. Verificando estructura de tabla lotes...\n";
    $stmt = $pdo->query("DESCRIBE lotes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredFields = ['cuota_inicial', 'monto_financiado', 'tasa_interes', 'numero_cuotas', 'fecha_inicio_amortizacion'];
    $foundFields = array_column($columns, 'Field');
    
    foreach ($requiredFields as $field) {
        if (in_array($field, $foundFields)) {
            echo "   ✓ Campo '$field' existe\n";
        } else {
            echo "   ✗ Campo '$field' NO existe\n";
        }
    }
    
    echo "\n===========================================\n";
    echo "✓ MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "===========================================\n\n";
    echo "Próximos pasos:\n";
    echo "1. Verifica que todos los campos existan\n";
    echo "2. Crea un plan de amortización de prueba\n";
    echo "3. Verifica que los cálculos del método francés sean correctos\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR EN LA MIGRACIÓN:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
