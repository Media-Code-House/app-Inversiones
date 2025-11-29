-- ==========================================
-- SCRIPT DDL: Sistema de Gestión de Lotes
-- ==========================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sistema_lotes
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE sistema_lotes;

-- ==========================================
-- TABLA: users
-- Gestiona los usuarios del sistema
-- ==========================================

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol_id TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=Usuario, 2=Vendedor, 3=Admin',
    reset_token VARCHAR(64) NULL COMMENT 'Token para recuperación de contraseña',
    reset_token_expires DATETIME NULL COMMENT 'Fecha de expiración del token',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Estado del usuario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol_id),
    INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- DATOS INICIALES
-- ==========================================

-- Insertar usuario administrador por defecto
-- Password: admin123
INSERT INTO users (email, password_hash, nombre, rol_id) VALUES
('admin@sistema.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyKqr0j4i9Uq', 'Administrador', 3);

-- ==========================================
-- NOTAS:
-- - Los índices mejoran el rendimiento de búsquedas
-- - El campo reset_token se usa para recuperación de contraseña
-- - El sistema de roles es extensible
-- ==========================================
