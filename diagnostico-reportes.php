<?php
/**
 * DIAGNÓSTICO COMPLETO DE REPORTES
 * Usar en producción: https://inversiones.mch.com.co/diagnostico-reportes.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Reportes</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #4CAF50; color: white; }
        .test-section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico Completo del Módulo de Reportes</h1>
    
    <?php
    session_start();
    
    // Simular sesión si no existe
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['user'] = [
            'id' => 1,
            'email' => 'admin@sistema.com',
            'nombre' => 'Administrador',
            'rol' => 'administrador'
        ];
        echo "<p class='warning'>⚠️ Sesión simulada para diagnóstico</p>";
    }
    
    // TEST 1: Archivos y Configuración
    echo '<div class="test-section">';
    echo '<h2>1️⃣ Verificación de Archivos</h2>';
    
    $archivos = [
        'config/config.php' => __DIR__ . '/config/config.php',
        'core/Database.php' => __DIR__ . '/core/Database.php',
        'core/Router.php' => __DIR__ . '/core/Router.php',
        'core/helpers.php' => __DIR__ . '/core/helpers.php',
        'app/Controllers/ReporteController.php' => __DIR__ . '/app/Controllers/ReporteController.php',
        'app/Views/reportes/index.php' => __DIR__ . '/app/Views/reportes/index.php',
        'app/Views/reportes/lotes-vendidos.php' => __DIR__ . '/app/Views/reportes/lotes-vendidos.php',
        'app/Views/reportes/cartera.php' => __DIR__ . '/app/Views/reportes/cartera.php',
        'app/Views/reportes/estado-clientes.php' => __DIR__ . '/app/Views/reportes/estado-clientes.php',
    ];
    
    echo '<table>';
    echo '<tr><th>Archivo</th><th>Estado</th></tr>';
    foreach ($archivos as $nombre => $ruta) {
        $existe = file_exists($ruta);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($nombre) . '</td>';
        echo '<td class="' . ($existe ? 'success' : 'error') . '">';
        echo $existe ? '✓ Existe' : '✗ NO EXISTE';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    
    // TEST 2: Cargar Dependencias
    echo '<div class="test-section">';
    echo '<h2>2️⃣ Carga de Dependencias</h2>';
    
    try {
        require_once __DIR__ . '/config/config.php';
        echo '<p class="success">✓ config.php cargado</p>';
        
        require_once __DIR__ . '/core/Database.php';
        echo '<p class="success">✓ Database.php cargado</p>';
        
        require_once __DIR__ . '/core/helpers.php';
        echo '<p class="success">✓ helpers.php cargado</p>';
        
        echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
        echo '<p><strong>Base de datos:</strong> ' . DB_NAME . '</p>';
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';
    
    // TEST 3: Conexión a Base de Datos
    echo '<div class="test-section">';
    echo '<h2>3️⃣ Conexión a Base de Datos</h2>';
    
    try {
        $db = Database::getInstance();
        echo '<p class="success">✓ Conexión establecida</p>';
        
        $result = $db->fetchAll("SELECT COUNT(*) as total FROM lotes WHERE estado = 'vendido'");
        echo '<p>📊 Lotes vendidos: <strong>' . $result[0]['total'] . '</strong></p>';
        
        $result = $db->fetchAll("SELECT COUNT(*) as total FROM amortizaciones");
        echo '<p>📊 Amortizaciones: <strong>' . $result[0]['total'] . '</strong></p>';
        
        $result = $db->fetchAll("SELECT COUNT(*) as total FROM clientes");
        echo '<p>📊 Clientes: <strong>' . $result[0]['total'] . '</strong></p>';
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';
    
    // TEST 4: Autoloader y Controlador
    echo '<div class="test-section">';
    echo '<h2>4️⃣ Autoloader y ReporteController</h2>';
    
    try {
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
        echo '<p class="success">✓ Autoloader configurado</p>';
        
        $controller = new \App\Controllers\ReporteController();
        echo '<p class="success">✓ ReporteController instanciado correctamente</p>';
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } catch (Error $e) {
        echo '<p class="error">✗ Fatal Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    echo '</div>';
    
    // TEST 5: Ejecutar Consultas
    echo '<div class="test-section">';
    echo '<h2>5️⃣ Prueba de Consultas SQL</h2>';
    
    try {
        // Lotes Vendidos
        $sql = "SELECT COUNT(*) as total FROM lotes l
                INNER JOIN proyectos p ON l.proyecto_id = p.id
                LEFT JOIN clientes c ON l.cliente_id = c.id
                LEFT JOIN users u ON l.vendedor_id = u.id
                WHERE l.estado = 'vendido'";
        $result = $db->fetchAll($sql);
        echo '<p>✓ Query Lotes Vendidos: <strong>' . $result[0]['total'] . ' registros</strong></p>';
        
        // Ventas por Proyecto
        $sql = "SELECT COUNT(*) as total FROM proyectos p LEFT JOIN lotes l ON p.id = l.proyecto_id GROUP BY p.id";
        $result = $db->fetchAll($sql);
        echo '<p>✓ Query Ventas por Proyecto: <strong>' . count($result) . ' proyectos</strong></p>';
        
        // Cartera
        $sql = "SELECT COUNT(*) as total FROM amortizaciones a
                INNER JOIN lotes l ON a.lote_id = l.id
                WHERE a.estado = 'pendiente' AND a.saldo > 0";
        $result = $db->fetchAll($sql);
        echo '<p>✓ Query Cartera: <strong>' . $result[0]['total'] . ' cuotas pendientes</strong></p>';
        
        // Estado de Clientes
        $sql = "SELECT COUNT(DISTINCT COALESCE(c.id, 0)) as total
                FROM lotes l
                LEFT JOIN clientes c ON l.cliente_id = c.id
                WHERE l.estado = 'vendido'";
        $result = $db->fetchAll($sql);
        echo '<p>✓ Query Estado Clientes: <strong>' . $result[0]['total'] . ' clientes</strong></p>';
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Error en queries: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';
    
    // TEST 6: Sistema de Permisos
    echo '<div class="test-section">';
    echo '<h2>6️⃣ Sistema de Permisos</h2>';
    
    $tienePermiso = can('ver_reportes');
    echo '<p class="' . ($tienePermiso ? 'success' : 'error') . '">';
    echo $tienePermiso ? '✓ Permiso "ver_reportes" concedido' : '✗ Permiso "ver_reportes" DENEGADO';
    echo '</p>';
    
    echo '<p><strong>Usuario actual:</strong> ' . ($_SESSION['user']['nombre'] ?? 'N/A') . '</p>';
    echo '<p><strong>Rol:</strong> ' . ($_SESSION['user']['rol'] ?? 'N/A') . '</p>';
    echo '</div>';
    
    // TEST 7: Enlaces de Prueba
    echo '<div class="test-section">';
    echo '<h2>7️⃣ Enlaces de Prueba</h2>';
    echo '<p>Si todos los tests anteriores pasaron, prueba estos enlaces:</p>';
    echo '<ul>';
    echo '<li><a href="/reportes" target="_blank">Panel Principal de Reportes</a></li>';
    echo '<li><a href="/reportes/lotes-vendidos" target="_blank">Lotes Vendidos</a></li>';
    echo '<li><a href="/reportes/ventas-proyecto" target="_blank">Ventas por Proyecto</a></li>';
    echo '<li><a href="/reportes/ventas-vendedor" target="_blank">Ventas por Vendedor</a></li>';
    echo '<li><a href="/reportes/cartera" target="_blank">Cartera Pendiente</a></li>';
    echo '<li><a href="/reportes/estado-clientes" target="_blank">Estado de Clientes</a></li>';
    echo '</ul>';
    echo '</div>';
    
    // Conclusión
    echo '<div class="test-section" style="background: #e8f5e9;">';
    echo '<h2>✅ Resumen</h2>';
    echo '<p>Si todos los tests pasaron pero los reportes dan error 500, el problema puede ser:</p>';
    echo '<ul>';
    echo '<li><strong>Caché del servidor:</strong> Limpiar caché de OPcache</li>';
    echo '<li><strong>.htaccess:</strong> Verificar que mod_rewrite esté activo</li>';
    echo '<li><strong>Permisos:</strong> Verificar permisos 644 en archivos PHP</li>';
    echo '<li><strong>Logs del servidor:</strong> Revisar /var/log/apache2/error.log</li>';
    echo '</ul>';
    echo '</div>';
    ?>
</body>
</html>
