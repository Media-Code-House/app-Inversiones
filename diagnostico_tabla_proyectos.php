<?php
/**
 * DIAGNÓSTICO: Estructura Real de la Tabla proyectos
 * Ejecutar desde: https://inversionesdevelop.mch.com.co/diagnostico_tabla_proyectos.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'core/Database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Tabla Proyectos</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: #252526; padding: 30px; border-radius: 8px; }
        h1 { color: #4ec9b0; border-bottom: 2px solid #4ec9b0; padding-bottom: 10px; }
        h2 { color: #569cd6; margin-top: 30px; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 5px; overflow-x: auto; border-left: 4px solid #569cd6; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #3e3e42; }
        th { color: #569cd6; background: #1e1e1e; }
        .highlight { background: #2d5a2d; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Diagnóstico: Estructura Real de la Tabla proyectos</h1>
    
<?php
try {
    $db = Database::getInstance();
    
    echo "<h2>1. Base de Datos Actual:</h2>\n";
    $dbName = $db->fetch("SELECT DATABASE() as db");
    echo "<p class='success'>📊 Base de datos: <strong>{$dbName['db']}</strong></p>\n";
    
    echo "<h2>2. Estructura de la tabla proyectos (DESCRIBE):</h2>\n";
    $columnas = $db->fetchAll("DESCRIBE proyectos");
    
    echo "<table>\n";
    echo "<tr><th>#</th><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>\n";
    
    $tiene_plano = false;
    foreach ($columnas as $i => $col) {
        if ($col['Field'] === 'plano_imagen') {
            $tiene_plano = true;
            $clase = ' class="highlight"';
        } else {
            $clase = '';
        }
        
        echo "<tr{$clase}>\n";
        echo "<td>" . ($i + 1) . "</td>\n";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>\n";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    if ($tiene_plano) {
        echo "<p class='success'>✅ La columna 'plano_imagen' EXISTE en la tabla</p>\n";
    } else {
        echo "<p class='error'>❌ La columna 'plano_imagen' NO EXISTE en la tabla</p>\n";
    }
    
    echo "<h2>3. Comando SHOW CREATE TABLE:</h2>\n";
    $create = $db->fetch("SHOW CREATE TABLE proyectos");
    echo "<pre>" . htmlspecialchars($create['Create Table']) . "</pre>\n";
    
    echo "<h2>4. Prueba de UPDATE simulado:</h2>\n";
    
    // Obtener un proyecto existente
    $proyecto = $db->fetch("SELECT * FROM proyectos LIMIT 1");
    
    if ($proyecto) {
        echo "<p>Probando UPDATE en proyecto ID: {$proyecto['id']}</p>\n";
        
        // Preparar columnas para UPDATE
        $columnas_update = [];
        foreach ($columnas as $col) {
            if ($col['Field'] !== 'id' && $col['Field'] !== 'total_lotes') {
                $columnas_update[] = $col['Field'];
            }
        }
        
        echo "<p>Columnas a actualizar:</p>\n<pre>";
        print_r($columnas_update);
        echo "</pre>\n";
        
        // Construir SQL de UPDATE
        $set_clause = implode(", ", array_map(function($col) {
            return "`{$col}` = ?";
        }, $columnas_update));
        
        $sql_test = "UPDATE proyectos SET {$set_clause} WHERE id = ?";
        
        echo "<p>SQL generado para UPDATE:</p>\n";
        echo "<pre>" . htmlspecialchars($sql_test) . "</pre>\n";
        
    } else {
        echo "<p class='warning'>No hay proyectos en la tabla</p>\n";
    }
    
    echo "<h2>5. Verificar OPCache / Caché de PHP:</h2>\n";
    if (function_exists('opcache_get_status')) {
        $opcache = opcache_get_status();
        if ($opcache !== false) {
            echo "<p class='warning'>⚠️ OPCache está ACTIVO</p>\n";
            echo "<p>Esto puede causar que PHP use código antiguo en caché.</p>\n";
            
            if (function_exists('opcache_reset')) {
                echo "<p><strong>Limpiando caché...</strong></p>\n";
                opcache_reset();
                echo "<p class='success'>✅ Caché limpiado</p>\n";
            }
        } else {
            echo "<p class='success'>✓ OPCache no está activo</p>\n";
        }
    } else {
        echo "<p class='success'>✓ OPCache no disponible</p>\n";
    }
    
    echo "<h2>6. Archivo ProyectoModel.php cargado:</h2>\n";
    $modelPath = __DIR__ . '/app/Models/ProyectoModel.php';
    if (file_exists($modelPath)) {
        echo "<p class='success'>✓ Archivo existe: {$modelPath}</p>\n";
        echo "<p>Última modificación: " . date('Y-m-d H:i:s', filemtime($modelPath)) . "</p>\n";
        echo "<p>Tamaño: " . filesize($modelPath) . " bytes</p>\n";
        
        // Mostrar líneas del método update
        $contenido = file_get_contents($modelPath);
        if (strpos($contenido, 'plano_imagen') !== false) {
            echo "<p class='success'>✓ El archivo CONTIENE 'plano_imagen'</p>\n";
        } else {
            echo "<p class='error'>❌ El archivo NO CONTIENE 'plano_imagen'</p>\n";
        }
        
        // Buscar el método update
        if (preg_match('/function update\(.*?\{(.+?)\n\s+\}/s', $contenido, $matches)) {
            echo "<h3>Código del método update():</h3>\n";
            echo "<pre>" . htmlspecialchars(substr($matches[0], 0, 800)) . "...</pre>\n";
        }
    } else {
        echo "<p class='error'>❌ Archivo no encontrado</p>\n";
    }
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ ERROR</h2>\n";
    echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}
?>

<h2>⚠️ IMPORTANTE:</h2>
<p><strong>Después de revisar este diagnóstico:</strong></p>
<ol>
    <li>Verifica si la columna plano_imagen existe</li>
    <li>Si NO existe, ejecútala en SQL: <code>ALTER TABLE proyectos ADD COLUMN plano_imagen VARCHAR(255) NULL AFTER fecha_inicio;</code></li>
    <li>Si SÍ existe, el problema puede ser caché de PHP</li>
    <li>Borra este archivo por seguridad</li>
</ol>

</div>
</body>
</html>