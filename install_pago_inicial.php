<?php
/**
 * Script de Instalación del Módulo de Pago Inicial Diferido
 * 
 * Este script ejecuta las migraciones necesarias para crear las tablas
 * y estructuras requeridas por el módulo de Pago Inicial Diferido.
 * 
 * IMPORTANTE: Ejecutar una sola vez en producción
 * 
 * Uso: php install_pago_inicial.php
 */

// Cargar configuración de base de datos
require_once __DIR__ . '/config/config.php';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  INSTALACIÓN: Módulo de Pago Inicial Diferido                ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

try {
    // Conectar a la base de datos
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Conexión a base de datos establecida\n";
    echo "  Base de datos: " . DB_NAME . "\n\n";

    // Verificar si ya existe la tabla pagos_iniciales
    $stmt = $pdo->query("SHOW TABLES LIKE 'pagos_iniciales'");
    if ($stmt->rowCount() > 0) {
        echo "⚠️  ADVERTENCIA: La tabla 'pagos_iniciales' ya existe\n";
        echo "   ¿Desea continuar? Esto podría causar errores si ya está instalado.\n";
        echo "   Presione ENTER para continuar o Ctrl+C para cancelar...\n";
        readline();
    }

    // Leer el archivo SQL
    $sqlFile = __DIR__ . '/database/update_pago_inicial.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Archivo de migración no encontrado: {$sqlFile}");
    }
    
    echo "✓ Archivo de migración encontrado\n";
    echo "  Archivo: update_pago_inicial.sql\n\n";

    $sql = file_get_contents($sqlFile);
    
    // Dividir por sentencias (usando delimitador ;)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   strpos($stmt, '--') !== 0 &&
                   strpos($stmt, '/*') !== 0;
        }
    );

    echo "📋 Ejecutando " . count($statements) . " sentencias SQL...\n\n";

    $pdo->beginTransaction();
    $executed = 0;

    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            // Identificar tipo de sentencia
            $type = 'QUERY';
            if (stripos($statement, 'CREATE TABLE') !== false) {
                $type = 'CREATE TABLE';
                preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
            } elseif (stripos($statement, 'ALTER TABLE') !== false) {
                $type = 'ALTER TABLE';
                preg_match('/ALTER TABLE.*?`([^`]+)`/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
            } elseif (stripos($statement, 'CREATE TRIGGER') !== false) {
                $type = 'CREATE TRIGGER';
                preg_match('/CREATE TRIGGER.*?`([^`]+)`/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
            } elseif (stripos($statement, 'CREATE VIEW') !== false || stripos($statement, 'CREATE OR REPLACE VIEW') !== false) {
                $type = 'CREATE VIEW';
                preg_match('/VIEW.*?`([^`]+)`/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
            }
            
            echo "  [" . ($index + 1) . "] Ejecutando {$type}";
            if (isset($tableName)) {
                echo ": {$tableName}";
            }
            
            $pdo->exec($statement);
            echo " ✓\n";
            $executed++;
            
        } catch (PDOException $e) {
            // Si el error es porque ya existe, continuamos
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo " ⚠️  (ya existe)\n";
                continue;
            }
            throw $e;
        }
    }

    $pdo->commit();
    
    echo "\n✅ INSTALACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "   Sentencias ejecutadas: {$executed}\n\n";

    // Verificar instalación
    echo "🔍 VERIFICANDO INSTALACIÓN...\n\n";

    // Verificar tabla pagos_iniciales
    $stmt = $pdo->query("SHOW TABLES LIKE 'pagos_iniciales'");
    if ($stmt->rowCount() > 0) {
        echo "  ✓ Tabla 'pagos_iniciales' creada correctamente\n";
        
        // Contar columnas
        $stmt = $pdo->query("DESCRIBE pagos_iniciales");
        $columns = $stmt->fetchAll();
        echo "    Columnas: " . count($columns) . "\n";
    } else {
        echo "  ✗ ERROR: Tabla 'pagos_iniciales' no encontrada\n";
    }

    // Verificar tabla pagos_iniciales_detalle
    $stmt = $pdo->query("SHOW TABLES LIKE 'pagos_iniciales_detalle'");
    if ($stmt->rowCount() > 0) {
        echo "  ✓ Tabla 'pagos_iniciales_detalle' creada correctamente\n";
        
        $stmt = $pdo->query("DESCRIBE pagos_iniciales_detalle");
        $columns = $stmt->fetchAll();
        echo "    Columnas: " . count($columns) . "\n";
    } else {
        echo "  ✗ ERROR: Tabla 'pagos_iniciales_detalle' no encontrada\n";
    }

    // Verificar campo plan_inicial_id en lotes
    $stmt = $pdo->query("SHOW COLUMNS FROM lotes LIKE 'plan_inicial_id'");
    if ($stmt->rowCount() > 0) {
        echo "  ✓ Campo 'plan_inicial_id' agregado a tabla 'lotes'\n";
    } else {
        echo "  ✗ ERROR: Campo 'plan_inicial_id' no encontrado en tabla 'lotes'\n";
    }

    // Verificar trigger
    $stmt = $pdo->query("SHOW TRIGGERS LIKE 'after_plan_inicial_completado'");
    if ($stmt->rowCount() > 0) {
        echo "  ✓ Trigger 'after_plan_inicial_completado' creado\n";
    } else {
        echo "  ⚠️  Trigger 'after_plan_inicial_completado' no encontrado\n";
    }

    // Verificar vista
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_" . DB_NAME . " = 'vista_planes_iniciales_resumen'");
    if ($stmt->rowCount() > 0) {
        echo "  ✓ Vista 'vista_planes_iniciales_resumen' creada\n";
    } else {
        echo "  ⚠️  Vista 'vista_planes_iniciales_resumen' no encontrada\n";
    }

    echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  INSTALACIÓN FINALIZADA                                       ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    echo "📝 PRÓXIMOS PASOS:\n\n";
    echo "1. Verificar que todas las estructuras se crearon correctamente\n";
    echo "2. Acceder a: https://inversiones.mch.com.co/lotes/inicial/create/13\n";
    echo "3. Probar la creación de un plan de pago inicial\n";
    echo "4. Revisar los logs en storage/logs/ si hay errores\n\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "\n❌ ERROR EN LA INSTALACIÓN:\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "Detalles técnicos:\n";
    echo "  Archivo: " . $e->getFile() . "\n";
    echo "  Línea: " . $e->getLine() . "\n\n";
    exit(1);
}
?>
