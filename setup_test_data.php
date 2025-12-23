<?php
/**
 * Script de Setup: Crear datos de prueba para Sistema de Saldo a Favor
 * Uso: http://tuapp.com/setup_test_data.php
 * 
 * Este script:
 * 1. Crea un cliente de prueba
 * 2. Crea un lote con 60 cuotas
 * 3. Simula pagos donde hay mora y saldo a favor
 * 4. Te muestra el ID del lote para ir a probarlo
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/helpers.php';

echo "<html><head><meta charset='UTF-8'><title>Setup Test Data</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
    h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
    .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #007bff; }
    .success { background: #d4edda; border-left-color: #28a745; }
    .warning { background: #fff3cd; border-left-color: #ffc107; }
    .error { background: #f8d7da; border-left-color: #dc3545; }
    .info { background: #d1ecf1; border-left-color: #17a2b8; }
    code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New'; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #007bff; color: white; }
    tr:hover { background: #f5f5f5; }
    .badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .badge-success { background: #28a745; color: white; }
    .badge-danger { background: #dc3545; color: white; }
    .badge-warning { background: #ffc107; color: black; }
    .badge-info { background: #17a2b8; color: white; }
    .cta-button { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    .cta-button:hover { background: #0056b3; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔧 Setup: Datos de Prueba - Saldo a Favor Global</h1>";

try {
    $db = Database::getInstance();
    
    // ============================================================================
    // PASO 1: Verificar que la columna existe
    // ============================================================================
    echo "<div class='section info'>";
    echo "<h3>Paso 1: Verificar Instalación</h3>";
    
    $sql_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor'";
    $result = $db->query($sql_check);
    
    if (!$result || count($result) == 0) {
        echo "<div class='section error'>";
        echo "<h4>❌ Error: Columna 'saldo_a_favor' NO existe</h4>";
        echo "<p>Debes ejecutar la migration SQL primero:</p>";
        echo "<pre><code>mysql -u root -p inversiones < database/migration_saldo_a_favor.sql</code></pre>";
        echo "</div>";
        exit;
    }
    
    echo "<p><span class='badge badge-success'>✅</span> Columna 'saldo_a_favor' existe correctamente</p>";
    echo "</div>";
    
    // ============================================================================
    // PASO 2: Crear Cliente de Prueba
    // ============================================================================
    echo "<div class='section info'>";
    echo "<h3>Paso 2: Crear/Verificar Cliente de Prueba</h3>";
    
    $sql_cliente = "SELECT id FROM clientes WHERE cedula = '1234567890' LIMIT 1";
    $cliente_result = $db->query($sql_cliente);
    
    if ($cliente_result && count($cliente_result) > 0) {
        $cliente_id = $cliente_result[0]['id'];
        echo "<p><span class='badge badge-info'>ℹ️</span> Cliente existente encontrado: ID <code>$cliente_id</code></p>";
    } else {
        $sql_insert_cliente = "INSERT INTO `clientes` (
            `nombres`, `apellidos`, `cedula`, `email`, `telefono`, 
            `ciudad`, `estado`, `fecha_registro`, `tipo_cliente`
        ) VALUES (
            'Cliente', 'Prueba Mora', '1234567890', 'prueba@test.com', '3001234567',
            'Medellín', 'activo', NOW(), 'persona'
        )";
        
        $db->execute($sql_insert_cliente, []);
        $cliente_id = $db->lastInsertId();
        echo "<p><span class='badge badge-success'>✅</span> Cliente creado: ID <code>$cliente_id</code></p>";
    }
    echo "</div>";
    
    // ============================================================================
    // PASO 3: Crear Proyecto de Prueba
    // ============================================================================
    echo "<div class='section info'>";
    echo "<h3>Paso 3: Crear/Verificar Proyecto de Prueba</h3>";
    
    $sql_proyecto = "SELECT id FROM proyectos WHERE nombre LIKE '%Prueba%' LIMIT 1";
    $proyecto_result = $db->query($sql_proyecto);
    
    if ($proyecto_result && count($proyecto_result) > 0) {
        $proyecto_id = $proyecto_result[0]['id'];
        echo "<p><span class='badge badge-info'>ℹ️</span> Proyecto existente encontrado: ID <code>$proyecto_id</code></p>";
    } else {
        $sql_insert_proyecto = "INSERT INTO `proyectos` (
            `nombre`, `descripcion`, `ubicacion`, `estado`, `fecha_inicio`
        ) VALUES (
            'Proyecto Prueba Saldo a Favor',
            'Proyecto de prueba para validar sistema de Saldo a Favor Global',
            'Medellín',
            'activo',
            '2025-01-01'
        )";
        
        $db->execute($sql_insert_proyecto, []);
        $proyecto_id = $db->lastInsertId();
        echo "<p><span class='badge badge-success'>✅</span> Proyecto creado: ID <code>$proyecto_id</code></p>";
    }
    echo "</div>";
    
    // ============================================================================
    // PASO 4: Crear Lote con Amortización
    // ============================================================================
    echo "<div class='section info'>";
    echo "<h3>Paso 4: Crear Lote con 60 Cuotas</h3>";
    
    $sql_insert_lote = "INSERT INTO `lotes` (
        `codigo_lote`, `proyecto_id`, `cliente_id`, `manzana`, `area_m2`, `valor_lote`,
        `numero_cuotas`, `valor_cuota`, `tasa_interes`, `fecha_inicio`, `estado`,
        `saldo_a_favor`
    ) VALUES (
        'LOTE-TEST-' . DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'),
        ?, ?, 'M-01', 150.00, 20000000.00,
        60, 444927.00, 12.00, '2025-01-15', 'activo', 0.00
    )";
    
    $db->execute($sql_insert_lote, [$proyecto_id, $cliente_id]);
    $lote_id = $db->lastInsertId();
    echo "<p><span class='badge badge-success'>✅</span> Lote creado: ID <code>$lote_id</code></p>";
    echo "<p><strong>Configuración:</strong> 60 cuotas de $444.927 | Tasa 12% anual | Monto: $20M</p>";
    echo "</div>";
    
    // ============================================================================
    // PASO 5: Crear Plan de Amortización (60 cuotas)
    // ============================================================================
    echo "<div class='section info'>";
    echo "<h3>Paso 5: Crear Plan de Amortización (60 Cuotas)</h3>";
    
    // Limpiar cuotas anteriores si existen
    $db->execute("DELETE FROM amortizaciones WHERE lote_id = ?", [$lote_id]);
    
    $saldo_actual = 20000000.00;
    $tasa_mensual = 0.01; // 1% mensual
    $cuota_mes = 444927.00;
    
    for ($contador = 1; $contador <= 60; $contador++) {
        $fecha_vencimiento = date('Y-m-d', strtotime('2025-01-15 +' . $contador . ' months'));
        $interes_mes = round($saldo_actual * $tasa_mensual, 2);
        $capital_mes = round($cuota_mes - $interes_mes, 2);
        
        $sql_cuota = "INSERT INTO `amortizaciones` (
            `lote_id`, `numero_cuota`, `fecha_vencimiento`, `saldo_inicial`,
            `capital`, `interes`, `valor_cuota`, `saldo_final`, `estado`, 
            `fecha_creacion`, `valor_pagado`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW(), 0.00)";
        
        $db->execute($sql_cuota, [
            $lote_id, $contador, $fecha_vencimiento, $saldo_actual,
            $capital_mes, $interes_mes, $cuota_mes, $saldo_actual - $capital_mes
        ]);
        
        $saldo_actual = $saldo_actual - $capital_mes;
    }
    
    echo "<p><span class='badge badge-success'>✅</span> 60 cuotas generadas correctamente</p>";
    echo "</div>";
    
    // ============================================================================
    // PASO 6: Registrar Pagos Realistas (Mora + Saldo a Favor)
    // ============================================================================
    echo "<div class='section info'>";
    echo "<h3>Paso 6: Registrar Pagos (Escenario Realista)</h3>";
    
    // Pago 1: Cuota 1 (Feb 15) - PAGO DE $1.000.000 (excede a $444.927)
    $cuota_1 = $db->query("SELECT id FROM amortizaciones WHERE lote_id = ? AND numero_cuota = 1 LIMIT 1", [$lote_id]);
    
    if ($cuota_1 && count($cuota_1) > 0) {
        $cuota_1_id = $cuota_1[0]['id'];
        $db->execute("INSERT INTO pagos (lote_id, amortizacion_id, monto_pagado, concepto, metodo_pago, fecha_pago, referencia, estado, fecha_registro) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                     [$lote_id, $cuota_1_id, 1000000.00, 'Pago Cuota 1 - Enero', 'transferencia', '2025-02-05', 'TRF-2025-02-001', 'aprobado']);
        
        $db->execute("UPDATE amortizaciones SET estado = 'pagada', valor_pagado = ? WHERE id = ?", [1000000.00, $cuota_1_id]);
        echo "<p><span class='badge badge-success'>✅</span> Pago Cuota 1: $1.000.000 (Exceso: $555.073)</p>";
    }
    
    // Pago 2: Cuota 4 (May 15) - PAGO NORMAL
    $cuota_4 = $db->query("SELECT id FROM amortizaciones WHERE lote_id = ? AND numero_cuota = 4 LIMIT 1", [$lote_id]);
    
    if ($cuota_4 && count($cuota_4) > 0) {
        $cuota_4_id = $cuota_4[0]['id'];
        $db->execute("INSERT INTO pagos (lote_id, amortizacion_id, monto_pagado, concepto, metodo_pago, fecha_pago, referencia, estado, fecha_registro) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                     [$lote_id, $cuota_4_id, 444927.00, 'Pago Cuota 4 - Abril', 'transferencia', '2025-04-10', 'TRF-2025-04-001', 'aprobado']);
        
        $db->execute("UPDATE amortizaciones SET estado = 'pagada', valor_pagado = ? WHERE id = ?", [444927.00, $cuota_4_id]);
        echo "<p><span class='badge badge-success'>✅</span> Pago Cuota 4: $444.927</p>";
    }
    
    // Pago 3: Cuota 5 (Jun 15) - PAGO NORMAL
    $cuota_5 = $db->query("SELECT id FROM amortizaciones WHERE lote_id = ? AND numero_cuota = 5 LIMIT 1", [$lote_id]);
    
    if ($cuota_5 && count($cuota_5) > 0) {
        $cuota_5_id = $cuota_5[0]['id'];
        $db->execute("INSERT INTO pagos (lote_id, amortizacion_id, monto_pagado, concepto, metodo_pago, fecha_pago, referencia, estado, fecha_registro) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                     [$lote_id, $cuota_5_id, 444927.00, 'Pago Cuota 5 - Mayo', 'transferencia', '2025-05-10', 'TRF-2025-05-001', 'aprobado']);
        
        $db->execute("UPDATE amortizaciones SET estado = 'pagada', valor_pagado = ? WHERE id = ?", [444927.00, $cuota_5_id]);
        echo "<p><span class='badge badge-success'>✅</span> Pago Cuota 5: $444.927</p>";
    }
    
    // Actualizar saldo a favor en lote
    $db->execute("UPDATE lotes SET saldo_a_favor = ? WHERE id = ?", [555073.00, $lote_id]);
    echo "<p><span class='badge badge-success'>✅</span> Saldo a Favor registrado: <strong>$555.073</strong></p>";
    
    echo "<p style='background: #fffacd; padding: 10px; border-radius: 5px;'><strong>⚠️ Nota:</strong> Cuotas 2 y 3 quedan SIN PAGAR (En MORA)</p>";
    echo "</div>";
    
    // ============================================================================
    // PASO 7: Mostrar Estado Actual
    // ============================================================================
    echo "<div class='section success'>";
    echo "<h3>Estado Actual del Lote</h3>";
    
    $cuotas = $db->query("
        SELECT numero_cuota, fecha_vencimiento, valor_cuota, estado, valor_pagado 
        FROM amortizaciones 
        WHERE lote_id = ? 
        ORDER BY numero_cuota ASC 
        LIMIT 6
    ", [$lote_id]);
    
    echo "<p><strong>Primeras 6 Cuotas:</strong></p>";
    echo "<table>";
    echo "<tr><th>Cuota</th><th>Vencimiento</th><th>Monto</th><th>Pagado</th><th>Estado</th></tr>";
    
    foreach ($cuotas as $cuota) {
        $estado_badge = $cuota['estado'] === 'pagada' 
            ? '<span class="badge badge-success">✅ PAGADA</span>' 
            : '<span class="badge badge-danger">⚠️ MORA</span>';
        
        echo "<tr>";
        echo "<td>#" . $cuota['numero_cuota'] . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($cuota['fecha_vencimiento'])) . "</td>";
        echo "<td>$" . number_format($cuota['valor_cuota'], 2) . "</td>";
        echo "<td>$" . number_format($cuota['valor_pagado'], 2) . "</td>";
        echo "<td>" . $estado_badge . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // ============================================================================
    // PASO 8: Call to Action
    // ============================================================================
    echo "<div class='section' style='background: #e3f2fd;'>";
    echo "<h3>🎯 Próximo Paso: Probar la Funcionalidad</h3>";
    echo "<p>El lote ha sido creado con:</p>";
    echo "<ul>";
    echo "<li><strong>Lote ID:</strong> <code>$lote_id</code></li>";
    echo "<li><strong>Cliente:</strong> Cliente Prueba Mora (Cédula: 1234567890)</li>";
    echo "<li><strong>Saldo a Favor:</strong> <strong>$555.073</strong></li>";
    echo "<li><strong>Cuotas en MORA:</strong> Cuota 2 y 3</li>";
    echo "</ul>";
    
    echo "<h4>Abre el lote en tu navegador:</h4>";
    echo "<a href='https://inversiones.mch.com.co/lotes/amortizacion/show/$lote_id' class='cta-button'>📊 Ver Amortización del Lote</a>";
    
    echo "<p style='margin-top: 20px;'><strong>Verás:</strong></p>";
    echo "<ol>";
    echo "<li>✅ El botón AZUL: <code> Aplicar Saldo a Favor (\$555.073)</code></li>";
    echo "<li>⚠️ Las cuotas 2 y 3 en MORA (sin pagar)</li>";
    echo "<li>✅ Las cuotas 1, 4 y 5 como PAGADAS</li>";
    echo "</ol>";
    
    echo "<p style='margin-top: 20px;'><strong>Haz click en el botón:</strong></p>";
    echo "<ol>";
    echo "<li>Aparecerá confirmación con el monto exact</li>";
    echo "<li>Después de confirmar, el sistema reajustará el plan</li>";
    echo "<li>Cuotas 2 y 3 se marcarán como PAGADA</li>";
    echo "<li>El cliente sale de MORA ✅</li>";
    echo "<li>El botón desaparecerá (saldo = 0)</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<div class='section error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>
