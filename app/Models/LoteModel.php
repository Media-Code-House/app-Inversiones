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
     * Obtiene todos los lotes con filtros opcionales (sin paginación)
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT l.*, 
                       p.nombre as proyecto_nombre, 
                       p.codigo as proyecto_codigo,
                       c.nombre as cliente_nombre,
                       (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id AND estado = 'pendiente') as tiene_amortizacion
                FROM lotes l 
                INNER JOIN proyectos p ON l.proyecto_id = p.id 
                LEFT JOIN clientes c ON l.cliente_id = c.id 
                WHERE 1=1 ";
        
        $params = [];
        
        // Filtro por proyecto
        if (!empty($filters['proyecto_id'])) {
            $sql .= " AND l.proyecto_id = ? ";
            $params[] = $filters['proyecto_id'];
        }
        
        // Filtro por estado
        if (!empty($filters['estado'])) {
            $sql .= " AND l.estado = ? ";
            $params[] = $filters['estado'];
        }
        
        // Búsqueda por texto
        if (!empty($filters['busqueda'])) {
            $sql .= " AND (l.codigo_lote LIKE ? OR l.manzana LIKE ? OR c.nombre LIKE ?) ";
            $busqueda = "%{$filters['busqueda']}%";
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
        
        $sql .= " ORDER BY p.nombre, l.codigo_lote ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtiene lotes con paginación y filtros
     * Retorna estructura: {data, total, per_page, current_page, last_page}
     */
    public function getAllPaginated($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 15;
        $offset = ($page - 1) * $perPage;
        
        // Query base con JOINs completos
        $baseSQL = "FROM lotes l 
                    INNER JOIN proyectos p ON l.proyecto_id = p.id 
                    LEFT JOIN clientes c ON l.cliente_id = c.id 
                    LEFT JOIN users u ON l.vendedor_id = u.id
                    WHERE 1=1 ";
        
        $params = [];
        $whereConditions = "";
        
        // Filtro por búsqueda (código, manzana, cliente, proyecto)
        if (!empty($filters['search'])) {
            $whereConditions .= " AND (l.codigo_lote LIKE ? 
                                      OR l.manzana LIKE ? 
                                      OR c.nombre LIKE ? 
                                      OR p.nombre LIKE ?) ";
            $busqueda = "%{$filters['search']}%";
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
        
        // Filtro por proyecto
        if (!empty($filters['proyecto_id'])) {
            $whereConditions .= " AND l.proyecto_id = ? ";
            $params[] = $filters['proyecto_id'];
        }
        
        // Filtro por estado
        if (!empty($filters['estado'])) {
            $whereConditions .= " AND l.estado = ? ";
            $params[] = $filters['estado'];
        }
        
        // Contar total de registros
        $countSQL = "SELECT COUNT(*) as total " . $baseSQL . $whereConditions;
        $totalResult = $this->db->fetch($countSQL, $params);
        $total = $totalResult['total'] ?? 0;
        
        // Query con datos completos
        $dataSQL = "SELECT l.*,
                           p.nombre as proyecto_nombre,
                           p.codigo as proyecto_codigo,
                           c.nombre as cliente_nombre,
                           c.numero_documento as cliente_documento,
                           u.nombre as vendedor_nombre,
                           (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id) as tiene_amortizacion
                    " . $baseSQL . $whereConditions . "
                    ORDER BY l.updated_at DESC, l.created_at DESC
                    LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $data = $this->db->fetchAll($dataSQL, $params);
        
        // Calcular páginas
        $lastPage = $total > 0 ? ceil($total / $perPage) : 1;
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage
        ];
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
     * Obtiene un lote por ID con información completa
     */
    public function findById($id)
    {
        $sql = "SELECT l.*, 
                       p.nombre as proyecto_nombre, 
                       p.codigo as proyecto_codigo,
                       p.ubicacion as proyecto_ubicacion,
                       c.nombre as cliente_nombre,
                       c.numero_documento as cliente_documento,
                       c.telefono as cliente_telefono,
                       c.email as cliente_email,
                       (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id) as tiene_amortizacion,
                       (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id AND estado IN ('pendiente', 'pagada')) as amortizacion_activa
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

    /**
     * Valida que los valores numéricos sean positivos
     */
    public function validatePositiveValues($data)
    {
        $errors = [];

        if (isset($data['area_m2']) && $data['area_m2'] <= 0) {
            $errors[] = "El área debe ser un valor positivo";
        }

        if (isset($data['precio_lista']) && $data['precio_lista'] <= 0) {
            $errors[] = "El precio de lista debe ser un valor positivo";
        }

        if (isset($data['precio_venta']) && !empty($data['precio_venta']) && $data['precio_venta'] <= 0) {
            $errors[] = "El precio de venta debe ser un valor positivo";
        }

        return $errors;
    }

    /**
     * Valida cambios de estado basados en reglas de negocio
     */
    public function canChangeEstado($loteId, $nuevoEstado)
    {
        $lote = $this->findById($loteId);
        
        if (!$lote) {
            return ['valid' => false, 'message' => 'Lote no encontrado'];
        }

        // Si el lote está vendido y tiene amortización activa, no se puede cambiar estado
        if ($lote['estado'] === 'vendido' && $lote['amortizacion_activa'] > 0) {
            if ($nuevoEstado !== 'vendido') {
                return ['valid' => false, 'message' => 'No se puede cambiar el estado de un lote vendido con amortización activa'];
            }
        }

        // Nota: La validación de cliente se hace en el controlador al procesar el POST
        // para permitir asignar cliente y estado vendido simultáneamente

        return ['valid' => true];
    }

    /**
     * Actualiza los campos de amortización del lote
     */
    public function updateAmortizacionFields($id, $data)
    {
        $sql = "UPDATE lotes SET 
                cuota_inicial = ?,
                monto_financiado = ?,
                tasa_interes = ?,
                numero_cuotas = ?,
                fecha_inicio_amortizacion = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['cuota_inicial'] ?? null,
            $data['monto_financiado'] ?? null,
            $data['tasa_interes'] ?? null,
            $data['numero_cuotas'] ?? null,
            $data['fecha_inicio_amortizacion'] ?? null,
            $id
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Obtiene todos los lotes de un cliente
     */
    public function getByCliente($clienteId)
    {
        $sql = "SELECT l.*, 
                       p.nombre as proyecto_nombre, 
                       p.codigo as proyecto_codigo,
                       (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id) as tiene_amortizacion,
                       (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id AND estado IN ('pendiente', 'pagada')) as amortizacion_activa
                FROM lotes l 
                INNER JOIN proyectos p ON l.proyecto_id = p.id 
                WHERE l.cliente_id = ? 
                ORDER BY l.fecha_venta DESC, l.created_at DESC";
        
        return $this->db->fetchAll($sql, [$clienteId]);
    }

    /**
     * Obtiene el saldo a favor de un lote
     * @param int $loteId ID del lote
     * @return float Saldo a favor disponible
     */
    public function getSaldoAFavor($loteId)
    {
        $sql = "SELECT saldo_a_favor FROM lotes WHERE id = ?";
        $result = $this->db->fetch($sql, [$loteId]);
        return (float) ($result['saldo_a_favor'] ?? 0);
    }

    /**
     * Actualiza el saldo a favor de un lote
     * @param int $loteId ID del lote
     * @param float $monto Nuevo monto de saldo a favor
     * @return bool Resultado de la actualización
     */
    public function setSaldoAFavor($loteId, $monto)
    {
        $monto = max(0, (float)$monto); // Asegurar que no sea negativo
        $sql = "UPDATE lotes SET saldo_a_favor = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$monto, $loteId]);
    }

    /**
     * Incrementa el saldo a favor de un lote (suma un monto)
     * @param int $loteId ID del lote
     * @param float $monto Monto a sumar al saldo a favor
     * @return bool Resultado de la actualización
     */
    public function incrementarSaldoAFavor($loteId, $monto)
    {
        $monto = (float)$monto;
        if ($monto == 0) return true; // No hacer nada si es cero
        
        $sql = "UPDATE lotes SET 
                saldo_a_favor = saldo_a_favor + ?,
                updated_at = NOW() 
                WHERE id = ?";
        return $this->db->execute($sql, [$monto, $loteId]);
    }

    /**
     * Decrementa el saldo a favor de un lote (resta un monto)
     * @param int $loteId ID del lote
     * @param float $monto Monto a restar del saldo a favor
     * @return bool Resultado de la actualización
     */
    public function decrementarSaldoAFavor($loteId, $monto)
    {
        $monto = (float)$monto;
        if ($monto == 0) return true;
        
        $sql = "UPDATE lotes SET 
                saldo_a_favor = GREATEST(0, saldo_a_favor - ?),
                updated_at = NOW() 
                WHERE id = ?";
        return $this->db->execute($sql, [$monto, $loteId]);
    }

    /**
     * Obtiene lotes con saldo a favor disponible
     * @param float $minimoSaldo Monto mínimo de saldo a favor
     * @return array Lotes con saldo a favor > minimoSaldo
     */
    public function getLotesConSaldoAFavor($minimoSaldo = 0.01)
    {
        $sql = "SELECT l.*, 
                       p.nombre as proyecto_nombre,
                       c.nombre as cliente_nombre
                FROM lotes l 
                INNER JOIN proyectos p ON l.proyecto_id = p.id 
                LEFT JOIN clientes c ON l.cliente_id = c.id 
                WHERE l.saldo_a_favor > ? 
                ORDER BY l.saldo_a_favor DESC, l.updated_at DESC";
        
        return $this->db->fetchAll($sql, [$minimoSaldo]);
    }
}
