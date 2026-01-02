<?php
require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== VERIFICAR Y AGREGAR COLUMNA plano_imagen ===\n\n";

// 1. Verificar estructura actual
echo "1. ESTRUCTURA ACTUAL DE LA TABLA proyectos:\n";
echo str_repeat("-", 80) . "\n";

$columnas = $db->fetchAll("DESCRIBE proyectos");
$tieneColumna = false;

foreach ($columnas as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
    if ($col['Field'] === 'plano_imagen') {
        $tieneColumna = true;
    }
}

echo "\n";

// 2. Agregar columna si no existe
if (!$tieneColumna) {
    echo "2. AGREGANDO COLUMNA 'plano_imagen':\n";
    echo str_repeat("-", 80) . "\n";
    
    try {
        $sql = "ALTER TABLE proyectos 
                ADD COLUMN plano_imagen VARCHAR(255) NULL 
                COMMENT 'Ruta de la imagen del plano del proyecto' 
                AFTER fecha_inicio";
        
        $db->query($sql);
        echo "✅ Columna 'plano_imagen' agregada exitosamente\n\n";
        
        // Verificar que se agregó
        $columnas = $db->fetchAll("DESCRIBE proyectos");
        echo "3. ESTRUCTURA ACTUALIZADA:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($columnas as $col) {
            $marca = $col['Field'] === 'plano_imagen' ? '✅ ' : '   ';
            echo "{$marca}{$col['Field']} ({$col['Type']})\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La columna 'plano_imagen' ya existe\n";
}

echo "\n✅ Proceso completado\n";
echo "\nAhora puedes intentar guardar el plano nuevamente.\n";
