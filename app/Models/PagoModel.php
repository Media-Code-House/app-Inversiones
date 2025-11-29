<?php

namespace App\Models;

/**
 * PagoModel - Modelo de Pagos
 * Maneja el registro histórico de pagos realizados
 */
class PagoModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtiene todos los pagos de una cuota
     */
    public function getByAmortizacion($amortizacionId)
    {
        $sql = "SELECT * FROM pagos WHERE amortizacion_id = ? ORDER BY fecha_pago DESC";
        return $this->db->fetchAll($sql, [$amortizacionId]);
    }

    /**
     * Obtiene todos los pagos de un lote
     */
    public function getByLote($loteId)
    {
        $sql = "SELECT p.*, a.numero_cuota 
                FROM pagos p
                INNER JOIN amortizaciones a ON p.amortizacion_id = a.id
                WHERE a.lote_id = ?
                ORDER BY p.fecha_pago DESC";
        
        return $this->db->fetchAll($sql, [$loteId]);
    }

    /**
     * Obtiene los últimos pagos registrados
     */
    public function getUltimosPagos($limite = 10)
    {
        $sql = "SELECT p.*, a.numero_cuota, l.codigo_lote, 
                       pr.nombre as proyecto_nombre, c.nombre as cliente_nombre
                FROM pagos p
                INNER JOIN amortizaciones a ON p.amortizacion_id = a.id
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos pr ON l.proyecto_id = pr.id
                INNER JOIN clientes c ON l.cliente_id = c.id
                ORDER BY p.fecha_pago DESC, p.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limite]);
    }

    /**
     * Obtiene pagos por fecha
     */
    public function getByFecha($fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            $sql = "SELECT p.*, a.numero_cuota, l.codigo_lote, 
                           pr.nombre as proyecto_nombre, c.nombre as cliente_nombre
                    FROM pagos p
                    INNER JOIN amortizaciones a ON p.amortizacion_id = a.id
                    INNER JOIN lotes l ON a.lote_id = l.id
                    INNER JOIN proyectos pr ON l.proyecto_id = pr.id
                    INNER JOIN clientes c ON l.cliente_id = c.id
                    WHERE p.fecha_pago BETWEEN ? AND ?
                    ORDER BY p.fecha_pago DESC";
            
            return $this->db->fetchAll($sql, [$fechaInicio, $fechaFin]);
        } else {
            $sql = "SELECT p.*, a.numero_cuota, l.codigo_lote, 
                           pr.nombre as proyecto_nombre, c.nombre as cliente_nombre
                    FROM pagos p
                    INNER JOIN amortizaciones a ON p.amortizacion_id = a.id
                    INNER JOIN lotes l ON a.lote_id = l.id
                    INNER JOIN proyectos pr ON l.proyecto_id = pr.id
                    INNER JOIN clientes c ON l.cliente_id = c.id
                    WHERE p.fecha_pago = ?
                    ORDER BY p.created_at DESC";
            
            return $this->db->fetchAll($sql, [$fechaInicio]);
        }
    }

    /**
     * Obtiene pagos de un cliente
     */
    public function getByCliente($clienteId)
    {
        $sql = "SELECT p.*, a.numero_cuota, l.codigo_lote, pr.nombre as proyecto_nombre
                FROM pagos p
                INNER JOIN amortizaciones a ON p.amortizacion_id = a.id
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos pr ON l.proyecto_id = pr.id
                WHERE l.cliente_id = ?
                ORDER BY p.fecha_pago DESC";
        
        return $this->db->fetchAll($sql, [$clienteId]);
    }

    /**
     * Obtiene un pago por ID
     */
    public function findById($id)
    {
        $sql = "SELECT p.*, a.numero_cuota, a.valor_cuota, l.codigo_lote, 
                       pr.nombre as proyecto_nombre, c.nombre as cliente_nombre
                FROM pagos p
                INNER JOIN amortizaciones a ON p.amortizacion_id = a.id
                INNER JOIN lotes l ON a.lote_id = l.id
                INNER JOIN proyectos pr ON l.proyecto_id = pr.id
                LEFT JOIN clientes c ON l.cliente_id = c.id
                WHERE p.id = ?
                LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Registra un nuevo pago
     */
    public function registrarPago($amortizacionId, $valorPagado, $metodoPago, $fechaPago = null, $numeroRecibo = null, $observaciones = null)
    {
        $this->db->beginTransaction();

        try {
            // Insertar el pago
            $sql = "INSERT INTO pagos 
                    (amortizacion_id, valor_pagado, metodo_pago, fecha_pago, numero_recibo, observaciones) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $params = [
                $amortizacionId,
                $valorPagado,
                $metodoPago,
                $fechaPago ?? date('Y-m-d'),
                $numeroRecibo,
                $observaciones
            ];

            $this->db->execute($sql, $params);
            $pagoId = $this->db->lastInsertId();

            // Actualizar la amortización
            $amortizacionModel = new AmortizacionModel();
            $amortizacionModel->registrarPago($amortizacionId, $valorPagado, $fechaPago);

            $this->db->commit();
            return $pagoId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un pago (solo si no está confirmado)
     */
    public function update($id, $data)
    {
        $sql = "UPDATE pagos SET 
                valor_pagado = ?,
                metodo_pago = ?,
                fecha_pago = ?,
                numero_recibo = ?,
                observaciones = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['valor_pagado'],
            $data['metodo_pago'],
            $data['fecha_pago'],
            $data['numero_recibo'] ?? null,
            $data['observaciones'] ?? null,
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Elimina un pago (y revierte el efecto en la amortización)
     */
    public function delete($id)
    {
        $pago = $this->findById($id);
        
        if (!$pago) {
            throw new \Exception("Pago no encontrado");
        }

        $this->db->beginTransaction();

        try {
            // Eliminar el pago
            $sql = "DELETE FROM pagos WHERE id = ?";
            $this->db->execute($sql, [$id]);

            // Revertir el valor pagado en la amortización
            $sqlUpdate = "UPDATE amortizaciones SET 
                          valor_pagado = valor_pagado - ?,
                          saldo_pendiente = saldo_pendiente + ?,
                          estado = CASE 
                              WHEN (valor_pagado - ?) <= 0 THEN 'pendiente'
                              ELSE estado
                          END,
                          fecha_pago = NULL,
                          updated_at = NOW()
                          WHERE id = ?";
            
            $this->db->execute($sqlUpdate, [
                $pago['valor_pagado'],
                $pago['valor_pagado'],
                $pago['valor_pagado'],
                $pago['amortizacion_id']
            ]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene total de pagos recibidos en un rango de fechas
     */
    public function getTotalPagosPeriodo($fechaInicio, $fechaFin)
    {
        $sql = "SELECT 
                    COUNT(*) as total_transacciones,
                    SUM(valor_pagado) as total_recaudado,
                    AVG(valor_pagado) as promedio_pago
                FROM pagos 
                WHERE fecha_pago BETWEEN ? AND ?";
        
        return $this->db->fetch($sql, [$fechaInicio, $fechaFin]);
    }

    /**
     * Obtiene estadísticas de pagos por método
     */
    public function getEstadisticasPorMetodo($fechaInicio = null, $fechaFin = null)
    {
        if ($fechaInicio && $fechaFin) {
            $sql = "SELECT 
                        metodo_pago,
                        COUNT(*) as cantidad,
                        SUM(valor_pagado) as total
                    FROM pagos 
                    WHERE fecha_pago BETWEEN ? AND ?
                    GROUP BY metodo_pago 
                    ORDER BY total DESC";
            
            return $this->db->fetchAll($sql, [$fechaInicio, $fechaFin]);
        } else {
            $sql = "SELECT 
                        metodo_pago,
                        COUNT(*) as cantidad,
                        SUM(valor_pagado) as total
                    FROM pagos 
                    GROUP BY metodo_pago 
                    ORDER BY total DESC";
            
            return $this->db->fetchAll($sql);
        }
    }

    /**
     * Obtiene resumen de pagos del día
     */
    public function getResumenDia($fecha = null)
    {
        $fecha = $fecha ?? date('Y-m-d');
        
        $sql = "SELECT 
                    COUNT(*) as total_pagos,
                    SUM(valor_pagado) as total_recaudado,
                    COUNT(DISTINCT amortizacion_id) as cuotas_pagadas
                FROM pagos 
                WHERE fecha_pago = ?";
        
        return $this->db->fetch($sql, [$fecha]);
    }
}
