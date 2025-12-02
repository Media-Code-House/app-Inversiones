-- =====================================================
-- SCRIPT: Crear Vendedor de Prueba para Perfil Robustecido
-- Fecha: 2 de diciembre de 2025
-- Propósito: Insertar registro de vendedor para user_id=4 (vendedor@sistema.com)
--           para probar la funcionalidad completa de la Tarjeta 3 en /perfil
-- =====================================================

-- Verificar que el usuario existe antes de insertar
SELECT 'Verificando usuario vendedor@sistema.com...' AS mensaje;

-- Insertar registro de vendedor para user_id=4 (vendedor@sistema.com)
-- Solo se insertará si no existe ya un registro para este user_id
INSERT INTO vendedores (
    user_id,                    -- Relación con tabla users
    codigo_vendedor,            -- Código único del vendedor
    tipo_documento,
    numero_documento,           -- Documento de identidad
    nombres,
    apellidos,
    telefono,
    celular,                    -- Celular corporativo (obligatorio)
    email,
    direccion,
    ciudad,
    fecha_ingreso,              -- Fecha de ingreso a la empresa
    fecha_salida,               -- NULL si está activo
    tipo_contrato,
    porcentaje_comision_default,-- Porcentaje de comisión
    banco,                      -- Banco para pagos
    tipo_cuenta,
    numero_cuenta,
    estado,                     -- activo, inactivo, suspendido
    observaciones,
    foto_perfil
)
SELECT
    4,                          -- user_id de vendedor@sistema.com
    'VEND-0002',                -- Código único del vendedor
    'CC',
    '987654321',                -- Número de documento
    'María',                    -- Nombres
    'Vendedora González',       -- Apellidos
    '6012345678',               -- Teléfono fijo
    '+57 300 123 4567',         -- Celular corporativo
    'vendedor@sistema.com',     -- Email
    'Calle 100 #20-30, Apto 501', -- Dirección
    'Bogotá',                   -- Ciudad
    '2024-01-15',               -- Fecha de ingreso
    NULL,                       -- No ha salido de la empresa
    'indefinido',               -- Tipo de contrato
    5.00,                       -- 5% de comisión
    'Bancolombia',              -- Banco
    'ahorros',                  -- Tipo de cuenta
    '12345678901234',           -- Número de cuenta
    'activo',                   -- Estado activo
    'Vendedora con excelente desempeño comercial. Especializada en lotes residenciales.',
    NULL                        -- Sin foto de perfil
WHERE NOT EXISTS (
    SELECT 1 FROM vendedores WHERE user_id = 4
);

-- Verificar inserción
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Vendedor creado exitosamente'
        ELSE 'El vendedor ya existía o no se pudo crear'
    END AS resultado
FROM vendedores 
WHERE user_id = 4;

-- Mostrar datos del vendedor creado
SELECT 
    v.id,
    v.codigo_vendedor,
    v.user_id,
    u.email,
    u.nombre AS nombre_usuario,
    u.rol,
    CONCAT(v.nombres, ' ', v.apellidos) AS nombre_completo_vendedor,
    v.celular,
    v.ciudad,
    v.fecha_ingreso,
    v.porcentaje_comision_default,
    v.estado,
    v.banco,
    v.tipo_cuenta,
    v.numero_cuenta
FROM vendedores v
INNER JOIN users u ON v.user_id = u.id
WHERE v.user_id = 4;

-- =====================================================
-- NOTAS IMPORTANTES:
-- =====================================================
-- 1. Este vendedor se asocia al usuario con email: vendedor@sistema.com
-- 2. Password del usuario: rbac2024 (ya hasheado en BD)
-- 3. Este registro permite que el usuario con rol 'vendedor' pueda:
--    - Ver la Tarjeta 3 (Datos de Vendedor) en /perfil
--    - Actualizar sus datos de contacto corporativo
--    - Visualizar su información de comisiones
-- 4. La consulta JOIN en PerfilController@index() traerá estos datos
-- 5. El estado 'activo' permite que aparezca en reportes y estadísticas
-- =====================================================

-- Resumen final
SELECT 
    COUNT(*) AS total_vendedores_activos,
    COUNT(CASE WHEN u.rol = 'vendedor' THEN 1 END) AS usuarios_vendedor,
    COUNT(CASE WHEN u.rol = 'administrador' THEN 1 END) AS usuarios_admin
FROM vendedores v
INNER JOIN users u ON v.user_id = u.id
WHERE v.estado = 'activo';
