<?php

/**
 * Archivo de configuración principal
 */

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Gestión de Lotes');

// Detectar APP_URL automáticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('APP_URL', $protocol . '://' . $host);

// Detectar si estamos en producción o desarrollo
$isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'mch.com.co') !== false;
define('DEBUG_MODE', !$isProduction);

// Configuración de base de datos
if ($isProduction) {
    // Configuración de producción (Hosting)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u418271893_inversiones');
    define('DB_USER', 'u418271893_santi');
    define('DB_PASS', '3pX/yi>u3212321123123');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Configuración de desarrollo (Local)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sistema_lotes');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// Configuración de sesión
define('SESSION_LIFETIME', 7200); // 2 horas en segundos

// Configuración de seguridad
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);

// Timezone
date_default_timezone_set('America/Mexico_City');

// Mostrar errores en modo debug
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
