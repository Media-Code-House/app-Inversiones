<?php

namespace App\Controllers;

use App\Models\LoteModel;
use App\Models\ProyectoModel;
use App\Models\ClienteModel;
use App\Models\AmortizacionModel;
use App\Models\PagoModel;

/**
 * ReporteController - Módulo de Business Intelligence
 * Reportes y análisis financiero del sistema
 */
class ReporteController extends Controller
{
    private $loteModel;
    private $proyectoModel;
    private $clienteModel;
    private $amortizacionModel;
    private $pagoModel;
    private $db;

    public function __construct()
    {
        $this->loteModel = new LoteModel();
        $this->proyectoModel = new ProyectoModel();
        $this->clienteModel = new ClienteModel();
        $this->amortizacionModel = new AmortizacionModel();
        $this->pagoModel = new PagoModel();
        $this->db = \Database::getInstance();
    }

    /**
     * Vista principal de reportes - Panel de selección
     * GET /reportes
     */
    public function index()
    {
        if (!can('ver_reportes')) {
            $_SESSION['error'] = 'No tienes permisos para ver reportes';
            redirect('/dashboard');
            return;
        }

        $data = [
            'pageTitle' => 'Reportes y Análisis'
        ];

        $this->view('reportes/index', $data);
    }

    /**
     * Reporte: Lotes Vendidos
     * GET /reportes/lotes-vendidos
     */
    public function lotesVendidos()
    {
        if (!can('ver_reportes')) {
            $_SESSION['error'] = 'No tienes permisos para ver reportes';
            redirect('/dashboard');
            return;
        }
        
        // RBAC: Obtener usuario autenticado
        $user = user();

        // Filtros
        $proyectoId = $_GET['proyecto_id'] ?? null;
        $vendedorId = $_GET['vendedor_id'] ?? null;
        $fechaDesde = $_GET['fecha_desde'] ?? null;
        $fechaHasta = $_GET['fecha_hasta'] ?? null;
        
        // RBAC: Si es vendedor, forzar filtro por su ID
        if ($user['rol'] === 'vendedor') {
            $vendedorId = $user['id'];
        }

        // Query con filtros dinámicos
        $sql = "SELECT 
                    l.id,
                    l.codigo_lote,
                    l.fecha_venta,
                    COALESCE(l.precio_venta, l.precio_lista) as precio_venta,
                    p.nombre as proyecto_nombre,
                    p.codigo as proyecto_codigo,
                    COALESCE(c.nombre, 'Sin cliente asignado') as cliente_nombre,
                    COALESCE(c.numero_documento, '') as cliente_documento,
                    COALESCE(u.nombre, 'Sin vendedor asignado') as vendedor_nombre,
                    (COALESCE(l.precio_venta, l.precio_lista) * 0.03) as comision_vendedor
                FROM lotes l
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes c ON l.cliente_id = c.id
                LEFT JOIN users u ON l.vendedor_id = u.id
                WHERE l.estado = 'vendido'";

        $params = [];

        if ($proyectoId) {
            $sql .= " AND l.proyecto_id = ?";
            $params[] = $proyectoId;
        }

        if ($vendedorId) {
            $sql .= " AND l.vendedor_id = ?";
            $params[] = $vendedorId;
        }

        if ($fechaDesde) {
            $sql .= " AND l.fecha_venta >= ?";
            $params[] = $fechaDesde;
        }

        if ($fechaHasta) {
            $sql .= " AND l.fecha_venta <= ?";
            $params[] = $fechaHasta;
        }

        $sql .= " ORDER BY l.fecha_venta DESC";

        $lotes = $this->db->fetchAll($sql, $params);
        
        // Asegurar que sea un array
        if (!is_array($lotes)) {
            $lotes = [];
        }

        // Calcular totales de forma segura
        $totalVentas = 0;
        $totalComisiones = 0;
        
        foreach ($lotes as $lote) {
            if (isset($lote['precio_venta']) && is_numeric($lote['precio_venta'])) {
                $totalVentas += floatval($lote['precio_venta']);
            }
            if (isset($lote['comision_vendedor']) && is_numeric($lote['comision_vendedor'])) {
                $totalComisiones += floatval($lote['comision_vendedor']);
            }
        }

        // Obtener proyectos y vendedores para filtros
        $proyectos = $this->proyectoModel->getAll();
        if (!is_array($proyectos)) {
            $proyectos = [];
        }
        
        $vendedores = $this->db->fetchAll("SELECT id, nombre FROM users WHERE rol = 'vendedor' ORDER BY nombre");
        if (!is_array($vendedores)) {
            $vendedores = [];
        }

        $data = [
            'pageTitle' => 'Reporte: Lotes Vendidos',
            'lotes' => $lotes,
            'totalVentas' => $totalVentas,
            'totalComisiones' => $totalComisiones,
            'proyectos' => $proyectos,
            'vendedores' => $vendedores,
            'filtros' => [
                'proyecto_id' => $proyectoId,
                'vendedor_id' => $vendedorId,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta
            ]
        ];

        $this->view('reportes/lotes-vendidos', $data);
    }

    /**
     * Reporte: Ventas por Proyecto
     * GET /reportes/ventas-proyecto
     */
    public function ventasPorProyecto()
    {
        if (!can('ver_reportes')) {
            $_SESSION['error'] = 'No tienes permisos para ver reportes';
            redirect('/dashboard');
            return;
        }
        
        // RBAC: Obtener usuario autenticado
        $user = user();

        // Usar la vista de resumen de proyectos
        $sql = "SELECT 
                    p.id,
                    p.codigo,
                    p.nombre,
                    p.ubicacion,
                    COUNT(l.id) as total_lotes,
                    SUM(CASE WHEN l.estado = 'disponible' THEN 1 ELSE 0 END) as lotes_disponibles,
                    SUM(CASE WHEN l.estado = 'vendido' THEN 1 ELSE 0 END) as lotes_vendidos,
                    SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) ELSE 0 END) as valor_ventas,
                    ROUND(SUM(CASE WHEN l.estado = 'vendido' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(l.id), 0), 1) as porcentaje_vendido
                FROM proyectos p
                LEFT JOIN lotes l ON p.id = l.proyecto_id";
        
        // RBAC: Si es vendedor, filtrar solo lotes asignados a él
        if ($user['rol'] === 'vendedor') {
            $sql .= " AND (l.vendedor_id = {$user['id']} OR l.vendedor_id IS NULL)";
        }
        
        $sql .= " GROUP BY p.id
                  ORDER BY valor_ventas DESC";

        $proyectos = $this->db->fetchAll($sql);

        // Preparar datos para gráfico
        $labels = array_column($proyectos, 'nombre');
        $valores = array_column($proyectos, 'valor_ventas');
        $porcentajes = array_column($proyectos, 'porcentaje_vendido');

        $data = [
            'pageTitle' => 'Reporte: Ventas por Proyecto',
            'proyectos' => $proyectos,
            'grafico' => [
                'labels' => $labels,
                'valores' => $valores,
                'porcentajes' => $porcentajes
            ],
            'totalVentasGeneral' => array_sum($valores)
        ];

        $this->view('reportes/ventas-proyecto', $data);
    }

    /**
     * Reporte: Ventas por Vendedor
     * GET /reportes/ventas-vendedor
     */
    public function ventasPorVendedor()
    {
        if (!can('ver_reportes')) {
            $_SESSION['error'] = 'No tienes permisos para ver reportes';
            redirect('/dashboard');
            return;
        }
        
        // RBAC: Obtener usuario autenticado
        $user = user();

        $fechaDesde = $_GET['fecha_desde'] ?? null;
        $fechaHasta = $_GET['fecha_hasta'] ?? null;

        $sql = "SELECT 
                    u.id,
                    u.nombre as vendedor_nombre,
                    u.email as vendedor_email,
                    COUNT(l.id) as total_lotes_vendidos,
                    SUM(COALESCE(l.precio_venta, l.precio_lista)) as total_ventas,
                    SUM(COALESCE(l.precio_venta, l.precio_lista) * 0.03) as total_comisiones,
                    MIN(l.fecha_venta) as primera_venta,
                    MAX(l.fecha_venta) as ultima_venta
                FROM users u
                LEFT JOIN lotes l ON u.id = l.vendedor_id AND l.estado = 'vendido'";

        $params = [];
        $whereConditions = [];
        
        // RBAC: Si es vendedor, filtrar solo su ID
        if ($user['rol'] === 'vendedor') {
            $whereConditions[] = "u.id = ?";
            $params[] = $user['id'];
        }

        if ($fechaDesde) {
            $whereConditions[] = "l.fecha_venta >= ?";
            $params[] = $fechaDesde;
        }

        if ($fechaHasta) {
            $whereConditions[] = "l.fecha_venta <= ?";
            $params[] = $fechaHasta;
        }

        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }

        $sql .= " GROUP BY u.id
                  HAVING total_lotes_vendidos > 0
                  ORDER BY total_ventas DESC";

        $vendedores = $this->db->fetchAll($sql, $params);

        $data = [
            'pageTitle' => 'Reporte: Ventas por Vendedor',
            'vendedores' => $vendedores,
            'totalVentasGeneral' => array_sum(array_column($vendedores, 'total_ventas')),
            'totalComisionesGeneral' => array_sum(array_column($vendedores, 'total_comisiones')),
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta
            ]
        ];

        $this->view('reportes/ventas-vendedor', $data);
    }

    /**
     * Reporte: Cartera Pendiente
     * GET /reportes/cartera
     */
    public function cartera()
    {
        if (!can('ver_reportes')) {
            $_SESSION['error'] = 'No tienes permisos para ver reportes';
            redirect('/dashboard');
            return;
        }
        
        // RBAC: Obtener usuario autenticado
        $user = user();

        $proyectoId = $_GET['proyecto_id'] ?? null;
        $estadoMora = $_GET['estado_mora'] ?? null; // 'todos', 'vigentes', 'vencidas'

        // Query de cartera con cálculo de mora
        $sql = "SELECT 
                    a.id,
                    a.numero_cuota,
                    a.fecha_vencimiento,
                    a.valor_cuota,
                    a.saldo,
                    a.estado,
                    l.codigo_lote,
                    COALESCE(l.precio_venta, l.precio_lista) as valor_lote,
                    p.nombre as proyecto_nombre,
                    COALESCE(c.nombre, 'Sin cliente') as cliente_nombre,
                    COALESCE(c.telefono, '') as cliente_telefono,
                    COALESCE(c.email, '') as cliente_email,
                    DATEDIFF(CURDATE(), a.fecha_vencimiento) as dias_mora,
                    CASE 
                        WHEN DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 THEN 'VENCIDA'
                        WHEN DATEDIFF(CURDATE(), a.fecha_vencimiento) BETWEEN -7 AND 0 THEN 'POR VENCER'
                        ELSE 'VIGENTE'
                    END as estado_mora
                FROM amortizaciones a
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes c ON l.cliente_id = c.id
                WHERE a.estado = 'pendiente' AND a.saldo > 0";

        $params = [];
        
        // RBAC: Si es vendedor, filtrar solo sus lotes
        if ($user['rol'] === 'vendedor') {
            $sql .= " AND l.vendedor_id = ?";
            $params[] = $user['id'];
        }

        if ($proyectoId) {
            $sql .= " AND l.proyecto_id = ?";
            $params[] = $proyectoId;
        }

        if ($estadoMora === 'vencidas') {
            $sql .= " AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0";
        } elseif ($estadoMora === 'vigentes') {
            $sql .= " AND DATEDIFF(CURDATE(), a.fecha_vencimiento) <= 0";
        }

        $sql .= " ORDER BY dias_mora DESC, a.fecha_vencimiento ASC";

        $cuotas = $this->db->fetchAll($sql, $params);
        
        // Asegurar que sea un array
        if (!is_array($cuotas)) {
            $cuotas = [];
        }

        // Calcular KPIs de forma segura
        $totalCartera = 0;
        $totalMora = 0;
        $cantidadVencidas = 0;
        
        foreach ($cuotas as $cuota) {
            if (isset($cuota['saldo']) && is_numeric($cuota['saldo'])) {
                $totalCartera += floatval($cuota['saldo']);
                
                if (isset($cuota['dias_mora']) && $cuota['dias_mora'] > 0) {
                    $totalMora += floatval($cuota['saldo']);
                    $cantidadVencidas++;
                }
            }
        }

        // Proyectos para filtro
        $proyectos = $this->proyectoModel->getAll();
        if (!is_array($proyectos)) {
            $proyectos = [];
        }

        $data = [
            'pageTitle' => 'Reporte: Cartera Pendiente',
            'cuotas' => $cuotas,
            'totalCartera' => $totalCartera,
            'totalMora' => $totalMora,
            'totalVigente' => $totalCartera - $totalMora,
            'cantidadCuotasVencidas' => $cantidadVencidas,
            'cantidadCuotasTotal' => count($cuotas),
            'proyectos' => $proyectos,
            'filtros' => [
                'proyecto_id' => $proyectoId,
                'estado_mora' => $estadoMora
            ]
        ];

        $this->view('reportes/cartera', $data);
    }

    /**
     * Reporte: Estado de Clientes
     * GET /reportes/estado-clientes
     */
    public function estadoClientes()
    {
        if (!can('ver_reportes')) {
            $_SESSION['error'] = 'No tienes permisos para ver reportes';
            redirect('/dashboard');
            return;
        }

        // Query consolidado por cliente (incluye lotes sin cliente asignado)
        $sql = "SELECT 
                    COALESCE(c.id, 0) as id,
                    COALESCE(c.nombre, 'Sin cliente asignado') as cliente_nombre,
                    COALESCE(c.tipo_documento, '-') as tipo_documento,
                    COALESCE(c.numero_documento, '-') as numero_documento,
                    COALESCE(c.telefono, '') as telefono,
                    COALESCE(c.email, '') as email,
                    COUNT(DISTINCT l.id) as total_lotes_comprados,
                    SUM(COALESCE(l.precio_venta, l.precio_lista)) as valor_total_compras,
                    SUM(CASE WHEN a.estado = 'pendiente' THEN a.saldo ELSE 0 END) as saldo_pendiente_global,
                    COUNT(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 THEN 1 END) as cuotas_vencidas,
                    MAX(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 
                        THEN DATEDIFF(CURDATE(), a.fecha_vencimiento) ELSE 0 END) as dias_mora_maxima,
                    CASE 
                        WHEN COUNT(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 30 THEN 1 END) > 0 THEN 'CRÍTICO'
                        WHEN COUNT(CASE WHEN a.estado = 'pendiente' AND DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 THEN 1 END) > 0 THEN 'EN MORA'
                        WHEN COUNT(CASE WHEN a.estado = 'pendiente' THEN 1 END) > 0 THEN 'AL DÍA'
                        ELSE 'PAGADO'
                    END as estado_credito
                FROM lotes l
                LEFT JOIN clientes c ON l.cliente_id = c.id
                LEFT JOIN amortizaciones a ON l.id = a.lote_id
                WHERE l.estado = 'vendido'
                GROUP BY COALESCE(c.id, 0), c.nombre, c.tipo_documento, c.numero_documento, c.telefono, c.email
                ORDER BY saldo_pendiente_global DESC, dias_mora_maxima DESC";

        $clientes = $this->db->fetchAll($sql);

        // Estadísticas generales
        $totalClientes = count($clientes);
        $clientesCriticos = array_filter($clientes, fn($c) => $c['estado_credito'] === 'CRÍTICO');
        $clientesEnMora = array_filter($clientes, fn($c) => $c['estado_credito'] === 'EN MORA');
        $clientesAlDia = array_filter($clientes, fn($c) => $c['estado_credito'] === 'AL DÍA');

        $data = [
            'pageTitle' => 'Reporte: Estado de Clientes',
            'clientes' => $clientes,
            'estadisticas' => [
                'total_clientes' => $totalClientes,
                'clientes_criticos' => count($clientesCriticos),
                'clientes_en_mora' => count($clientesEnMora),
                'clientes_al_dia' => count($clientesAlDia),
                'saldo_total_cartera' => array_sum(array_column($clientes, 'saldo_pendiente_global'))
            ]
        ];

        $this->view('reportes/estado-clientes', $data);
    }
}
