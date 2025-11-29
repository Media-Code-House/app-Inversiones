<?php

namespace App\Controllers;

use App\Models\ComisionModel;

/**
 * ComisionController - Gestión de Comisiones de Vendedores
 */
class ComisionController extends Controller
{
    private $comisionModel;

    public function __construct()
    {
        $this->comisionModel = new ComisionModel();
    }

    /**
     * Lista todas las comisiones con filtros
     * GET /comisiones
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $filtros = [
            'vendedor_id' => $_GET['vendedor_id'] ?? null,
            'estado' => $_GET['estado'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
        ];

        $comisiones = $this->comisionModel->getAll($filtros);
        
        // Obtener lista de vendedores para filtro
        $db = \Database::getInstance();
        $vendedores = $db->fetchAll(
            "SELECT id, nombre FROM users 
             WHERE rol IN ('administrador', 'vendedor') 
             AND activo = 1 
             ORDER BY nombre"
        );

        // Calcular totales
        $totales = [
            'total_ventas' => count($comisiones),
            'total_comisiones' => array_sum(array_column($comisiones, 'valor_comision')),
            'total_pendiente' => 0,
            'total_pagado' => 0
        ];

        foreach ($comisiones as $comision) {
            if ($comision['estado'] === 'pendiente') {
                $totales['total_pendiente'] += $comision['valor_comision'];
            } elseif ($comision['estado'] === 'pagada') {
                $totales['total_pagado'] += $comision['valor_comision'];
            }
        }

        $this->view('comisiones/index', [
            'title' => 'Gestión de Comisiones',
            'comisiones' => $comisiones,
            'vendedores' => $vendedores,
            'filtros' => $filtros,
            'totales' => $totales
        ]);
    }

    /**
     * Muestra resumen de comisiones por vendedor
     * GET /comisiones/resumen
     */
    public function resumen()
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $resumen = $this->comisionModel->getResumenPorVendedor();

        $this->view('comisiones/resumen', [
            'title' => 'Resumen de Comisiones por Vendedor',
            'resumen' => $resumen
        ]);
    }

    /**
     * Ver detalle de una comisión
     * GET /comisiones/show/{id}
     */
    public function show($id)
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $comision = $this->comisionModel->findById($id);

        if (!$comision) {
            $this->flash('error', 'Comisión no encontrada');
            $this->redirect('/comisiones');
            return;
        }

        $this->view('comisiones/show', [
            'title' => 'Detalle de Comisión',
            'comision' => $comision
        ]);
    }

    /**
     * Muestra formulario para marcar comisión como pagada
     * GET /comisiones/pagar/{id}
     */
    public function pagar($id)
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $comision = $this->comisionModel->findById($id);

        if (!$comision) {
            $this->flash('error', 'Comisión no encontrada');
            $this->redirect('/comisiones');
            return;
        }

        if ($comision['estado'] !== 'pendiente') {
            $this->flash('warning', 'Esta comisión ya fue procesada');
            $this->redirect('/comisiones/show/' . $id);
            return;
        }

        $this->view('comisiones/pagar', [
            'title' => 'Registrar Pago de Comisión',
            'comision' => $comision
        ]);
    }

    /**
     * Procesa el pago de una comisión
     * POST /comisiones/registrar-pago/{id}
     */
    public function registrarPago($id)
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);
        
        try {
            $comision = $this->comisionModel->findById($id);

            if (!$comision) {
                throw new \Exception('Comisión no encontrada');
            }

            if ($comision['estado'] !== 'pendiente') {
                throw new \Exception('Esta comisión ya fue procesada');
            }

            // Validar datos requeridos
            if (empty($_POST['fecha_pago']) || empty($_POST['metodo_pago'])) {
                throw new \Exception('Fecha de pago y método de pago son obligatorios');
            }

            $data = [
                'fecha_pago' => $_POST['fecha_pago'],
                'metodo_pago' => $_POST['metodo_pago'],
                'referencia_pago' => $_POST['referencia_pago'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null
            ];

            $this->comisionModel->marcarComoPagada($id, $data);

            \Logger::info("Comisión pagada", [
                'comision_id' => $id,
                'vendedor_id' => $comision['vendedor_id'],
                'vendedor' => $comision['vendedor_nombre'],
                'valor' => $comision['valor_comision'],
                'metodo' => $data['metodo_pago'],
                'usuario' => $_SESSION['user']['nombre']
            ]);

            $this->flash('success', 'Pago de comisión registrado exitosamente. Valor: ' . formatMoney($comision['valor_comision']));
            $this->redirect('/comisiones/show/' . $id);

        } catch (\Exception $e) {
            $this->flash('error', 'Error al registrar pago: ' . $e->getMessage());
            $this->redirect('/comisiones/pagar/' . $id);
        }
    }

    /**
     * Configuración de porcentajes de comisión
     * GET /comisiones/configuracion
     */
    public function configuracion()
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $db = \Database::getInstance();
        
        // Obtener vendedores con su configuración
        $vendedores = $db->fetchAll(
            "SELECT 
                u.id, u.nombre, u.email, u.rol,
                COALESCE(cc.porcentaje_comision, 3.00) as porcentaje_comision,
                cc.observaciones,
                cc.updated_at as fecha_actualizacion
             FROM users u
             LEFT JOIN configuracion_comisiones cc ON u.id = cc.vendedor_id AND cc.activo = 1
             WHERE u.rol IN ('administrador', 'vendedor')
             AND u.activo = 1
             ORDER BY u.nombre"
        );

        $this->view('comisiones/configuracion', [
            'title' => 'Configuración de Comisiones',
            'vendedores' => $vendedores
        ]);
    }

    /**
     * Actualiza la configuración de comisión de un vendedor
     * POST /comisiones/actualizar-configuracion/{id}
     */
    public function actualizarConfiguracion($vendedorId)
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);
        
        try {
            if (empty($_POST['porcentaje_comision'])) {
                throw new \Exception('El porcentaje de comisión es obligatorio');
            }

            $porcentaje = (float)$_POST['porcentaje_comision'];
            
            if ($porcentaje < 0 || $porcentaje > 100) {
                throw new \Exception('El porcentaje debe estar entre 0 y 100');
            }

            $observaciones = $_POST['observaciones'] ?? null;

            $this->comisionModel->actualizarConfiguracion($vendedorId, $porcentaje, $observaciones);

            \Logger::info("Configuración de comisión actualizada", [
                'vendedor_id' => $vendedorId,
                'porcentaje' => $porcentaje,
                'usuario' => $_SESSION['user']['nombre']
            ]);

            $this->flash('success', 'Configuración de comisión actualizada correctamente');
            $this->redirect('/comisiones/configuracion');

        } catch (\Exception $e) {
            $this->flash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/comisiones/configuracion');
        }
    }

    /**
     * Mis comisiones (para vendedores)
     * GET /comisiones/mis-comisiones
     */
    public function misComisiones()
    {
        $this->requireAuth();

        $vendedorId = $_SESSION['user']['id'];

        $filtros = [
            'vendedor_id' => $vendedorId,
            'estado' => $_GET['estado'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
        ];

        $comisiones = $this->comisionModel->getAll($filtros);
        $resumen = $this->comisionModel->getResumenPorVendedor($vendedorId);

        $this->view('comisiones/mis-comisiones', [
            'title' => 'Mis Comisiones',
            'comisiones' => $comisiones,
            'resumen' => $resumen[0] ?? null,
            'filtros' => $filtros
        ]);
    }
}
