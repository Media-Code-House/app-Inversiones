<?php

namespace App\Models;

use Database;

/**
 * UserModel - Gestión de usuarios del sistema
 */
class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca un usuario por ID
     */
    public function findById($id)
    {
        $sql = "SELECT id, email, nombre, rol, activo, created_at, updated_at 
                FROM users 
                WHERE id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Busca un usuario por email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT id, email, nombre, rol, activo, created_at, updated_at 
                FROM users 
                WHERE email = ?";
        
        return $this->db->fetch($sql, [$email]);
    }

    /**
     * Obtiene un usuario con contraseña (para autenticación)
     */
    public function findByEmailWithPassword($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    /**
     * Actualiza los datos básicos de un usuario
     */
    public function update($id, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->execute($sql, $values);
    }

    /**
     * Actualiza la contraseña de un usuario
     */
    public function updatePassword($id, $passwordHash)
    {
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$passwordHash, $id]);
    }

    /**
     * Crea un nuevo usuario
     */
    public function create($data)
    {
        $sql = "INSERT INTO users (email, password, nombre, rol, activo) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['email'],
            $data['password'],
            $data['nombre'],
            $data['rol'] ?? 'consulta',
            $data['activo'] ?? 1
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Obtiene todos los usuarios
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT id, email, nombre, rol, activo, created_at, updated_at 
                FROM users 
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['rol'])) {
            $sql .= " AND rol = ?";
            $params[] = $filters['rol'];
        }

        if (isset($filters['activo'])) {
            $sql .= " AND activo = ?";
            $params[] = $filters['activo'];
        }

        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
}
