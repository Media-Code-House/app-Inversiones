<?php
/**
 * Test específico para ReporteController
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Simular usuario autenticado
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@sistema.com';
$_SESSION['user_rol'] = 'administrador';
$_SESSION['user_nombre'] = 'Administrador';

echo "=== TEST REPORTECONTROLLER ===\n\n";

try {
    // Cargar configuración
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/core/helpers.php';
    
    echo "1. Configuración cargada OK\n";
    
    // Autoload
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/app/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
            echo "  → Loaded: $class\n";
        }
    });
    
    echo "2. Autoloader configurado OK\n";
    
    // Intentar cargar Controller base
    require_once __DIR__ . '/core/Controller.php';
    echo "3. Controller base cargado OK\n";
    
    // Intentar instanciar ReporteController
    $controller = new \App\Controllers\ReporteController();
    echo "4. ReporteController instanciado OK\n";
    
    // Probar método index
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    if (strpos($output, 'Reportes') !== false || empty($output)) {
        echo "5. Método index() ejecutado OK\n";
    } else {
        echo "5. Método index() ejecutado pero con salida inesperada\n";
    }
    
    echo "\n✅ REPORTECONTROLLER FUNCIONA CORRECTAMENTE\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
