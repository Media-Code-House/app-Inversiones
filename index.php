<?php

/**
 * Punto de entrada principal de la aplicación
 * Este archivo está en la raíz para compatibilidad con hosting
 */

// Servir archivos estáticos directamente en servidor PHP integrado
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

session_start();

// Autoloader simple
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

// Cargar configuración y dependencias
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/helpers.php';

// Crear instancia del router
$router = new Router();

// ==========================================
// RUTAS DE AUTENTICACIÓN
// ==========================================

// Login
$router->get('/', 'AuthController@showLogin');
$router->get('/auth/login', 'AuthController@showLogin');
$router->post('/auth/login', 'AuthController@login');

// Registro
$router->get('/auth/register', 'AuthController@showRegister');
$router->post('/auth/register', 'AuthController@register');

// Recuperación de contraseña
$router->get('/auth/recover', 'AuthController@showRecover');
$router->post('/auth/recover', 'AuthController@recover');

// Restablecer contraseña
$router->get('/auth/reset/{token}', 'AuthController@showReset');
$router->post('/auth/reset', 'AuthController@reset');

// Cambiar contraseña (usuario logueado)
$router->post('/auth/change-password', 'AuthController@changePassword');

// Logout
$router->get('/auth/logout', 'AuthController@logout');

// ==========================================
// RUTAS PRINCIPALES
// ==========================================

// Dashboard (Home)
$router->get('/dashboard', 'HomeController@dashboard');

// ==========================================
// RUTAS DE LOTES
// ==========================================

// Listar lotes (con filtros)
$router->get('/lotes', 'LoteController@index');

// Formulario crear lote
$router->get('/lotes/create', 'LoteController@create');

// Guardar nuevo lote
$router->post('/lotes/store', 'LoteController@store');

// Ver detalle de lote
$router->get('/lotes/show/{id}', 'LoteController@show');

// Formulario editar lote
$router->get('/lotes/edit/{id}', 'LoteController@edit');

// Actualizar lote
$router->post('/lotes/update/{id}', 'LoteController@update');

// ==========================================
// DESPACHAR RUTA
// ==========================================

try {
    $router->dispatch();
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    
    // Log del error
    error_log("Error en aplicación: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    
    if (DEBUG_MODE) {
        echo "<!DOCTYPE html><html><head><title>Error</title><style>body{font-family:Arial;padding:20px;background:#f5f5f5;}h1{color:#dc3545;}pre{background:#fff;padding:15px;border-radius:5px;overflow:auto;}</style></head><body>";
        echo "<h1>Error en la aplicación</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</body></html>";
    } else {
        echo "<!DOCTYPE html><html><head><title>Error</title><style>body{font-family:Arial;padding:50px;text-align:center;background:#f5f5f5;}</style></head><body>";
        echo "<h1>Ha ocurrido un error</h1>";
        echo "<p>Por favor, contacte al administrador del sistema.</p>";
        echo "</body></html>";
    }
}
