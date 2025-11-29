<?php

namespace App\Models;

/**
 * ComisionModel - Modelo de Comisiones
 * Maneja el registro y control de comisiones por ventas
 */
class ComisionModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtiene todas las comisiones con información completa
     */
    public function getAll($filtros = [])
    {
        $sql = "SELECT 
                    c.*,
                    u.nombre as vendedor_nombre,
                    u.email as vendedor_email,
                    l.codigo_lote,
                    l.precio_venta as lote_precio_venta,
                    p.nombre as proyecto_nombre,
                    cl.nombre as cliente_nombre
                FROM comisiones c
                INNER JOIN users u ON c.vendedor_id = u.id
                INNER JOIN lotes l ON c.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes cl ON l.cliente_id = cl.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtro por vendedor
        if (!empty($filtros['vendedor_id'])) {
            $sql .= " AND c.vendedor_id = ?";
            $params[] = $filtros['vendedor_id'];
        }
        
        // Filtro por estado
        if (!empty($filtros['estado'])) {
            $sql .= " AND c.estado = ?";
            $params[] = $filtros['estado'];
        }
        
        // Filtro por rango de fechas
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND c.fecha_venta >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND c.fecha_venta <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        $sql .= " ORDER BY c.fecha_venta DESC, c.id DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtiene comisión por ID
     */
    public function findById($id)
    {
        $sql = "SELECT 
                    c.*,
                    u.nombre as vendedor_nombre,
                    u.email as vendedor_email,
                    u.rol as vendedor_rol,
                    l.codigo_lote,
                    l.precio_venta as lote_precio_venta,
                    p.nombre as proyecto_nombre,
                    p.codigo as proyecto_codigo,
                    cl.nombre as cliente_nombre,
                    cl.numero_documento as cliente_documento
                FROM comisiones c
                INNER JOIN users u ON c.vendedor_id = u.id
                INNER JOIN lotes l ON c.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes cl ON l.cliente_id = cl.id
                WHERE c.id = ?
                LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Marcar comisión como pagada
     */
    public function marcarComoPagada($id, $data)
    {
        $sql = "UPDATE comisiones 
                SET estado = 'pagada',
                    fecha_pago_comision = ?,
                    metodo_pago = ?,
                    referencia_pago = ?,
                    observaciones = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['fecha_pago'],
            $data['metodo_pago'],
            $data['referencia_pago'] ?? null,
            $data['observaciones'] ?? null,
            $id
        ]);
    }

    /**
     * Obtener resumen de comisiones por vendedor
     */
    public function getResumenPorVendedor($vendedorId = null)
    {
        $sql = "SELECT 
                    u.id as vendedor_id,
                    u.nombre as vendedor_nombre,
                    COUNT(c.id) as total_ventas,
                    SUM(CASE WHEN c.estado = 'pendiente' THEN 1 ELSE 0 END) as comisiones_pendientes,
                    SUM(CASE WHEN c.estado = 'pagada' THEN 1 ELSE 0 END) as comisiones_pagadas,
                    SUM(c.valor_comision) as total_comisiones,
                    SUM(CASE WHEN c.estado = 'pendiente' THEN c.valor_comision ELSE 0 END) as total_pendiente,
                    SUM(CASE WHEN c.estado = 'pagada' THEN c.valor_comision ELSE 0 END) as total_pagado
                FROM users u
                LEFT JOIN comisiones c ON u.id = c.vendedor_id
                WHERE u.rol IN ('administrador', 'vendedor')
                AND u.activo = 1";
        
        $params = [];
        
        if ($vendedorId) {
            $sql .= " AND u.id = ?";
            $params[] = $vendedorId;
        }
        
        $sql .= " GROUP BY u.id, u.nombre
                  ORDER BY total_comisiones DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtener configuración de comisión para un vendedor
     */
    public function getConfiguracionVendedor($vendedorId)
    {
        $sql = "SELECT * FROM configuracion_comisiones 
                WHERE vendedor_id = ? AND activo = 1 
                LIMIT 1";
        
        return $this->db->fetch($sql, [$vendedorId]);
    }

    /**
     * Actualizar configuración de comisión
     */
    public function actualizarConfiguracion($vendedorId, $porcentaje, $observaciones = null)
    {
        // Verificar si existe configuración
        $config = $this->getConfiguracionVendedor($vendedorId);
        
        if ($config) {
            $sql = "UPDATE configuracion_comisiones 
                    SET porcentaje_comision = ?,
                        observaciones = ?,
                        updated_at = NOW()
                    WHERE vendedor_id = ?";
            
            return $this->db->execute($sql, [$porcentaje, $observaciones, $vendedorId]);
        } else {
            $sql = "INSERT INTO configuracion_comisiones 
                    (vendedor_id, porcentaje_comision, observaciones, activo)
                    VALUES (?, ?, ?, 1)";
            
            return $this->db->execute($sql, [$vendedorId, $porcentaje, $observaciones]);
        }
    }

    /**
     * Crear comisión manualmente (cuando el trigger no se ejecutó)
     */
    public function create($data)
    {
        $sql = "INSERT INTO comisiones (
                    lote_id, vendedor_id, valor_venta, 
                    porcentaje_comision, valor_comision, 
                    estado, fecha_venta, observaciones
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $data['lote_id'],
            $data['vendedor_id'],
            $data['valor_venta'],
            $data['porcentaje_comision'],
            $data['valor_comision'],
            $data['estado'] ?? 'pendiente',
            $data['fecha_venta'],
            $data['observaciones'] ?? null
        ]);
    }
}
