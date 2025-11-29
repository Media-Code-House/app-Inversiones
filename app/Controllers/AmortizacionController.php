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
        if (!can('crear_amortizacion')) {
            $_SESSION['error'] = 'No tienes permisos para crear planes de amortización';
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
        $required = ['lote_id', 'cuota_inicial', 'monto_financiado', 'tasa_interes', 'numero_cuotas', 'fecha_inicio'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "El campo {$field} es requerido";
                redirect($_SERVER['HTTP_REFERER'] ?? '/lotes');
                return;
            }
        }

        $lote_id = (int)$_POST['lote_id'];
        $cuota_inicial = (float)$_POST['cuota_inicial'];
        $monto_financiado = (float)$_POST['monto_financiado'];
        $tasa_interes_anual = (float)$_POST['tasa_interes'];
        $numero_cuotas = (int)$_POST['numero_cuotas'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $observaciones = $_POST['observaciones'] ?? null;

        // Validar lote
        $lote = $this->loteModel->findById($lote_id);
        
        if (!$lote || $lote['estado'] !== 'vendido') {
            $_SESSION['error'] = 'Lote inválido o no vendido';
            redirect('/lotes');
            return;
        }

        // Validar que no exista plan activo
        if ($this->amortizacionModel->hasActiveAmortization($lote_id)) {
            $_SESSION['error'] = 'Este lote ya tiene un plan de amortización activo';
            redirect('/lotes/show/' . $lote_id);
            return;
        }

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

            $_SESSION['success'] = "Plan de amortización creado exitosamente con {$numero_cuotas} cuotas";
            redirect('/lotes/amortizacion/show/' . $lote_id);

        } catch (\Exception $e) {
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
            'kpis' => $kpis
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
}
