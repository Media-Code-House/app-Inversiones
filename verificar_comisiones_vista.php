<?php
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'app/Models/ComisionModel.php';

$comisionModel = new App\Models\ComisionModel();

echo "=== TODAS LAS COMISIONES (getAll) ===\n\n";
$comisiones = $comisionModel->getAll();

echo "Total retornado: " . count($comisiones) . "\n\n";

foreach ($comisiones as $c) {
    echo "ID: {$c['id']}\n";
    echo "  Vendedor: {$c['vendedor_nombre']} (ID: {$c['vendedor_id']})\n";
    echo "  Lote: {$c['codigo_lote']} | Proyecto: {$c['proyecto_nombre']}\n";
    echo "  Cliente: {$c['cliente_nombre']}\n";
    echo "  Valor venta: $" . number_format($c['valor_venta'], 0, ',', '.') . "\n";
    echo "  Comisión {$c['porcentaje_comision']}%: $" . number_format($c['valor_comision'], 0, ',', '.') . "\n";
    echo "  Estado: {$c['estado']}\n";
    echo "  Fecha: {$c['fecha_venta']}\n";
    echo "---\n";
}

echo "\n\n=== DIRECTAMENTE DE LA BD ===\n\n";
$db = Database::getInstance();
$directas = $db->query("SELECT c.*, l.codigo_lote 
                        FROM comisiones c 
                        INNER JOIN lotes l ON c.lote_id = l.id 
                        ORDER BY c.created_at DESC")->fetchAll();

echo "Total en BD: " . count($directas) . "\n\n";
foreach ($directas as $d) {
    echo "ID: {$d['id']} | Lote: {$d['codigo_lote']} | Vendedor ID: {$d['vendedor_id']} | Estado: {$d['estado']}\n";
}
