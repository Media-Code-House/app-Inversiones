<?php
/**
 * Script para ejecutar en el SERVIDOR
 * Agrega la columna plano_imagen a la tabla proyectos
 * 
 * INSTRUCCIONES:
 * 1. Sube este archivo a la raíz del proyecto
 * 2. Accede desde: https://inversionesdevelop.mch.com.co/ejecutar_agregar_plano_imagen.php
 * 3. Borra este archivo después de ejecutarlo
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'core/Database.php';

// Solo permitir en modo debug o desde servidor
if (!DEBUG_MODE && !isset($_GET['force'])) {
    die("Este script solo puede ejecutarse en modo debug. Agrega ?force=1 para forzar.");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Columna plano_imagen</title>
    <style>
        body { 
            font-family: 'Courier New', monospace; 
            background: #1e1e1e; 
            color: #d4d4d4; 
            padding: 20px;
            line-height: 1.6;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: #252526; 
            padding: 30px; 
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        h1 { color: #4ec9b0; border-bottom: 2px solid #4ec9b0; padding-bottom: 10px; }
        h2 { color: #569cd6; margin-top: 30px; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #9cdcfe; }
        pre { 
            background: #1e1e1e; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            border-left: 4px solid #569cd6;
        }
        .badge { 
            display: inline-block; 
            padding: 4px 8px; 
            border-radius: 3px; 
            font-weight: bold; 
            margin-right: 8px;
        }
        .badge-success { background: #107c10; }
        .badge-error { background: #c72a1c; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007acc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover { background: #005a9e; }
        hr { border: 1px solid #3e3e42; margin: 30px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #3e3e42; }
        th { color: #569cd6; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Agregar Columna plano_imagen</h1>
    
<?php
try {
    $db = Database::getInstance();
    
    echo "<h2>1. Verificando si la columna existe...</h2>\n";
    
    $check = $db->fetch("
        SELECT COUNT(*) as existe 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'proyectos' 
        AND COLUMN_NAME = 'plano_imagen'
    ");
    
    if ($check['existe'] > 0) {
        echo "<p class='success'><span class='badge badge-success'>✓</span> La columna 'plano_imagen' YA EXISTE</p>\n";
    } else {
        echo "<p class='error'><span class='badge badge-error'>✗</span> La columna 'plano_imagen' NO EXISTE</p>\n";
        echo "<h2>2. Agregando columna...</h2>\n";
        
        $sql = "ALTER TABLE proyectos 
                ADD COLUMN plano_imagen VARCHAR(255) NULL 
                COMMENT 'Ruta de la imagen del plano del proyecto' 
                AFTER fecha_inicio";
        
        try {
            $db->query($sql);
            echo "<p class='success'><span class='badge badge-success'>✓</span> ¡Columna agregada exitosamente!</p>\n";
        } catch (Exception $e) {
            echo "<p class='error'><span class='badge badge-error'>✗</span> Error al agregar columna: " . htmlspecialchars($e->getMessage()) . "</p>\n";
            throw $e;
        }
    }
    
    echo "<hr>\n";
    echo "<h2>3. Estructura de la tabla proyectos:</h2>\n";
    
    $columnas = $db->fetchAll("DESCRIBE proyectos");
    echo "<table>\n";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>\n";
    
    foreach ($columnas as $col) {
        $clase = $col['Field'] === 'plano_imagen' ? ' class="success"' : '';
        $marca = $col['Field'] === 'plano_imagen' ? '<span class="badge badge-success">✓</span> ' : '';
        echo "<tr{$clase}>\n";
        echo "<td>{$marca}" . htmlspecialchars($col['Field']) . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>\n";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<hr>\n";
    echo "<h2 class='success'>✓ PROCESO COMPLETADO</h2>\n";
    echo "<p class='info'>Ahora puedes:</p>\n";
    echo "<ul>\n";
    echo "<li>Subir imágenes de planos desde <code>/proyectos/edit/{id}</code></li>\n";
    echo "<li>Ver el plano interactivo en <code>/proyectos/show/{id}</code></li>\n";
    echo "<li>Posicionar lotes en el plano</li>\n";
    echo "</ul>\n";
    
    echo "<p class='warning'><strong>⚠️ IMPORTANTE:</strong> Borra este archivo por seguridad después de usarlo.</p>\n";
    
    echo "<a href='/proyectos' class='btn'>← Ir a Proyectos</a>\n";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ ERROR</h2>\n";
    echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<h3>Stack trace:</h3>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}
?>

</div>
</body>
</html>
