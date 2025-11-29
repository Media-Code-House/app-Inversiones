<?php

namespace App\Models;

/**
 * VendedorModel - Modelo de Vendedores
 * Gestión completa de vendedores y sus estadísticas
 */
class VendedorModel
{
    private $db;

    public function __construct()
    {
        \Logger::info('VendedorModel::__construct - Iniciando constructor del modelo');
        try {
            \Logger::info('VendedorModel::__construct - Obteniendo instancia de Database...');
            $this->db = \Database::getInstance();
            \Logger::info('VendedorModel::__construct - Database obtenida OK');
        } catch (\Exception $e) {
            \Logger::error('VendedorModel::__construct - ERROR: ' . $e->getMessage());
            \Logger::error('VendedorModel::__construct - Archivo: ' . $e->getFile() . ' línea ' . $e->getLine());
            throw $e;
        }
    }

    /**
     * Obtiene todos los vendedores con sus estadísticas
     */
    public function getAll($filtros = [])
    {
        try {
            \Logger::info('VendedorModel::getAll - Iniciando con filtros: ' . json_encode($filtros));
            
            $sql = "SELECT 
                        v.*,
                        u.email as user_email,
                        u.rol as user_rol,
                        u.activo as user_activo,
                        CONCAT(v.nombres, ' ', v.apellidos) as nombre_completo,
                        
                        -- Estadísticas de ventas
                        COUNT(DISTINCT l.id) as total_lotes_vendidos,
                        COALESCE(SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) ELSE 0 END), 0) as valor_total_vendido,
                        
                        -- Estadísticas de comisiones
                        COUNT(DISTINCT c.id) as total_comisiones,
                        COALESCE(SUM(c.valor_comision), 0) as total_comisiones_generadas,
                        COALESCE(SUM(CASE WHEN c.estado = 'pendiente' THEN c.valor_comision ELSE 0 END), 0) as comisiones_pendientes,
                        COALESCE(SUM(CASE WHEN c.estado = 'pagada' THEN c.valor_comision ELSE 0 END), 0) as comisiones_pagadas
                        
                    FROM vendedores v
                    INNER JOIN users u ON v.user_id = u.id
                    LEFT JOIN lotes l ON u.id = l.vendedor_id AND l.estado = 'vendido'
                    LEFT JOIN comisiones c ON v.id = c.vendedor_id
                    WHERE 1=1";
            
            \Logger::info('VendedorModel::getAll - Query base construida');
            
            $params = [];
            
            // Filtro por estado
            if (!empty($filtros['estado'])) {
                $sql .= " AND v.estado = ?";
                $params[] = $filtros['estado'];
            }
            
            // Filtro por búsqueda (nombre, código, documento)
            if (!empty($filtros['search'])) {
                $sql .= " AND (v.nombres LIKE ? OR v.apellidos LIKE ? OR v.codigo_vendedor LIKE ? OR v.numero_documento LIKE ?)";
                $searchTerm = '%' . $filtros['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " GROUP BY v.id, v.user_id, v.codigo_vendedor, v.tipo_documento, v.numero_documento, 
                      v.nombres, v.apellidos, v.telefono, v.celular, v.email, v.direccion, v.ciudad,
                      v.fecha_ingreso, v.fecha_salida, v.tipo_contrato, v.porcentaje_comision_default,
                      v.banco, v.tipo_cuenta, v.numero_cuenta, v.estado, v.observaciones, v.foto_perfil,
                      v.created_at, v.updated_at, u.email, u.rol, u.activo
                      ORDER BY v.nombres, v.apellidos";
            
            \Logger::info('VendedorModel::getAll - Query final construida');
            \Logger::info('VendedorModel::getAll - SQL: ' . substr($sql, 0, 200) . '...');
            \Logger::info('VendedorModel::getAll - Params: ' . json_encode($params));
            
            \Logger::info('VendedorModel::getAll - Ejecutando fetchAll...');
            $resultado = $this->db->fetchAll($sql, $params);
            \Logger::info('VendedorModel::getAll - fetchAll ejecutado exitosamente');
            
            \Logger::info('VendedorModel::getAll - Resultados: ' . count($resultado));
            
            return $resultado;
            
        } catch (\Exception $e) {
            \Logger::error('VendedorModel::getAll - ERROR CAPTURADO EN MODELO');
            \Logger::error('VendedorModel::getAll - Mensaje: ' . $e->getMessage());
            \Logger::error('VendedorModel::getAll - Archivo: ' . $e->getFile() . ' línea ' . $e->getLine());
            \Logger::error('VendedorModel::getAll - Código error: ' . $e->getCode());
            \Logger::error('VendedorModel::getAll - Tipo excepción: ' . get_class($e));
            \Logger::error('VendedorModel::getAll - Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Obtiene un vendedor por ID con estadísticas completas
     */
    public function findById($id)
    {
        $sql = "SELECT 
                    v.*,
                    u.email as user_email,
                    u.rol as user_rol,
                    u.activo as user_activo,
                    CONCAT(v.nombres, ' ', v.apellidos) as nombre_completo,
                    
                    -- Estadísticas
                    (SELECT COUNT(*) FROM lotes l2 WHERE l2.vendedor_id = v.user_id AND l2.estado = 'vendido') as total_lotes_vendidos,
                    (SELECT COALESCE(SUM(l2.precio_venta), 0) FROM lotes l2 WHERE l2.vendedor_id = v.user_id AND l2.estado = 'vendido') as valor_total_vendido,
                    (SELECT COUNT(*) FROM comisiones c2 WHERE c2.vendedor_id = v.id) as total_comisiones,
                    (SELECT COALESCE(SUM(c2.valor_comision), 0) FROM comisiones c2 WHERE c2.vendedor_id = v.id) as total_comisiones_generadas,
                    (SELECT COALESCE(SUM(c2.valor_comision), 0) FROM comisiones c2 WHERE c2.vendedor_id = v.id AND c2.estado = 'pendiente') as comisiones_pendientes,
                    (SELECT COALESCE(SUM(c2.valor_comision), 0) FROM comisiones c2 WHERE c2.vendedor_id = v.id AND c2.estado = 'pagada') as comisiones_pagadas
                    
                FROM vendedores v
                INNER JOIN users u ON v.user_id = u.id
                WHERE v.id = ?
                LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Obtiene vendedor por user_id
     */
    public function findByUserId($userId)
    {
        $sql = "SELECT v.*, CONCAT(v.nombres, ' ', v.apellidos) as nombre_completo
                FROM vendedores v
                WHERE v.user_id = ?
                LIMIT 1";
        
        return $this->db->fetch($sql, [$userId]);
    }

    /**
     * Crear nuevo vendedor
     */
    public function create($data)
    {
        $sql = "INSERT INTO vendedores (
                    user_id, codigo_vendedor, tipo_documento, numero_documento,
                    nombres, apellidos, telefono, celular, email,
                    direccion, ciudad, fecha_ingreso, tipo_contrato,
                    porcentaje_comision_default, banco, tipo_cuenta, numero_cuenta,
                    estado, observaciones
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->execute($sql, [
            $data['user_id'],
            $data['codigo_vendedor'],
            $data['tipo_documento'] ?? 'CC',
            $data['numero_documento'],
            $data['nombres'],
            $data['apellidos'],
            $data['telefono'] ?? null,
            $data['celular'] ?? null,
            $data['email'],
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['fecha_ingreso'],
            $data['tipo_contrato'] ?? 'indefinido',
            $data['porcentaje_comision_default'] ?? 3.00,
            $data['banco'] ?? null,
            $data['tipo_cuenta'] ?? null,
            $data['numero_cuenta'] ?? null,
            $data['estado'] ?? 'activo',
            $data['observaciones'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Actualizar vendedor
     */
    public function update($id, $data)
    {
        $sql = "UPDATE vendedores SET
                    codigo_vendedor = ?,
                    tipo_documento = ?,
                    numero_documento = ?,
                    nombres = ?,
                    apellidos = ?,
                    telefono = ?,
                    celular = ?,
                    email = ?,
                    direccion = ?,
                    ciudad = ?,
                    fecha_ingreso = ?,
                    fecha_salida = ?,
                    tipo_contrato = ?,
                    porcentaje_comision_default = ?,
                    banco = ?,
                    tipo_cuenta = ?,
                    numero_cuenta = ?,
                    estado = ?,
                    observaciones = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['codigo_vendedor'],
            $data['tipo_documento'],
            $data['numero_documento'],
            $data['nombres'],
            $data['apellidos'],
            $data['telefono'] ?? null,
            $data['celular'] ?? null,
            $data['email'],
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['fecha_ingreso'],
            $data['fecha_salida'] ?? null,
            $data['tipo_contrato'],
            $data['porcentaje_comision_default'],
            $data['banco'] ?? null,
            $data['tipo_cuenta'] ?? null,
            $data['numero_cuenta'] ?? null,
            $data['estado'],
            $data['observaciones'] ?? null,
            $id
        ]);
    }

    /**
     * Verificar si existe un código de vendedor
     */
    public function codigoExists($codigo, $excludeId = null)
    {
        $sql = "SELECT id FROM vendedores WHERE codigo_vendedor = ?";
        $params = [$codigo];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->fetch($sql, $params) !== null;
    }

    /**
     * Verificar si existe un documento
     */
    public function documentoExists($documento, $excludeId = null)
    {
        $sql = "SELECT id FROM vendedores WHERE numero_documento = ?";
        $params = [$documento];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->fetch($sql, $params) !== null;
    }

    /**
     * Obtener lotes vendidos por un vendedor
     */
    public function getLotesVendidos($vendedorId, $limit = null)
    {
        $vendedor = $this->findById($vendedorId);
        if (!$vendedor) return [];
        
        $sql = "SELECT 
                    l.*,
                    p.nombre as proyecto_nombre,
                    p.codigo as proyecto_codigo,
                    c.nombre as cliente_nombre,
                    c.numero_documento as cliente_documento
                FROM lotes l
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes c ON l.cliente_id = c.id
                WHERE l.vendedor_id = ? AND l.estado = 'vendido'
                ORDER BY l.fecha_venta DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $this->db->fetchAll($sql, [$vendedor['user_id']]);
    }

    /**
     * Obtener comisiones de un vendedor
     */
    public function getComisiones($vendedorId, $estado = null)
    {
        $sql = "SELECT 
                    c.*,
                    l.codigo_lote,
                    p.nombre as proyecto_nombre,
                    cl.nombre as cliente_nombre
                FROM comisiones c
                INNER JOIN lotes l ON c.lote_id = l.id
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes cl ON l.cliente_id = cl.id
                WHERE c.vendedor_id = ?";
        
        $params = [$vendedorId];
        
        if ($estado) {
            $sql .= " AND c.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY c.fecha_venta DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtener vendedores activos (para selectores)
     */
    public function getActivos()
    {
        $sql = "SELECT 
                    v.id,
                    v.user_id,
                    v.codigo_vendedor,
                    CONCAT(v.nombres, ' ', v.apellidos) as nombre_completo,
                    v.email,
                    v.porcentaje_comision_default,
                    u.rol
                FROM vendedores v
                INNER JOIN users u ON v.user_id = u.id
                WHERE v.estado = 'activo' AND u.activo = 1
                ORDER BY v.nombres, v.apellidos";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Eliminar vendedor (soft delete - cambiar a inactivo)
     */
    public function delete($id)
    {
        $sql = "UPDATE vendedores SET estado = 'inactivo', updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Obtener ranking de vendedores por ventas
     */
    public function getRanking($periodo = 'mes')
    {
        $fechaFiltro = '';
        switch ($periodo) {
            case 'mes':
                $fechaFiltro = "AND l.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'trimestre':
                $fechaFiltro = "AND l.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                break;
            case 'año':
                $fechaFiltro = "AND l.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
            default:
                $fechaFiltro = "";
        }
        
        $sql = "SELECT 
                    v.id,
                    v.codigo_vendedor,
                    CONCAT(v.nombres, ' ', v.apellidos) as nombre_completo,
                    COUNT(l.id) as total_ventas,
                    COALESCE(SUM(l.precio_venta), 0) as valor_total,
                    COALESCE(SUM(c.valor_comision), 0) as comisiones_generadas
                FROM vendedores v
                INNER JOIN users u ON v.user_id = u.id
                LEFT JOIN lotes l ON u.id = l.vendedor_id AND l.estado = 'vendido' {$fechaFiltro}
                LEFT JOIN comisiones c ON v.id = c.vendedor_id
                WHERE v.estado = 'activo'
                GROUP BY v.id, v.codigo_vendedor, v.nombres, v.apellidos
                ORDER BY total_ventas DESC, valor_total DESC
                LIMIT 10";
        
        return $this->db->fetchAll($sql);
    }
}
