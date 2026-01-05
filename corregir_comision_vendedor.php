<?php
require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== CORRIGIENDO COMISION ID:1 ===\n\n";

// Actualizar la comisión ID:1 al vendedor ID:2
$result = $db->execute("UPDATE comisiones SET vendedor_id = 2 WHERE id = 1");

if ($result) {
    echo "✅ Comisión ID:1 actualizada al vendedor ID:2 (María Vendedora González)\n\n";
    
    // Verificar
    $comision = $db->query("SELECT c.*, l.codigo_lote FROM comisiones c INNER JOIN lotes l ON c.lote_id = l.id WHERE c.id = 1")->fetch();
    echo "Comisión corregida:\n";
    echo "ID: {$comision['id']}\n";
    echo "Lote: {$comision['codigo_lote']}\n";
    echo "Vendedor ID: {$comision['vendedor_id']} (ahora válido)\n";
    echo "Valor: $" . number_format($comision['valor_comision'], 0, ',', '.') . "\n";
    echo "Estado: {$comision['estado']}\n";
} else {
    echo "❌ Error al actualizar la comisión\n";
}
