<?php
require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== VERIFICANDO TRIGGER after_lote_vendido ===\n\n";

$trigger = $db->query("SHOW TRIGGERS WHERE `Trigger` = 'after_lote_vendido'");

if (empty($trigger)) {
    echo "❌ TRIGGER NO EXISTE\n\n";
    echo "Necesitas ejecutar el SQL para crear el trigger.\n";
} else {
    echo "✅ TRIGGER EXISTE:\n";
    print_r($trigger);
}

echo "\n\n=== VERIFICANDO COMISIONES ACTUALES ===\n\n";
$comisiones = $db->query("SELECT c.*, l.codigo_lote, l.estado as lote_estado, l.vendedor_id as lote_vendedor_id 
                          FROM comisiones c 
                          INNER JOIN lotes l ON c.lote_id = l.id 
                          ORDER BY c.created_at DESC 
                          LIMIT 10")->fetchAll();

if (empty($comisiones)) {
    echo "No hay comisiones registradas.\n";
} else {
    echo "Total comisiones: " . count($comisiones) . "\n\n";
    foreach ($comisiones as $c) {
        echo "ID: {$c['id']} | Lote: {$c['codigo_lote']} | Vendedor ID: {$c['vendedor_id']} | Estado lote: {$c['lote_estado']} | Valor: " . number_format($c['valor_comision'], 2) . "\n";
    }
}

echo "\n\n=== VERIFICANDO LOTES VENDIDOS SIN COMISION ===\n\n";
$lotesSinComision = $db->query("SELECT l.id, l.codigo_lote, l.vendedor_id, l.estado, l.fecha_venta, l.precio_venta
                                FROM lotes l
                                WHERE l.estado = 'vendido' 
                                AND l.vendedor_id IS NOT NULL
                                AND NOT EXISTS (
                                    SELECT 1 FROM comisiones c WHERE c.lote_id = l.id
                                )
                                ORDER BY l.fecha_venta DESC")->fetchAll();

if (empty($lotesSinComision)) {
    echo "✅ Todos los lotes vendidos con vendedor tienen comisión.\n";
} else {
    echo "⚠️ HAY " . count($lotesSinComision) . " LOTES VENDIDOS SIN COMISIÓN:\n\n";
    foreach ($lotesSinComision as $l) {
        echo "ID: {$l['id']} | Código: {$l['codigo_lote']} | Vendedor ID: {$l['vendedor_id']} | Fecha: {$l['fecha_venta']}\n";
    }
}

echo "\n\n=== VERIFICANDO VENDEDORES ===\n\n";
$vendedores = $db->query("SELECT id, user_id, CONCAT(nombres, ' ', apellidos) as nombre, porcentaje_comision_default, estado 
                          FROM vendedores 
                          WHERE estado = 'activo'")->fetchAll();

if (empty($vendedores)) {
    echo "❌ NO HAY VENDEDORES ACTIVOS\n";
} else {
    echo "Vendedores activos:\n";
    foreach ($vendedores as $v) {
        echo "ID: {$v['id']} | User ID: {$v['user_id']} | Nombre: {$v['nombre']} | Comisión: {$v['porcentaje_comision_default']}%\n";
    }
}
