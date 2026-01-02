<?php
/**
 * Script de Diagnóstico: Error al Guardar Plano de Proyecto
 */

require_once 'config/config.php';
require_once 'core/Database.php';

echo "=== DIAGNÓSTICO: ERROR AL GUARDAR PLANO ===\n\n";

// 1. Verificar configuración de PHP
echo "1. CONFIGURACIÓN DE PHP:\n";
echo str_repeat("-", 80) . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Habilitado' : 'Deshabilitado') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "\n";

// 2. Verificar directorio de uploads
echo "2. VERIFICAR DIRECTORIOS:\n";
echo str_repeat("-", 80) . "\n";

$directorios = [
    'uploads' => __DIR__ . '/uploads',
    'uploads/planos' => __DIR__ . '/uploads/planos'
];

foreach ($directorios as $nombre => $ruta) {
    echo "Directorio: {$nombre}\n";
    
    if (is_dir($ruta)) {
        echo "  ✓ Existe\n";
        
        $permisos = substr(sprintf('%o', fileperms($ruta)), -4);
        echo "  Permisos: {$permisos}\n";
        
        $writable = is_writable($ruta);
        echo "  Escribible: " . ($writable ? 'SI' : 'NO') . "\n";
        
        if (!$writable) {
            echo "  ❌ ERROR: No se puede escribir en este directorio\n";
        }
    } else {
        echo "  ❌ NO EXISTE\n";
        echo "  Intentando crear...\n";
        
        if (mkdir($ruta, 0777, true)) {
            echo "  ✓ Directorio creado exitosamente\n";
        } else {
            echo "  ❌ ERROR: No se pudo crear el directorio\n";
        }
    }
    echo "\n";
}

// 3. Verificar el proyecto 23
echo "3. INFORMACIÓN DEL PROYECTO 23:\n";
echo str_repeat("-", 80) . "\n";

$db = Database::getInstance();
$proyecto = $db->fetch("SELECT * FROM proyectos WHERE id = 23");

if ($proyecto) {
    echo "ID: {$proyecto['id']}\n";
    echo "Código: {$proyecto['codigo']}\n";
    echo "Nombre: {$proyecto['nombre']}\n";
    echo "Plano actual: " . ($proyecto['plano_imagen'] ?: 'Sin plano') . "\n";
    
    if ($proyecto['plano_imagen']) {
        $rutaPlano = __DIR__ . '/' . $proyecto['plano_imagen'];
        echo "Ruta completa: {$rutaPlano}\n";
        echo "Archivo existe: " . (file_exists($rutaPlano) ? 'SI' : 'NO') . "\n";
    }
} else {
    echo "❌ Proyecto no encontrado\n";
}
echo "\n";

// 4. Simular subida de archivo
echo "4. PRUEBA DE ESCRITURA:\n";
echo str_repeat("-", 80) . "\n";

$testDir = __DIR__ . '/uploads/planos';
$testFile = $testDir . '/test_' . time() . '.txt';

if (is_dir($testDir)) {
    $contenido = "Test de escritura - " . date('Y-m-d H:i:s');
    
    if (file_put_contents($testFile, $contenido)) {
        echo "✓ Se pudo escribir archivo de prueba\n";
        echo "  Archivo: {$testFile}\n";
        
        // Eliminar archivo de prueba
        if (unlink($testFile)) {
            echo "✓ Se pudo eliminar archivo de prueba\n";
        } else {
            echo "⚠️  No se pudo eliminar archivo de prueba\n";
        }
    } else {
        echo "❌ ERROR: No se pudo escribir archivo de prueba\n";
        echo "   Posible causa: Permisos insuficientes\n";
    }
} else {
    echo "❌ Directorio de prueba no existe\n";
}
echo "\n";

// 5. Verificar logs recientes
echo "5. LOGS DE ERROR RECIENTES:\n";
echo str_repeat("-", 80) . "\n";

$logDir = __DIR__ . '/storage/logs';
if (is_dir($logDir)) {
    $logFiles = glob($logDir . '/*.log');
    
    if (!empty($logFiles)) {
        // Obtener el archivo más reciente
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $latestLog = $logFiles[0];
        echo "Archivo de log más reciente: " . basename($latestLog) . "\n";
        echo "Última modificación: " . date('Y-m-d H:i:s', filemtime($latestLog)) . "\n\n";
        
        // Leer últimas 20 líneas
        $lines = file($latestLog);
        $recentLines = array_slice($lines, -20);
        
        echo "Últimas 20 líneas:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($recentLines as $line) {
            echo $line;
        }
    } else {
        echo "No hay archivos de log\n";
    }
} else {
    echo "Directorio de logs no existe\n";
}
echo "\n";

// 6. Verificar errores comunes
echo "6. CHECKLIST DE ERRORES COMUNES:\n";
echo str_repeat("-", 80) . "\n";

$checks = [
    'Directorio uploads/planos/ existe' => is_dir(__DIR__ . '/uploads/planos'),
    'Directorio uploads/planos/ es escribible' => is_writable(__DIR__ . '/uploads/planos'),
    'upload_max_filesize >= 5M' => (int)ini_get('upload_max_filesize') >= 5,
    'post_max_size >= 8M' => (int)ini_get('post_max_size') >= 8,
    'file_uploads habilitado' => ini_get('file_uploads'),
];

foreach ($checks as $check => $result) {
    $icon = $result ? '✓' : '❌';
    echo "{$icon} {$check}\n";
}

echo "\n";

// 7. RECOMENDACIONES
echo "=== RECOMENDACIONES ===\n";
echo str_repeat("-", 80) . "\n";

if (!is_writable(__DIR__ . '/uploads/planos')) {
    echo "❌ PROBLEMA: Directorio uploads/planos/ no es escribible\n";
    echo "   SOLUCIÓN:\n";
    echo "   En local:\n";
    echo "     No se requiere acción (Windows maneja permisos diferente)\n\n";
    echo "   En servidor Linux/Unix:\n";
    echo "     chmod 755 uploads/\n";
    echo "     chmod 755 uploads/planos/\n\n";
}

if ((int)ini_get('upload_max_filesize') < 5) {
    echo "❌ PROBLEMA: upload_max_filesize muy pequeño\n";
    echo "   SOLUCIÓN: Aumentar en php.ini:\n";
    echo "     upload_max_filesize = 10M\n";
    echo "     post_max_size = 12M\n\n";
}

echo "PARA DEBUGGING EN PRODUCCIÓN:\n";
echo "1. Agregar ?debug=1 a la URL para ver errores PHP\n";
echo "2. Revisar logs del servidor: storage/logs/\n";
echo "3. Revisar logs de Apache/Nginx\n";
echo "4. Verificar que el formulario tenga enctype=\"multipart/form-data\"\n";
echo "5. Verificar que el input file tenga name=\"plano_imagen\"\n";

echo "\n✅ Diagnóstico completado\n";
