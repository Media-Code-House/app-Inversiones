<?php

namespace App\Controllers;

use App\Models\AmortizacionModel;
use App\Models\LoteModel;
use App\Models\PagoModel;

/**
 * AmortizacionController
 * Gestiona el plan de amortización con método francés (cuota fija)
 */
class AmortizacionController extends Controller
{
    private $amortizacionModel;
    private $loteModel;
    private $pagoModel;

    public function __construct()
    {
        $this->amortizacionModel = new AmortizacionModel();
        $this->loteModel = new LoteModel();
        $this->pagoModel = new PagoModel();
    }

    /**
     * Muestra el formulario para crear plan de amortización
     * GET /lotes/amortizacion/create/{lote_id}
     */
    public function create($loteId)
    {
        try {
            if (!can('crear_amortizacion')) {
                $_SESSION['error'] = 'No tienes permisos para crear planes de amortización';
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

        // Validar que el lote esté vendido
        if ($lote['estado'] !== 'vendido') {
            $_SESSION['error'] = 'Solo se puede crear amortización para lotes vendidos';
            redirect('/lotes/show/' . $loteId);
            return;
        }

        // Validar que no tenga plan activo
        if ($this->amortizacionModel->hasActiveAmortization($loteId)) {
            $_SESSION['error'] = 'Este lote ya tiene un plan de amortización activo';
            redirect('/lotes/amortizacion/show/' . $loteId);
            return;
        }

        // Calcular valores sugeridos
        $precio_venta = $lote['precio_venta'] ?? $lote['precio_lista'];
        $cuota_inicial_sugerida = $precio_venta * 0.30; // 30% sugerido
        $monto_financiado_sugerido = $precio_venta - $cuota_inicial_sugerida;

        $data = [
            'pageTitle' => 'Crear Plan de Amortización',
            'lote' => $lote,
            'precio_venta' => $precio_venta,
            'cuota_inicial_sugerida' => $cuota_inicial_sugerida,
            'monto_financiado_sugerido' => $monto_financiado_sugerido
        ];

            $this->view('lotes/crear_amortizacion', $data);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar formulario: ' . $e->getMessage();
            redirect('/lotes/show/' . $loteId);
        }
    }

    /**
     * Procesa y guarda el plan de amortización con método francés
     * POST /lotes/amortizacion/store
     */
    public function store()
    {
        // Limpiar cualquier output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Log de inicio
        \Logger::info("=== INICIO store() de AmortizacionController ===");
        \Logger::debug("POST data", $_POST);
        
        if (!can('crear_amortizacion')) {
            \Logger::error("Usuario no tiene permisos para crear_amortizacion");
            $_SESSION['error'] = 'No tienes permisos para crear planes de amortización';
            redirect('/lotes');
            return;
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            \Logger::error("Token CSRF inválido");
            $_SESSION['error'] = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
            redirect('/lotes/amortizacion/create/' . ($_POST['lote_id'] ?? ''));
            return;
        }

        // Validar datos requeridos
        $required = ['lote_id', 'cuota_inicial', 'monto_financiado', 'tasa_interes', 'numero_cuotas', 'fecha_inicio'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                \Logger::error("Campo requerido faltante: {$field}");
                $_SESSION['error'] = "El campo {$field} es requerido";
                redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
                return;
            }
        }
        
        \Logger::info("Validaciones pasadas exitosamente");

        $lote_id = (int)$_POST['lote_id'];
        $cuota_inicial = (float)$_POST['cuota_inicial'];
        $monto_financiado = (float)$_POST['monto_financiado'];
        $tasa_interes_anual = (float)$_POST['tasa_interes'];
        $numero_cuotas = (int)$_POST['numero_cuotas'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $observaciones = $_POST['observaciones'] ?? null;

        // Validar lote
        \Logger::info("Buscando lote con ID: {$lote_id}");
        $lote = $this->loteModel->findById($lote_id);
        
        if (!$lote) {
            \Logger::error("Lote no encontrado");
            $_SESSION['error'] = 'Lote no encontrado';
            redirect('/lotes');
            return;
        }
        
        if ($lote['estado'] !== 'vendido') {
            \Logger::error("Lote no está en estado vendido", ['estado_actual' => $lote['estado']]);
            $_SESSION['error'] = 'El lote no está en estado vendido';
            redirect('/lotes/show/' . $lote_id);
            return;
        }
        
        \Logger::info("Lote validado correctamente");

        // Validar que no exista plan activo
        \Logger::info("Verificando si existe plan activo...");
        if ($this->amortizacionModel->hasActiveAmortization($lote_id)) {
            \Logger::error("Ya existe un plan de amortización activo");
            $_SESSION['error'] = 'Este lote ya tiene un plan de amortización activo';
            redirect('/lotes/show/' . $lote_id);
            return;
        }
        
        \Logger::info("No existe plan activo, continuando...");

        // Validaciones de negocio
        if ($monto_financiado <= 0) {
            $_SESSION['error'] = 'El monto financiado debe ser mayor a cero';
            redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
            return;
        }

        if ($numero_cuotas < 1 || $numero_cuotas > 360) {
            $_SESSION['error'] = 'El número de cuotas debe estar entre 1 y 360';
            redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
            return;
        }

        if ($tasa_interes_anual < 0 || $tasa_interes_anual > 100) {
            $_SESSION['error'] = 'La tasa de interés debe estar entre 0% y 100%';
            redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
            return;
        }

        try {
            // MÉTODO FRANCÉS: Calcular tabla de amortización con cuota fija
            $plan = $this->calcularPlanAmortizacionFrances(
                $monto_financiado,
                $tasa_interes_anual,
                $numero_cuotas,
                $fecha_inicio
            );

            // Guardar cuota inicial en lotes (actualizar campos)
            $updateLote = $this->loteModel->updateAmortizacionFields($lote_id, [
                'cuota_inicial' => $cuota_inicial,
                'monto_financiado' => $monto_financiado,
                'tasa_interes' => $tasa_interes_anual,
                'numero_cuotas' => $numero_cuotas,
                'fecha_inicio_amortizacion' => $fecha_inicio
            ]);

            // Insertar cuotas en la tabla amortizaciones
            $db = \Database::getInstance();
            $db->beginTransaction();

            foreach ($plan as $cuota) {
                $sql = "INSERT INTO amortizaciones 
                        (lote_id, numero_cuota, fecha_vencimiento, valor_cuota, capital, interes, saldo, estado, observaciones) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)";
                
                $params = [
                    $lote_id,
                    $cuota['numero'],
                    $cuota['fecha_vencimiento'],
                    $cuota['cuota_fija'],
                    $cuota['capital'],
                    $cuota['interes'],
                    $cuota['saldo'],
                    $observaciones
                ];

                $db->execute($sql, $params);
            }

            $db->commit();
            
            \Logger::info("=== PLAN CREADO EXITOSAMENTE ===");
            \Logger::info("Redirigiendo a: /lotes/amortizacion/show/{$lote_id}");

            $_SESSION['success'] = "Plan de amortización creado exitosamente con {$numero_cuotas} cuotas";
            
            // Limpiar buffers nuevamente antes de redirigir
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            redirect('/lotes/amortizacion/show/' . $lote_id);
            exit();

        } catch (\Exception $e) {
            \Logger::error("=== ERROR EN CREACIÓN DE PLAN ===", [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (isset($db)) {
                $db->rollback();
            }
            
            $_SESSION['error'] = 'Error al crear el plan: ' . $e->getMessage();
            redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
        }
    }

    /**
     * Muestra la tabla de amortización completa del lote
     * GET /lotes/amortizacion/show/{lote_id}
     */
    public function show($loteId)
    {
        try {
            if (!can('ver_amortizacion')) {
                $_SESSION['error'] = 'No tienes permisos para ver amortizaciones';
                redirect('/lotes');
                return;
            }

            // Obtener lote con información relacionada
            $lote = $this->loteModel->findById($loteId);

            if (!$lote) {
                $_SESSION['error'] = 'Lote no encontrado';
                redirect('/lotes');
                return;
            }

            // Obtener todas las cuotas
            $cuotas = $this->amortizacionModel->getByLote($loteId);

            if (empty($cuotas)) {
                $_SESSION['info'] = 'Este lote no tiene plan de amortización. ¿Desea crear uno?';
                redirect('/lotes/amortizacion/create/' . $loteId);
                return;
            }

        // Obtener resumen del plan
        $resumen = $this->amortizacionModel->getResumenByLote($loteId);

        // Calcular métricas financieras
        $precio_venta = $lote['precio_venta'] ?? $lote['precio_lista'];
        $cuota_inicial = $lote['cuota_inicial'] ?? 0;
        $monto_financiado = $lote['monto_financiado'] ?? ($precio_venta - $cuota_inicial);
        $tasa_interes = $lote['tasa_interes'] ?? 0;
        $numero_cuotas = $resumen['total_cuotas'] ?? 0;

        // 6 Métricas clave (superior derecha según imagen)
        $metricas = [
            'valor_lote' => $precio_venta,
            'cuota_inicial' => $cuota_inicial,
            'monto_financiado' => $monto_financiado,
            'tasa_interes' => $tasa_interes,
            'numero_cuotas' => $numero_cuotas,
            'valor_cuota' => $numero_cuotas > 0 ? ($cuotas[0]['valor_cuota'] ?? 0) : 0
        ];

        // 4 KPIs visuales inferiores (según imagen)
        $kpis = [
            'total_pagado' => $resumen['total_pagado'] ?? 0,
            'saldo_pendiente' => $resumen['saldo_total'] ?? 0,
            'progreso' => $resumen['total_cuotas'] > 0 
                ? round(($resumen['cuotas_pagadas'] / $resumen['total_cuotas']) * 100, 1) 
                : 0,
            'cuotas_info' => [
                'pagadas' => $resumen['cuotas_pagadas'] ?? 0,
                'pendientes' => $resumen['cuotas_pendientes'] ?? 0,
                'vencidas' => $resumen['cuotas_vencidas'] ?? 0,
                'total' => $resumen['total_cuotas'] ?? 0
            ]
        ];

        // Clasificar cuotas por estado visual (colores)
        $hoy = date('Y-m-d');
        foreach ($cuotas as &$cuota) {
            // Determinar clase CSS según estado
            if ($cuota['estado'] === 'pagada') {
                $cuota['clase_fila'] = 'table-success'; // Verde
                $cuota['etiqueta_estado'] = 'Pagada';
                $cuota['clase_badge'] = 'bg-success';
            } elseif ($cuota['fecha_vencimiento'] < $hoy) {
                $cuota['clase_fila'] = 'table-danger'; // Rojo para mora
                $cuota['etiqueta_estado'] = 'En Mora';
                $cuota['clase_badge'] = 'bg-danger';
                
                // Calcular días de mora
                $fecha_venc = new \DateTime($cuota['fecha_vencimiento']);
                $fecha_hoy = new \DateTime($hoy);
                $dias_mora = $fecha_hoy->diff($fecha_venc)->days;
                $cuota['dias_mora'] = $dias_mora;
            } elseif ($cuota['fecha_vencimiento'] <= date('Y-m-d', strtotime('+7 days'))) {
                $cuota['clase_fila'] = 'table-warning'; // Amarillo para próxima
                $cuota['etiqueta_estado'] = 'Próxima';
                $cuota['clase_badge'] = 'bg-warning';
            } else {
                $cuota['clase_fila'] = ''; // Normal para futuras
                $cuota['etiqueta_estado'] = 'Pendiente';
                $cuota['clase_badge'] = 'bg-secondary';
            }
        }

        $data = [
            'pageTitle' => 'Amortización del Lote',
            'lote' => $lote,
            'cuotas' => $cuotas,
            'resumen' => $resumen,
            'metricas' => $metricas,
            'kpis' => $kpis,
            'saldo_a_favor' => $this->loteModel->getSaldoAFavor($loteId)
        ];

            $this->view('lotes/amortizacion', $data);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar amortización: ' . $e->getMessage();
            redirect('/lotes/show/' . $loteId);
        }
    }

    /**
     * Calcula el plan de amortización con método francés (cuota fija)
     * 
     * @param float $monto_financiado Capital inicial
     * @param float $tasa_anual Tasa de interés anual (%)
     * @param int $numero_cuotas Plazo en meses
     * @param string $fecha_inicio Fecha de inicio del plan
     * @return array Plan de amortización completo
     */
    private function calcularPlanAmortizacionFrances($monto_financiado, $tasa_anual, $numero_cuotas, $fecha_inicio)
    {
        // Convertir tasa anual a mensual
        $tasa_mensual = ($tasa_anual / 100) / 12;

        // FÓRMULA DEL MÉTODO FRANCÉS (Cuota Fija):
        // C = P * [i * (1 + i)^n] / [(1 + i)^n - 1]
        // Donde:
        // C = Cuota fija
        // P = Principal (monto financiado)
        // i = Tasa de interés mensual (decimal)
        // n = Número de cuotas

        if ($tasa_mensual > 0) {
            $factor = pow(1 + $tasa_mensual, $numero_cuotas);
            $cuota_fija = $monto_financiado * ($tasa_mensual * $factor) / ($factor - 1);
        } else {
            // Si la tasa es 0%, cuota fija = monto / cuotas (sin interés)
            $cuota_fija = $monto_financiado / $numero_cuotas;
        }

        // Generar tabla de amortización
        $plan = [];
        $saldo = $monto_financiado;
        $fecha = new \DateTime($fecha_inicio);

        for ($i = 1; $i <= $numero_cuotas; $i++) {
            // Avanzar un mes desde la fecha de inicio
            $fecha_actual = clone $fecha;
            $fecha_actual->modify("+{$i} months");

            // Calcular interés de la cuota
            $interes = $saldo * $tasa_mensual;

            // Calcular capital (amortización)
            $capital = $cuota_fija - $interes;

            // Actualizar saldo
            $saldo_anterior = $saldo;
            $saldo = $saldo - $capital;

            // Ajuste para última cuota (evitar decimales residuales)
            if ($i == $numero_cuotas && $saldo != 0) {
                $capital += $saldo;
                $cuota_fija = $capital + $interes;
                $saldo = 0;
            }

            $plan[] = [
                'numero' => $i,
                'fecha_vencimiento' => $fecha_actual->format('Y-m-d'),
                'cuota_fija' => round($cuota_fija, 2),
                'capital' => round($capital, 2),
                'interes' => round($interes, 2),
                'saldo' => round(max(0, $saldo), 2) // Evitar saldos negativos
            ];
        }

        return $plan;
    }

    /**
     * Recalcula el plan de amortización (usado cuando hay abono a capital)
     * POST /lotes/amortizacion/recalcular/{lote_id}
     */
    public function recalcular($loteId)
    {
        if (!can('editar_amortizacion')) {
            $_SESSION['error'] = 'No tienes permisos para recalcular amortización';
            redirect('/lotes/amortizacion/show/' . $loteId);
            return;
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('/lotes/amortizacion/show/' . $loteId);
            return;
        }

        try {
            $lote = $this->loteModel->findById($loteId);
            
            if (!$lote) {
                throw new \Exception('Lote no encontrado');
            }

            // Obtener cuotas pendientes
            $cuotas_pendientes = $this->amortizacionModel->getPendientesByLote($loteId);
            
            if (empty($cuotas_pendientes)) {
                throw new \Exception('No hay cuotas pendientes para recalcular');
            }

            // Calcular saldo actual pendiente
            $saldo_actual = array_sum(array_column($cuotas_pendientes, 'saldo_pendiente'));
            $numero_cuotas_restantes = count($cuotas_pendientes);
            $tasa_anual = $lote['tasa_interes'] ?? 0;
            $primera_cuota_pendiente = $cuotas_pendientes[0];
            $fecha_inicio = $primera_cuota_pendiente['fecha_vencimiento'];

            // Recalcular plan con saldo actual
            $nuevo_plan = $this->calcularPlanAmortizacionFrances(
                $saldo_actual,
                $tasa_anual,
                $numero_cuotas_restantes,
                $fecha_inicio
            );

            // Actualizar cuotas en base de datos
            $db = \Database::getInstance();
            $db->beginTransaction();

            foreach ($nuevo_plan as $index => $cuota_data) {
                $cuota_id = $cuotas_pendientes[$index]['id'];
                
                $sql = "UPDATE amortizaciones 
                        SET valor_cuota = ?, capital = ?, interes = ?, saldo = ?, updated_at = NOW()
                        WHERE id = ?";
                
                $params = [
                    $cuota_data['cuota_fija'],
                    $cuota_data['capital'],
                    $cuota_data['interes'],
                    $cuota_data['saldo'],
                    $cuota_id
                ];

                $db->execute($sql, $params);
            }

            $db->commit();

            $_SESSION['success'] = 'Plan de amortización recalculado exitosamente';
            redirect('/lotes/amortizacion/show/' . $loteId);

        } catch (\Exception $e) {
            if (isset($db)) {
                $db->rollback();
            }
            
            $_SESSION['error'] = 'Error al recalcular: ' . $e->getMessage();
            redirect('/lotes/amortizacion/show/' . $loteId);
        }
    }

    /**
     * Reajusta el plan aplicando saldo a favor para compensar mora
     * POST /lotes/amortizacion/reajustar/{lote_id}
     * 
     * Lógica:
     * - Obtiene saldo_a_favor del lote
     * - Itera sobre cuotas futuras pendientes (orden cronológico)
     * - Aplica saldo_a_favor al valor_pagado de cada cuota
     * - Marca como pagada si es cubierta completamente
     * - Reduce saldo_a_favor hasta agotarlo
     * - Evita que cuotas entren en mora
     */
    public function reajustarPlan($loteId)
    {
        \Logger::info("=== INICIO reajustarPlan() ===", ['lote_id' => $loteId]);
        
        if (!can('registrar_pagos')) {
            \Logger::error("Permiso denegado: registrar_pagos");
            $_SESSION['error'] = 'No tienes permisos para reajustar el plan de amortización';
            redirect('/lotes');
            return;
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            \Logger::error("Token CSRF inválido");
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('/lotes/amortizacion/show/' . $loteId);
            return;
        }

        try {
            // Obtener información del lote
            $lote = $this->loteModel->findById($loteId);
            
            if (!$lote) {
                \Logger::error("Lote no encontrado: {$loteId}");
                throw new \Exception('Lote no encontrado');
            }
            
            \Logger::info("Lote encontrado: {$lote['codigo_lote']}");

            // Obtener saldo_a_favor disponible
            $saldo_a_favor = $this->loteModel->getSaldoAFavor($loteId);
            
            \Logger::info("Saldo a favor disponible: {$saldo_a_favor}");

            if ($saldo_a_favor <= 0.01) {
                \Logger::warning("Saldo a favor insuficiente o cero");
                $_SESSION['warning'] = 'No hay saldo a favor disponible para reajustar';
                redirect('/lotes/amortizacion/show/' . $loteId);
                return;
            }

            // Obtener cuotas pendientes en orden cronológico (numero_cuota ascendente)
            $sql_cuotas_pendientes = "SELECT * FROM amortizaciones 
                                      WHERE lote_id = ? AND estado = 'pendiente' 
                                      ORDER BY numero_cuota ASC";
            
            $db = \Database::getInstance();
            $cuotas_pendientes = $db->fetchAll($sql_cuotas_pendientes, [$loteId]);
            
            \Logger::info("Cuotas pendientes encontradas: " . count($cuotas_pendientes));

            if (empty($cuotas_pendientes)) {
                \Logger::warning("No hay cuotas pendientes para reajustar");
                $_SESSION['warning'] = 'No hay cuotas pendientes para compensar';
                redirect('/lotes/amortizacion/show/' . $loteId);
                return;
            }

            // Iniciar transacción
            $db->beginTransaction();
            \Logger::info("Transacción iniciada");

            $saldo_aplicable = $saldo_a_favor;
            $cuotas_compensadas = 0;
            $monto_total_aplicado = 0;
            $cuotas_actualizadas = [];

            // Iterar sobre cuotas futuras y aplicar saldo
            foreach ($cuotas_pendientes as $cuota) {
                if ($saldo_aplicable <= 0.01) {
                    \Logger::debug("Saldo agotado, finalizando compensación");
                    break;
                }

                $cuota_id = $cuota['id'];
                $numero_cuota = $cuota['numero_cuota'];
                $valor_cuota = $cuota['valor_cuota'];
                $valor_pagado_actual = $cuota['valor_pagado'];
                $saldo_pendiente_cuota = $valor_cuota - $valor_pagado_actual;

                \Logger::debug("Procesando cuota", [
                    'numero' => $numero_cuota,
                    'saldo_pendiente' => $saldo_pendiente_cuota,
                    'saldo_aplicable' => $saldo_aplicable
                ]);

                // Calcular cuánto aplicar a esta cuota
                $monto_a_aplicar = min($saldo_aplicable, $saldo_pendiente_cuota);

                // Actualizar cuota
                $nuevo_valor_pagado = $valor_pagado_actual + $monto_a_aplicar;
                $nuevo_saldo_pendiente = $valor_cuota - $nuevo_valor_pagado;
                $nuevo_estado = $nuevo_saldo_pendiente <= 0.01 ? 'pagada' : 'pendiente';

                \Logger::debug("Aplicando compensación", [
                    'cuota_id' => $cuota_id,
                    'monto_a_aplicar' => $monto_a_aplicar,
                    'nuevo_valor_pagado' => $nuevo_valor_pagado,
                    'nuevo_estado' => $nuevo_estado
                ]);

                $sql_update = "UPDATE amortizaciones 
                              SET valor_pagado = ?, 
                                  saldo_pendiente = ?,
                                  estado = ?,
                                  updated_at = NOW()
                              WHERE id = ?";
                
                $params_update = [
                    $nuevo_valor_pagado,
                    max(0, $nuevo_saldo_pendiente),
                    $nuevo_estado,
                    $cuota_id
                ];

                $db->execute($sql_update, $params_update);

                // Registrar en tabla de pagos (para auditoría)
                $sql_pago = "INSERT INTO pagos 
                            (amortizacion_id, valor_pagado, metodo_pago, fecha_pago, numero_recibo, observaciones, created_at) 
                            VALUES (?, ?, 'saldo_a_favor', ?, ?, ?, NOW())";
                
                $numero_recibo = 'REAJ-SAF-' . date('YmdHis') . '-' . $cuota_id;
                $observaciones = 'Aplicación automática de Saldo a Favor - Reajuste de Mora';

                $params_pago = [
                    $cuota_id,
                    $monto_a_aplicar,
                    date('Y-m-d'),
                    $numero_recibo,
                    $observaciones
                ];

                $db->execute($sql_pago, $params_pago);

                // Actualizar saldo aplicable
                $saldo_aplicable -= $monto_a_aplicar;
                $monto_total_aplicado += $monto_a_aplicar;

                if ($nuevo_estado === 'pagada') {
                    $cuotas_compensadas++;
                }

                $cuotas_actualizadas[] = [
                    'numero_cuota' => $numero_cuota,
                    'monto_aplicado' => $monto_a_aplicar,
                    'nuevo_estado' => $nuevo_estado
                ];

                \Logger::info("Cuota compensada exitosamente", [
                    'numero_cuota' => $numero_cuota,
                    'monto_aplicado' => $monto_a_aplicar,
                    'nuevo_estado' => $nuevo_estado
                ]);
            }

            // Actualizar saldo_a_favor del lote (restar lo que se aplicó)
            $nuevo_saldo_a_favor = $saldo_a_favor - $monto_total_aplicado;
            $sql_saldo = "UPDATE lotes SET 
                          saldo_a_favor = GREATEST(0, saldo_a_favor - ?),
                          updated_at = NOW()
                          WHERE id = ?";
            
            $db->execute($sql_saldo, [$monto_total_aplicado, $loteId]);

            \Logger::info("Saldo a favor actualizado", [
                'saldo_anterior' => $saldo_a_favor,
                'monto_aplicado' => $monto_total_aplicado,
                'saldo_nuevo' => $nuevo_saldo_a_favor
            ]);

            // Confirmar transacción
            $db->commit();
            \Logger::info("Transacción completada exitosamente");

            // Construir mensaje de éxito
            $mensaje = "Plan reajustado exitosamente. ";
            $mensaje .= "Monto aplicado: " . formatMoney($monto_total_aplicado) . ". ";
            $mensaje .= "Cuotas compensadas (pagadas): {$cuotas_compensadas}. ";
            $mensaje .= "Saldo a favor restante: " . formatMoney($nuevo_saldo_a_favor) . ".";

            \Logger::info("=== REAJUSTE COMPLETADO EXITOSAMENTE ===", [
                'lote_id' => $loteId,
                'monto_total_aplicado' => $monto_total_aplicado,
                'cuotas_compensadas' => $cuotas_compensadas,
                'saldo_a_favor_restante' => $nuevo_saldo_a_favor
            ]);

            $_SESSION['success'] = $mensaje;
            redirect('/lotes/amortizacion/show/' . $loteId);

        } catch (\Exception $e) {
            \Logger::error("=== ERROR EN REAJUSTE DE PLAN ===");
            \Logger::error("Mensaje: " . $e->getMessage());
            \Logger::error("Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
            \Logger::error("Stack trace: " . $e->getTraceAsString());
            
            if (isset($db)) {
                \Logger::info("Ejecutando rollback");
                $db->rollback();
                \Logger::info("Rollback completado");
            }
            
            $_SESSION['error'] = 'Error al reajustar plan: ' . $e->getMessage();
            redirect('/lotes/amortizacion/show/' . $loteId);
        }
    }
}
