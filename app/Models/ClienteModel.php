<?php

namespace App\Models;

/**
 * ClienteModel - Modelo de Clientes
 * Maneja todas las operaciones relacionadas con clientes
 */
class ClienteModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtiene todos los clientes
     */
    public function getAll()
    {
        $sql = "SELECT * FROM clientes ORDER BY nombre ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtiene un cliente por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM clientes WHERE id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Obtiene un cliente por número de documento
     */
    public function findByDocumento($tipoDocumento, $numeroDocumento)
    {
        $sql = "SELECT * FROM clientes WHERE tipo_documento = ? AND numero_documento = ? LIMIT 1";
        return $this->db->fetch($sql, [$tipoDocumento, $numeroDocumento]);
    }

    /**
     * Busca clientes por nombre o documento
     */
    public function buscar($termino)
    {
        $sql = "SELECT * FROM clientes 
                WHERE nombre LIKE ? OR numero_documento LIKE ? 
                ORDER BY nombre ASC 
                LIMIT 50";
        
        $busqueda = "%{$termino}%";
        return $this->db->fetchAll($sql, [$busqueda, $busqueda]);
    }

    /**
     * Obtiene clientes con lotes vendidos
     */
    public function getConLotes()
    {
        $sql = "SELECT DISTINCT c.* 
                FROM clientes c 
                INNER JOIN lotes l ON c.id = l.cliente_id 
                ORDER BY c.nombre ASC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtiene información detallada de un cliente con sus lotes
     */
    public function getDetalleConLotes($id)
    {
        $cliente = $this->findById($id);
        
        if (!$cliente) {
            return null;
        }

        // Obtener lotes del cliente
        $sqlLotes = "SELECT l.*, p.nombre as proyecto_nombre 
                     FROM lotes l 
                     INNER JOIN proyectos p ON l.proyecto_id = p.id 
                     WHERE l.cliente_id = ? 
                     ORDER BY l.fecha_venta DESC";
        
        $cliente['lotes'] = $this->db->fetchAll($sqlLotes, [$id]);
        
        // Calcular estadísticas
        $sqlEstadisticas = "SELECT 
                                COUNT(*) as total_lotes,
                                SUM(COALESCE(precio_venta, precio_lista)) as total_invertido
                            FROM lotes 
                            WHERE cliente_id = ?";
        
        $estadisticas = $this->db->fetch($sqlEstadisticas, [$id]);
        $cliente['estadisticas'] = $estadisticas;

        return $cliente;
    }

    /**
     * Crea un nuevo cliente
     */
    public function create($data)
    {
        $sql = "INSERT INTO clientes 
                (tipo_documento, numero_documento, nombre, telefono, email, direccion, ciudad, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['tipo_documento'],
            $data['numero_documento'],
            $data['nombre'],
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['observaciones'] ?? null
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Actualiza un cliente
     */
    public function update($id, $data)
    {
        $sql = "UPDATE clientes SET 
                tipo_documento = ?, 
                numero_documento = ?, 
                nombre = ?, 
                telefono = ?,
                email = ?, 
                direccion = ?,
                ciudad = ?,
                observaciones = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['tipo_documento'],
            $data['numero_documento'],
            $data['nombre'],
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['observaciones'] ?? null,
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Elimina un cliente (solo si no tiene lotes asociados)
     */
    public function delete($id)
    {
        // Verificar si tiene lotes asociados
        $sqlCheck = "SELECT COUNT(*) as total FROM lotes WHERE cliente_id = ?";
        $result = $this->db->fetch($sqlCheck, [$id]);
        
        if ($result['total'] > 0) {
            throw new \Exception("No se puede eliminar el cliente porque tiene {$result['total']} lotes asociados");
        }

        $sql = "DELETE FROM clientes WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Verifica si un documento ya existe
     */
    public function documentoExists($tipoDocumento, $numeroDocumento, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM clientes WHERE tipo_documento = ? AND numero_documento = ? AND id != ?";
            $result = $this->db->fetch($sql, [$tipoDocumento, $numeroDocumento, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM clientes WHERE tipo_documento = ? AND numero_documento = ?";
            $result = $this->db->fetch($sql, [$tipoDocumento, $numeroDocumento]);
        }

        return $result['count'] > 0;
    }

    /**
     * Cuenta total de clientes
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as total FROM clientes";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Crea un cliente rápido con datos mínimos para venta de lote
     */
    public function createQuick($data)
    {
        // Validar campos mínimos requeridos
        if (empty($data['tipo_documento']) || empty($data['numero_documento']) || empty($data['nombre'])) {
            throw new \Exception("Tipo de documento, número de documento y nombre son obligatorios");
        }

        // Verificar si el documento ya existe
        if ($this->documentoExists($data['tipo_documento'], $data['numero_documento'])) {
            throw new \Exception("Ya existe un cliente con ese tipo y número de documento");
        }

        // Insertar solo con campos mínimos
        $sql = "INSERT INTO clientes 
                (tipo_documento, numero_documento, nombre, telefono) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['tipo_documento'],
            $data['numero_documento'],
            $data['nombre'],
            $data['telefono'] ?? null
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
}
