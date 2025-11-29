<?php
/**
 * Instalador de Tablas para Producción
 * Crea las tablas del Módulo 3 en el servidor de hosting
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Instalador de Tablas</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:800px;margin:0 auto;}";
echo "h1{color:#007BFF;}pre{background:#fff;padding:15px;border-radius:5px;overflow:auto;}";
echo ".success{color:#28A745;}.error{color:#DC3545;}.warning{color:#FFC107;}</style></head><body>";

echo "<h1>🚀 Instalador de Tablas - Módulo 3</h1>";
echo "<p>Creando estructura de base de datos en producción...</p><hr>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p class='success'>✓ Conectado a la base de datos: <strong>" . DB_NAME . "</strong></p>";
    
    // Verificar tablas existentes
    echo "<h2>📊 Verificando tablas existentes...</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tablasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tablas encontradas: <strong>" . count($tablasExistentes) . "</strong></p>";
    echo "<pre>" . implode("\n", $tablasExistentes) . "</pre>";
    
    // Definir queries
    $queries = [
        // Tabla proyectos
        "proyectos" => "CREATE TABLE IF NOT EXISTS proyectos (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla clientes
        "clientes" => "CREATE TABLE IF NOT EXISTS clientes (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla lotes
        "lotes" => "CREATE TABLE IF NOT EXISTS lotes (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla amortizaciones
        "amortizaciones" => "CREATE TABLE IF NOT EXISTS amortizaciones (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla pagos
        "pagos" => "CREATE TABLE IF NOT EXISTS pagos (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    echo "<h2>🔨 Creando tablas...</h2>";
    $creadas = 0;
    $existentes = 0;
    $errores = 0;
    
    foreach ($queries as $nombreTabla => $query) {
        try {
            $pdo->exec($query);
            if (!in_array($nombreTabla, $tablasExistentes)) {
                echo "<p class='success'>✓ Tabla <strong>$nombreTabla</strong> creada</p>";
                $creadas++;
            } else {
                echo "<p class='warning'>⚠ Tabla <strong>$nombreTabla</strong> ya existía</p>";
                $existentes++;
            }
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Error en tabla <strong>$nombreTabla</strong>: " . $e->getMessage() . "</p>";
            $errores++;
        }
    }
    
    // Crear vista
    echo "<h2>👁 Creando vista...</h2>";
    try {
        $vistaSQL = "CREATE OR REPLACE VIEW vista_proyectos_resumen AS
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
        
        $pdo->exec($vistaSQL);
        echo "<p class='success'>✓ Vista <strong>vista_proyectos_resumen</strong> creada</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Error al crear vista: " . $e->getMessage() . "</p>";
    }
    
    // Actualizar tabla users si es necesaria
    echo "<h2>👤 Verificando estructura de tabla users...</h2>";
    $stmt = $pdo->query("DESCRIBE users");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $necesitaActualizacion = false;
    
    if (in_array('is_active', $columnas)) {
        echo "<p class='warning'>⚠ La tabla users tiene estructura antigua (is_active)</p>";
        echo "<p class='warning'>⚠ Se necesita actualizar a nueva estructura (activo)</p>";
        $necesitaActualizacion = true;
    } else if (in_array('activo', $columnas)) {
        echo "<p class='success'>✓ La tabla users tiene estructura correcta</p>";
    }
    
    if ($necesitaActualizacion) {
        echo "<p class='warning'>⚠ Por favor ejecuta <strong>recrear_users.php</strong> en producción</p>";
    }
    
    // Resumen final
    echo "<hr><h2>📋 Resumen de Instalación</h2>";
    echo "<ul>";
    echo "<li>Tablas creadas: <strong class='success'>$creadas</strong></li>";
    echo "<li>Tablas ya existentes: <strong class='warning'>$existentes</strong></li>";
    echo "<li>Errores: <strong class='" . ($errores > 0 ? 'error' : 'success') . "'>$errores</strong></li>";
    echo "</ul>";
    
    // Verificar resultado final
    $stmt = $pdo->query("SHOW TABLES");
    $tablasFinales = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>✅ Tablas Actuales en la Base de Datos</h2>";
    echo "<pre>";
    foreach ($tablasFinales as $tabla) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• $tabla ({$result['total']} registros)\n";
    }
    echo "</pre>";
    
    if ($errores == 0) {
        echo "<p class='success' style='font-size:1.2em;'><strong>🎉 ¡INSTALACIÓN COMPLETADA CON ÉXITO!</strong></p>";
        echo "<p>Ahora puedes acceder al dashboard: <a href='/dashboard'>ir al dashboard</a></p>";
    } else {
        echo "<p class='error' style='font-size:1.2em;'><strong>⚠ Instalación completada con errores</strong></p>";
        echo "<p>Revisa los errores anteriores y corrige los problemas.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'><strong>✗ Error Fatal:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
