<?php

/**
 * Punto de entrada principal de la aplicación
 * Este archivo está en la raíz para compatibilidad con hosting
 */

// DEBUG TEMPORAL - Activar con ?debug=1 en la URL
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

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
require_once __DIR__ . '/core/Logger.php';
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
// RUTAS DE PROYECTOS
// ==========================================

// Listar proyectos
$router->get('/proyectos', 'ProyectoController@index');

// Formulario crear proyecto
$router->get('/proyectos/create', 'ProyectoController@create');

// Guardar nuevo proyecto
$router->post('/proyectos/store', 'ProyectoController@store');

// Ver detalle de proyecto
$router->get('/proyectos/show/{id}', 'ProyectoController@show');

// Formulario editar proyecto
$router->get('/proyectos/edit/{id}', 'ProyectoController@edit');

// Actualizar proyecto
$router->post('/proyectos/update/{id}', 'ProyectoController@update');

// Eliminar proyecto
$router->post('/proyectos/delete/{id}', 'ProyectoController@delete');

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

// Eliminar lote
$router->post('/lotes/delete/{id}', 'LoteController@delete');

// ==========================================
// RUTAS DE CLIENTES
// ==========================================

// Listar clientes
$router->get('/clientes', 'ClienteController@index');

// Formulario crear cliente
$router->get('/clientes/create', 'ClienteController@create');

// Guardar nuevo cliente
$router->post('/clientes/store', 'ClienteController@store');

// Ver detalle de cliente
$router->get('/clientes/show/{id}', 'ClienteController@show');

// Formulario editar cliente
$router->get('/clientes/edit/{id}', 'ClienteController@edit');

// Actualizar cliente
$router->post('/clientes/update/{id}', 'ClienteController@update');

// Eliminar cliente
$router->post('/clientes/delete/{id}', 'ClienteController@delete');

// Buscar cliente (AJAX)
$router->post('/clientes/buscar', 'ClienteController@buscar');

// ==========================================
// AMORTIZACIÓN (Módulo 5)
// ==========================================

// Ver tabla de amortización completa del lote
$router->get('/lotes/amortizacion/show/{id}', 'AmortizacionController@show');

// Formulario para crear plan de amortización
$router->get('/lotes/amortizacion/create/{id}', 'AmortizacionController@create');

// Guardar nuevo plan de amortización (método francés)
$router->post('/lotes/amortizacion/store', 'AmortizacionController@store');

// Recalcular plan de amortización (abono a capital)
$router->post('/lotes/amortizacion/recalcular/{id}', 'AmortizacionController@recalcular');

// Reajustar plan aplicando saldo a favor (compensar mora)
$router->post('/lotes/amortizacion/reajustar/{id}', 'AmortizacionController@reajustarPlan');

// ==========================================
// PAGOS (Módulo 5)
// ==========================================

// Formulario para registrar pago
$router->get('/lotes/pago/create/{id}', 'PagoController@create');

// Guardar nuevo pago (con distribución y excedentes)
$router->post('/lotes/pago/store', 'PagoController@store');

// API: Calcular distribución de pago (AJAX)
$router->post('/lotes/pago/calcular-distribucion', 'PagoController@calcularDistribucion');

// ==========================================
// REPORTES - MÓDULO 6 (Business Intelligence)
// ==========================================

// Panel principal de reportes
$router->get('/reportes', 'ReporteController@index');

// Reporte: Lotes Vendidos
$router->get('/reportes/lotes-vendidos', 'ReporteController@lotesVendidos');

// Reporte: Ventas por Proyecto
$router->get('/reportes/ventas-proyecto', 'ReporteController@ventasPorProyecto');

// Reporte: Ventas por Vendedor
$router->get('/reportes/ventas-vendedor', 'ReporteController@ventasPorVendedor');

// Reporte: Cartera Pendiente
$router->get('/reportes/cartera', 'ReporteController@cartera');

// Reporte: Estado de Clientes
$router->get('/reportes/estado-clientes', 'ReporteController@estadoClientes');

// ==========================================
// COMISIONES - MÓDULO 7 (Gestión de Comisiones)
// ==========================================

// Gestión de comisiones (solo administrador)
$router->get('/comisiones', 'ComisionController@index');
$router->get('/comisiones/resumen', 'ComisionController@resumen');
$router->get('/comisiones/show/{id}', 'ComisionController@show');
$router->get('/comisiones/pagar/{id}', 'ComisionController@pagar');
$router->post('/comisiones/registrar-pago/{id}', 'ComisionController@registrarPago');
$router->get('/comisiones/configuracion', 'ComisionController@configuracion');
$router->post('/comisiones/actualizar-configuracion/{id}', 'ComisionController@actualizarConfiguracion');

// Mis comisiones (vendedores pueden ver sus propias comisiones)
$router->get('/comisiones/mis-comisiones', 'ComisionController@misComisiones');

// ==========================================
// VENDEDORES - MÓDULO 8 (Gestión de Vendedores)
// ==========================================

// CRUD de vendedores
$router->get('/vendedores', 'VendedorController@index');
$router->get('/vendedores/create', 'VendedorController@create');
$router->post('/vendedores/store', 'VendedorController@store');
$router->get('/vendedores/show/{id}', 'VendedorController@show');
$router->get('/vendedores/edit/{id}', 'VendedorController@edit');
$router->post('/vendedores/update/{id}', 'VendedorController@update');

// Ranking y estadísticas
$router->get('/vendedores/ranking', 'VendedorController@ranking');
$router->get('/vendedores/mi-perfil', 'VendedorController@miPerfil');

// ==========================================
// PERFIL DE USUARIO - MÓDULO 8
// ==========================================

// Ver y actualizar perfil personal
$router->get('/perfil', 'PerfilController@index');
$router->post('/perfil/update', 'PerfilController@updateData');
$router->post('/perfil/update-password', 'PerfilController@updatePassword');

// ==========================================
// LOGS DEL SISTEMA (Admin)
// ==========================================

// Ver logs del sistema
$router->get('/logs', 'LogController@index');

// Obtener logs en JSON (AJAX)
$router->get('/logs/fetch', 'LogController@fetch');

// Limpiar logs
$router->post('/logs/clear', 'LogController@clear');

// Descargar archivo de logs
$router->get('/logs/download', 'LogController@download');

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
