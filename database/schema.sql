-- ==========================================
-- SCRIPT DDL: Sistema de Gestión de Lotes
-- Versión: 2.0 - Módulo 3
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
-- TABLA: proyectos
-- Gestiona los proyectos inmobiliarios
-- ==========================================

CREATE TABLE IF NOT EXISTS proyectos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Código único del proyecto',
    nombre VARCHAR(150) NOT NULL,
    ubicacion VARCHAR(255) NULL,
    descripcion TEXT NULL,
    estado ENUM('activo', 'pausado', 'finalizado') NOT NULL DEFAULT 'activo',
    fecha_inicio DATE NULL,
    fecha_finalizacion DATE NULL,
    total_lotes INT UNSIGNED DEFAULT 0 COMMENT 'Total de lotes en el proyecto',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_codigo (codigo),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: clientes
-- Gestiona la información de clientes
-- ==========================================

CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_documento ENUM('CC', 'CE', 'NIT', 'TI', 'PASAPORTE') NOT NULL DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL UNIQUE COMMENT 'Cédula u otro documento',
    nombre_completo VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    direccion VARCHAR(255) NULL,
    ciudad VARCHAR(100) NULL,
    departamento VARCHAR(100) NULL,
    fecha_nacimiento DATE NULL,
    estado_civil ENUM('soltero', 'casado', 'union_libre', 'viudo', 'divorciado') NULL,
    ocupacion VARCHAR(100) NULL,
    observaciones TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_documento (numero_documento),
    INDEX idx_nombre (nombre_completo),
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: lotes
-- Gestiona los lotes de cada proyecto
-- ==========================================

CREATE TABLE IF NOT EXISTS lotes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id INT UNSIGNED NOT NULL,
    codigo_lote VARCHAR(20) NOT NULL COMMENT 'Código del lote dentro del proyecto',
    manzana VARCHAR(10) NULL COMMENT 'Manzana o sector',
    area_m2 DECIMAL(10,2) NOT NULL COMMENT 'Área en metros cuadrados',
    precio_lista DECIMAL(15,2) NOT NULL COMMENT 'Precio de lista del lote',
    precio_venta DECIMAL(15,2) NULL COMMENT 'Precio real de venta (si difiere)',
    estado ENUM('disponible', 'reservado', 'vendido', 'bloqueado') NOT NULL DEFAULT 'disponible',
    cliente_id INT UNSIGNED NULL COMMENT 'Cliente dueño (si está vendido)',
    vendedor_id INT UNSIGNED NULL COMMENT 'Usuario vendedor',
    fecha_venta DATETIME NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (vendedor_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uk_proyecto_codigo (proyecto_id, codigo_lote),
    INDEX idx_estado (estado),
    INDEX idx_proyecto (proyecto_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_vendedor (vendedor_id),
    INDEX idx_fecha_venta (fecha_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: amortizaciones
-- Plan de pagos para cada venta de lote
-- ==========================================

CREATE TABLE IF NOT EXISTS amortizaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lote_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    numero_cuota SMALLINT UNSIGNED NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    valor_cuota DECIMAL(15,2) NOT NULL,
    saldo_pendiente DECIMAL(15,2) NOT NULL DEFAULT 0,
    estado_pago ENUM('pendiente', 'pagada', 'mora', 'cancelada') NOT NULL DEFAULT 'pendiente',
    dias_mora INT UNSIGNED DEFAULT 0,
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_lote (lote_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado_pago),
    INDEX idx_fecha_vencimiento (fecha_vencimiento),
    INDEX idx_mora (dias_mora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: pagos
-- Registro de pagos realizados
-- ==========================================

CREATE TABLE IF NOT EXISTS pagos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amortizacion_id INT UNSIGNED NOT NULL,
    lote_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    fecha_pago DATETIME NOT NULL,
    valor_pagado DECIMAL(15,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta', 'otro') NOT NULL DEFAULT 'efectivo',
    numero_recibo VARCHAR(50) NULL COMMENT 'Número de recibo o comprobante',
    observaciones TEXT NULL,
    registrado_por INT UNSIGNED NULL COMMENT 'Usuario que registró el pago',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (amortizacion_id) REFERENCES amortizaciones(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_amortizacion (amortizacion_id),
    INDEX idx_lote (lote_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha_pago (fecha_pago),
    INDEX idx_recibo (numero_recibo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- DATOS INICIALES
-- ==========================================

-- Usuario administrador por defecto
-- Password: admin123
INSERT INTO users (email, password_hash, nombre, rol_id) VALUES
('admin@sistema.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyKqr0j4i9Uq', 'Administrador', 3)
ON DUPLICATE KEY UPDATE email = email;

-- ==========================================
-- TRIGGERS Y FUNCIONES
-- ==========================================

-- Trigger: Actualizar total_lotes al insertar/actualizar/eliminar lotes
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS after_lote_insert
AFTER INSERT ON lotes
FOR EACH ROW
BEGIN
    UPDATE proyectos 
    SET total_lotes = (SELECT COUNT(*) FROM lotes WHERE proyecto_id = NEW.proyecto_id)
    WHERE id = NEW.proyecto_id;
END$$

CREATE TRIGGER IF NOT EXISTS after_lote_delete
AFTER DELETE ON lotes
FOR EACH ROW
BEGIN
    UPDATE proyectos 
    SET total_lotes = (SELECT COUNT(*) FROM lotes WHERE proyecto_id = OLD.proyecto_id)
    WHERE id = OLD.proyecto_id;
END$$

-- Trigger: Actualizar días de mora en amortizaciones
CREATE TRIGGER IF NOT EXISTS before_amortizacion_update
BEFORE UPDATE ON amortizaciones
FOR EACH ROW
BEGIN
    IF NEW.estado_pago = 'mora' THEN
        SET NEW.dias_mora = DATEDIFF(CURDATE(), NEW.fecha_vencimiento);
    END IF;
END$$

DELIMITER ;

-- ==========================================
-- VISTAS ÚTILES
-- ==========================================

-- Vista: Resumen de proyectos con estadísticas
CREATE OR REPLACE VIEW vista_proyectos_resumen AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.estado,
    p.total_lotes,
    COUNT(DISTINCT CASE WHEN l.estado = 'disponible' THEN l.id END) as lotes_disponibles,
    COUNT(DISTINCT CASE WHEN l.estado = 'vendido' THEN l.id END) as lotes_vendidos,
    COUNT(DISTINCT CASE WHEN l.estado = 'reservado' THEN l.id END) as lotes_reservados,
    SUM(CASE WHEN l.estado IN ('disponible', 'reservado') THEN l.precio_lista ELSE 0 END) as valor_inventario,
    SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) ELSE 0 END) as valor_ventas
FROM proyectos p
LEFT JOIN lotes l ON p.id = l.proyecto_id
WHERE p.estado = 'activo'
GROUP BY p.id, p.codigo, p.nombre, p.estado, p.total_lotes;

-- ==========================================
-- NOTAS FINALES
-- ==========================================
-- Integridad Referencial: ON DELETE RESTRICT previene eliminación accidental
-- Índices: Optimizan consultas frecuentes
-- Triggers: Mantienen consistencia automática
-- Vistas: Simplifican consultas complejas
-- ==========================================
