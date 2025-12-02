<?php

namespace App\Controllers;

use App\Models\LoteModel;
use App\Models\ClienteModel;
use App\Models\ProyectoModel;

/**
 * InicialController
 * 
 * Controlador específico para gestionar el Plan de Pago Inicial Diferido.
 * Permite crear un plan de cuotas para la inicial antes de generar
 * el plan de amortización principal del lote.
 * 
 * FLUJO DE ESTADOS:
 * 1. Lote en estado 'reservado' con plan_inicial_id activo
 * 2. Se registran pagos contra el plan inicial
 * 3. Al completar el pago inicial, el lote cambia a 'vendido' automáticamente
 * 4. Recién entonces se puede crear el plan de amortización principal
 * 
 * @author Sistema APP-Inversiones
 * @version 1.0.0
 * @date 2025-12-02
 */
class InicialController extends Controller
{
    private $loteModel;
    private $clienteModel;
    private $proyectoModel;
    private $db;

    public function __construct()
    {
        $this->loteModel = new LoteModel();
        $this->clienteModel = new ClienteModel();
        $this->proyectoModel = new ProyectoModel();
        $this->db = \Database::getInstance();
    }

    /**
     * Muestra el formulario para crear un Plan de Pago Inicial Diferido
     * GET /lotes/inicial/create/{lote_id}
     */
    public function create($loteId)
    {
        $this->requireAuth();
        $this->requireRole(['administrador', 'consulta']);

        try {
            // Obtener información del lote
            $lote = $this->loteModel->findById($loteId);
            
            if (!$lote) {
                throw new \Exception("Lote no encontrado");
            }

            // VALIDACIÓN CRÍTICA: El lote debe estar vendido sin plan inicial activo
            if ($lote['estado'] !== 'vendido') {
                throw new \Exception("Solo se puede crear plan inicial para lotes vendidos");
            }

            if (!empty($lote['plan_inicial_id'])) {
                throw new \Exception("Este lote ya tiene un plan de pago inicial activo");
            }

            // Verificar que el lote no tenga amortización activa
            $amortizacionActiva = $this->db->fetchOne(
                "SELECT COUNT(*) as total FROM amortizaciones WHERE lote_id = ? LIMIT 1",
                [$loteId]
            );

            if ($amortizacionActiva['total'] > 0) {
                throw new \Exception("No se puede crear plan inicial. El lote ya tiene un plan de amortización principal activo");
            }

            // Obtener información del cliente y proyecto
            $cliente = $this->clienteModel->findById($lote['cliente_id']);
            $proyecto = $this->proyectoModel->findById($lote['proyecto_id']);

            $this->view('lotes/inicial/create', [
                'title' => 'Crear Plan de Pago Inicial Diferido',
                'lote' => $lote,
                'cliente' => $cliente,
                'proyecto' => $proyecto
            ]);

        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/lotes/show/' . $loteId);
        }
    }

    /**
     * Procesa la creación del Plan de Pago Inicial
     * POST /lotes/inicial/store/{lote_id}
     */
    public function store($loteId)
    {
        $this->requireAuth();
        $this->requireRole(['administrador', 'consulta']);

        try {
            $this->db->beginTransaction();

            // Validar datos requeridos
            $required = ['monto_inicial_total', 'monto_pagado_hoy', 'plazo_meses', 'fecha_inicio'];
            foreach ($required as $field) {
                if (!isset($_POST[$field]) || $_POST[$field] === '') {
                    throw new \Exception("El campo {$field} es obligatorio");
                }
            }

            $lote = $this->loteModel->findById($loteId);
            if (!$lote) {
                throw new \Exception("Lote no encontrado");
            }

            // Validaciones de negocio
            $montoInicial = (float)$_POST['monto_inicial_total'];
            $montoPagadoHoy = (float)$_POST['monto_pagado_hoy'];
            $plazoMeses = (int)$_POST['plazo_meses'];
            $fechaInicio = $_POST['fecha_inicio'];

            if ($montoInicial <= 0) {
                throw new \Exception("El monto inicial debe ser mayor a cero");
            }

            if ($montoPagadoHoy < 0) {
                throw new \Exception("El monto pagado hoy no puede ser negativo");
            }

            if ($montoPagadoHoy > $montoInicial) {
                throw new \Exception("El monto pagado hoy no puede ser mayor al monto inicial total");
            }

            if ($plazoMeses <= 0 || $plazoMeses > 120) {
                throw new \Exception("El plazo debe ser entre 1 y 120 meses");
            }

            // Calcular monto pendiente a diferir
            $montoPendiente = $montoInicial - $montoPagadoHoy;

            if ($montoPendiente < 0) {
                throw new \Exception("Error en cálculo: el monto pendiente no puede ser negativo");
            }

            // Calcular cuota mensual
            $cuotaMensual = $montoPendiente > 0 ? round($montoPendiente / $plazoMeses, 2) : 0;

            // Crear el plan de pago inicial
            $planId = $this->db->insert('pagos_iniciales', [
                'lote_id' => $loteId,
                'monto_inicial_total_requerido' => $montoInicial,
                'monto_pagado_hoy' => $montoPagadoHoy,
                'monto_pendiente_diferir' => $montoPendiente,
                'plazo_meses' => $plazoMeses,
                'cuota_mensual' => $cuotaMensual,
                'fecha_inicio' => $fechaInicio,
                'estado' => $montoPendiente > 0 ? 'en_curso' : 'pagado_total',
                'observaciones' => $_POST['observaciones'] ?? null
            ]);

            // Registrar el pago inicial si hubo abono hoy
            if ($montoPagadoHoy > 0) {
                $this->db->insert('pagos_iniciales_detalle', [
                    'plan_inicial_id' => $planId,
                    'fecha_pago' => $fechaInicio,
                    'valor_pagado' => $montoPagadoHoy,
                    'metodo_pago' => $_POST['metodo_pago'] ?? 'efectivo',
                    'numero_recibo' => $_POST['numero_recibo'] ?? null,
                    'saldo_pendiente_despues' => $montoPendiente,
                    'observaciones' => 'Pago inicial - Abono del primer día'
                ]);
            }

            // Actualizar el lote: cambiar estado y vincular plan
            if ($montoPendiente > 0) {
                // Si hay saldo pendiente, el lote pasa a 'reservado'
                $this->db->update('lotes', $loteId, [
                    'estado' => 'reservado',
                    'plan_inicial_id' => $planId
                ]);
            } else {
                // Si se pagó todo hoy, el lote se queda 'vendido' y sin plan activo
                $this->db->update('lotes', $loteId, [
                    'plan_inicial_id' => null
                ]);
            }

            $this->db->commit();

            // Log de auditoría
            \Logger::info("Plan de pago inicial creado", [
                'lote_id' => $loteId,
                'plan_id' => $planId,
                'monto_inicial' => $montoInicial,
                'monto_pagado_hoy' => $montoPagadoHoy,
                'plazo_meses' => $plazoMeses,
                'usuario' => user()['email']
            ]);

            $this->flash('success', 'Plan de pago inicial creado exitosamente. ' . 
                ($montoPendiente > 0 
                    ? "El lote está en estado RESERVADO hasta completar el pago inicial." 
                    : "El pago inicial fue completado. El lote está VENDIDO."));
            
            $this->redirect('/lotes/show/' . $loteId);

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Error al crear plan de pago inicial: ' . $e->getMessage());
            $this->redirect('/lotes/inicial/create/' . $loteId);
        }
    }

    /**
     * Muestra el formulario para registrar un pago contra el plan inicial
     * GET /lotes/inicial/pago/{lote_id}
     */
    public function pago($loteId)
    {
        $this->requireAuth();
        $this->requireRole(['administrador', 'consulta']);

        try {
            // Obtener información del lote
            $lote = $this->loteModel->findById($loteId);
            
            if (!$lote) {
                throw new \Exception("Lote no encontrado");
            }

            // VALIDACIÓN: El lote debe tener un plan inicial activo
            if (empty($lote['plan_inicial_id'])) {
                throw new \Exception("Este lote no tiene un plan de pago inicial activo");
            }

            // Obtener el plan inicial activo
            $planInicial = $this->db->fetchOne(
                "SELECT * FROM pagos_iniciales WHERE id = ? AND lote_id = ?",
                [$lote['plan_inicial_id'], $loteId]
            );

            if (!$planInicial) {
                throw new \Exception("Plan de pago inicial no encontrado");
            }

            if ($planInicial['estado'] === 'pagado_total') {
                throw new \Exception("El plan de pago inicial ya está completado");
            }

            if ($planInicial['estado'] === 'cancelado') {
                throw new \Exception("El plan de pago inicial está cancelado");
            }

            // Obtener historial de pagos
            $pagos = $this->db->fetchAll(
                "SELECT * FROM pagos_iniciales_detalle 
                 WHERE plan_inicial_id = ? 
                 ORDER BY fecha_pago DESC, created_at DESC",
                [$planInicial['id']]
            );

            // Calcular totales
            $totalPagado = 0;
            foreach ($pagos as $pago) {
                $totalPagado += $pago['valor_pagado'];
            }

            $saldoPendiente = $planInicial['monto_inicial_total_requerido'] - $totalPagado;

            // Información del cliente y proyecto
            $cliente = $this->clienteModel->findById($lote['cliente_id']);
            $proyecto = $this->proyectoModel->findById($lote['proyecto_id']);

            $this->view('lotes/inicial/pago', [
                'title' => 'Registrar Pago Inicial',
                'lote' => $lote,
                'planInicial' => $planInicial,
                'pagos' => $pagos,
                'totalPagado' => $totalPagado,
                'saldoPendiente' => $saldoPendiente,
                'cliente' => $cliente,
                'proyecto' => $proyecto
            ]);

        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/lotes/show/' . $loteId);
        }
    }

    /**
     * Procesa el registro de un pago contra el plan inicial
     * POST /lotes/inicial/registrar-pago/{lote_id}
     */
    public function registrarPago($loteId)
    {
        $this->requireAuth();
        $this->requireRole(['administrador', 'consulta']);

        try {
            $this->db->beginTransaction();

            // Validar datos
            if (empty($_POST['valor_pagado']) || empty($_POST['fecha_pago'])) {
                throw new \Exception("Valor y fecha de pago son obligatorios");
            }

            $valorPagado = (float)$_POST['valor_pagado'];
            $fechaPago = $_POST['fecha_pago'];

            if ($valorPagado <= 0) {
                throw new \Exception("El valor del pago debe ser mayor a cero");
            }

            // Obtener lote y plan inicial
            $lote = $this->loteModel->findById($loteId);
            if (!$lote || empty($lote['plan_inicial_id'])) {
                throw new \Exception("Lote sin plan de pago inicial activo");
            }

            $planInicial = $this->db->fetchOne(
                "SELECT * FROM pagos_iniciales WHERE id = ?",
                [$lote['plan_inicial_id']]
            );

            if (!$planInicial) {
                throw new \Exception("Plan inicial no encontrado");
            }

            // Calcular saldo actual
            $totalPagadoAntes = $this->db->fetchOne(
                "SELECT COALESCE(SUM(valor_pagado), 0) as total 
                 FROM pagos_iniciales_detalle 
                 WHERE plan_inicial_id = ?",
                [$planInicial['id']]
            )['total'];

            $saldoAntes = $planInicial['monto_inicial_total_requerido'] - $totalPagadoAntes;

            // Validar que no se exceda el saldo pendiente
            if ($valorPagado > $saldoAntes) {
                throw new \Exception("El valor del pago ($" . number_format($valorPagado, 0) . 
                    ") excede el saldo pendiente ($" . number_format($saldoAntes, 0) . ")");
            }

            $saldoDespues = $saldoAntes - $valorPagado;

            // Registrar el pago
            $pagoId = $this->db->insert('pagos_iniciales_detalle', [
                'plan_inicial_id' => $planInicial['id'],
                'fecha_pago' => $fechaPago,
                'valor_pagado' => $valorPagado,
                'metodo_pago' => $_POST['metodo_pago'] ?? 'efectivo',
                'numero_recibo' => $_POST['numero_recibo'] ?? null,
                'saldo_pendiente_despues' => $saldoDespues,
                'observaciones' => $_POST['observaciones'] ?? null
            ]);

            // LÓGICA CRÍTICA: Si el saldo llega a cero, completar el plan
            if ($saldoDespues <= 0.01) { // Tolerancia de 1 centavo
                // Actualizar estado del plan a 'pagado_total'
                $this->db->update('pagos_iniciales', $planInicial['id'], [
                    'estado' => 'pagado_total'
                ]);

                // El TRIGGER 'after_plan_inicial_completado' se encargará de:
                // 1. Cambiar el lote de 'reservado' a 'vendido'
                // 2. Limpiar el campo plan_inicial_id del lote
                
                $mensaje = "Pago registrado exitosamente. ¡PLAN INICIAL COMPLETADO! El lote ha cambiado a estado VENDIDO. Ahora puede crear el plan de amortización principal.";
            } else {
                $mensaje = "Pago registrado exitosamente. Saldo pendiente: $" . number_format($saldoDespues, 0);
            }

            $this->db->commit();

            // Log de auditoría
            \Logger::info("Pago inicial registrado", [
                'lote_id' => $loteId,
                'plan_id' => $planInicial['id'],
                'pago_id' => $pagoId,
                'valor_pagado' => $valorPagado,
                'saldo_despues' => $saldoDespues,
                'plan_completado' => $saldoDespues <= 0.01,
                'usuario' => user()['email']
            ]);

            $this->flash('success', $mensaje);
            $this->redirect('/lotes/show/' . $loteId);

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Error al registrar pago: ' . $e->getMessage());
            $this->redirect('/lotes/inicial/pago/' . $loteId);
        }
    }

    /**
     * Muestra el detalle y resumen del plan de pago inicial
     * GET /lotes/inicial/show/{lote_id}
     */
    public function show($loteId)
    {
        $this->requireAuth();

        try {
            $lote = $this->loteModel->findById($loteId);
            
            if (!$lote) {
                throw new \Exception("Lote no encontrado");
            }

            if (empty($lote['plan_inicial_id'])) {
                throw new \Exception("Este lote no tiene un plan de pago inicial");
            }

            // Obtener plan inicial con resumen
            $resumen = $this->db->fetchOne(
                "SELECT * FROM vista_planes_iniciales_resumen WHERE plan_id = ?",
                [$lote['plan_inicial_id']]
            );

            if (!$resumen) {
                throw new \Exception("No se pudo cargar el resumen del plan inicial");
            }

            // Obtener pagos detallados
            $pagos = $this->db->fetchAll(
                "SELECT * FROM pagos_iniciales_detalle 
                 WHERE plan_inicial_id = ? 
                 ORDER BY fecha_pago ASC, created_at ASC",
                [$lote['plan_inicial_id']]
            );

            $this->view('lotes/inicial/show', [
                'title' => 'Detalle Plan de Pago Inicial',
                'lote' => $lote,
                'resumen' => $resumen,
                'pagos' => $pagos
            ]);

        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/lotes/show/' . $loteId);
        }
    }
}
