<?php
/**
 * Importador de Schema - Método directo por tabla
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

echo "Creando estructura de base de datos...\n\n";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Conectado a la base de datos\n\n";
    
    // Array de todas las sentencias SQL
    $queries = [];
    
    // 1. Tabla proyectos
    $queries[] = "CREATE TABLE IF NOT EXISTS proyectos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) UNIQUE NOT NULL,
        nombre VARCHAR(200) NOT NULL,
        ubicacion VARCHAR(255),
        descripcion TEXT,
        total_lotes INT UNSIGNED DEFAULT 0,
        estado ENUM('activo', 'completado', 'pausado', 'cancelado') DEFAULT 'activo',
        fecha_inicio DATE,
        fecha_finalizacion DATE,
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_estado (estado),
        INDEX idx_codigo (codigo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 2. Tabla clientes
    $queries[] = "CREATE TABLE IF NOT EXISTS clientes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tipo_documento ENUM('CC', 'NIT', 'CE', 'pasaporte') DEFAULT 'CC',
        numero_documento VARCHAR(50) UNIQUE NOT NULL,
        nombre VARCHAR(200) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(100),
        direccion VARCHAR(255),
        ciudad VARCHAR(100),
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_documento (tipo_documento, numero_documento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 3. Tabla lotes
    $queries[] = "CREATE TABLE IF NOT EXISTS lotes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        proyecto_id INT UNSIGNED NOT NULL,
        codigo_lote VARCHAR(50) NOT NULL,
        manzana VARCHAR(20),
        area_m2 DECIMAL(10,2),
        precio_lista DECIMAL(15,2) NOT NULL,
        precio_venta DECIMAL(15,2),
        fecha_venta DATE,
        cliente_id INT UNSIGNED,
        estado ENUM('disponible', 'vendido', 'reservado', 'bloqueado') DEFAULT 'disponible',
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE RESTRICT,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
        UNIQUE KEY unique_lote_proyecto (proyecto_id, codigo_lote),
        INDEX idx_proyecto (proyecto_id),
        INDEX idx_cliente (cliente_id),
        INDEX idx_estado (estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 4. Tabla amortizaciones
    $queries[] = "CREATE TABLE IF NOT EXISTS amortizaciones (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lote_id INT UNSIGNED NOT NULL,
        numero_cuota INT UNSIGNED NOT NULL,
        valor_cuota DECIMAL(15,2) NOT NULL,
        valor_pagado DECIMAL(15,2) DEFAULT 0,
        saldo_pendiente DECIMAL(15,2) GENERATED ALWAYS AS (valor_cuota - valor_pagado) STORED,
        fecha_vencimiento DATE NOT NULL,
        fecha_pago DATE,
        dias_mora INT DEFAULT 0,
        estado ENUM('pendiente', 'pagada', 'cancelada') DEFAULT 'pendiente',
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE RESTRICT,
        INDEX idx_lote (lote_id),
        INDEX idx_estado (estado),
        INDEX idx_vencimiento (fecha_vencimiento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 5. Tabla pagos
    $queries[] = "CREATE TABLE IF NOT EXISTS pagos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        amortizacion_id INT UNSIGNED NOT NULL,
        valor_pagado DECIMAL(15,2) NOT NULL,
        metodo_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta', 'otro') DEFAULT 'efectivo',
        fecha_pago DATE NOT NULL,
        numero_recibo VARCHAR(100),
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (amortizacion_id) REFERENCES amortizaciones(id) ON DELETE RESTRICT,
        INDEX idx_amortizacion (amortizacion_id),
        INDEX idx_fecha_pago (fecha_pago)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // 6. Vista de proyectos
    $queries[] = "CREATE OR REPLACE VIEW vista_proyectos_resumen AS
    SELECT 
        p.id,
        p.codigo,
        p.nombre,
        p.ubicacion,
        p.estado,
        p.total_lotes,
        COUNT(DISTINCT CASE WHEN l.estado = 'disponible' THEN l.id END) as lotes_disponibles,
        COUNT(DISTINCT CASE WHEN l.estado = 'vendido' THEN l.id END) as lotes_vendidos,
        COUNT(DISTINCT CASE WHEN l.estado = 'reservado' THEN l.id END) as lotes_reservados,
        COUNT(DISTINCT CASE WHEN l.estado = 'bloqueado' THEN l.id END) as lotes_bloqueados,
        SUM(CASE WHEN l.estado IN ('disponible', 'reservado') THEN l.precio_lista ELSE 0 END) as valor_inventario,
        SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) ELSE 0 END) as valor_ventas
    FROM proyectos p
    LEFT JOIN lotes l ON p.id = l.proyecto_id
    GROUP BY p.id, p.codigo, p.nombre, p.ubicacion, p.estado, p.total_lotes";
    
    // Ejecutar todas las queries
    foreach ($queries as $index => $query) {
        try {
            $pdo->exec($query);
            
            // Detectar qué se creó
            if (stripos($query, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?IF NOT EXISTS\s+(\w+)/i', $query, $matches);
                echo "  ✓ Tabla: " . ($matches[1] ?? 'desconocida') . "\n";
            } elseif (stripos($query, 'CREATE.*VIEW') !== false) {
                echo "  ✓ Vista: vista_proyectos_resumen\n";
            }
            
        } catch (PDOException $e) {
            if (stripos($e->getMessage(), 'already exists') === false) {
                echo "  ✗ Error en query " . ($index + 1) . ": " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ Estructura de base de datos creada\n\n";
    
    // Verificar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tablas en la base de datos:\n";
    foreach ($tables as $table) {
        echo "  • $table\n";
    }
    
    echo "\n✓ ¡Listo! Ahora puedes usar el sistema.\n";
    echo "   Accede a: http://127.0.0.1:8008/\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
