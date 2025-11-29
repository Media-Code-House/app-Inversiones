<?php
/**
 * Script web para agregar columnas de amortización
 * ELIMINAR DESPUÉS DE EJECUTAR
 */

// Seguridad: Solo permitir ejecución desde localhost o con clave
$clave_secreta = "inversiones2024"; // Cambiar por una clave segura
if (!isset($_GET['clave']) || $_GET['clave'] !== $clave_secreta) {
    die("Acceso denegado. Usa: ?clave=inversiones2024");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Migración DB</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;} .success{color:#4ec9b0;} .info{color:#9cdcfe;} .error{color:#f48771;}</style></head><body>";

try {
    echo "<h2>🔧 Agregando columnas de amortización a tabla lotes</h2>";
    
    // Verificar y agregar cada columna
    $columnas = [
        ['nombre' => 'cuota_inicial', 'tipo' => 'DECIMAL(15,2)', 'despues' => 'precio_venta'],
        ['nombre' => 'monto_financiado', 'tipo' => 'DECIMAL(15,2)', 'despues' => 'cuota_inicial'],
        ['nombre' => 'tasa_interes', 'tipo' => 'DECIMAL(5,2)', 'despues' => 'monto_financiado'],
        ['nombre' => 'numero_cuotas', 'tipo' => 'INT', 'despues' => 'tasa_interes'],
        ['nombre' => 'fecha_inicio_amortizacion', 'tipo' => 'DATE', 'despues' => 'numero_cuotas']
    ];
    
    foreach ($columnas as $col) {
        $checkSql = "SHOW COLUMNS FROM lotes LIKE '{$col['nombre']}'";
        $exists = $db->query($checkSql);
        
        if (empty($exists)) {
            echo "<p class='success'>✓ Agregando columna: {$col['nombre']}</p>";
            $alterSql = "ALTER TABLE lotes ADD COLUMN {$col['nombre']} {$col['tipo']} NULL DEFAULT NULL AFTER {$col['despues']}";
            $db->query($alterSql);
        } else {
            echo "<p class='info'>○ La columna '{$col['nombre']}' ya existe</p>";
        }
    }
    
    echo "<br><h3 class='success'>✅ Proceso completado exitosamente!</h3>";
    echo "<p><strong>Columnas verificadas/agregadas:</strong></p><ul>";
    foreach ($columnas as $col) {
        echo "<li>{$col['nombre']} ({$col['tipo']})</li>";
    }
    echo "</ul>";
    
    echo "<br><p style='color:#f48771;font-weight:bold;'>⚠️ IMPORTANTE: Elimina este archivo (migrate-amortizacion.php) inmediatamente por seguridad.</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
