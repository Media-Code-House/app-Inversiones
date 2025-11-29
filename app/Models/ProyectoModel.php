<?php

namespace App\Models;

/**
 * ProyectoModel - Modelo de Proyectos
 * Maneja todas las operaciones relacionadas con proyectos inmobiliarios
 */
class ProyectoModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Obtiene todos los proyectos
     */
    public function getAll()
    {
        $sql = "SELECT * FROM proyectos ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtiene proyectos activos
     */
    public function getActivos()
    {
        $sql = "SELECT * FROM proyectos WHERE estado = 'activo' ORDER BY nombre ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtiene un proyecto por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM proyectos WHERE id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Obtiene un proyecto por código
     */
    public function findByCodigo($codigo)
    {
        $sql = "SELECT * FROM proyectos WHERE codigo = ? LIMIT 1";
        return $this->db->fetch($sql, [$codigo]);
    }

    /**
     * Cuenta total de proyectos activos
     */
    public function countActivos()
    {
        $sql = "SELECT COUNT(*) as total FROM proyectos WHERE estado = 'activo'";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Obtiene resumen de proyectos con estadísticas
     * Usa la vista creada en schema.sql
     */
    public function getResumenProyectos()
    {
        $sql = "SELECT * FROM vista_proyectos_resumen ORDER BY nombre ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtiene estadísticas detalladas de un proyecto
     */
    public function getEstadisticas($proyectoId)
    {
        $sql = "SELECT 
                    p.id,
                    p.codigo,
                    p.nombre,
                    p.total_lotes,
                    COUNT(DISTINCT l.id) as total_lotes_registrados,
                    COUNT(DISTINCT CASE WHEN l.estado = 'disponible' THEN l.id END) as lotes_disponibles,
                    COUNT(DISTINCT CASE WHEN l.estado = 'vendido' THEN l.id END) as lotes_vendidos,
                    COUNT(DISTINCT CASE WHEN l.estado = 'reservado' THEN l.id END) as lotes_reservados,
                    COUNT(DISTINCT CASE WHEN l.estado = 'bloqueado' THEN l.id END) as lotes_bloqueados,
                    SUM(CASE WHEN l.estado IN ('disponible', 'reservado') THEN l.precio_lista ELSE 0 END) as valor_inventario,
                    SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) ELSE 0 END) as valor_ventas,
                    MIN(l.precio_lista) as precio_minimo,
                    MAX(l.precio_lista) as precio_maximo,
                    AVG(l.precio_lista) as precio_promedio
                FROM proyectos p
                LEFT JOIN lotes l ON p.id = l.proyecto_id
                WHERE p.id = ?
                GROUP BY p.id, p.codigo, p.nombre, p.total_lotes";
        
        return $this->db->fetch($sql, [$proyectoId]);
    }

    /**
     * Crea un nuevo proyecto
     */
    public function create($data)
    {
        $sql = "INSERT INTO proyectos 
                (codigo, nombre, ubicacion, descripcion, estado, fecha_inicio, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['codigo'],
            $data['nombre'],
            $data['ubicacion'] ?? null,
            $data['descripcion'] ?? null,
            $data['estado'] ?? 'activo',
            $data['fecha_inicio'] ?? null,
            $data['observaciones'] ?? null
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Actualiza un proyecto
     */
    public function update($id, $data)
    {
        $sql = "UPDATE proyectos SET 
                codigo = ?, 
                nombre = ?, 
                ubicacion = ?, 
                descripcion = ?,
                estado = ?, 
                fecha_inicio = ?,
                fecha_finalizacion = ?,
                observaciones = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['codigo'],
            $data['nombre'],
            $data['ubicacion'] ?? null,
            $data['descripcion'] ?? null,
            $data['estado'],
            $data['fecha_inicio'] ?? null,
            $data['fecha_finalizacion'] ?? null,
            $data['observaciones'] ?? null,
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Elimina un proyecto (solo si no tiene lotes asociados)
     */
    public function delete($id)
    {
        // Verificar si tiene lotes asociados
        $sqlCheck = "SELECT COUNT(*) as total FROM lotes WHERE proyecto_id = ?";
        $result = $this->db->fetch($sqlCheck, [$id]);
        
        if ($result['total'] > 0) {
            throw new \Exception("No se puede eliminar el proyecto porque tiene {$result['total']} lotes asociados");
        }

        $sql = "DELETE FROM proyectos WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Verifica si un código de proyecto ya existe
     */
    public function codigoExists($codigo, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM proyectos WHERE codigo = ? AND id != ?";
            $result = $this->db->fetch($sql, [$codigo, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM proyectos WHERE codigo = ?";
            $result = $this->db->fetch($sql, [$codigo]);
        }

        return $result['count'] > 0;
    }
}
