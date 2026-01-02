<?php
/**
 * SOLUCIÓN: Verificar y Corregir Imágenes de Planos Faltantes
 */

require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== SOLUCIÓN: IMÁGENES DE PLANOS FALTANTES ===\n\n";

// 1. Listar proyectos con planos faltantes
echo "1. PROYECTOS CON IMÁGENES FALTANTES:\n";
echo str_repeat("-", 80) . "\n";

$proyectos = $db->fetchAll("
    SELECT id, codigo, nombre, plano_imagen
    FROM proyectos
    WHERE plano_imagen IS NOT NULL
    AND plano_imagen != ''
    ORDER BY id
");

$proyectosConProblema = [];
$proyectosOK = [];

foreach ($proyectos as $proy) {
    $rutaAbsoluta = __DIR__ . '/' . $proy['plano_imagen'];
    
    if (!file_exists($rutaAbsoluta)) {
        $proyectosConProblema[] = $proy;
        echo "❌ ID {$proy['id']}: {$proy['nombre']}\n";
        echo "   Archivo esperado: {$proy['plano_imagen']}\n";
        echo "   No existe en: {$rutaAbsoluta}\n\n";
    } else {
        $proyectosOK[] = $proy;
        echo "✓ ID {$proy['id']}: {$proy['nombre']} - OK\n";
    }
}

echo "\nResumen:\n";
echo "✓ Proyectos con imagen correcta: " . count($proyectosOK) . "\n";
echo "❌ Proyectos con imagen faltante: " . count($proyectosConProblema) . "\n\n";

// 2. Buscar imágenes disponibles
echo "2. IMÁGENES DISPONIBLES EN uploads/planos/:\n";
echo str_repeat("-", 80) . "\n";

$dirPlanos = __DIR__ . '/uploads/planos';
$imagenesDisponibles = [];

if (is_dir($dirPlanos)) {
    $archivos = scandir($dirPlanos);
    foreach ($archivos as $archivo) {
        if ($archivo != '.' && $archivo != '..') {
            $rutaCompleta = $dirPlanos . '/' . $archivo;
            if (is_file($rutaCompleta)) {
                $tamaño = filesize($rutaCompleta);
                $imagenesDisponibles[] = $archivo;
                echo "  - {$archivo} (" . number_format($tamaño) . " bytes)\n";
            }
        }
    }
    echo "\nTotal de imágenes disponibles: " . count($imagenesDisponibles) . "\n\n";
} else {
    echo "❌ Directorio uploads/planos no existe\n\n";
}

// 3. Opciones de solución
echo "3. OPCIONES DE SOLUCIÓN:\n";
echo str_repeat("-", 80) . "\n";

if (count($proyectosConProblema) > 0) {
    echo "\nOPCIÓN A: Limpiar registros de planos faltantes (BD)\n";
    echo "Esto borrará la referencia al plano en la base de datos.\n";
    echo "Los proyectos quedarán sin plano asignado.\n\n";
    
    echo "¿Deseas limpiar los registros de planos faltantes? (s/n): ";
    $respuesta = trim(fgets(STDIN));
    
    if (strtolower($respuesta) === 's') {
        foreach ($proyectosConProblema as $proy) {
            $db->execute(
                "UPDATE proyectos SET plano_imagen = NULL WHERE id = ?",
                [$proy['id']]
            );
            echo "✓ Limpiado: {$proy['nombre']}\n";
        }
        echo "\n✅ Se limpiaron " . count($proyectosConProblema) . " registros\n";
        echo "Ahora puedes subir nuevas imágenes desde /proyectos/edit/{id}\n\n";
    } else {
        echo "Operación cancelada\n\n";
    }
}

// 4. Verificar configuración para servidor
echo "4. CONFIGURACIÓN PARA SERVIDOR:\n";
echo str_repeat("-", 80) . "\n";
echo "Para que las imágenes funcionen en el servidor, verifica:\n\n";

echo "a) Permisos de directorio:\n";
echo "   chmod 755 uploads/\n";
echo "   chmod 755 uploads/planos/\n";
echo "   chmod 644 uploads/planos/*.jpg\n";
echo "   chmod 644 uploads/planos/*.png\n\n";

echo "b) .htaccess está configurado para servir archivos estáticos:\n";
$htaccessPath = __DIR__ . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo "   ✓ .htaccess existe\n";
    $contenido = file_get_contents($htaccessPath);
    if (strpos($contenido, 'RewriteCond %{REQUEST_FILENAME} !-f') !== false) {
        echo "   ✓ Configuración correcta para servir archivos\n";
    }
} else {
    echo "   ❌ .htaccess no encontrado\n";
}
echo "\n";

echo "c) Verificar que las rutas en HTML son correctas:\n";
if (!empty($proyectosOK)) {
    $ejemplo = $proyectosOK[0];
    echo "   Ejemplo: <img src=\"/{$ejemplo['plano_imagen']}\" />\n";
    echo "   URL: " . APP_URL . "/{$ejemplo['plano_imagen']}\n";
}
echo "\n";

echo "d) En el servidor, probar acceso directo a la imagen:\n";
if (!empty($proyectosOK)) {
    $ejemplo = $proyectosOK[0];
    echo "   https://inversionesdevelop.mch.com.co/{$ejemplo['plano_imagen']}\n";
}
echo "\n";

// 5. Verificar diferencias entre local y servidor
echo "5. CHECKLIST PARA EL SERVIDOR:\n";
echo str_repeat("-", 80) . "\n";
echo "[ ] 1. Directorio uploads/planos/ existe\n";
echo "[ ] 2. Directorio uploads/planos/ tiene permisos 755\n";
echo "[ ] 3. Imágenes tienen permisos 644\n";
echo "[ ] 4. .htaccess está en la raíz del proyecto\n";
echo "[ ] 5. mod_rewrite está habilitado en Apache\n";
echo "[ ] 6. Las imágenes están subidas al servidor (no solo en local)\n";
echo "[ ] 7. La ruta en la BD no tiene caracteres raros\n";
echo "[ ] 8. Probar acceso directo a: https://servidor.com/uploads/planos/imagen.jpg\n";
echo "\n";

echo "✅ Script completado\n";
