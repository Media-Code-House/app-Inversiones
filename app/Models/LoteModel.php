<?php

namespace App\Models;

/**
 * LoteModel - Modelo de Lotes
 * Maneja todas las operaciones relacionadas con lotes
 */
class LoteModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtiene todos los lotes de un proyecto
     */
    public function getByProyecto($proyectoId)
    {
        $sql = "SELECT l.*, c.nombre as cliente_nombre 
                FROM lotes l 
                LEFT JOIN clientes c ON l.cliente_id = c.id 
                WHERE l.proyecto_id = ? 
                ORDER BY l.codigo_lote ASC";
        
        return $this->db->fetchAll($sql, [$proyectoId]);
    }

    /**
     * Obtiene lotes disponibles de un proyecto
     */
    public function getDisponibles($proyectoId = null)
    {
        if ($proyectoId) {
            $sql = "SELECT l.*, p.nombre as proyecto_nombre 
                    FROM lotes l 
                    INNER JOIN proyectos p ON l.proyecto_id = p.id 
                    WHERE l.estado = 'disponible' AND l.proyecto_id = ? 
                    ORDER BY l.precio_lista ASC";
            return $this->db->fetchAll($sql, [$proyectoId]);
        } else {
            $sql = "SELECT l.*, p.nombre as proyecto_nombre 
                    FROM lotes l 
                    INNER JOIN proyectos p ON l.proyecto_id = p.id 
                    WHERE l.estado = 'disponible' 
                    ORDER BY p.nombre, l.codigo_lote ASC";
            return $this->db->fetchAll($sql);
        }
    }

    /**
     * Obtiene lotes vendidos de un proyecto
     */
    public function getVendidos($proyectoId = null)
    {
        if ($proyectoId) {
            $sql = "SELECT l.*, p.nombre as proyecto_nombre, c.nombre as cliente_nombre 
                    FROM lotes l 
                    INNER JOIN proyectos p ON l.proyecto_id = p.id 
                    LEFT JOIN clientes c ON l.cliente_id = c.id 
                    WHERE l.estado = 'vendido' AND l.proyecto_id = ? 
                    ORDER BY l.fecha_venta DESC";
            return $this->db->fetchAll($sql, [$proyectoId]);
        } else {
            $sql = "SELECT l.*, p.nombre as proyecto_nombre, c.nombre as cliente_nombre 
                    FROM lotes l 
                    INNER JOIN proyectos p ON l.proyecto_id = p.id 
                    LEFT JOIN clientes c ON l.cliente_id = c.id 
                    WHERE l.estado = 'vendido' 
                    ORDER BY l.fecha_venta DESC";
            return $this->db->fetchAll($sql);
        }
    }

    /**
     * Cuenta lotes por estado
     */
    public function countByEstado($estado = null)
    {
        if ($estado) {
            $sql = "SELECT COUNT(*) as total FROM lotes WHERE estado = ?";
            $result = $this->db->fetch($sql, [$estado]);
        } else {
            $sql = "SELECT estado, COUNT(*) as total FROM lotes GROUP BY estado";
            return $this->db->fetchAll($sql);
        }
        
        return $result['total'] ?? 0;
    }

    /**
     * Calcula valor total de inventario (lotes disponibles + reservados)
     */
    public function getValorInventario()
    {
        $sql = "SELECT SUM(precio_lista) as total 
                FROM lotes 
                WHERE estado IN ('disponible', 'reservado')";
        
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Calcula valor total de ventas (lotes vendidos)
     */
    public function getValorVentas()
    {
        $sql = "SELECT SUM(COALESCE(precio_venta, precio_lista)) as total 
                FROM lotes 
                WHERE estado = 'vendido'";
        
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Obtiene un lote por ID
     */
    public function findById($id)
    {
        $sql = "SELECT l.*, p.nombre as proyecto_nombre, c.nombre as cliente_nombre 
                FROM lotes l 
                INNER JOIN proyectos p ON l.proyecto_id = p.id 
                LEFT JOIN clientes c ON l.cliente_id = c.id 
                WHERE l.id = ? 
                LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Obtiene estadísticas generales de lotes
     */
    public function getEstadisticas($proyectoId = null)
    {
        if ($proyectoId) {
            $sql = "SELECT 
                        COUNT(*) as total_lotes,
                        COUNT(CASE WHEN estado = 'disponible' THEN 1 END) as disponibles,
                        COUNT(CASE WHEN estado = 'vendido' THEN 1 END) as vendidos,
                        COUNT(CASE WHEN estado = 'reservado' THEN 1 END) as reservados,
                        COUNT(CASE WHEN estado = 'bloqueado' THEN 1 END) as bloqueados,
                        SUM(CASE WHEN estado IN ('disponible', 'reservado') THEN precio_lista ELSE 0 END) as valor_inventario,
                        SUM(CASE WHEN estado = 'vendido' THEN COALESCE(precio_venta, precio_lista) ELSE 0 END) as valor_ventas,
                        AVG(precio_lista) as precio_promedio,
                        MIN(precio_lista) as precio_minimo,
                        MAX(precio_lista) as precio_maximo
                    FROM lotes 
                    WHERE proyecto_id = ?";
            
            return $this->db->fetch($sql, [$proyectoId]);
        } else {
            $sql = "SELECT 
                        COUNT(*) as total_lotes,
                        COUNT(CASE WHEN estado = 'disponible' THEN 1 END) as disponibles,
                        COUNT(CASE WHEN estado = 'vendido' THEN 1 END) as vendidos,
                        COUNT(CASE WHEN estado = 'reservado' THEN 1 END) as reservados,
                        COUNT(CASE WHEN estado = 'bloqueado' THEN 1 END) as bloqueados,
                        SUM(CASE WHEN estado IN ('disponible', 'reservado') THEN precio_lista ELSE 0 END) as valor_inventario,
                        SUM(CASE WHEN estado = 'vendido' THEN COALESCE(precio_venta, precio_lista) ELSE 0 END) as valor_ventas,
                        AVG(precio_lista) as precio_promedio,
                        MIN(precio_lista) as precio_minimo,
                        MAX(precio_lista) as precio_maximo
                    FROM lotes";
            
            return $this->db->fetch($sql);
        }
    }

    /**
     * Crea un nuevo lote
     */
    public function create($data)
    {
        $sql = "INSERT INTO lotes 
                (proyecto_id, codigo_lote, manzana, area_m2, precio_lista, estado, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['proyecto_id'],
            $data['codigo_lote'],
            $data['manzana'] ?? null,
            $data['area_m2'] ?? null,
            $data['precio_lista'],
            $data['estado'] ?? 'disponible',
            $data['observaciones'] ?? null
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Actualiza un lote
     */
    public function update($id, $data)
    {
        $sql = "UPDATE lotes SET 
                codigo_lote = ?, 
                manzana = ?, 
                area_m2 = ?, 
                precio_lista = ?,
                estado = ?, 
                observaciones = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['codigo_lote'],
            $data['manzana'] ?? null,
            $data['area_m2'] ?? null,
            $data['precio_lista'],
            $data['estado'],
            $data['observaciones'] ?? null,
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Vende un lote (asocia cliente y actualiza estado)
     */
    public function vender($id, $clienteId, $precioVenta = null, $fechaVenta = null)
    {
        $sql = "UPDATE lotes SET 
                cliente_id = ?,
                precio_venta = ?,
                fecha_venta = ?,
                estado = 'vendido',
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $clienteId,
            $precioVenta,
            $fechaVenta ?? date('Y-m-d'),
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Reserva un lote
     */
    public function reservar($id)
    {
        $sql = "UPDATE lotes SET 
                estado = 'reservado',
                updated_at = NOW()
                WHERE id = ? AND estado = 'disponible'";
        
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Libera un lote (lo pone disponible)
     */
    public function liberar($id)
    {
        $sql = "UPDATE lotes SET 
                estado = 'disponible',
                cliente_id = NULL,
                precio_venta = NULL,
                fecha_venta = NULL,
                updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Elimina un lote
     */
    public function delete($id)
    {
        // Verificar si tiene amortizaciones asociadas
        $sqlCheck = "SELECT COUNT(*) as total FROM amortizaciones WHERE lote_id = ?";
        $result = $this->db->fetch($sqlCheck, [$id]);
        
        if ($result['total'] > 0) {
            throw new \Exception("No se puede eliminar el lote porque tiene amortizaciones asociadas");
        }

        $sql = "DELETE FROM lotes WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Verifica si un código de lote ya existe en el proyecto
     */
    public function codigoExists($proyectoId, $codigoLote, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM lotes WHERE proyecto_id = ? AND codigo_lote = ? AND id != ?";
            $result = $this->db->fetch($sql, [$proyectoId, $codigoLote, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM lotes WHERE proyecto_id = ? AND codigo_lote = ?";
            $result = $this->db->fetch($sql, [$proyectoId, $codigoLote]);
        }

        return $result['count'] > 0;
    }
}
