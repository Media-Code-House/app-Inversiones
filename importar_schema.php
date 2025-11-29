<?php
/**
 * Script para importar schema.sql
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

echo "Importando schema.sql...\n\n";

try {
    // Conectar
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Conectado a la base de datos\n";
    
    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    
    if (!$sql) {
        die("✗ Error: No se pudo leer database/schema.sql\n");
    }
    
    echo "✓ Archivo schema.sql leído\n";
    
    // Dividir en statements individuales
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   substr($stmt, 0, 2) !== '--' && 
                   substr($stmt, 0, 2) !== '/*';
        }
    );
    
    echo "✓ Encontrados " . count($statements) . " statements SQL\n\n";
    
    // Ejecutar cada statement
    $ejecutados = 0;
    $errores = 0;
    
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $ejecutados++;
            
            // Mostrar progreso
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  ✓ Tabla creada: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'CREATE TRIGGER') !== false) {
                preg_match('/CREATE TRIGGER.*?`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  ✓ Trigger creado: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'CREATE VIEW') !== false) {
                preg_match('/CREATE.*?VIEW.*?`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  ✓ Vista creada: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO.*?`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  ✓ Datos insertados en: {$matches[1]}\n";
                }
            }
            
        } catch (PDOException $e) {
            $errores++;
            // Ignorar errores de "tabla ya existe" o "trigger ya existe"
            if (stripos($e->getMessage(), 'already exists') === false) {
                echo "  ✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n========================================\n";
    echo "IMPORTACIÓN COMPLETADA\n";
    echo "========================================\n";
    echo "Statements ejecutados: $ejecutados\n";
    echo "Errores: $errores\n\n";
    
    // Verificar tablas
    echo "Verificando tablas creadas:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$table`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "  • $table ({$result['total']} registros)\n";
    }
    
    echo "\n✓ Schema importado exitosamente!\n";
    echo "\nCredenciales por defecto:\n";
    echo "  Email: admin@inversiones.com\n";
    echo "  Password: admin123\n";
    
} catch (Exception $e) {
    echo "✗ Error fatal: " . $e->getMessage() . "\n";
    exit(1);
}
