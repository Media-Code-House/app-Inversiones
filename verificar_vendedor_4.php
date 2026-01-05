<?php
require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== BUSCANDO VENDEDOR ID 4 ===\n\n";
$vendedor = $db->query("SELECT * FROM vendedores WHERE id = 4")->fetch();

if ($vendedor) {
    echo "✅ VENDEDOR ID 4 EXISTE:\n";
    print_r($vendedor);
} else {
    echo "❌ VENDEDOR ID 4 NO EXISTE EN LA TABLA VENDEDORES\n\n";
    echo "Esta comisión tiene un vendedor_id inválido.\n";
    echo "Necesitamos corregir esta comisión o crear el vendedor.\n\n";
    
    // Ver qué comisión tiene este problema
    $comision = $db->query("SELECT c.*, l.codigo_lote FROM comisiones c INNER JOIN lotes l ON c.lote_id = l.id WHERE c.vendedor_id = 4")->fetch();
    if ($comision) {
        echo "Comisión afectada:\n";
        echo "ID: {$comision['id']}\n";
        echo "Lote: {$comision['codigo_lote']}\n";
        echo "Vendedor ID: {$comision['vendedor_id']}\n";
        echo "Valor: $" . number_format($comision['valor_comision'], 0, ',', '.') . "\n";
        echo "Estado: {$comision['estado']}\n";
    }
}

echo "\n\n=== TODOS LOS VENDEDORES ===\n\n";
$vendedores = $db->query("SELECT * FROM vendedores")->fetchAll();
echo "Total vendedores: " . count($vendedores) . "\n\n";
foreach ($vendedores as $v) {
    echo "ID: {$v['id']} | User ID: {$v['user_id']} | {$v['nombres']} {$v['apellidos']} | Estado: {$v['estado']}\n";
}
