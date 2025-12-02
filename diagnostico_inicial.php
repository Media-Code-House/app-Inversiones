<?php
/**
 * Script de Diagnóstico - Error 500 en Pago Inicial
 * Identifica el error exacto que está causando el HTTP 500
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIAGNÓSTICO: Módulo Pago Inicial ===\n\n";

// 1. Verificar que config existe
echo "1. Verificando configuración...\n";
if (!file_exists(__DIR__ . '/config/config.php')) {
    die("❌ ERROR: No se encuentra config/config.php\n");
}
require_once __DIR__ . '/config/config.php';
echo "✓ Config cargado\n\n";

// 2. Verificar conexión a BD
echo "2. Verificando conexión a base de datos...\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conexión exitosa a: " . DB_NAME . "\n\n";
} catch (PDOException $e) {
    die("❌ ERROR de conexión: " . $e->getMessage() . "\n");
}

// 3. Verificar tablas
echo "3. Verificando tablas del módulo...\n";
$tablas = ['pagos_iniciales', 'pagos_iniciales_detalle'];
foreach ($tablas as $tabla) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabla '$tabla' existe\n";
    } else {
        echo "❌ Tabla '$tabla' NO existe\n";
    }
}
echo "\n";

// 4. Verificar campo plan_inicial_id
echo "4. Verificando campo plan_inicial_id en lotes...\n";
$stmt = $pdo->query("SHOW COLUMNS FROM lotes LIKE 'plan_inicial_id'");
if ($stmt->rowCount() > 0) {
    echo "✓ Campo 'plan_inicial_id' existe\n";
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($col);
} else {
    echo "❌ Campo 'plan_inicial_id' NO existe\n";
}
echo "\n";

// 5. Verificar que el lote 13 existe
echo "5. Verificando lote #13...\n";
$stmt = $pdo->prepare("SELECT * FROM lotes WHERE id = 13");
$stmt->execute();
$lote = $stmt->fetch(PDO::FETCH_ASSOC);
if ($lote) {
    echo "✓ Lote #13 encontrado\n";
    echo "  - Código: " . $lote['codigo_lote'] . "\n";
    echo "  - Estado: " . $lote['estado'] . "\n";
    echo "  - Cliente ID: " . ($lote['cliente_id'] ?? 'NULL') . "\n";
    echo "  - Plan Inicial ID: " . ($lote['plan_inicial_id'] ?? 'NULL') . "\n";
} else {
    echo "❌ Lote #13 NO existe\n";
}
echo "\n";

// 6. Verificar modelos
echo "6. Verificando modelos...\n";
$modelos = [
    'LoteModel' => __DIR__ . '/app/Models/LoteModel.php',
    'ClienteModel' => __DIR__ . '/app/Models/ClienteModel.php',
    'ProyectoModel' => __DIR__ . '/app/Models/ProyectoModel.php'
];
foreach ($modelos as $nombre => $path) {
    if (file_exists($path)) {
        echo "✓ $nombre existe\n";
    } else {
        echo "❌ $nombre NO existe en: $path\n";
    }
}
echo "\n";

// 7. Verificar controlador
echo "7. Verificando InicialController...\n";
$controllerPath = __DIR__ . '/app/Controllers/InicialController.php';
if (file_exists($controllerPath)) {
    echo "✓ InicialController.php existe\n";
    
    // Verificar sintaxis PHP
    $output = [];
    $return = 0;
    exec("php -l \"$controllerPath\" 2>&1", $output, $return);
    if ($return === 0) {
        echo "✓ Sin errores de sintaxis\n";
    } else {
        echo "❌ Error de sintaxis:\n";
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "❌ InicialController.php NO existe\n";
}
echo "\n";

// 8. Simular carga del controlador
echo "8. Intentando cargar InicialController...\n";
try {
    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/app/Controllers/Controller.php';
    require_once __DIR__ . '/app/Models/LoteModel.php';
    require_once __DIR__ . '/app/Models/ClienteModel.php';
    require_once __DIR__ . '/app/Models/ProyectoModel.php';
    require_once __DIR__ . '/app/Controllers/InicialController.php';
    
    echo "✓ Todas las clases cargadas correctamente\n";
    
    // Intentar instanciar
    $controller = new \App\Controllers\InicialController();
    echo "✓ InicialController instanciado correctamente\n";
    
} catch (Throwable $e) {
    echo "❌ ERROR al cargar controlador:\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "  Archivo: " . $e->getFile() . "\n";
    echo "  Línea: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}
echo "\n";

// 9. Verificar vista
echo "9. Verificando vista create.php...\n";
$vistaPath = __DIR__ . '/app/Views/lotes/inicial/create.php';
if (file_exists($vistaPath)) {
    echo "✓ Vista create.php existe\n";
} else {
    echo "❌ Vista create.php NO existe en: $vistaPath\n";
}
echo "\n";

// 10. Verificar rutas
echo "10. Verificando rutas en index.php...\n";
$indexContent = file_get_contents(__DIR__ . '/index.php');
if (strpos($indexContent, '/lotes/inicial/create') !== false) {
    echo "✓ Ruta /lotes/inicial/create encontrada\n";
} else {
    echo "❌ Ruta /lotes/inicial/create NO encontrada en index.php\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
