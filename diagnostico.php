<?php
/**
 * Archivo de diagnóstico para hosting
 * ELIMINAR después de verificar que todo funciona
 */

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico del Sistema</h1>";

// 1. Verificar PHP
echo "<h2>1. Versión de PHP</h2>";
echo "Versión: " . PHP_VERSION . "<br>";
echo "SAPI: " . php_sapi_name() . "<br><br>";

// 2. Verificar archivos
echo "<h2>2. Archivos Principales</h2>";
$files = [
    'index.php',
    'config/config.php',
    'core/Router.php',
    'core/Database.php',
    'core/helpers.php',
    'app/Controllers/AuthController.php',
    'app/Models/AuthModel.php'
];

foreach ($files as $file) {
    echo $file . ": " . (file_exists($file) ? "✓ Existe" : "✗ NO EXISTE") . "<br>";
}
echo "<br>";

// 3. Verificar configuración
echo "<h2>3. Configuración</h2>";
require_once 'config/config.php';
echo "APP_NAME: " . APP_NAME . "<br>";
echo "APP_URL: " . APP_URL . "<br>";
echo "DEBUG_MODE: " . (DEBUG_MODE ? "Sí" : "No") . "<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_CHARSET: " . DB_CHARSET . "<br><br>";

// 4. Verificar conexión a base de datos
echo "<h2>4. Conexión a Base de Datos</h2>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "✓ Conexión exitosa<br>";
    
    // Verificar tabla users
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabla 'users' existe<br>";
        
        // Contar usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total de usuarios: " . $result['count'] . "<br>";
    } else {
        echo "✗ Tabla 'users' NO existe<br>";
    }
} catch (PDOException $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 5. Verificar extensiones PHP necesarias
echo "<h2>5. Extensiones PHP</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json'];
foreach ($extensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "✓ Cargada" : "✗ NO CARGADA") . "<br>";
}
echo "<br>";

// 6. Verificar permisos
echo "<h2>6. Permisos de Escritura</h2>";
$dirs = ['.', 'app', 'config', 'core'];
foreach ($dirs as $dir) {
    echo $dir . ": " . (is_writable($dir) ? "✓ Escritura OK" : "✗ Sin permisos de escritura") . "<br>";
}
echo "<br>";

// 7. Verificar mod_rewrite
echo "<h2>7. Mod Rewrite</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? "✓ Activo" : "✗ NO ACTIVO") . "<br>";
} else {
    echo "No se puede verificar (función apache_get_modules no disponible)<br>";
}
echo "<br>";

echo "<hr>";
echo "<p style='color: red;'><strong>IMPORTANTE:</strong> Elimina este archivo (diagnostico.php) después de revisar.</p>";
?>
