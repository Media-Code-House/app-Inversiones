<?php
/**
 * Script de Diagnóstico Detallado
 * Identifica el error específico
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico del Sistema</h1>";
echo "<hr>";

// 1. Verificar config
echo "<h2>1. Configuración</h2>";
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
    echo "✓ config.php cargado<br>";
    echo "DEBUG_MODE: " . (DEBUG_MODE ? 'SÍ' : 'NO') . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
} else {
    die("✗ config.php no encontrado");
}

// 2. Verificar conexión
echo "<h2>2. Conexión a Base de Datos</h2>";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conexión a MySQL exitosa<br>";
    
    // Verificar si existe la BD
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Base de datos '" . DB_NAME . "' existe<br>";
        
        // Seleccionar la BD
        $pdo->exec("USE " . DB_NAME);
        
        // Verificar tablas
        echo "<h3>Tablas existentes:</h3>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<strong style='color:red;'>✗ No hay tablas. Debes importar schema.sql</strong><br>";
        } else {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
            
            // Verificar tabla users
            if (in_array('users', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "✓ Usuarios en BD: " . $result['total'] . "<br>";
            }
        }
    } else {
        echo "<strong style='color:red;'>✗ Base de datos '" . DB_NAME . "' NO existe</strong><br>";
        echo "<p><strong>SOLUCIÓN:</strong> Ejecuta estos comandos en MySQL:</p>";
        echo "<pre>CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>";
        echo "<p>Luego importa el archivo: <strong>database/schema.sql</strong></p>";
    }
    
} catch (PDOException $e) {
    echo "<strong style='color:red;'>✗ Error de conexión: " . $e->getMessage() . "</strong><br>";
}

// 3. Verificar archivos core
echo "<h2>3. Archivos del Sistema</h2>";
$archivos = [
    'index.php',
    'core/Router.php',
    'core/Database.php',
    'core/helpers.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/HomeController.php',
    'app/Models/AuthModel.php',
    'app/Models/ProyectoModel.php',
    'app/Views/layouts/app.php'
];

foreach ($archivos as $archivo) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "✓ $archivo<br>";
    } else {
        echo "<strong style='color:red;'>✗ $archivo (NO EXISTE)</strong><br>";
    }
}

// 4. Intentar cargar el sistema
echo "<h2>4. Carga del Sistema</h2>";
try {
    require_once __DIR__ . '/core/Database.php';
    echo "✓ Database.php cargado<br>";
    
    $db = Database::getInstance();
    echo "✓ Singleton de Database funciona<br>";
    
    // Probar una consulta
    $result = $db->fetch("SELECT 1 as test");
    if ($result && $result['test'] == 1) {
        echo "✓ Consulta de prueba exitosa<br>";
    }
    
} catch (Exception $e) {
    echo "<strong style='color:red;'>✗ Error: " . $e->getMessage() . "</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Si todos los checks son ✓, el sistema debería funcionar.</strong></p>";
echo "<p><a href='/'>Ir a la página principal</a></p>";
