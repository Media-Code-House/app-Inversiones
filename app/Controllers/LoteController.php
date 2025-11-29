<?php

namespace App\Controllers;

use App\Models\LoteModel;
use App\Models\ProyectoModel;
use App\Models\ClienteModel;
use App\Models\AmortizacionModel;

/**
 * LoteController - Controlador de Lotes
 * Gestiona CRUD completo de lotes
 */
class LoteController extends Controller
{
    private $loteModel;
    private $proyectoModel;
    private $clienteModel;
    private $amortizacionModel;

    public function __construct()
    {
        $this->loteModel = new LoteModel();
        $this->proyectoModel = new ProyectoModel();
        $this->clienteModel = new ClienteModel();
        $this->amortizacionModel = new AmortizacionModel();
    }

    /**
     * Lista todos los lotes con filtros
     * GET /lotes/
     */
    public function index()
    {
        $this->requireAuth();
        
        // Construir filtros desde parámetros GET
        $filters = [];
        
        if (!empty($_GET['proyecto_id'])) {
            $filters['proyecto_id'] = (int)$_GET['proyecto_id'];
        }
        
        if (!empty($_GET['estado'])) {
            $filters['estado'] = $_GET['estado'];
        }
        
        if (!empty($_GET['busqueda'])) {
            $filters['busqueda'] = $_GET['busqueda'];
        }

        // Obtener lotes con filtros
        $lotes = $this->loteModel->getAll($filters);

        // Obtener lista de proyectos para el filtro
        $proyectos = $this->proyectoModel->getAll();

        // Lista de estados para el filtro
        $estados = ['disponible', 'reservado', 'vendido', 'bloqueado'];

        $this->view('lotes/index', [
            'title' => 'Gestión de Lotes',
            'lotes' => $lotes,
            'proyectos' => $proyectos,
            'estados' => $estados,
            'filtros' => $filters
        ]);
    }

    /**
     * Muestra formulario para crear nuevo lote
     * GET /lotes/create
     */
    public function create()
    {
        $this->requireAuth();
        
        $proyectos = $this->proyectoModel->getAll();
        $clientes = $this->clienteModel->getAll();
        
        // Validar que existan proyectos
        if (empty($proyectos)) {
            $this->flash('warning', 'Debes crear al menos un proyecto antes de poder agregar lotes');
            $this->redirect('/proyectos/create');
            return;
        }

        $this->view('lotes/create', [
            'title' => 'Crear Nuevo Lote',
            'proyectos' => $proyectos,
            'clientes' => $clientes
        ]);
    }

    /**
     * Procesa creación de nuevo lote
     * POST /lotes/store
     */
    public function store()
    {
        $this->requireAuth();
        
        try {
            // Validar datos requeridos
            $required = ['proyecto_id', 'codigo_lote', 'area', 'precio_lista'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            // INTEGRIDAD: Validar que el proyecto existe
            $proyecto = $this->proyectoModel->findById($_POST['proyecto_id']);
            if (!$proyecto) {
                throw new \Exception("El proyecto seleccionado no existe");
            }

            // Validar valores positivos
            $errors = $this->loteModel->validatePositiveValues($_POST);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // INTEGRIDAD: Validar unicidad compuesta (proyecto_id + codigo_lote)
            if ($this->loteModel->codigoExists($_POST['proyecto_id'], $_POST['codigo_lote'])) {
                throw new \Exception("Ya existe un lote con el código '{$_POST['codigo_lote']}' en el proyecto '{$proyecto['nombre']}'");
            }

            // Preparar datos base
            $data = [
                'proyecto_id' => (int)$_POST['proyecto_id'],
                'codigo_lote' => trim($_POST['codigo_lote']),
                'area' => (float)$_POST['area'],
                'precio_lista' => (float)$_POST['precio_lista'],
                'estado' => $_POST['estado'] ?? 'disponible',
                'ubicacion' => $_POST['ubicacion'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? null
            ];

            // LÓGICA DE VENTA: Si el estado es vendido, manejar cliente
            if ($data['estado'] === 'vendido') {
                $clienteId = $this->handleClienteForVenta($_POST);
                
                $data['cliente_id'] = $clienteId;
                $data['precio_venta'] = !empty($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : null;
                $data['fecha_venta'] = !empty($_POST['fecha_venta']) ? $_POST['fecha_venta'] : date('Y-m-d');
            }

            // Crear lote
            $loteId = $this->loteModel->create($data);

            $this->flash('success', 'Lote creado exitosamente en el proyecto ' . $proyecto['nombre']);
            $this->redirect('/lotes/show/' . $loteId);

        } catch (\Exception $e) {
            $this->flash('error', 'Error al crear lote: ' . $e->getMessage());
            $this->redirect('/lotes/create');
        }
    }

    /**
     * Maneja la asociación de cliente para venta
     * Crea el cliente automáticamente si no existe
     */
    private function handleClienteForVenta($postData)
    {
        // Opción 1: Cliente existente seleccionado
        if (!empty($postData['cliente_id'])) {
            $cliente = $this->clienteModel->findById($postData['cliente_id']);
            if (!$cliente) {
                throw new \Exception("El cliente seleccionado no existe");
            }
            return (int)$postData['cliente_id'];
        }

        // Opción 2: Crear cliente nuevo con datos mínimos
        if (!empty($postData['nuevo_cliente'])) {
            // Validar datos mínimos del nuevo cliente
            if (empty($postData['cliente_tipo_documento']) || 
                empty($postData['cliente_numero_documento']) || 
                empty($postData['cliente_nombre'])) {
                throw new \Exception("Para crear un nuevo cliente se requiere: tipo de documento, número de documento y nombre");
            }

            // Verificar si ya existe por documento
            $existente = $this->clienteModel->findByDocumento(
                $postData['cliente_tipo_documento'], 
                $postData['cliente_numero_documento']
            );

            if ($existente) {
                // Si existe, usar ese cliente
                return (int)$existente['id'];
            }

            // Crear nuevo cliente
            $clienteId = $this->clienteModel->createQuick([
                'tipo_documento' => $postData['cliente_tipo_documento'],
                'numero_documento' => $postData['cliente_numero_documento'],
                'nombre' => $postData['cliente_nombre'],
                'telefono' => $postData['cliente_telefono'] ?? null
            ]);

            return $clienteId;
        }

        throw new \Exception("Para vender un lote debe seleccionar un cliente existente o crear uno nuevo");
    }

    /**
     * Muestra formulario para editar lote
     * GET /lotes/edit/{id}
     */
    public function edit($id)
    {
        $this->requireAuth();
        
        $lote = $this->loteModel->findById($id);

        if (!$lote) {
            $this->flash('error', 'Lote no encontrado');
            $this->redirect('/lotes');
            return;
        }

        // Verificar si se puede editar (validación de negocio)
        $puedeEditar = true;
        $mensajeBloqueo = '';

        if ($lote['estado'] === 'vendido' && $lote['amortizacion_activa'] > 0) {
            $puedeEditar = false;
            $mensajeBloqueo = 'Este lote vendido tiene una amortización activa. Solo se pueden modificar campos descriptivos.';
        }

        $proyectos = $this->proyectoModel->getAll();
        $clientes = $this->clienteModel->getAll();

        $this->view('lotes/edit', [
            'title' => 'Editar Lote',
            'lote' => $lote,
            'proyectos' => $proyectos,
            'clientes' => $clientes,
            'puedeEditar' => $puedeEditar,
            'mensajeBloqueo' => $mensajeBloqueo
        ]);
    }

    /**
     * Procesa actualización de lote
     * POST /lotes/update/{id}
     */
    public function update($id)
    {
        $this->requireAuth();
        
        try {
            $lote = $this->loteModel->findById($id);

            if (!$lote) {
                throw new \Exception("Lote no encontrado");
            }

            // Validar datos requeridos
            $required = ['proyecto_id', 'codigo_lote', 'area', 'precio_lista'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            // INTEGRIDAD: Validar que el proyecto existe
            $proyecto = $this->proyectoModel->findById($_POST['proyecto_id']);
            if (!$proyecto) {
                throw new \Exception("El proyecto seleccionado no existe");
            }

            // Validar valores positivos
            $errors = $this->loteModel->validatePositiveValues($_POST);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // INTEGRIDAD: Validar unicidad compuesta (proyecto_id + codigo_lote) excluyendo este lote
            if ($this->loteModel->codigoExists($_POST['proyecto_id'], $_POST['codigo_lote'], $id)) {
                throw new \Exception("Ya existe un lote con el código '{$_POST['codigo_lote']}' en el proyecto '{$proyecto['nombre']}'");
            }

            // Validar cambio de estado
            if (isset($_POST['estado']) && $_POST['estado'] !== $lote['estado']) {
                $validacion = $this->loteModel->canChangeEstado($id, $_POST['estado']);
                if (!$validacion['valid']) {
                    throw new \Exception($validacion['message']);
                }
            }

            // Preparar datos
            $data = [
                'proyecto_id' => (int)$_POST['proyecto_id'],
                'codigo_lote' => trim($_POST['codigo_lote']),
                'area' => (float)$_POST['area'],
                'precio_lista' => (float)$_POST['precio_lista'],
                'estado' => $_POST['estado'] ?? $lote['estado'],
                'ubicacion' => $_POST['ubicacion'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? null
            ];

            // LÓGICA DE VENTA: Si el estado es vendido, manejar cliente
            if ($data['estado'] === 'vendido') {
                $clienteId = $this->handleClienteForVenta($_POST);
                
                $data['cliente_id'] = $clienteId;
                $data['precio_venta'] = !empty($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : null;
                $data['fecha_venta'] = !empty($_POST['fecha_venta']) ? $_POST['fecha_venta'] : ($lote['fecha_venta'] ?? date('Y-m-d'));
            }

            // Actualizar lote
            $this->loteModel->update($id, $data);

            $this->flash('success', 'Lote actualizado exitosamente en el proyecto ' . $proyecto['nombre']);
            $this->redirect('/lotes/show/' . $id);

        } catch (\Exception $e) {
            $this->flash('error', 'Error al actualizar lote: ' . $e->getMessage());
            $this->redirect('/lotes/edit/' . $id);
        }
    }

    /**
     * Muestra detalle completo de un lote
     * GET /lotes/show/{id}
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $lote = $this->loteModel->findById($id);

        if (!$lote) {
            $this->flash('error', 'Lote no encontrado');
            $this->redirect('/lotes');
            return;
        }

        // Obtener resumen de amortización si existe
        $resumenAmortizacion = null;
        if ($lote['tiene_amortizacion'] > 0) {
            $resumenAmortizacion = $this->amortizacionModel->getResumenByLote($id);
        }

        $this->view('lotes/show', [
            'title' => 'Detalle del Lote',
            'lote' => $lote,
            'resumenAmortizacion' => $resumenAmortizacion
        ]);
    }
}
