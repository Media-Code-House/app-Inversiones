<?php
/**
 * Script de Test: Verificar si el sistema Saldo a Favor está funcionando
 * Uso: http://tuapp.com/test_saldo_a_favor.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/app/Models/LoteModel.php';

echo "<pre>";
echo "=".str_repeat("=", 78)."\n";
echo " TEST: SISTEMA SALDO A FAVOR GLOBAL\n";
echo "=".str_repeat("=", 78)."\n\n";

try {
    $db = Database::getInstance();
    
    // 1. Verificar que la columna existe
    echo "1. VERIFICANDO COLUMNA 'saldo_a_favor' EN TABLA 'lotes'...\n";
    $sql_check = "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor'";
    
    $result = $db->query($sql_check);
    
    if ($result && count($result) > 0) {
        echo "   ✅ COLUMNA EXISTE\n";
        echo "   Tipo: " . $result[0]['COLUMN_TYPE'] . "\n";
        echo "   Default: " . $result[0]['COLUMN_DEFAULT'] . "\n\n";
    } else {
        echo "   ❌ COLUMNA NO EXISTE\n";
        echo "   🔧 SOLUCIÓN: Ejecutar migration SQL:\n";
        echo "      mysql -u root -p inversiones < database/migration_saldo_a_favor.sql\n\n";
    }
    
    // 2. Verificar que el índice existe
    echo "2. VERIFICANDO ÍNDICE 'idx_lotes_saldo_a_favor'...\n";
    $sql_index = "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_NAME='lotes' AND INDEX_NAME='idx_lotes_saldo_a_favor'";
    
    $result_index = $db->query($sql_index);
    
    if ($result_index && count($result_index) > 0) {
        echo "   ✅ ÍNDICE EXISTE\n\n";
    } else {
        echo "   ⚠️  ÍNDICE NO EXISTE (NO es crítico)\n\n";
    }
    
    // 3. Obtener datos del Lote #2
    echo "3. VERIFICANDO LOTE #2...\n";
    $sql_lote = "SELECT id, codigo_lote, saldo_a_favor FROM lotes WHERE id = 2";
    $lote = $db->query($sql_lote);
    
    if ($lote && count($lote) > 0) {
        echo "   ✅ Lote encontrado\n";
        echo "   Código: " . $lote[0]['codigo_lote'] . "\n";
        echo "   Saldo a Favor: " . $lote[0]['saldo_a_favor'] . "\n\n";
    } else {
        echo "   ❌ Lote #2 no encontrado\n\n";
    }
    
    // 4. Verificar LoteModel
    echo "4. VERIFICANDO LoteModel::getSaldoAFavor()...\n";
    $loteModel = new LoteModel();
    $saldo = $loteModel->getSaldoAFavor(2);
    
    if ($saldo !== null) {
        echo "   ✅ Método funciona\n";
        echo "   Saldo obtenido: " . $saldo . "\n";
        echo "   Tipo: " . gettype($saldo) . "\n\n";
    } else {
        echo "   ❌ Error al obtener saldo\n\n";
    }
    
    // 5. Verificar si hay pagos excedentes en tabla pagos
    echo "5. VERIFICANDO PAGOS EXCEDENTES EN LOTE #2...\n";
    $sql_pagos = "SELECT id, monto_pagado, concepto, fecha_pago 
                  FROM pagos 
                  WHERE lote_id = 2 
                  ORDER BY fecha_pago DESC 
                  LIMIT 5";
    $pagos = $db->query($sql_pagos);
    
    if ($pagos && count($pagos) > 0) {
        echo "   ✅ Se encontraron pagos:\n";
        foreach ($pagos as $pago) {
            echo "      - Pago: $" . number_format($pago['monto_pagado'], 2) . " | " . $pago['concepto'] . " | " . $pago['fecha_pago'] . "\n";
        }
        echo "\n";
    } else {
        echo "   ℹ️  No hay pagos registrados aún\n\n";
    }
    
    // 6. Resumen Final
    echo "=".str_repeat("=", 78)."\n";
    echo " DIAGNÓSTICO FINAL\n";
    echo "=".str_repeat("=", 78)."\n\n";
    
    if ($result && count($result) > 0 && $saldo !== null && $saldo > 0.01) {
        echo "✅ SISTEMA FUNCIONANDO CORRECTAMENTE\n";
        echo "   - Columna existe\n";
        echo "   - Modelo funciona\n";
        echo "   - Hay saldo a favor: $" . number_format($saldo, 2) . "\n";
        echo "   - El botón DEBE aparecer en amortizacion.php\n";
    } elseif ($result && count($result) > 0) {
        echo "✅ SISTEMA INSTALADO CORRECTAMENTE\n";
        echo "   - Columna existe\n";
        echo "   - Modelo funciona\n";
        echo "   ⚠️  Saldo a favor actual: $" . number_format($saldo ?? 0, 2) . "\n";
        echo "   - El botón aparecerá cuando haya excedentes registrados\n";
    } else {
        echo "❌ PROBLEMA DETECTADO\n";
        echo "   - La migración SQL NO se ha ejecutado\n";
        echo "   - Ejecuta: mysql -u root -p inversiones < database/migration_saldo_a_favor.sql\n";
    }
    
    echo "\n" . "=".str_repeat("=", 78)."\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>
