-- ===================================================================
-- SCRIPT PARA CREAR USUARIOS DE PRUEBA RBAC
-- ===================================================================
-- Ejecutar en phpMyAdmin o línea de comandos MySQL
-- Este script crea 3 usuarios para probar el sistema RBAC
-- ===================================================================

USE `inversiones_db`;

-- ===================================================================
-- 1. USUARIO ADMINISTRADOR (ya existe)
-- ===================================================================
-- Email: admin@sistema.com
-- Password: Admin123
-- Este usuario YA existe en tu base de datos

-- ===================================================================
-- 2. USUARIO CONSULTA
-- ===================================================================
-- Email: consulta@sistema.com
-- Password: Consulta123

INSERT INTO `users` (`email`, `password`, `nombre`, `rol`, `activo`) VALUES
('consulta@sistema.com', '$2y$12$37tBHKBBuHq5jxfwbuxiie5eYHlfvUgnI.XeWzyYiRzEQTSKyAxzi', 'Usuario Consulta', 'consulta', 1);

-- ===================================================================
-- 3. USUARIO VENDEDOR
-- ===================================================================
-- Email: vendedor@sistema.com
-- Password: Vendedor123

INSERT INTO `users` (`email`, `password`, `nombre`, `rol`, `activo`) VALUES
('vendedor@sistema.com', '$2y$12$qvoj5UqVT8t9Ux6/JCFgjuNd4FncLCEqmvMd44jsWrsAchteXN3zK', 'María Vendedor', 'vendedor', 1);

-- ===================================================================
-- IMPORTANTE: Crear registro en tabla vendedores para el usuario vendedor
-- ===================================================================

-- Primero obtener el ID del usuario vendedor recién creado
SET @vendedor_user_id = LAST_INSERT_ID();

-- Crear registro en tabla vendedores
INSERT INTO `vendedores` (
    `user_id`,
    `codigo_vendedor`,
    `tipo_documento`,
    `numero_documento`,
    `nombres`,
    `apellidos`,
    `email`,
    `celular`,
    `fecha_nacimiento`,
    `direccion`,
    `ciudad`,
    `fecha_ingreso`,
    `tipo_contrato`,
    `porcentaje_comision_default`,
    `estado`
) VALUES (
    @vendedor_user_id,
    'V001',
    'CC',
    '1234567890',
    'María',
    'Vendedor',
    'vendedor@sistema.com',
    '3001234567',
    '1990-01-15',
    'Calle 123 #45-67',
    'Bogotá',
    CURDATE(),
    'indefinido',
    3.00,
    'activo'
);

-- ===================================================================
-- VERIFICACIÓN
-- ===================================================================

SELECT 
    u.id,
    u.email,
    u.nombre,
    u.rol,
    u.activo,
    v.codigo_vendedor,
    CONCAT(v.nombres, ' ', v.apellidos) as nombre_vendedor
FROM users u
LEFT JOIN vendedores v ON u.id = v.user_id
ORDER BY u.id;

-- ===================================================================
-- RESULTADO ESPERADO:
-- ===================================================================
-- ID | Email                    | Nombre           | Rol           | Activo | Codigo | Nombre Vendedor
-- 1  | admin@sistema.com        | Administrador    | administrador | 1      | NULL   | NULL
-- 2  | consulta@sistema.com     | Usuario Consulta | consulta      | 1      | NULL   | NULL
-- 3  | vendedor@sistema.com     | María Vendedor   | vendedor      | 1      | V001   | María Vendedor
-- ===================================================================
