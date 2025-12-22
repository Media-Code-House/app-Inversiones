<?php
/**
 * Script de depuración para verificar coordenadas guardadas
 */

require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

$proyectoId = 23;

echo "=== DEBUG: Puntos guardados del proyecto $proyectoId ===\n\n";

$sql = "SELECT id, codigo_lote, estado, plano_x, plano_y, manzana, precio_lista
        FROM lotes 
        WHERE proyecto_id = ?
        ORDER BY codigo_lote ASC";

$lotes = $db->fetchAll($sql, [$proyectoId]);

if (empty($lotes)) {
    echo "❌ No se encontraron lotes para el proyecto $proyectoId\n";
    exit;
}

echo "Total de lotes: " . count($lotes) . "\n\n";

$conCoordenadas = 0;
$sinCoordenadas = 0;

foreach ($lotes as $lote) {
    echo "Lote: {$lote['codigo_lote']}\n";
    echo "  ID: {$lote['id']}\n";
    echo "  Estado: {$lote['estado']}\n";
    echo "  Manzana: {$lote['manzana']}\n";
    echo "  Precio: $" . number_format($lote['precio_lista'], 2) . "\n";
    echo "  Coordenadas: ";
    
    if ($lote['plano_x'] !== null && $lote['plano_y'] !== null) {
        echo "X={$lote['plano_x']}, Y={$lote['plano_y']} ✓\n";
        $conCoordenadas++;
    } else {
        echo "SIN COORDENADAS ❌\n";
        $sinCoordenadas++;
    }
    echo "\n";
}

echo "\n=== RESUMEN ===\n";
echo "Lotes con coordenadas: $conCoordenadas\n";
echo "Lotes sin coordenadas: $sinCoordenadas\n";
echo "\nSi guardaste puntos y aquí aparecen SIN COORDENADAS,\n";
echo "significa que el guardado no está funcionando correctamente.\n";
