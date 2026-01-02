<?php
/**
 * Script para ejecutar en el SERVIDOR
 * Agrega la columna plano_imagen a la tabla proyectos
 */

require_once 'config/config.php';
require_once 'core/Database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== AGREGAR COLUMNA plano_imagen ===\n\n";

try {
    $db = Database::getInstance();
    
    // 1. Verificar si existe
    echo "1. Verificando si la columna existe...\n";
    $check = $db->fetch("
        SELECT COUNT(*) as existe 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'proyectos' 
        AND COLUMN_NAME = 'plano_imagen'
    ");
    
    if ($check['existe'] > 0) {
        echo "✅ La columna 'plano_imagen' YA EXISTE\n\n";
    } else {
        echo "❌ La columna 'plano_imagen' NO EXISTE\n";
        echo "2. Agregando columna...\n";
        
        $sql = "ALTER TABLE proyectos 
                ADD COLUMN plano_imagen VARCHAR(255) NULL 
                COMMENT 'Ruta de la imagen del plano del proyecto' 
                AFTER fecha_inicio";
        
        $db->query($sql);
        echo "✅ Columna agregada exitosamente\n\n";
    }
    
    // 3. Mostrar estructura
    echo "3. Estructura de la tabla proyectos:\n";
    echo str_repeat("-", 80) . "\n";
    
    $columnas = $db->fetchAll("DESCRIBE proyectos");
    foreach ($columnas as $col) {
        $marca = $col['Field'] === 'plano_imagen' ? '✅ ' : '   ';
        echo "{$marca}{$col['Field']} | {$col['Type']} | {$col['Null']} | {$col['Key']}\n";
    }
    
    echo "\n✅ PROCESO COMPLETADO\n";
    echo "\nAhora puedes:\n";
    echo "1. Subir imágenes de planos desde /proyectos/edit/{id}\n";
    echo "2. Ver el plano en /proyectos/show/{id}\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
