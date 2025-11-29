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
// DESPACHAR RUTA
// ==========================================

try {
    $router->dispatch();
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    if (DEBUG_MODE) {
        echo "<h1>Error</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "Ha ocurrido un error. Por favor, contacte al administrador.";
    }
}
