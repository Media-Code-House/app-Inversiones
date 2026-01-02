<?php
/**
 * Script de diagnóstico para problemas de CSRF en producción
 */

session_start();

echo "=== DIAGNÓSTICO DE CSRF Y SESIÓN ===\n\n";

// 1. Verificar sesión
echo "1. INFORMACIÓN DE SESIÓN:\n";
echo "   Session ID: " . session_id() . "\n";
echo "   Session Status: " . session_status() . " (1=disabled, 2=active)\n";
echo "   Session Name: " . session_name() . "\n";
echo "   Session Save Path: " . session_save_path() . "\n\n";

// 2. Verificar token CSRF
echo "2. TOKEN CSRF:\n";
if (isset($_SESSION['csrf_token'])) {
    echo "   ✓ Token existe en sesión\n";
    echo "   Token: " . substr($_SESSION['csrf_token'], 0, 20) . "...\n";
    echo "   Longitud: " . strlen($_SESSION['csrf_token']) . " caracteres\n";
} else {
    echo "   ❌ Token NO existe en sesión\n";
    echo "   Generando nuevo token...\n";
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo "   ✓ Token generado: " . substr($_SESSION['csrf_token'], 0, 20) . "...\n";
}
echo "\n";

// 3. Verificar headers disponibles
echo "3. MÉTODOS DE OBTENER HEADERS:\n";
if (function_exists('getallheaders')) {
    echo "   ✓ getallheaders() disponible\n";
} else {
    echo "   ❌ getallheaders() NO disponible\n";
}

if (function_exists('apache_request_headers')) {
    echo "   ✓ apache_request_headers() disponible\n";
} else {
    echo "   ❌ apache_request_headers() NO disponible\n";
}
echo "\n";

// 4. Verificar variables de servidor relevantes
echo "4. VARIABLES DE SERVIDOR:\n";
echo "   SERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'no definido') . "\n";
echo "   REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'no definido') . "\n";
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'no definido') . "\n";
echo "   SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'no definido') . "\n\n";

// 5. Simular recepción de headers CSRF
echo "5. SIMULACIÓN DE RECEPCIÓN DE TOKEN:\n";
$_SERVER['HTTP_X_CSRF_TOKEN'] = 'test_token_123';
echo "   Simulando header X-CSRF-Token...\n";

$token = '';
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? '';
    echo "   vía getallheaders(): " . ($token ? "✓ $token" : "❌ no encontrado") . "\n";
}

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
echo "   vía \$_SERVER['HTTP_X_CSRF_TOKEN']: " . ($token ? "✓ $token" : "❌ no encontrado") . "\n";
echo "\n";

// 6. Verificar permisos de escritura
echo "6. PERMISOS DE SESIÓN:\n";
$sessionPath = session_save_path();
if (empty($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}
echo "   Directorio de sesiones: $sessionPath\n";
if (is_writable($sessionPath)) {
    echo "   ✓ Directorio escribible\n";
} else {
    echo "   ❌ Directorio NO escribible\n";
}
echo "\n";

// 7. Resumen
echo "=== RESUMEN ===\n";
$issues = [];
if (!isset($_SESSION['csrf_token'])) $issues[] = "Token CSRF no existe en sesión";
if (!function_exists('getallheaders') && !function_exists('apache_request_headers')) {
    $issues[] = "Ninguna función de headers disponible - usar \$_SERVER";
}
if (!is_writable($sessionPath)) $issues[] = "Directorio de sesiones no escribible";

if (empty($issues)) {
    echo "✅ Todo parece estar configurado correctamente\n";
    echo "\nSi aún tienes problemas de CSRF:\n";
    echo "- Verifica que la sesión se mantenga entre requests\n";
    echo "- Asegúrate de que el token se esté enviando desde el navegador\n";
    echo "- Revisa los logs del servidor web\n";
} else {
    echo "⚠️  Se encontraron los siguientes problemas:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
}
