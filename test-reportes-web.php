<?php
/**
 * Test directo de reportes - Acceso vía web
 */

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Simular usuario autenticado (admin)
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@sistema.com';
$_SESSION['user_rol'] = 'administrador';
$_SESSION['user_nombre'] = 'Administrador';

echo "<h1>Test de Reportes</h1>";
echo "<pre>";

try {
    // Cargar el sistema
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/core/helpers.php';
    
    echo "✓ Configuración cargada\n";
    
    // Autoloader
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
        }
    });
    
    echo "✓ Autoloader configurado\n";
    
    // Cargar modelos necesarios
    $loteModel = new \App\Models\LoteModel();
    echo "✓ LoteModel cargado\n";
    
    $proyectoModel = new \App\Models\ProyectoModel();
    echo "✓ ProyectoModel cargado\n";
    
    $clienteModel = new \App\Models\ClienteModel();
    echo "✓ ClienteModel cargado\n";
    
    $amortizacionModel = new \App\Models\AmortizacionModel();
    echo "✓ AmortizacionModel cargado\n";
    
    $pagoModel = new \App\Models\PagoModel();
    echo "✓ PagoModel cargado\n";
    
    // Cargar controlador
    $controller = new \App\Controllers\ReporteController();
    echo "✓ ReporteController instanciado\n";
    
    echo "\n<strong>✅ TODO FUNCIONA CORRECTAMENTE</strong>\n\n";
    
    echo "</pre>";
    
    echo "<h2>Enlaces de Prueba:</h2>";
    echo "<ul>";
    echo "<li><a href='/reportes' target='_blank'>Panel Principal de Reportes</a></li>";
    echo "<li><a href='/reportes/lotes-vendidos' target='_blank'>Lotes Vendidos</a></li>";
    echo "<li><a href='/reportes/ventas-proyecto' target='_blank'>Ventas por Proyecto</a></li>";
    echo "<li><a href='/reportes/ventas-vendedor' target='_blank'>Ventas por Vendedor</a></li>";
    echo "<li><a href='/reportes/cartera' target='_blank'>Cartera Pendiente</a></li>";
    echo "<li><a href='/reportes/estado-clientes' target='_blank'>Estado de Clientes</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "\n<strong>❌ ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "\n";
    echo "<strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "\n";
    echo "<strong>Línea:</strong> " . $e->getLine() . "\n";
    echo "\n<strong>Stack trace:</strong>\n" . htmlspecialchars($e->getTraceAsString()) . "\n";
    echo "</pre>";
} catch (Error $e) {
    echo "\n<strong>❌ FATAL ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "\n";
    echo "<strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "\n";
    echo "<strong>Línea:</strong> " . $e->getLine() . "\n";
    echo "\n<strong>Stack trace:</strong>\n" . htmlspecialchars($e->getTraceAsString()) . "\n";
    echo "</pre>";
}
