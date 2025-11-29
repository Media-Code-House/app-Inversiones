<?php

namespace App\Controllers;

use App\Models\VendedorModel;
use App\Models\ComisionModel;

/**
 * VendedorController - Gestión completa de Vendedores
 */
class VendedorController extends Controller
{
    private $vendedorModel;
    private $comisionModel;

    public function __construct()
    {
        \Logger::info('VendedorController::__construct - Iniciando constructor');
        try {
            \Logger::info('VendedorController::__construct - Creando VendedorModel...');
            $this->vendedorModel = new VendedorModel();
            \Logger::info('VendedorController::__construct - VendedorModel creado OK');
            
            \Logger::info('VendedorController::__construct - Creando ComisionModel...');
            $this->comisionModel = new ComisionModel();
            \Logger::info('VendedorController::__construct - ComisionModel creado OK');
            
            \Logger::info('VendedorController::__construct - Constructor completado exitosamente');
        } catch (\Exception $e) {
            \Logger::error('VendedorController::__construct - ERROR en constructor: ' . $e->getMessage());
            \Logger::error('VendedorController::__construct - Archivo: ' . $e->getFile() . ' línea ' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Lista todos los vendedores
     * GET /vendedores
     */
    public function index()
    {
        try {
            \Logger::info('VendedorController::index - Iniciando');
            
            \Logger::info('VendedorController::index - Verificando autenticación...');
            $this->requireAuth();
            \Logger::info('VendedorController::index - requireAuth() OK');
            
            \Logger::info('VendedorController::index - Verificando rol administrador...');
            $this->requireRole(['administrador']);
            \Logger::info('VendedorController::index - requireRole() OK');
            
            \Logger::info('VendedorController::index - Auth verificada - Iniciando modelo');

            $filtros = [
                'search' => $_GET['search'] ?? '',
                'estado' => $_GET['estado'] ?? ''
            ];
            
            \Logger::info('VendedorController::index - Filtros: ' . json_encode($filtros));

            \Logger::info('VendedorController::index - Llamando a vendedorModel->getAll()...');
            $vendedores = $this->vendedorModel->getAll($filtros);
            \Logger::info('VendedorController::index - vendedorModel->getAll() completado');
            
            \Logger::info('VendedorController::index - Vendedores obtenidos: ' . count($vendedores));

            \Logger::info('VendedorController::index - Preparando datos para vista...');
            $viewData = [
                'title' => 'Gestión de Vendedores',
                'vendedores' => $vendedores,
                'filtros' => $filtros
            ];
            \Logger::info('VendedorController::index - Datos preparados, renderizando vista vendedores/index...');
            
            $this->view('vendedores/index', $viewData);
            
            \Logger::info('VendedorController::index - Vista renderizada correctamente');
            
        } catch (\Exception $e) {
            \Logger::error('VendedorController::index - ERROR CAPTURADO');
            \Logger::error('VendedorController::index - Mensaje: ' . $e->getMessage());
            \Logger::error('VendedorController::index - Archivo: ' . $e->getFile() . ' línea ' . $e->getLine());
            \Logger::error('VendedorController::index - Código: ' . $e->getCode());
            \Logger::error('VendedorController::index - Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Ver detalle de un vendedor
     * GET /vendedores/show/{id}
     */
    public function show($id)
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $vendedor = $this->vendedorModel->findById($id);

        if (!$vendedor) {
            $this->flash('error', 'Vendedor no encontrado');
            $this->redirect('/vendedores');
            return;
        }

        // Obtener lotes vendidos
        $lotesVendidos = $this->vendedorModel->getLotesVendidos($id, 10);

        // Obtener comisiones
        $comisiones = $this->vendedorModel->getComisiones($id);

        // Calcular estadísticas adicionales
        $estadisticas = [
            'comisiones_pendientes_count' => 0,
            'comisiones_pagadas_count' => 0,
            'ultimo_pago' => null
        ];

        foreach ($comisiones as $c) {
            if ($c['estado'] === 'pendiente') {
                $estadisticas['comisiones_pendientes_count']++;
            } elseif ($c['estado'] === 'pagada') {
                $estadisticas['comisiones_pagadas_count']++;
            }
        }

        $this->view('vendedores/show', [
            'title' => 'Detalle de Vendedor',
            'vendedor' => $vendedor,
            'lotesVendidos' => $lotesVendidos,
            'comisiones' => $comisiones,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Formulario para crear vendedor
     * GET /vendedores/create
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        // Obtener usuarios sin vendedor asignado
        $db = \Database::getInstance();
        $usuariosDisponibles = $db->fetchAll(
            "SELECT u.id, u.nombre, u.email, u.rol
             FROM users u
             WHERE u.rol IN ('administrador', 'vendedor')
             AND u.activo = 1
             AND NOT EXISTS (SELECT 1 FROM vendedores v WHERE v.user_id = u.id)
             ORDER BY u.nombre"
        );

        $this->view('vendedores/create', [
            'title' => 'Crear Vendedor',
            'usuariosDisponibles' => $usuariosDisponibles
        ]);
    }

    /**
     * Guardar nuevo vendedor
     * POST /vendedores/store
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        try {
            // Validar campos requeridos
            $required = ['user_id', 'codigo_vendedor', 'numero_documento', 'nombres', 'apellidos', 'email', 'fecha_ingreso'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            // Validar unicidad
            if ($this->vendedorModel->codigoExists($_POST['codigo_vendedor'])) {
                throw new \Exception("El código de vendedor ya existe");
            }

            if ($this->vendedorModel->documentoExists($_POST['numero_documento'])) {
                throw new \Exception("El número de documento ya está registrado");
            }

            // Validar user_id no tenga vendedor ya
            if ($this->vendedorModel->findByUserId($_POST['user_id'])) {
                throw new \Exception("Este usuario ya tiene un perfil de vendedor");
            }

            $data = [
                'user_id' => (int)$_POST['user_id'],
                'codigo_vendedor' => trim($_POST['codigo_vendedor']),
                'tipo_documento' => $_POST['tipo_documento'] ?? 'CC',
                'numero_documento' => trim($_POST['numero_documento']),
                'nombres' => trim($_POST['nombres']),
                'apellidos' => trim($_POST['apellidos']),
                'telefono' => $_POST['telefono'] ?? null,
                'celular' => $_POST['celular'] ?? null,
                'email' => trim($_POST['email']),
                'direccion' => $_POST['direccion'] ?? null,
                'ciudad' => $_POST['ciudad'] ?? null,
                'fecha_ingreso' => $_POST['fecha_ingreso'],
                'tipo_contrato' => $_POST['tipo_contrato'] ?? 'indefinido',
                'porcentaje_comision_default' => (float)($_POST['porcentaje_comision_default'] ?? 3.00),
                'banco' => $_POST['banco'] ?? null,
                'tipo_cuenta' => $_POST['tipo_cuenta'] ?? null,
                'numero_cuenta' => $_POST['numero_cuenta'] ?? null,
                'estado' => 'activo',
                'observaciones' => $_POST['observaciones'] ?? null
            ];

            $vendedorId = $this->vendedorModel->create($data);

            \Logger::info("Vendedor creado", [
                'vendedor_id' => $vendedorId,
                'codigo' => $data['codigo_vendedor'],
                'nombre' => $data['nombres'] . ' ' . $data['apellidos'],
                'usuario' => $_SESSION['user']['nombre']
            ]);

            $this->flash('success', 'Vendedor creado exitosamente');
            $this->redirect('/vendedores/show/' . $vendedorId);

        } catch (\Exception $e) {
            $this->flash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/vendedores/create');
        }
    }

    /**
     * Formulario para editar vendedor
     * GET /vendedores/edit/{id}
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $vendedor = $this->vendedorModel->findById($id);

        if (!$vendedor) {
            $this->flash('error', 'Vendedor no encontrado');
            $this->redirect('/vendedores');
            return;
        }

        $this->view('vendedores/edit', [
            'title' => 'Editar Vendedor',
            'vendedor' => $vendedor
        ]);
    }

    /**
     * Actualizar vendedor
     * POST /vendedores/update/{id}
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        try {
            $vendedor = $this->vendedorModel->findById($id);

            if (!$vendedor) {
                throw new \Exception('Vendedor no encontrado');
            }

            // Validar campos requeridos
            $required = ['codigo_vendedor', 'numero_documento', 'nombres', 'apellidos', 'email', 'fecha_ingreso'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            // Validar unicidad
            if ($this->vendedorModel->codigoExists($_POST['codigo_vendedor'], $id)) {
                throw new \Exception("El código de vendedor ya existe");
            }

            if ($this->vendedorModel->documentoExists($_POST['numero_documento'], $id)) {
                throw new \Exception("El número de documento ya está registrado");
            }

            $data = [
                'codigo_vendedor' => trim($_POST['codigo_vendedor']),
                'tipo_documento' => $_POST['tipo_documento'],
                'numero_documento' => trim($_POST['numero_documento']),
                'nombres' => trim($_POST['nombres']),
                'apellidos' => trim($_POST['apellidos']),
                'telefono' => $_POST['telefono'] ?? null,
                'celular' => $_POST['celular'] ?? null,
                'email' => trim($_POST['email']),
                'direccion' => $_POST['direccion'] ?? null,
                'ciudad' => $_POST['ciudad'] ?? null,
                'fecha_ingreso' => $_POST['fecha_ingreso'],
                'fecha_salida' => $_POST['fecha_salida'] ?? null,
                'tipo_contrato' => $_POST['tipo_contrato'],
                'porcentaje_comision_default' => (float)$_POST['porcentaje_comision_default'],
                'banco' => $_POST['banco'] ?? null,
                'tipo_cuenta' => $_POST['tipo_cuenta'] ?? null,
                'numero_cuenta' => $_POST['numero_cuenta'] ?? null,
                'estado' => $_POST['estado'],
                'observaciones' => $_POST['observaciones'] ?? null
            ];

            $this->vendedorModel->update($id, $data);

            \Logger::info("Vendedor actualizado", [
                'vendedor_id' => $id,
                'codigo' => $data['codigo_vendedor'],
                'usuario' => $_SESSION['user']['nombre']
            ]);

            $this->flash('success', 'Vendedor actualizado exitosamente');
            $this->redirect('/vendedores/show/' . $id);

        } catch (\Exception $e) {
            $this->flash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/vendedores/edit/' . $id);
        }
    }

    /**
     * Ranking de vendedores
     * GET /vendedores/ranking
     */
    public function ranking()
    {
        $this->requireAuth();
        $this->requireRole(['administrador']);

        $periodo = $_GET['periodo'] ?? 'mes';
        $ranking = $this->vendedorModel->getRanking($periodo);

        $this->view('vendedores/ranking', [
            'title' => 'Ranking de Vendedores',
            'ranking' => $ranking,
            'periodo' => $periodo
        ]);
    }

    /**
     * Mi perfil de vendedor (para vendedores ver su propia info)
     * GET /vendedores/mi-perfil
     */
    public function miPerfil()
    {
        $this->requireAuth();

        $userId = $_SESSION['user']['id'];
        $vendedor = $this->vendedorModel->findByUserId($userId);

        if (!$vendedor) {
            $this->flash('error', 'No tienes un perfil de vendedor asignado');
            $this->redirect('/dashboard');
            return;
        }

        // Redirigir al show con sus propios datos
        $this->redirect('/vendedores/show/' . $vendedor['id']);
    }
}
