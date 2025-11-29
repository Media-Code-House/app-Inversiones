<?php
/**
 * Script de Verificación del Dashboard - Módulo 3
 * Prueba las consultas y modelos sin necesidad del navegador
 * 
 * Ejecutar: php verificar_dashboard.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/app/Models/ProyectoModel.php';
require_once __DIR__ . '/app/Models/LoteModel.php';
require_once __DIR__ . '/app/Models/ClienteModel.php';
require_once __DIR__ . '/app/Models/AmortizacionModel.php';
require_once __DIR__ . '/app/Models/PagoModel.php';

use App\Models\ProyectoModel;
use App\Models\LoteModel;
use App\Models\ClienteModel;
use App\Models\AmortizacionModel;
use App\Models\PagoModel;

// Colores para terminal
function color($text, $color = 'green') {
    $colors = [
        'green' => "\033[0;32m",
        'red' => "\033[0;31m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

echo "\n" . color("================================================", "blue") . "\n";
echo color("VERIFICACIÓN DEL DASHBOARD - MÓDULO 3", "blue") . "\n";
echo color("================================================", "blue") . "\n\n";

try {
    // Probar conexión a BD
    echo color("✓ Conexión a Base de Datos:", "green") . " ";
    $db = Database::getInstance();
    echo color("OK", "green") . "\n";

    // Instanciar modelos
    echo color("✓ Instanciando Modelos:", "green") . " ";
    $proyectoModel = new ProyectoModel();
    $loteModel = new LoteModel();
    $clienteModel = new ClienteModel();
    $amortizacionModel = new AmortizacionModel();
    $pagoModel = new PagoModel();
    echo color("OK", "green") . "\n\n";

    // KPI 1: Proyectos Activos
    echo color("━━━ KPI 1: PROYECTOS ACTIVOS ━━━", "yellow") . "\n";
    $totalProyectosActivos = $proyectoModel->countActivos();
    echo "Total: " . color($totalProyectosActivos, "blue") . "\n";
    
    $proyectos = $proyectoModel->getActivos();
    foreach ($proyectos as $proyecto) {
        echo "  • {$proyecto['codigo']} - {$proyecto['nombre']}\n";
    }
    echo "\n";

    // KPI 2: Estadísticas de Lotes
    echo color("━━━ KPI 2: ESTADÍSTICAS DE LOTES ━━━", "yellow") . "\n";
    $estadisticasLotes = $loteModel->getEstadisticas();
    echo "Disponibles: " . color($estadisticasLotes['disponibles'] ?? 0, "green") . "\n";
    echo "Vendidos: " . color($estadisticasLotes['vendidos'] ?? 0, "blue") . "\n";
    echo "Reservados: " . color($estadisticasLotes['reservados'] ?? 0, "yellow") . "\n";
    echo "Bloqueados: " . color($estadisticasLotes['bloqueados'] ?? 0, "red") . "\n";
    echo "Total: " . ($estadisticasLotes['total_lotes'] ?? 0) . "\n";
    echo "\n";

    // KPI 3: Valores Financieros
    echo color("━━━ KPI 3: VALORES FINANCIEROS ━━━", "yellow") . "\n";
    $valorInventario = $estadisticasLotes['valor_inventario'] ?? 0;
    $valorVentas = $estadisticasLotes['valor_ventas'] ?? 0;
    echo "Valor Inventario: " . color("$" . number_format($valorInventario, 0, ',', '.'), "yellow") . "\n";
    echo "Valor Ventas: " . color("$" . number_format($valorVentas, 0, ',', '.'), "green") . "\n";
    echo "\n";

    // KPI 4: Cartera
    echo color("━━━ KPI 4: CARTERA ━━━", "yellow") . "\n";
    $cartera = $amortizacionModel->getCarteraPendiente();
    echo "Cartera Pendiente: " . color("$" . number_format($cartera['cartera_total'] ?? 0, 0, ',', '.'), "red") . "\n";
    echo "Cartera Vencida: " . color("$" . number_format($cartera['cartera_vencida'] ?? 0, 0, ',', '.'), "red") . "\n";
    echo "Cuotas Vencidas: " . color($cartera['cuotas_vencidas'] ?? 0, "red") . "\n";
    echo "Total Cuotas Pendientes: " . ($cartera['total_cuotas_pendientes'] ?? 0) . "\n";
    echo "\n";

    // KPI 5: Clientes
    echo color("━━━ KPI 5: CLIENTES ━━━", "yellow") . "\n";
    $totalClientes = $clienteModel->count();
    echo "Total Clientes: " . color($totalClientes, "blue") . "\n";
    
    $clientesConLotes = $clienteModel->getConLotes();
    echo "Clientes con Lotes: " . count($clientesConLotes) . "\n";
    echo "\n";

    // KPI 6: Pagos del Mes
    echo color("━━━ KPI 6: RECAUDACIÓN DEL MES ━━━", "yellow") . "\n";
    $primerDiaMes = date('Y-m-01');
    $ultimoDiaMes = date('Y-m-t');
    $pagosMes = $pagoModel->getTotalPagosPeriodo($primerDiaMes, $ultimoDiaMes);
    echo "Total Recaudado: " . color("$" . number_format($pagosMes['total_recaudado'] ?? 0, 0, ',', '.'), "green") . "\n";
    echo "Transacciones: " . ($pagosMes['total_transacciones'] ?? 0) . "\n";
    echo "Promedio: $" . number_format($pagosMes['promedio_pago'] ?? 0, 0, ',', '.') . "\n";
    echo "\n";

    // Cuotas en Mora
    echo color("━━━ CUOTAS EN MORA ━━━", "red") . "\n";
    $cuotasMora = $amortizacionModel->getCuotasMora();
    if (empty($cuotasMora)) {
        echo color("✓ No hay cuotas en mora", "green") . "\n";
    } else {
        foreach ($cuotasMora as $cuota) {
            echo "• {$cuota['cliente_nombre']} - {$cuota['proyecto_nombre']} - Lote {$cuota['codigo_lote']}\n";
            echo "  Cuota #{$cuota['numero_cuota']} - {$cuota['dias_mora']} días - $" . number_format($cuota['saldo_pendiente'], 0, ',', '.') . "\n";
        }
    }
    echo "\n";

    // Próximas Cuotas
    echo color("━━━ PRÓXIMAS CUOTAS (15 DÍAS) ━━━", "yellow") . "\n";
    $proximasCuotas = $amortizacionModel->getProximasCuotas(15);
    if (empty($proximasCuotas)) {
        echo color("✓ No hay cuotas próximas a vencer", "green") . "\n";
    } else {
        foreach ($proximasCuotas as $cuota) {
            echo "• {$cuota['cliente_nombre']} - {$cuota['proyecto_nombre']} - Lote {$cuota['codigo_lote']}\n";
            echo "  Cuota #{$cuota['numero_cuota']} - Vence: {$cuota['fecha_vencimiento']} - $" . number_format($cuota['valor_cuota'], 0, ',', '.') . "\n";
        }
    }
    echo "\n";

    // Últimos Pagos
    echo color("━━━ ÚLTIMOS 5 PAGOS ━━━", "green") . "\n";
    $ultimosPagos = $pagoModel->getUltimosPagos(5);
    if (empty($ultimosPagos)) {
        echo color("✓ No hay pagos registrados", "yellow") . "\n";
    } else {
        foreach ($ultimosPagos as $pago) {
            echo "• {$pago['fecha_pago']} - {$pago['cliente_nombre']} - {$pago['proyecto_nombre']}\n";
            echo "  Lote {$pago['codigo_lote']} - Cuota #{$pago['numero_cuota']} - {$pago['metodo_pago']}\n";
            echo "  Valor: " . color("$" . number_format($pago['valor_pagado'], 0, ',', '.'), "green") . "\n";
        }
    }
    echo "\n";

    // Resumen de Proyectos
    echo color("━━━ RESUMEN DE PROYECTOS (VISTA) ━━━", "blue") . "\n";
    $resumenProyectos = $proyectoModel->getResumenProyectos();
    foreach ($resumenProyectos as $proyecto) {
        echo "\n{$proyecto['codigo']} - {$proyecto['nombre']}\n";
        echo "  Lotes: {$proyecto['total_lotes']} | ";
        echo "Disponibles: " . color($proyecto['lotes_disponibles'], "green") . " | ";
        echo "Vendidos: " . color($proyecto['lotes_vendidos'], "blue") . "\n";
        echo "  Inventario: $" . number_format($proyecto['valor_inventario'], 0, ',', '.') . " | ";
        echo "Ventas: $" . number_format($proyecto['valor_ventas'], 0, ',', '.') . "\n";
    }
    echo "\n";

    // Resumen Final
    echo color("================================================", "green") . "\n";
    echo color("✓ VERIFICACIÓN COMPLETADA CON ÉXITO", "green") . "\n";
    echo color("================================================", "green") . "\n";
    echo "\nTodos los modelos y consultas funcionan correctamente.\n";
    echo "El dashboard está listo para usarse.\n\n";

} catch (Exception $e) {
    echo color("\n✗ ERROR: " . $e->getMessage(), "red") . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    exit(1);
}
