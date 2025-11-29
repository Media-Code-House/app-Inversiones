-- ==========================================
-- INSTRUCCIONES DE INSTALACIÓN
-- ==========================================

-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS sistema_lotes
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

-- 2. Seleccionar la base de datos
USE sistema_lotes;

-- 3. Ejecutar el script principal
SOURCE schema.sql;

-- 4. Verificar que la tabla se creó correctamente
SHOW TABLES;

-- 5. Verificar el usuario administrador
SELECT * FROM users WHERE rol_id = 3;

-- ==========================================
-- CREDENCIALES POR DEFECTO
-- ==========================================
-- Email: admin@sistema.com
-- Contraseña: admin123
-- ==========================================

-- Para cambiar la contraseña del administrador:
-- UPDATE users SET password_hash = '$2y$12$TU_NUEVO_HASH' WHERE email = 'admin@sistema.com';

-- Para generar un nuevo hash de contraseña en PHP:
-- password_hash('tu_contraseña', PASSWORD_BCRYPT, ['cost' => 12]);
