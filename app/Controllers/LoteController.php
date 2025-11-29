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
     * Lista todos los lotes con filtros y paginación
     * GET /lotes/
     */
    public function index()
    {
        $this->requireAuth();
        
        // Parámetros de paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 15;
        
        // Construir filtros desde parámetros GET
        $filters = [
            'search' => $_GET['search'] ?? '',
            'proyecto_id' => !empty($_GET['proyecto_id']) ? (int)$_GET['proyecto_id'] : null,
            'estado' => $_GET['estado'] ?? '',
            'page' => $page,
            'per_page' => $perPage
        ];

        // Obtener lotes paginados con JOINs completos
        $result = $this->loteModel->getAllPaginated($filters);
        
        // Calcular variables adicionales para cada lote
        foreach ($result['data'] as &$lote) {
            // Calcular precio por metro cuadrado
            $lote['precio_m2'] = $lote['area_m2'] > 0 
                ? round($lote['precio_lista'] / $lote['area_m2'], 0) 
                : 0;
            
            // Badge class según estado
            $lote['badgeClass'] = $this->getBadgeClass($lote['estado']);
        }

        // Obtener lista de proyectos activos para filtros
        $proyectos = $this->proyectoModel->getAll();

        // Array asociativo de estados
        $estados = [
            'disponible' => 'Disponible',
            'reservado' => 'Reservado',
            'vendido' => 'Vendido',
            'bloqueado' => 'Bloqueado'
        ];

        $this->view('lotes/index', [
            'title' => 'Gestión de Lotes - ' . APP_NAME,
            'lotes' => $result, // Estructura: data, total, per_page, current_page, last_page
            'proyectos' => $proyectos,
            'estados' => $estados,
            'filtros' => $filters
        ]);
    }
    
    /**
     * Determina la clase CSS del badge según el estado
     */
    private function getBadgeClass($estado)
    {
        $classes = [
            'disponible' => 'bg-success',
            'reservado' => 'bg-warning text-dark',
            'vendido' => 'bg-primary',
            'bloqueado' => 'bg-secondary'
        ];
        
        return $classes[$estado] ?? 'bg-secondary';
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

            // Preparar datos actualizados
            $data = [
                'proyecto_id' => (int)$_POST['proyecto_id'],
                'codigo_lote' => trim($_POST['codigo_lote']),
                'area_m2' => (float)$_POST['area'],
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
     * Muestra detalle completo de un lote con información financiera
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

        // Calcular variables de resumen
        $lote['precio_m2'] = $lote['area_m2'] > 0 
            ? round($lote['precio_lista'] / $lote['area_m2'], 0) 
            : 0;

        // Obtener información financiera si el lote está vendido
        $amortizacion = null;
        $cuotas = [];
        $pagos = [];
        $resumenPlan = null;
        
        if ($lote['tiene_amortizacion'] > 0) {
            // Resumen del plan de amortización
            $resumenAmortizacion = $this->amortizacionModel->getResumenByLote($id);
            
            // Cuotas del plan
            $cuotas = $this->amortizacionModel->getByLote($id);
            
            // Calcular variables financieras
            $total_pagado = $resumenAmortizacion['total_pagado'] ?? 0;
            $valor_total = $resumenAmortizacion['valor_total_financiado'] ?? 0;
            $saldo_pendiente = $resumenAmortizacion['saldo_total'] ?? 0;
            $porcentaje_pagado = $valor_total > 0 ? round(($total_pagado / $valor_total) * 100, 2) : 0;
            $cuotas_mora = $resumenAmortizacion['cuotas_vencidas'] ?? 0;
            $cuotas_pagadas = $resumenAmortizacion['cuotas_pagadas'] ?? 0;
            
            $resumenPlan = [
                'total_cuotas' => $resumenAmortizacion['total_cuotas'] ?? 0,
                'valor_total' => $valor_total,
                'total_pagado' => $total_pagado,
                'saldo_pendiente' => $saldo_pendiente,
                'porcentaje_pagado' => $porcentaje_pagado,
                'cuotas_pagadas' => $cuotas_pagadas,
                'cuotas_pendientes' => $resumenAmortizacion['cuotas_pendientes'] ?? 0,
                'cuotas_mora' => $cuotas_mora,
                'max_dias_mora' => $resumenAmortizacion['max_dias_mora'] ?? 0
            ];
        }

        // Historial de auditoría (simulado - preparado para Módulo 5)
        $historial = $this->getHistorialSimulado($lote);

        $this->view('lotes/show', [
            'title' => 'Detalle del Lote: ' . $lote['codigo_lote'],
            'lote' => $lote,
            'amortizacion' => $resumenPlan,
            'cuotas' => $cuotas,
            'pagos' => $pagos,
            'historial' => $historial,
            'total_pagado' => $total_pagado ?? 0,
            'saldo_pendiente' => $saldo_pendiente ?? 0,
            'porcentaje_pagado' => $porcentaje_pagado ?? 0,
            'cuotas_mora' => $cuotas_mora ?? 0,
            'cuotas_pagadas' => $cuotas_pagadas ?? 0,
            'precio_m2' => $lote['precio_m2']
        ]);
    }
    
    /**
     * Genera historial simulado para auditoría
     * (Será reemplazado en Módulo 5 con tabla de auditoría real)
     */
    private function getHistorialSimulado($lote)
    {
        $historial = [];
        
        // Evento de creación
        $historial[] = [
            'fecha' => $lote['created_at'],
            'evento' => 'Creación de lote',
            'usuario' => 'Sistema',
            'descripcion' => 'Lote creado en el sistema'
        ];
        
        // Evento de venta si aplica
        if ($lote['estado'] === 'vendido' && !empty($lote['fecha_venta'])) {
            $historial[] = [
                'fecha' => $lote['fecha_venta'],
                'evento' => 'Venta realizada',
                'usuario' => $lote['vendedor_nombre'] ?? 'No registrado',
                'descripcion' => 'Lote vendido a ' . ($lote['cliente_nombre'] ?? 'Cliente no especificado')
            ];
        }
        
        return $historial;
    }
    
    /**
     * Muestra el plan de amortización del lote
     * GET /lotes/amortizacion/{id}
     * PREPARADO PARA MÓDULO 5
     */
    public function verAmortizacion($id)
    {
        $this->requireAuth();
        
        $lote = $this->loteModel->findById($id);
        
        if (!$lote) {
            $this->flash('error', 'Lote no encontrado');
            $this->redirect('/lotes');
            return;
        }
        
        // Obtener cuotas y resumen
        $cuotas = $this->amortizacionModel->getByLote($id);
        $resumenPlan = $this->amortizacionModel->getResumenByLote($id);
        
        $this->view('lotes/amortizacion', [
            'title' => 'Plan de Amortización - ' . $lote['codigo_lote'],
            'lote' => $lote,
            'cuotas' => $cuotas,
            'resumen_plan' => $resumenPlan
        ]);
    }
    
    /**
     * Muestra formulario para registrar pago
     * GET /lotes/registrar-pago/{id}
     * PREPARADO PARA MÓDULO 5
     */
    public function registrarPago($id)
    {
        $this->requireAuth();
        
        $lote = $this->loteModel->findById($id);
        
        if (!$lote) {
            $this->flash('error', 'Lote no encontrado');
            $this->redirect('/lotes');
            return;
        }
        
        // Obtener cuotas pendientes
        $cuotasPendientes = $this->amortizacionModel->getPendientesByLote($id);
        
        $this->view('lotes/registrar_pago', [
            'title' => 'Registrar Pago - ' . $lote['codigo_lote'],
            'lote' => $lote,
            'cuotas_pendientes' => $cuotasPendientes
        ]);
    }

    /**
     * Elimina un lote
     * POST /lotes/delete/{id}
     */
    public function delete($id)
    {
        $this->requireAuth();

        // Verificar permisos
        if (!can('eliminar_lotes')) {
            setFlash('error', 'No tienes permisos para eliminar lotes');
            redirect('/lotes');
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            setFlash('error', 'Token de seguridad inválido');
            redirect('/lotes');
        }

        // Verificar si el lote existe
        $lote = $this->loteModel->findById($id);
        if (!$lote) {
            setFlash('error', 'Lote no encontrado');
            redirect('/lotes');
        }

        $proyectoId = $lote['proyecto_id'];

        // Advertencia especial si está vendido (validación adicional en el futuro)
        if ($lote['estado'] === 'vendido') {
            // Por ahora solo advertimos, en Módulo 6 se validará contra pagos
            // setFlash('warning', 'El lote estaba vendido. Se ha eliminado toda la información asociada.');
        }

        // Eliminar lote
        if ($this->loteModel->delete($id)) {
            setFlash('success', 'Lote eliminado correctamente');
        } else {
            setFlash('error', 'Error al eliminar el lote');
        }

        redirect('/proyectos/show/' . $proyectoId);
    }
}
