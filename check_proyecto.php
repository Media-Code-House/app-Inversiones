<?php
require_once 'core/Database.php';
require_once 'config/config.php';

$db = Database::getInstance();

echo "=== Estado del Proyecto 23 ===\n\n";

$proyecto = $db->fetch("SELECT id, codigo, nombre, plano_imagen FROM proyectos WHERE id = 23");

if ($proyecto) {
    echo "ID: {$proyecto['id']}\n";
    echo "Código: {$proyecto['codigo']}\n";
    echo "Nombre: {$proyecto['nombre']}\n";
    echo "Plano imagen: " . ($proyecto['plano_imagen'] ? $proyecto['plano_imagen'] : "(vacío)") . "\n\n";
    
    if (empty($proyecto['plano_imagen'])) {
        echo "⚠️ El proyecto NO tiene una imagen de plano cargada.\n";
        echo "   Necesitas subir una imagen en: Editar Proyecto → Plano del Proyecto\n\n";
    } else {
        echo "✓ El proyecto tiene plano: {$proyecto['plano_imagen']}\n";
        
        // Verificar si el archivo existe
        $rutaArchivo = __DIR__ . '/' . $proyecto['plano_imagen'];
        if (file_exists($rutaArchivo)) {
            echo "✓ El archivo existe en el servidor\n";
        } else {
            echo "✗ El archivo NO existe en: {$rutaArchivo}\n";
        }
    }
} else {
    echo "✗ Proyecto 23 no encontrado\n";
}

echo "\n=== Lotes del Proyecto ===\n";
$lotes = $db->fetchAll("SELECT id, codigo_lote, estado, plano_x, plano_y FROM lotes WHERE proyecto_id = 23");
echo "Total de lotes: " . count($lotes) . "\n\n";
foreach ($lotes as $lote) {
    $coordenadas = ($lote['plano_x'] && $lote['plano_y']) 
        ? "X:{$lote['plano_x']}, Y:{$lote['plano_y']}" 
        : "Sin posición";
    echo "- {$lote['codigo_lote']} ({$lote['estado']}) - {$coordenadas}\n";
}
