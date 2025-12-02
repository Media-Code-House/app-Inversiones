<?php
/**
 * INSTRUCCIONES:
 * 1. Sube este archivo a la raíz de inversiones.mch.com.co
 * 2. Accede a: https://inversiones.mch.com.co/check-inicial-produccion.php
 * 3. Copia TODO el resultado y envíamelo
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "=== DIAGNÓSTICO PRODUCCIÓN: Pago Inicial ===\n\n";

// 1. Config
echo "1. Config:\n";
require_once __DIR__ . '/config/config.php';
echo "✓ DB_NAME: " . DB_NAME . "\n";
echo "✓ DB_HOST: " . DB_HOST . "\n\n";

// 2. Conexión BD
echo "2. Base de datos:\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conexión exitosa\n\n";
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

// 3. Tablas
echo "3. Tablas:\n";
$tablas = ['pagos_iniciales', 'pagos_iniciales_detalle'];
foreach ($tablas as $tabla) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
    echo ($stmt->rowCount() > 0 ? "✓" : "❌") . " $tabla\n";
}
echo "\n";

// 4. Campo plan_inicial_id
echo "4. Campo plan_inicial_id:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM lotes LIKE 'plan_inicial_id'");
if ($stmt->rowCount() > 0) {
    echo "✓ Existe\n";
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Tipo: " . $col['Type'] . "\n";
    echo "  Null: " . $col['Null'] . "\n";
    echo "  Key: " . $col['Key'] . "\n";
} else {
    echo "❌ NO existe\n";
}
echo "\n";

// 5. Lote 13
echo "5. Lote #13:\n";
$stmt = $pdo->prepare("SELECT id, codigo_lote, estado, cliente_id, plan_inicial_id FROM lotes WHERE id = 13");
$stmt->execute();
$lote = $stmt->fetch(PDO::FETCH_ASSOC);
if ($lote) {
    echo "✓ Encontrado\n";
    foreach ($lote as $k => $v) {
        echo "  $k: " . ($v ?? 'NULL') . "\n";
    }
} else {
    echo "❌ No existe\n";
}
echo "\n";

// 6. Archivos
echo "6. Archivos:\n";
$archivos = [
    'Controller' => 'app/Controllers/InicialController.php',
    'Vista' => 'app/Views/lotes/inicial/create.php',
    'LoteModel' => 'app/Models/LoteModel.php',
    'ClienteModel' => 'app/Models/ClienteModel.php',
    'ProyectoModel' => 'app/Models/ProyectoModel.php'
];
foreach ($archivos as $nombre => $path) {
    $existe = file_exists(__DIR__ . '/' . $path);
    echo ($existe ? "✓" : "❌") . " $nombre\n";
}
echo "\n";

// 7. Test carga InicialController
echo "7. Test carga InicialController:\n";
try {
    require_once __DIR__ . '/core/Database.php';
    require_once __DIR__ . '/app/Controllers/Controller.php';
    require_once __DIR__ . '/app/Models/LoteModel.php';
    require_once __DIR__ . '/app/Models/ClienteModel.php';
    require_once __DIR__ . '/app/Models/ProyectoModel.php';
    require_once __DIR__ . '/app/Controllers/InicialController.php';
    
    echo "✓ Clases cargadas\n";
    
    // Instanciar
    session_start();
    $controller = new \App\Controllers\InicialController();
    echo "✓ Instancia creada\n";
    
    // Test método create
    ob_start();
    $controller->create(13);
    $output = ob_get_clean();
    
    if (strlen($output) > 0) {
        echo "✓ Método create() ejecutado (" . strlen($output) . " bytes)\n";
    } else {
        echo "⚠ Método create() no generó output\n";
    }
    
} catch (Throwable $e) {
    echo "❌ ERROR:\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "  Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN ===\n";
echo "</pre>";
?>
