<?php

namespace App\Models;

/**
 * AuthModel - Modelo de autenticación
 * Maneja todas las operaciones relacionadas con usuarios y autenticación
 */
class AuthModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Busca un usuario por email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ? AND activo = 1 LIMIT 1";
        return $this->db->fetch($sql, [$email]);
    }

    /**
     * Busca un usuario por ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ? AND activo = 1 LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Crea un nuevo usuario
     */
    public function create($data)
    {
        $sql = "INSERT INTO users (email, password, nombre, rol) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['email'],
            $data['password'],
            $data['nombre'],
            $data['rol'] ?? 'usuario' // Por defecto rol Usuario
        ];

        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Actualiza la contraseña de un usuario
     */
    public function updatePassword($userId, $newPasswordHash)
    {
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$newPasswordHash, $userId]);
    }

    /**
     * Genera y guarda un token de recuperación
     */
    public function createResetToken($email)
    {
        $token = generateToken(32);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?";
        $this->db->execute($sql, [$token, $expires, $email]);

        return $token;
    }

    /**
     * Valida un token de recuperación
     */
    public function validateResetToken($token)
    {
        $sql = "SELECT * FROM users 
                WHERE reset_token = ? 
                AND reset_token_expires > NOW() 
                AND activo = 1 
                LIMIT 1";
        
        return $this->db->fetch($sql, [$token]);
    }

    /**
     * Restablece la contraseña usando un token
     */
    public function resetPassword($token, $newPasswordHash)
    {
        // Primero validar el token
        $user = $this->validateResetToken($token);
        
        if (!$user) {
            return false;
        }

        // Actualizar contraseña y limpiar token
        $sql = "UPDATE users 
                SET password = ?, 
                    reset_token = NULL, 
                    reset_token_expires = NULL,
                    updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [$newPasswordHash, $user['id']]) > 0;
    }

    /**
     * Verifica si un email ya existe
     */
    public function emailExists($email, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?";
            $result = $this->db->fetch($sql, [$email, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
            $result = $this->db->fetch($sql, [$email]);
        }

        return $result['count'] > 0;
    }

    /**
     * Actualiza el último inicio de sesión
     */
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE users SET updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$userId]);
    }
}
