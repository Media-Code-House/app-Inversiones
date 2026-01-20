<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\LoteModel;

/**
 * ClienteController
 * Gestiona el CRUD completo de clientes y visualización de propiedades asociadas
 */
class ClienteController extends Controller
{
    private $clienteModel;
    private $loteModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->loteModel = new LoteModel();
    }

    /**
     * Lista todos los clientes con filtros y paginación
     * GET /clientes
     */
    public function index()
    {
        if (!can('ver_clientes')) {
            $_SESSION['error'] = 'No tienes permisos para ver clientes';
            redirect('/dashboard');
            return;
        }

        // Parámetros de paginación y filtros
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 15;
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'tipo_documento' => $_GET['tipo_documento'] ?? '',
            'page' => $page,
            'per_page' => $perPage
        ];

        // Obtener clientes paginados
        $clientes = $this->clienteModel->getAllPaginated($filters);

        $this->view('clientes/index', [
            'pageTitle' => 'Gestión de Clientes',
            'clientes' => $clientes,
            'filtros' => $filters
        ]);
    }

    /**
     * Muestra formulario para crear nuevo cliente
     * GET /clientes/create
     */
    public function create()
    {
        if (!can('crear_clientes')) {
            $_SESSION['error'] = 'No tienes permisos para crear clientes';
            redirect('/clientes');
            return;
        }

        $this->view('clientes/create', [
            'pageTitle' => 'Crear Cliente'
        ]);
    }

    /**
     * Procesa creación de nuevo cliente
     * POST /clientes/store
     */
    public function store()
    {
        if (!can('crear_clientes')) {
            $_SESSION['error'] = 'No tienes permisos para crear clientes';
            redirect('/clientes');
            return;
        }

        try {
            // Validar CSRF si viene de formulario normal (no AJAX)
            if (!isset($_POST['ajax'])) {
                if (!$this->validateCsrf()) {
                    throw new \Exception('Token de seguridad inválido');
                }
            }

            // Validar datos requeridos
            $required = ['tipo_documento', 'numero_documento', 'nombre'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            // Validar que no exista el documento
            $existente = $this->clienteModel->findByDocumento(
                $_POST['tipo_documento'], 
                $_POST['numero_documento']
            );

            if ($existente) {
                throw new \Exception("Ya existe un cliente con el documento {$_POST['tipo_documento']} {$_POST['numero_documento']}");
            }

            // Preparar datos
            $data = [
                'tipo_documento' => $_POST['tipo_documento'],
                'numero_documento' => trim($_POST['numero_documento']),
                'nombre' => trim($_POST['nombre']),
                'telefono' => $_POST['telefono'] ?? null,
                'email' => $_POST['email'] ?? null,
                'direccion' => $_POST['direccion'] ?? null,
                'ciudad' => $_POST['ciudad'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null
            ];

            // Crear cliente
            $clienteId = $this->clienteModel->create($data);

            // Si es petición AJAX, devolver JSON
            if (isset($_POST['ajax'])) {
                $cliente = $this->clienteModel->findById($clienteId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'cliente' => $cliente,
                    'message' => 'Cliente creado exitosamente'
                ]);
                exit;
            }

            $_SESSION['success'] = 'Cliente creado exitosamente';
            redirect('/clientes/create');

        } catch (\Exception $e) {
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }

            $_SESSION['error'] = 'Error al crear cliente: ' . $e->getMessage();
            saveOldInput($_POST);
            redirect('/clientes/create');
        }
    }

    /**
     * Muestra detalle de un cliente con propiedades asociadas
     * GET /clientes/show/{id}
     */
    public function show($id)
    {
        if (!can('ver_clientes')) {
            $_SESSION['error'] = 'No tienes permisos para ver clientes';
            redirect('/clientes');
            return;
        }

        $cliente = $this->clienteModel->findById($id);

        if (!$cliente) {
            $_SESSION['error'] = 'Cliente no encontrado';
            redirect('/clientes');
            return;
        }

        // Obtener lotes/propiedades asociados al cliente
        $propiedades = $this->loteModel->getByCliente($id);

        // Calcular estadísticas del cliente
        $estadisticas = [
            'total_propiedades' => count($propiedades),
            'propiedades_vendidas' => 0,
            'propiedades_reservadas' => 0,
            'valor_total_compras' => 0,
            'saldo_pendiente' => 0,
            'total_pagado' => 0
        ];

        foreach ($propiedades as $propiedad) {
            if ($propiedad['estado'] === 'vendido') {
                $estadisticas['propiedades_vendidas']++;
                $estadisticas['valor_total_compras'] += $propiedad['precio_venta'] ?? $propiedad['precio_lista'];
            } elseif ($propiedad['estado'] === 'reservado') {
                $estadisticas['propiedades_reservadas']++;
            }

            // Si tiene amortización, obtener saldos
            if ($propiedad['tiene_amortizacion'] > 0) {
                $resumen = $this->clienteModel->getResumenAmortizacion($propiedad['id']);
                $estadisticas['saldo_pendiente'] += $resumen['saldo_total'] ?? 0;
                $estadisticas['total_pagado'] += $resumen['total_pagado'] ?? 0;
            }
        }

        $this->view('clientes/show', [
            'pageTitle' => 'Detalle del Cliente',
            'cliente' => $cliente,
            'propiedades' => $propiedades,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Muestra formulario para editar cliente
     * GET /clientes/edit/{id}
     */
    public function edit($id)
    {
        if (!can('editar_clientes')) {
            $_SESSION['error'] = 'No tienes permisos para editar clientes';
            redirect('/clientes');
            return;
        }

        $cliente = $this->clienteModel->findById($id);

        if (!$cliente) {
            $_SESSION['error'] = 'Cliente no encontrado';
            redirect('/clientes');
            return;
        }

        // Obtener propiedades asociadas para verificar si se puede eliminar
        $lotes = $this->loteModel->getByCliente($id);
        $cliente['total_propiedades'] = count($lotes);

        $this->view('clientes/edit', [
            'pageTitle' => 'Editar Cliente',
            'cliente' => $cliente
        ]);
    }

    /**
     * Procesa actualización de cliente
     * POST /clientes/update/{id}
     */
    public function update($id)
    {
        if (!can('editar_clientes')) {
            $_SESSION['error'] = 'No tienes permisos para editar clientes';
            redirect('/clientes');
            return;
        }

        try {
            $cliente = $this->clienteModel->findById($id);

            if (!$cliente) {
                throw new \Exception("Cliente no encontrado");
            }

            // Validar datos requeridos
            $required = ['tipo_documento', 'numero_documento', 'nombre'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            // Validar unicidad de documento (excluyendo este cliente)
            $existente = $this->clienteModel->findByDocumento(
                $_POST['tipo_documento'], 
                $_POST['numero_documento']
            );

            if ($existente && $existente['id'] != $id) {
                throw new \Exception("Ya existe otro cliente con el documento {$_POST['tipo_documento']} {$_POST['numero_documento']}");
            }

            // Preparar datos
            $data = [
                'tipo_documento' => $_POST['tipo_documento'],
                'numero_documento' => trim($_POST['numero_documento']),
                'nombre' => trim($_POST['nombre']),
                'telefono' => $_POST['telefono'] ?? null,
                'email' => $_POST['email'] ?? null,
                'direccion' => $_POST['direccion'] ?? null,
                'ciudad' => $_POST['ciudad'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null
            ];

            // Actualizar cliente
            $this->clienteModel->update($id, $data);

            $_SESSION['success'] = 'Cliente actualizado exitosamente';
            clearOldInput();
            clearErrors();
            redirect('/clientes/show/' . $id);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al actualizar cliente: ' . $e->getMessage();
            saveOldInput($_POST);
            redirect('/clientes/edit/' . $id);
        }
    }

    /**
     * Elimina un cliente (solo si no tiene propiedades asociadas)
     * POST /clientes/delete/{id}
     */
    public function delete($id)
    {
        if (!can('eliminar_clientes')) {
            $_SESSION['error'] = 'No tienes permisos para eliminar clientes';
            redirect('/clientes');
            return;
        }

        try {
            $cliente = $this->clienteModel->findById($id);

            if (!$cliente) {
                throw new \Exception("Cliente no encontrado");
            }

            // Verificar si tiene propiedades asociadas
            $propiedades = $this->loteModel->getByCliente($id);

            if (!empty($propiedades)) {
                throw new \Exception("No se puede eliminar el cliente porque tiene {count($propiedades)} propiedad(es) asociada(s)");
            }

            // Eliminar cliente
            $this->clienteModel->delete($id);

            $_SESSION['success'] = 'Cliente eliminado exitosamente';
            redirect('/clientes');

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al eliminar cliente: ' . $e->getMessage();
            redirect('/clientes/show/' . $id);
        }
    }

    /**
     * Búsqueda AJAX de clientes por documento o nombre
     * POST /clientes/buscar
     */
    public function buscar()
    {
        header('Content-Type: application/json');

        if (!can('ver_clientes')) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            exit;
        }

        try {
            $search = $_POST['search'] ?? '';
            
            if (strlen($search) < 2) {
                echo json_encode(['success' => true, 'clientes' => []]);
                exit;
            }

            $clientes = $this->clienteModel->search($search);

            echo json_encode(['success' => true, 'clientes' => $clientes]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
