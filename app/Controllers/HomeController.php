<?php

namespace App\Controllers;

use App\Models\ProyectoModel;
use App\Models\LoteModel;
use App\Models\ClienteModel;
use App\Models\AmortizacionModel;
use App\Models\PagoModel;

/**
 * HomeController - Controlador principal
 * Maneja el dashboard y páginas generales
 */
class HomeController
{
    /**
     * Dashboard principal con KPIs y estadísticas
     */
    public function dashboard()
    {
        requireAuth();
        
        // Instanciar modelos
        $proyectoModel = new ProyectoModel();
        $loteModel = new LoteModel();
        $clienteModel = new ClienteModel();
        $amortizacionModel = new AmortizacionModel();
        $pagoModel = new PagoModel();

        // KPI 1: Total de proyectos activos
        $totalProyectosActivos = $proyectoModel->countActivos();

        // KPI 2: Estadísticas de lotes
        $estadisticasLotes = $loteModel->getEstadisticas();
        $lotesDisponibles = $estadisticasLotes['disponibles'] ?? 0;
        $lotesVendidos = $estadisticasLotes['vendidos'] ?? 0;
        $lotesReservados = $estadisticasLotes['reservados'] ?? 0;
        $valorInventario = $estadisticasLotes['valor_inventario'] ?? 0;
        $valorVentas = $estadisticasLotes['valor_ventas'] ?? 0;

        // KPI 3: Cartera
        $cartera = $amortizacionModel->getCarteraPendiente();
        $carteraPendiente = $cartera['cartera_total'] ?? 0;
        $carteraVencida = $cartera['cartera_vencida'] ?? 0;
        $cuotasVencidas = $cartera['cuotas_vencidas'] ?? 0;

        // KPI 4: Total de clientes
        $totalClientes = $clienteModel->count();

        // Datos para gráficos y tablas
        $cuotasMora = $amortizacionModel->getCuotasMora();
        $proximasCuotas = $amortizacionModel->getProximasCuotas(15); // Próximos 15 días
        $ultimosPagos = $pagoModel->getUltimosPagos(10);
        $resumenProyectos = $proyectoModel->getResumenProyectos();

        // Resumen de pagos del mes actual
        $primerDiaMes = date('Y-m-01');
        $ultimoDiaMes = date('Y-m-t');
        $pagosMes = $pagoModel->getTotalPagosPeriodo($primerDiaMes, $ultimoDiaMes);
        $totalRecaudadoMes = $pagosMes['total_recaudado'] ?? 0;

        $this->render('home/dashboard', [
            'title' => 'Dashboard - ' . APP_NAME,
            'totalProyectosActivos' => $totalProyectosActivos,
            'lotesDisponibles' => $lotesDisponibles,
            'lotesVendidos' => $lotesVendidos,
            'lotesReservados' => $lotesReservados,
            'valorInventario' => $valorInventario,
            'valorVentas' => $valorVentas,
            'carteraPendiente' => $carteraPendiente,
            'carteraVencida' => $carteraVencida,
            'cuotasVencidas' => $cuotasVencidas,
            'totalClientes' => $totalClientes,
            'totalRecaudadoMes' => $totalRecaudadoMes,
            'cuotasMora' => $cuotasMora,
            'proximasCuotas' => $proximasCuotas,
            'ultimosPagos' => $ultimosPagos,
            'resumenProyectos' => $resumenProyectos
        ]);
    }

    /**
     * Renderiza una vista
     */
    private function render($view, $data = [])
    {
        extract($data);
        
        ob_start();
        require_once __DIR__ . "/../Views/{$view}.php";
        $content = ob_get_clean();
        
        require_once __DIR__ . "/../Views/layouts/app.php";
    }
}
