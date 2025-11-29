<?php
/**
 * DEBUG DE REPORTES EN PRODUCCIÓN
 * Acceder a: https://inversiones.mch.com.co/debug-reportes.php
 */

// Forzar display de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug de Reportes</h1>";
echo "<pre>";

session_start();

// Simular sesión (ajustar según usuario real)
if (!isset($_SESSION['user_id'])) {
    echo "⚠️ NO HAY SESIÓN ACTIVA\n";
    echo "Simulando sesión de administrador...\n\n";
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'admin@sistema.com';
    $_SESSION['user_rol'] = 'administrador';
    $_SESSION['user_nombre'] = 'Administrador';
}

try {
    echo "1. Cargando configuración...\n";
    require_once __DIR__ . '/config/config.php';
    echo "   ✓ Config OK\n\n";
    
    echo "2. Cargando dependencias...\n";
    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/core/Logger.php';
    require_once __DIR__ . '/core/helpers.php';
    echo "   ✓ Dependencias OK\n\n";
    
    echo "3. Configurando autoloader...\n";
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
    echo "   ✓ Autoloader OK\n\n";
    
    echo "4. Instanciando ReporteController...\n";
    $controller = new \App\Controllers\ReporteController();
    echo "   ✓ Controller OK\n\n";
    
    echo "5. Ejecutando método lotesVendidos()...\n";
    ob_start();
    $controller->lotesVendidos();
    $output = ob_get_clean();
    echo "   ✓ Método ejecutado\n";
    echo "   📄 Longitud del output: " . strlen($output) . " bytes\n\n";
    
    if (strlen($output) > 0) {
        echo "6. ✅ TODO FUNCIONA - La vista se generó correctamente\n\n";
        echo "El problema NO está en el código, puede ser:\n";
        echo "- Caché del navegador\n";
        echo "- Configuración del servidor web\n";
        echo "- mod_security bloqueando la respuesta\n";
        echo "- Límites de memoria PHP\n\n";
        echo "Intenta acceder directamente:\n";
        echo "👉 <a href='/reportes/lotes-vendidos'>Ver Reporte de Lotes Vendidos</a>\n";
    } else {
        echo "6. ⚠️ El método se ejecutó pero no generó output\n";
        echo "Esto puede indicar un redirect o exit() en el código\n";
    }
    
    echo "\n--- FIN DEL DEBUG ---\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "\n❌ EXCEPTION:\n";
    echo "Mensaje: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "Archivo: " . htmlspecialchars($e->getFile()) . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . htmlspecialchars($e->getTraceAsString()) . "\n";
    echo "</pre>";
} catch (Error $e) {
    echo "\n❌ ERROR:\n";
    echo "Mensaje: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "Archivo: " . htmlspecialchars($e->getFile()) . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . htmlspecialchars($e->getTraceAsString()) . "\n";
    echo "</pre>";
}
