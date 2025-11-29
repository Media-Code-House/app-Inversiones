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
        parent::__construct();
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
        $proyectos = $this->proyectoModel->getAll();
        $clientes = $this->clienteModel->getAll();

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
        try {
            // Validar datos requeridos
            $required = ['proyecto_id', 'codigo_lote', 'area', 'precio_lista'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            // Validar valores positivos
            $errors = $this->loteModel->validatePositiveValues($_POST);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // Validar código único en el proyecto
            if ($this->loteModel->codigoExists($_POST['proyecto_id'], $_POST['codigo_lote'])) {
                throw new \Exception("Ya existe un lote con ese código en el proyecto seleccionado");
            }

            // Preparar datos
            $data = [
                'proyecto_id' => (int)$_POST['proyecto_id'],
                'codigo_lote' => trim($_POST['codigo_lote']),
                'area' => (float)$_POST['area'],
                'precio_lista' => (float)$_POST['precio_lista'],
                'estado' => $_POST['estado'] ?? 'disponible',
                'ubicacion' => $_POST['ubicacion'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? null
            ];

            // Si el estado es vendido, validar y agregar datos de venta
            if ($data['estado'] === 'vendido') {
                if (empty($_POST['cliente_id'])) {
                    throw new \Exception("Debe seleccionar un cliente para marcar el lote como vendido");
                }
                
                $data['cliente_id'] = (int)$_POST['cliente_id'];
                $data['precio_venta'] = !empty($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : null;
                $data['fecha_venta'] = !empty($_POST['fecha_venta']) ? $_POST['fecha_venta'] : date('Y-m-d');
            }

            // Crear lote
            $loteId = $this->loteModel->create($data);

            $this->flash('success', 'Lote creado exitosamente');
            $this->redirect('/lotes/show/' . $loteId);

        } catch (\Exception $e) {
            $this->flash('error', 'Error al crear lote: ' . $e->getMessage());
            $this->redirect('/lotes/create');
        }
    }

    /**
     * Muestra formulario para editar lote
     * GET /lotes/edit/{id}
     */
    public function edit($id)
    {
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

            // Validar valores positivos
            $errors = $this->loteModel->validatePositiveValues($_POST);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            // Validar código único (excluyendo este lote)
            if ($this->loteModel->codigoExists($_POST['proyecto_id'], $_POST['codigo_lote'], $id)) {
                throw new \Exception("Ya existe un lote con ese código en el proyecto seleccionado");
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

            // Si el estado es vendido, gestionar datos de venta
            if ($data['estado'] === 'vendido') {
                if (empty($_POST['cliente_id'])) {
                    throw new \Exception("Debe seleccionar un cliente para marcar el lote como vendido");
                }
                
                $data['cliente_id'] = (int)$_POST['cliente_id'];
                $data['precio_venta'] = !empty($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : null;
                $data['fecha_venta'] = !empty($_POST['fecha_venta']) ? $_POST['fecha_venta'] : ($lote['fecha_venta'] ?? date('Y-m-d'));
            }

            // Actualizar lote
            $this->loteModel->update($id, $data);

            $this->flash('success', 'Lote actualizado exitosamente');
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
