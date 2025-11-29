<?php

namespace App\Controllers;

use App\Models\PagoModel;
use App\Models\AmortizacionModel;
use App\Models\LoteModel;

/**
 * PagoController
 * Gestiona el registro de pagos con distribución inteligente y manejo de excedentes
 */
class PagoController extends Controller
{
    private $pagoModel;
    private $amortizacionModel;
    private $loteModel;

    public function __construct()
    {
        $this->pagoModel = new PagoModel();
        $this->amortizacionModel = new AmortizacionModel();
        $this->loteModel = new LoteModel();
    }

    /**
     * Muestra el formulario de registro de pago
     * GET /lotes/pago/create/{lote_id}
     */
    public function create($loteId)
    {
        if (!can('registrar_pagos')) {
            $_SESSION['error'] = 'No tienes permisos para registrar pagos';
            redirect('/lotes');
            return;
        }

        // Obtener información del lote
        $lote = $this->loteModel->findById($loteId);

        if (!$lote) {
            $_SESSION['error'] = 'Lote no encontrado';
            redirect('/lotes');
            return;
        }

        // Validar que el lote tenga plan de amortización
        if (!$this->amortizacionModel->hasActiveAmortization($loteId)) {
            $_SESSION['error'] = 'Este lote no tiene un plan de amortización activo';
            redirect('/lotes/show/' . $loteId);
            return;
        }

        // Obtener cuotas pendientes (ordenadas por fecha)
        $cuotas_pendientes = $this->amortizacionModel->getPendientesByLote($loteId);

        if (empty($cuotas_pendientes)) {
            $_SESSION['success'] = 'Todas las cuotas de este lote están pagadas';
            redirect('/lotes/amortizacion/show/' . $loteId);
            return;
        }

        // Obtener resumen del lote
        $resumen = $this->amortizacionModel->getResumenByLote($loteId);

        // Calcular saldo total pendiente
        $saldo_total_pendiente = $resumen['saldo_total'] ?? 0;

        // Obtener historial de pagos
        $historial_pagos = $this->pagoModel->getByLote($loteId);

        $data = [
            'pageTitle' => 'Registrar Pago',
            'lote' => $lote,
            'cuotas_pendientes' => $cuotas_pendientes,
            'resumen' => $resumen,
            'saldo_total_pendiente' => $saldo_total_pendiente,
            'historial_pagos' => $historial_pagos,
            'monto_financiado' => $lote['monto_financiado'] ?? 0,
            'fecha_hoy' => date('Y-m-d')
        ];

        $this->view('lotes/registrar_pago', $data);
    }

    /**
     * Procesa el registro de pago con distribución inteligente
     * POST /lotes/pago/store
     */
    public function store()
    {
        if (!can('registrar_pagos')) {
            $_SESSION['error'] = 'No tienes permisos para registrar pagos';
            redirect('/lotes');
            return;
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('/lotes');
            return;
        }

        // Validar datos requeridos
        $required = ['lote_id', 'monto_pago', 'fecha_pago', 'metodo_pago'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "El campo {$field} es requerido";
                redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
                return;
            }
        }

        $lote_id = (int)$_POST['lote_id'];
        $monto_pago = (float)$_POST['monto_pago'];
        $fecha_pago = $_POST['fecha_pago'];
        $metodo_pago = $_POST['metodo_pago'];
        $referencia = $_POST['referencia'] ?? null;
        $observaciones = $_POST['observaciones'] ?? null;
        
        // Opciones de distribución
        $opcion_excedente = $_POST['opcion_excedente'] ?? 'aplicar_capital'; // 'aplicar_capital' o 'pagar_siguientes'
        $cuotas_seleccionadas = $_POST['cuotas_seleccionadas'] ?? []; // Array de IDs de cuotas

        // Validar lote
        $lote = $this->loteModel->findById($lote_id);
        
        if (!$lote) {
            $_SESSION['error'] = 'Lote no encontrado';
            redirect('/lotes');
            return;
        }

        // Validar monto
        if ($monto_pago <= 0) {
            $_SESSION['error'] = 'El monto del pago debe ser mayor a cero';
            redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
            return;
        }

        // Obtener resumen para validar saldo
        $resumen = $this->amortizacionModel->getResumenByLote($lote_id);
        $saldo_total = $resumen['saldo_total'] ?? 0;

        if ($monto_pago > $saldo_total) {
            $_SESSION['error'] = 'El monto del pago no puede ser mayor al saldo pendiente: ' . formatMoney($saldo_total);
            redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
            return;
        }

        try {
            $db = \Database::getInstance();
            $db->beginTransaction();

            // Determinar cuotas a pagar
            if (!empty($cuotas_seleccionadas)) {
                // Usuario seleccionó cuotas específicas
                $cuotas_a_pagar = [];
                foreach ($cuotas_seleccionadas as $cuota_id) {
                    $cuota = $this->amortizacionModel->findById($cuota_id);
                    if ($cuota && $cuota['lote_id'] == $lote_id && $cuota['estado'] === 'pendiente') {
                        $cuotas_a_pagar[] = $cuota;
                    }
                }
            } else {
                // Por defecto: pagar cuotas en orden cronológico (más antiguas primero)
                $cuotas_a_pagar = $this->amortizacionModel->getPendientesByLote($lote_id);
            }

            if (empty($cuotas_a_pagar)) {
                throw new \Exception('No hay cuotas disponibles para aplicar el pago');
            }

            // DISTRIBUIR EL PAGO
            $resultado_distribucion = $this->distribuirPago($monto_pago, $cuotas_a_pagar, $opcion_excedente);

            // Registrar pagos en base de datos
            $pagos_registrados = 0;
            $cuotas_actualizadas = 0;

            foreach ($resultado_distribucion['pagos'] as $pago_info) {
                $amortizacion_id = $pago_info['cuota_id'];
                $valor_aplicado = $pago_info['valor_aplicado'];

                // Insertar registro en tabla pagos
                $sql_pago = "INSERT INTO pagos 
                            (amortizacion_id, valor_pagado, metodo_pago, fecha_pago, numero_recibo, observaciones, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())";
                
                $numero_recibo = $referencia ?? 'PAG-' . date('YmdHis') . '-' . $amortizacion_id;
                
                $params_pago = [
                    $amortizacion_id,
                    $valor_aplicado,
                    $metodo_pago,
                    $fecha_pago,
                    $numero_recibo,
                    $observaciones
                ];

                $db->execute($sql_pago, $params_pago);
                $pagos_registrados++;

                // Actualizar cuota en tabla amortizaciones
                $cuota_actual = $this->amortizacionModel->findById($amortizacion_id);
                $nuevo_valor_pagado = $cuota_actual['valor_pagado'] + $valor_aplicado;
                $nuevo_saldo = $cuota_actual['valor_cuota'] - $nuevo_valor_pagado;
                $nuevo_estado = $nuevo_saldo <= 0.01 ? 'pagada' : 'pendiente'; // Tolerancia de 1 centavo

                $sql_update = "UPDATE amortizaciones 
                              SET valor_pagado = ?, 
                                  saldo_pendiente = ?,
                                  estado = ?,
                                  fecha_pago = CASE WHEN ? = 'pagada' THEN ? ELSE fecha_pago END,
                                  updated_at = NOW()
                              WHERE id = ?";
                
                $params_update = [
                    $nuevo_valor_pagado,
                    max(0, $nuevo_saldo),
                    $nuevo_estado,
                    $nuevo_estado,
                    $fecha_pago,
                    $amortizacion_id
                ];

                $db->execute($sql_update, $params_update);
                $cuotas_actualizadas++;
            }

            // Si hay excedente y se eligió aplicar a capital, recalcular tabla
            if ($resultado_distribucion['excedente'] > 0 && $opcion_excedente === 'aplicar_capital') {
                $this->aplicarAbonoCapital($lote_id, $resultado_distribucion['excedente'], $db);
            }

            $db->commit();

            // Mensaje de éxito
            $mensaje = "Pago registrado exitosamente. ";
            $mensaje .= "Monto: " . formatMoney($monto_pago) . ". ";
            $mensaje .= "Cuotas actualizadas: {$cuotas_actualizadas}. ";
            
            if ($resultado_distribucion['excedente'] > 0) {
                if ($opcion_excedente === 'aplicar_capital') {
                    $mensaje .= "Excedente de " . formatMoney($resultado_distribucion['excedente']) . " aplicado como abono a capital. Plan recalculado.";
                } else {
                    $mensaje .= "Excedente de " . formatMoney($resultado_distribucion['excedente']) . " aplicado a cuotas siguientes.";
                }
            }

            $_SESSION['success'] = $mensaje;
            redirect('/lotes/amortizacion/show/' . $lote_id);

        } catch (\Exception $e) {
            if (isset($db)) {
                $db->rollback();
            }
            
            $_SESSION['error'] = 'Error al registrar el pago: ' . $e->getMessage();
            redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
        }
    }

    /**
     * Distribuye el monto del pago entre las cuotas seleccionadas
     * 
     * @param float $monto_pago Monto total a distribuir
     * @param array $cuotas Array de cuotas pendientes
     * @param string $opcion_excedente 'aplicar_capital' o 'pagar_siguientes'
     * @return array Resultado con pagos aplicados y excedente
     */
    private function distribuirPago($monto_pago, $cuotas, $opcion_excedente)
    {
        $monto_disponible = $monto_pago;
        $pagos = [];
        $excedente = 0;

        foreach ($cuotas as $cuota) {
            if ($monto_disponible <= 0) {
                break;
            }

            $saldo_cuota = $cuota['saldo_pendiente'];
            
            if ($monto_disponible >= $saldo_cuota) {
                // Pago completo de la cuota
                $valor_a_aplicar = $saldo_cuota;
                $monto_disponible -= $saldo_cuota;
            } else {
                // Pago parcial de la cuota
                $valor_a_aplicar = $monto_disponible;
                $monto_disponible = 0;
            }

            $pagos[] = [
                'cuota_id' => $cuota['id'],
                'numero_cuota' => $cuota['numero_cuota'],
                'valor_cuota' => $cuota['valor_cuota'],
                'saldo_anterior' => $cuota['saldo_pendiente'],
                'valor_aplicado' => $valor_a_aplicar,
                'nuevo_saldo' => $cuota['saldo_pendiente'] - $valor_a_aplicar
            ];
        }

        // Si queda monto disponible, es excedente
        if ($monto_disponible > 0) {
            if ($opcion_excedente === 'pagar_siguientes') {
                // Continuar pagando cuotas siguientes (ya se hizo en el loop)
                // El excedente restante se registra
                $excedente = $monto_disponible;
            } else {
                // Aplicar como abono a capital (se recalculará después)
                $excedente = $monto_disponible;
            }
        }

        return [
            'pagos' => $pagos,
            'excedente' => $excedente,
            'total_aplicado' => $monto_pago - $excedente
        ];
    }

    /**
     * Aplica un abono a capital y recalcula el plan de amortización
     * 
     * @param int $lote_id ID del lote
     * @param float $monto_abono Monto del abono a capital
     * @param object $db Instancia de base de datos (para transacción)
     */
    private function aplicarAbonoCapital($lote_id, $monto_abono, $db)
    {
        // Obtener cuotas pendientes
        $cuotas_pendientes = $this->amortizacionModel->getPendientesByLote($lote_id);
        
        if (empty($cuotas_pendientes)) {
            return;
        }

        // Calcular saldo total actual
        $saldo_total_actual = array_sum(array_column($cuotas_pendientes, 'saldo_pendiente'));
        
        // Nuevo saldo después del abono
        $nuevo_saldo = $saldo_total_actual - $monto_abono;
        
        if ($nuevo_saldo <= 0) {
            // El abono cubre todo el saldo restante
            foreach ($cuotas_pendientes as $cuota) {
                $sql = "UPDATE amortizaciones 
                        SET valor_pagado = valor_cuota, 
                            saldo_pendiente = 0, 
                            estado = 'pagada',
                            updated_at = NOW()
                        WHERE id = ?";
                $db->execute($sql, [$cuota['id']]);
            }
            return;
        }

        // Recalcular cuotas con método francés
        $lote = $this->loteModel->findById($lote_id);
        $tasa_anual = $lote['tasa_interes'] ?? 0;
        $numero_cuotas_restantes = count($cuotas_pendientes);
        $fecha_inicio = $cuotas_pendientes[0]['fecha_vencimiento'];

        // Calcular nuevo plan
        $tasa_mensual = ($tasa_anual / 100) / 12;

        if ($tasa_mensual > 0) {
            $factor = pow(1 + $tasa_mensual, $numero_cuotas_restantes);
            $nueva_cuota_fija = $nuevo_saldo * ($tasa_mensual * $factor) / ($factor - 1);
        } else {
            $nueva_cuota_fija = $nuevo_saldo / $numero_cuotas_restantes;
        }

        // Generar nueva tabla
        $saldo = $nuevo_saldo;
        
        for ($i = 0; $i < $numero_cuotas_restantes; $i++) {
            $cuota_id = $cuotas_pendientes[$i]['id'];
            
            $interes = $saldo * $tasa_mensual;
            $capital = $nueva_cuota_fija - $interes;
            $saldo = $saldo - $capital;

            // Ajuste para última cuota
            if ($i == $numero_cuotas_restantes - 1 && $saldo != 0) {
                $capital += $saldo;
                $nueva_cuota_fija = $capital + $interes;
                $saldo = 0;
            }

            $sql = "UPDATE amortizaciones 
                    SET valor_cuota = ?, 
                        capital = ?, 
                        interes = ?, 
                        saldo = ?,
                        saldo_pendiente = valor_cuota - valor_pagado,
                        updated_at = NOW()
                    WHERE id = ?";
            
            $params = [
                round($nueva_cuota_fija, 2),
                round($capital, 2),
                round($interes, 2),
                round(max(0, $saldo), 2),
                $cuota_id
            ];

            $db->execute($sql, $params);
        }
    }

    /**
     * API para calcular distribución de pago (AJAX)
     * POST /lotes/pago/calcular-distribucion
     */
    public function calcularDistribucion()
    {
        header('Content-Type: application/json');

        if (!can('registrar_pagos')) {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $lote_id = $_POST['lote_id'] ?? null;
        $monto = $_POST['monto'] ?? 0;
        $cuotas_ids = $_POST['cuotas_ids'] ?? [];

        if (!$lote_id || $monto <= 0) {
            echo json_encode(['error' => 'Datos inválidos']);
            return;
        }

        try {
            // Obtener cuotas
            if (!empty($cuotas_ids)) {
                $cuotas = [];
                foreach ($cuotas_ids as $id) {
                    $cuota = $this->amortizacionModel->findById($id);
                    if ($cuota && $cuota['lote_id'] == $lote_id) {
                        $cuotas[] = $cuota;
                    }
                }
            } else {
                $cuotas = $this->amortizacionModel->getPendientesByLote($lote_id);
            }

            // Simular distribución
            $resultado = $this->distribuirPago($monto, $cuotas, 'pagar_siguientes');

            echo json_encode([
                'success' => true,
                'distribucion' => $resultado
            ]);

        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
