<?php

namespace App\Models;

/**
 * AmortizacionModel - Modelo de Amortizaciones (Cuotas)
 * Maneja el plan de pagos y cuotas de los lotes vendidos
 */
class AmortizacionModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtiene todas las cuotas de un lote
     */
    public function getByLote($loteId)
    {
        $sql = "SELECT * FROM amortizaciones WHERE lote_id = ? ORDER BY numero_cuota ASC";
        return $this->db->fetchAll($sql, [$loteId]);
    }

    /**
     * Obtiene cuotas pendientes de un lote
     */
    public function getPendientesByLote($loteId)
    {
        $sql = "SELECT * FROM amortizaciones 
                WHERE lote_id = ? AND estado = 'pendiente' 
                ORDER BY numero_cuota ASC";
        
        return $this->db->fetchAll($sql, [$loteId]);
    }

    /**
     * Obtiene cuotas en mora (vencidas y no pagadas)
     */
    public function getCuotasMora()
    {
        $sql = "SELECT a.*, l.codigo_lote, p.nombre as proyecto_nombre, c.nombre as cliente_nombre,
                       l.proyecto_id, l.cliente_id
                FROM amortizaciones a
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                INNER JOIN clientes c ON l.cliente_id = c.id
                WHERE a.estado = 'pendiente' 
                  AND a.fecha_vencimiento < CURDATE()
                  AND a.dias_mora > 0
                ORDER BY a.dias_mora DESC, a.fecha_vencimiento ASC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtiene próximas cuotas a vencer (próximos 30 días)
     */
    public function getProximasCuotas($dias = 30)
    {
        $sql = "SELECT a.*, l.codigo_lote, p.nombre as proyecto_nombre, c.nombre as cliente_nombre,
                       l.proyecto_id, l.cliente_id
                FROM amortizaciones a
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                INNER JOIN clientes c ON l.cliente_id = c.id
                WHERE a.estado = 'pendiente' 
                  AND a.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY a.fecha_vencimiento ASC";
        
        return $this->db->fetchAll($sql, [$dias]);
    }

    /**
     * Obtiene total de cartera pendiente (saldo por cobrar)
     */
    public function getCarteraPendiente()
    {
        $sql = "SELECT 
                    SUM(valor_cuota - valor_pagado) as cartera_total,
                    COUNT(*) as total_cuotas_pendientes,
                    COUNT(CASE WHEN dias_mora > 0 THEN 1 END) as cuotas_vencidas,
                    SUM(CASE WHEN dias_mora > 0 THEN (valor_cuota - valor_pagado) ELSE 0 END) as cartera_vencida
                FROM amortizaciones 
                WHERE estado = 'pendiente'";
        
        return $this->db->fetch($sql);
    }

    /**
     * Obtiene cartera de un cliente específico
     */
    public function getCarteraByCliente($clienteId)
    {
        $sql = "SELECT a.*, l.codigo_lote, p.nombre as proyecto_nombre
                FROM amortizaciones a
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                WHERE l.cliente_id = ? AND a.estado = 'pendiente'
                ORDER BY a.fecha_vencimiento ASC";
        
        return $this->db->fetchAll($sql, [$clienteId]);
    }

    /**
     * Obtiene una cuota por ID
     */
    public function findById($id)
    {
        $sql = "SELECT a.*, l.codigo_lote, p.nombre as proyecto_nombre, c.nombre as cliente_nombre
                FROM amortizaciones a
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes c ON l.cliente_id = c.id
                WHERE a.id = ?
                LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Crea cuotas de amortización para un lote
     */
    public function generarCuotas($loteId, $cantidadCuotas, $valorCuota, $fechaInicio, $observaciones = null)
    {
        $cuotasCreadas = [];
        
        for ($i = 1; $i <= $cantidadCuotas; $i++) {
            // Calcular fecha de vencimiento (mensual)
            $fechaVencimiento = date('Y-m-d', strtotime($fechaInicio . " +{$i} months"));
            
            $sql = "INSERT INTO amortizaciones 
                    (lote_id, numero_cuota, valor_cuota, fecha_vencimiento, estado, observaciones) 
                    VALUES (?, ?, ?, ?, 'pendiente', ?)";
            
            $params = [
                $loteId,
                $i,
                $valorCuota,
                $fechaVencimiento,
                $observaciones
            ];

            $this->db->execute($sql, $params);
            $cuotasCreadas[] = $this->db->lastInsertId();
        }

        return $cuotasCreadas;
    }

    /**
     * Registra un pago parcial o total de una cuota
     */
    public function registrarPago($id, $valorPagado, $fechaPago = null)
    {
        $cuota = $this->findById($id);
        
        if (!$cuota) {
            throw new \Exception("Cuota no encontrada");
        }

        $nuevoValorPagado = $cuota['valor_pagado'] + $valorPagado;
        $saldoPendiente = $cuota['valor_cuota'] - $nuevoValorPagado;
        
        // Determinar nuevo estado
        $nuevoEstado = $saldoPendiente <= 0 ? 'pagada' : 'pendiente';
        $fechaPagoFinal = $fechaPago ?? date('Y-m-d');

        $sql = "UPDATE amortizaciones SET 
                valor_pagado = ?,
                saldo_pendiente = ?,
                estado = ?,
                fecha_pago = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $nuevoValorPagado,
            max(0, $saldoPendiente),
            $nuevoEstado,
            $nuevoEstado === 'pagada' ? $fechaPagoFinal : null,
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Actualiza una cuota
     */
    public function update($id, $data)
    {
        $sql = "UPDATE amortizaciones SET 
                valor_cuota = ?,
                fecha_vencimiento = ?,
                observaciones = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['valor_cuota'],
            $data['fecha_vencimiento'],
            $data['observaciones'] ?? null,
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Elimina una cuota (solo si no tiene pagos registrados)
     */
    public function delete($id)
    {
        // Verificar si tiene pagos registrados
        $sqlCheck = "SELECT COUNT(*) as total FROM pagos WHERE amortizacion_id = ?";
        $result = $this->db->fetch($sqlCheck, [$id]);
        
        if ($result['total'] > 0) {
            throw new \Exception("No se puede eliminar la cuota porque tiene pagos registrados");
        }

        $sql = "DELETE FROM amortizaciones WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Elimina todas las cuotas de un lote
     */
    public function deleteByLote($loteId)
    {
        // Verificar si hay cuotas con pagos
        $sqlCheck = "SELECT COUNT(*) as total 
                     FROM amortizaciones a
                     INNER JOIN pagos p ON a.id = p.amortizacion_id
                     WHERE a.lote_id = ?";
        
        $result = $this->db->fetch($sqlCheck, [$loteId]);
        
        if ($result['total'] > 0) {
            throw new \Exception("No se pueden eliminar las cuotas porque tienen pagos registrados");
        }

        $sql = "DELETE FROM amortizaciones WHERE lote_id = ?";
        return $this->db->execute($sql, [$loteId]);
    }

    /**
     * Obtiene resumen de amortizaciones de un lote
     */
    public function getResumenByLote($loteId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_cuotas,
                    SUM(valor_cuota) as valor_total_financiado,
                    SUM(valor_pagado) as total_pagado,
                    SUM(saldo_pendiente) as saldo_total,
                    COUNT(CASE WHEN estado = 'pagada' THEN 1 END) as cuotas_pagadas,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as cuotas_pendientes,
                    COUNT(CASE WHEN dias_mora > 0 THEN 1 END) as cuotas_vencidas,
                    MAX(dias_mora) as max_dias_mora
                FROM amortizaciones 
                WHERE lote_id = ?";
        
        return $this->db->fetch($sql, [$loteId]);
    }

    /**
     * Verifica si un lote tiene amortización activa
     */
    public function hasActiveAmortization($loteId)
    {
        $sql = "SELECT COUNT(*) as count FROM amortizaciones WHERE lote_id = ? AND estado IN ('pendiente', 'pagada')";
        $result = $this->db->fetch($sql, [$loteId]);
        return $result['count'] > 0;
    }
}
