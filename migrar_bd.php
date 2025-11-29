<?php
/**
 * Script de Migración de Base de Datos para Producción
 * Adapta la estructura actual a los modelos del Módulo 3
 * 
 * EJECUTAR DESDE: https://inversiones.mch.com.co/migrar_bd.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Migración de Base de Datos</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}";
echo "h1{color:#007BFF;}h2{color:#333;border-bottom:2px solid #007BFF;padding-bottom:10px;}";
echo "pre{background:#fff;padding:15px;border-radius:5px;overflow:auto;border-left:4px solid #007BFF;}";
echo ".success{color:#28A745;font-weight:bold;}.error{color:#DC3545;font-weight:bold;}";
echo ".warning{color:#FFC107;font-weight:bold;}.info{color:#17A2B8;}";
echo ".step{background:#fff;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #007BFF;}";
echo "</style></head><body>";

echo "<h1>🔧 Migración de Base de Datos - Módulo 3</h1>";
echo "<p class='info'>Este script adaptará la estructura actual de la BD para ser compatible con los modelos.</p>";
echo "<hr>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<div class='step'><span class='success'>✓</span> Conectado a: <strong>" . DB_NAME . "</strong></div>";
    
    $errores = 0;
    $advertencias = 0;
    $exitosos = 0;
    
    // ========================================
    // PASO 1: MODIFICAR TABLA USERS
    // ========================================
    echo "<h2>Paso 1: Modificar tabla USERS</h2>";
    
    $queries = [
        "Agregar columna 'rol'" => "ALTER TABLE `users` ADD COLUMN `rol` ENUM('administrador', 'vendedor', 'consulta') DEFAULT 'consulta' AFTER `nombre`",
        "Renombrar 'password_hash' a 'password'" => "ALTER TABLE `users` CHANGE COLUMN `password_hash` `password` VARCHAR(255) NOT NULL",
        "Renombrar 'is_active' a 'activo'" => "ALTER TABLE `users` CHANGE COLUMN `is_active` `activo` TINYINT(1) DEFAULT 1",
        "Renombrar 'reset_token_expires'" => "ALTER TABLE `users` CHANGE COLUMN `reset_token_expires` `reset_token_expira` DATETIME DEFAULT NULL",
        "Actualizar rol del admin" => "UPDATE `users` SET `rol` = 'administrador' WHERE `id` = 1",
        "Eliminar columna 'rol_id'" => "ALTER TABLE `users` DROP COLUMN `rol_id`"
    ];
    
    foreach ($queries as $desc => $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='step'><span class='success'>✓</span> $desc</div>";
            $exitosos++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Duplicate column") !== false || 
                strpos($e->getMessage(), "Can't DROP") !== false ||
                strpos($e->getMessage(), "Unknown column") !== false) {
                echo "<div class='step'><span class='warning'>⚠</span> $desc (ya aplicado o no necesario)</div>";
                $advertencias++;
            } else {
                echo "<div class='step'><span class='error'>✗</span> $desc: " . $e->getMessage() . "</div>";
                $errores++;
            }
        }
    }
    
    // ========================================
    // PASO 2: MODIFICAR TABLA CLIENTES
    // ========================================
    echo "<h2>Paso 2: Modificar tabla CLIENTES</h2>";
    
    $queries = [
        "Renombrar 'nombre_completo' a 'nombre'" => "ALTER TABLE `clientes` CHANGE COLUMN `nombre_completo` `nombre` VARCHAR(200) NOT NULL",
        "Ajustar tipo_documento" => "ALTER TABLE `clientes` MODIFY COLUMN `tipo_documento` ENUM('CC', 'NIT', 'CE', 'pasaporte') DEFAULT 'CC'",
        "Eliminar 'departamento'" => "ALTER TABLE `clientes` DROP COLUMN `departamento`",
        "Eliminar 'fecha_nacimiento'" => "ALTER TABLE `clientes` DROP COLUMN `fecha_nacimiento`",
        "Eliminar 'estado_civil'" => "ALTER TABLE `clientes` DROP COLUMN `estado_civil`",
        "Eliminar 'ocupacion'" => "ALTER TABLE `clientes` DROP COLUMN `ocupacion`",
        "Eliminar 'is_active'" => "ALTER TABLE `clientes` DROP COLUMN `is_active`"
    ];
    
    foreach ($queries as $desc => $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='step'><span class='success'>✓</span> $desc</div>";
            $exitosos++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Can't DROP") !== false || 
                strpos($e->getMessage(), "Unknown column") !== false ||
                strpos($e->getMessage(), "Duplicate column") !== false) {
                echo "<div class='step'><span class='warning'>⚠</span> $desc (ya aplicado)</div>";
                $advertencias++;
            } else {
                echo "<div class='step'><span class='error'>✗</span> $desc: " . $e->getMessage() . "</div>";
                $errores++;
            }
        }
    }
    
    // ========================================
    // PASO 3: MODIFICAR TABLA LOTES
    // ========================================
    echo "<h2>Paso 3: Modificar tabla LOTES</h2>";
    
    $queries = [
        "Eliminar FK lotes_ibfk_3" => "ALTER TABLE `lotes` DROP FOREIGN KEY `lotes_ibfk_3`",
        "Eliminar 'vendedor_id'" => "ALTER TABLE `lotes` DROP COLUMN `vendedor_id`",
        "Cambiar fecha_venta a DATE" => "ALTER TABLE `lotes` MODIFY COLUMN `fecha_venta` DATE DEFAULT NULL"
    ];
    
    foreach ($queries as $desc => $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='step'><span class='success'>✓</span> $desc</div>";
            $exitosos++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Can't DROP") !== false || 
                strpos($e->getMessage(), "Unknown column") !== false ||
                strpos($e->getMessage(), "check that column") !== false) {
                echo "<div class='step'><span class='warning'>⚠</span> $desc (ya aplicado)</div>";
                $advertencias++;
            } else {
                echo "<div class='step'><span class='error'>✗</span> $desc: " . $e->getMessage() . "</div>";
                $errores++;
            }
        }
    }
    
    // ========================================
    // PASO 4: MODIFICAR TABLA AMORTIZACIONES
    // ========================================
    echo "<h2>Paso 4: Modificar tabla AMORTIZACIONES</h2>";
    
    $queries = [
        "Agregar 'valor_pagado'" => "ALTER TABLE `amortizaciones` ADD COLUMN `valor_pagado` DECIMAL(15,2) DEFAULT 0 AFTER `valor_cuota`",
        "Agregar 'fecha_pago'" => "ALTER TABLE `amortizaciones` ADD COLUMN `fecha_pago` DATE DEFAULT NULL AFTER `fecha_vencimiento`",
        "Agregar 'estado'" => "ALTER TABLE `amortizaciones` ADD COLUMN `estado` ENUM('pendiente', 'pagada', 'cancelada') DEFAULT 'pendiente' AFTER `dias_mora`",
        "Eliminar FK cliente" => "ALTER TABLE `amortizaciones` DROP FOREIGN KEY `amortizaciones_ibfk_2`",
        "Eliminar 'cliente_id'" => "ALTER TABLE `amortizaciones` DROP COLUMN `cliente_id`",
        "Eliminar 'estado_pago'" => "ALTER TABLE `amortizaciones` DROP COLUMN `estado_pago`"
    ];
    
    foreach ($queries as $desc => $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='step'><span class='success'>✓</span> $desc</div>";
            $exitosos++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Duplicate column") !== false || 
                strpos($e->getMessage(), "Can't DROP") !== false ||
                strpos($e->getMessage(), "Unknown column") !== false ||
                strpos($e->getMessage(), "check that column") !== false) {
                echo "<div class='step'><span class='warning'>⚠</span> $desc (ya aplicado)</div>";
                $advertencias++;
            } else {
                echo "<div class='step'><span class='error'>✗</span> $desc: " . $e->getMessage() . "</div>";
                $errores++;
            }
        }
    }
    
    // ========================================
    // PASO 5: MODIFICAR TABLA PAGOS
    // ========================================
    echo "<h2>Paso 5: Modificar tabla PAGOS</h2>";
    
    $queries = [
        "Eliminar FK pagos_ibfk_2" => "ALTER TABLE `pagos` DROP FOREIGN KEY `pagos_ibfk_2`",
        "Eliminar FK pagos_ibfk_3" => "ALTER TABLE `pagos` DROP FOREIGN KEY `pagos_ibfk_3`",
        "Eliminar FK pagos_ibfk_4" => "ALTER TABLE `pagos` DROP FOREIGN KEY `pagos_ibfk_4`",
        "Eliminar 'lote_id'" => "ALTER TABLE `pagos` DROP COLUMN `lote_id`",
        "Eliminar 'cliente_id'" => "ALTER TABLE `pagos` DROP COLUMN `cliente_id`",
        "Eliminar 'registrado_por'" => "ALTER TABLE `pagos` DROP COLUMN `registrado_por`",
        "Cambiar fecha_pago a DATE" => "ALTER TABLE `pagos` MODIFY COLUMN `fecha_pago` DATE NOT NULL"
    ];
    
    foreach ($queries as $desc => $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='step'><span class='success'>✓</span> $desc</div>";
            $exitosos++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Can't DROP") !== false || 
                strpos($e->getMessage(), "Unknown column") !== false ||
                strpos($e->getMessage(), "check that column") !== false) {
                echo "<div class='step'><span class='warning'>⚠</span> $desc (ya aplicado)</div>";
                $advertencias++;
            } else {
                echo "<div class='step'><span class='error'>✗</span> $desc: " . $e->getMessage() . "</div>";
                $errores++;
            }
        }
    }
    
    // ========================================
    // PASO 6: MODIFICAR TABLA PROYECTOS
    // ========================================
    echo "<h2>Paso 6: Modificar tabla PROYECTOS</h2>";
    
    try {
        $pdo->exec("ALTER TABLE `proyectos` MODIFY COLUMN `estado` ENUM('activo', 'completado', 'pausado', 'cancelado') DEFAULT 'activo'");
        echo "<div class='step'><span class='success'>✓</span> Actualizar estados disponibles</div>";
        $exitosos++;
    } catch (PDOException $e) {
        echo "<div class='step'><span class='warning'>⚠</span> Estados: " . $e->getMessage() . "</div>";
        $advertencias++;
    }
    
    // ========================================
    // PASO 7: RECREAR VISTA
    // ========================================
    echo "<h2>Paso 7: Recrear VISTA</h2>";
    
    try {
        $pdo->exec("DROP VIEW IF EXISTS `vista_proyectos_resumen`");
        
        $vistaSQL = "CREATE VIEW `vista_proyectos_resumen` AS
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
        echo "<div class='step'><span class='success'>✓</span> Vista recreada correctamente</div>";
        $exitosos++;
    } catch (PDOException $e) {
        echo "<div class='step'><span class='error'>✗</span> Error al recrear vista: " . $e->getMessage() . "</div>";
        $errores++;
    }
    
    // ========================================
    // RESUMEN FINAL
    // ========================================
    echo "<hr><h2>📊 Resumen de Migración</h2>";
    echo "<pre>";
    echo "Operaciones exitosas: <span class='success'>$exitosos</span>\n";
    echo "Advertencias: <span class='warning'>$advertencias</span>\n";
    echo "Errores: <span class='" . ($errores > 0 ? 'error' : 'success') . "'>$errores</span>\n";
    echo "</pre>";
    
    // Verificar estructura final
    echo "<h2>✅ Estructura Final</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<pre>";
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• $tabla ({$result['total']} registros)\n";
    }
    echo "</pre>";
    
    if ($errores == 0) {
        echo "<div style='background:#D4EDDA;color:#155724;padding:20px;border-radius:5px;margin:20px 0;'>";
        echo "<h3 style='margin:0;'>🎉 ¡MIGRACIÓN COMPLETADA!</h3>";
        echo "<p>La base de datos ha sido actualizada y es compatible con los modelos del Módulo 3.</p>";
        echo "<p><strong>Próximo paso:</strong> <a href='/dashboard'>Acceder al Dashboard</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background:#F8D7DA;color:#721C24;padding:20px;border-radius:5px;margin:20px 0;'>";
        echo "<h3 style='margin:0;'>⚠ Migración con errores</h3>";
        echo "<p>Revisa los errores anteriores. Es posible que algunas operaciones requieran ajuste manual.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#F8D7DA;color:#721C24;padding:20px;border-radius:5px;'>";
    echo "<h3>✗ Error Fatal</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
