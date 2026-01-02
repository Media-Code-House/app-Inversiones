<?php
/**
 * Diagnóstico: Problema del Mapa en Servidor
 * Verifica por qué el plano interactivo no se muestra en producción
 */

require_once 'config/config.php';
require_once 'core/Database.php';

$db = Database::getInstance();

echo "=== DIAGNÓSTICO DEL MAPA EN SERVIDOR ===\n\n";

// 1. Verificar configuración del servidor
echo "1. CONFIGURACIÓN DEL ENTORNO:\n";
echo str_repeat("-", 80) . "\n";
echo "Host: " . ($_SERVER['HTTP_HOST'] ?? 'CLI') . "\n";
echo "Servidor Web: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'CLI') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? getcwd()) . "\n";
echo "Script Path: " . __DIR__ . "\n";
echo "APP_URL: " . APP_URL . "\n";
echo "DEBUG_MODE: " . (DEBUG_MODE ? 'SI' : 'NO') . "\n\n";

// 2. Verificar proyectos con plano
echo "2. PROYECTOS CON PLANO CARGADO:\n";
echo str_repeat("-", 80) . "\n";
$proyectos = $db->fetchAll("
    SELECT 
        id,
        codigo,
        nombre,
        plano_imagen
    FROM proyectos
    WHERE plano_imagen IS NOT NULL
    AND plano_imagen != ''
    ORDER BY id
");

if (empty($proyectos)) {
    echo "❌ NO HAY PROYECTOS CON PLANO CARGADO\n\n";
} else {
    foreach ($proyectos as $proy) {
        echo "✓ Proyecto ID {$proy['id']}: {$proy['nombre']}\n";
        echo "  Código: {$proy['codigo']}\n";
        echo "  Ruta plano: {$proy['plano_imagen']}\n";
        
        // Verificar si el archivo existe
        $rutaRelativa = $proy['plano_imagen'];
        $rutaAbsoluta = __DIR__ . '/' . $rutaRelativa;
        
        if (file_exists($rutaAbsoluta)) {
            $tamaño = filesize($rutaAbsoluta);
            $permisos = substr(sprintf('%o', fileperms($rutaAbsoluta)), -4);
            echo "  ✓ Archivo existe: {$rutaAbsoluta}\n";
            echo "  ✓ Tamaño: " . number_format($tamaño) . " bytes\n";
            echo "  ✓ Permisos: {$permisos}\n";
            
            // Verificar tipo MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $rutaAbsoluta);
            finfo_close($finfo);
            echo "  ✓ Tipo MIME: {$mimeType}\n";
        } else {
            echo "  ❌ ARCHIVO NO EXISTE: {$rutaAbsoluta}\n";
        }
        echo "\n";
    }
}

// 3. Verificar directorio de uploads
echo "3. VERIFICAR DIRECTORIO DE UPLOADS:\n";
echo str_repeat("-", 80) . "\n";
$directorios = [
    'uploads',
    'uploads/planos',
    'public/uploads',
    'public/uploads/planos'
];

foreach ($directorios as $dir) {
    $rutaCompleta = __DIR__ . '/' . $dir;
    echo "Directorio: {$dir}\n";
    
    if (is_dir($rutaCompleta)) {
        $permisos = substr(sprintf('%o', fileperms($rutaCompleta)), -4);
        echo "  ✓ Existe\n";
        echo "  ✓ Permisos: {$permisos}\n";
        
        // Listar archivos
        $archivos = scandir($rutaCompleta);
        $archivos = array_diff($archivos, ['.', '..']);
        echo "  ✓ Archivos: " . count($archivos) . "\n";
        
        if (count($archivos) > 0) {
            echo "  Archivos encontrados:\n";
            foreach (array_slice($archivos, 0, 5) as $archivo) {
                $rutaArchivo = $rutaCompleta . '/' . $archivo;
                if (is_file($rutaArchivo)) {
                    $tamaño = filesize($rutaArchivo);
                    echo "    - {$archivo} (" . number_format($tamaño) . " bytes)\n";
                }
            }
        }
    } else {
        echo "  ❌ No existe\n";
    }
    echo "\n";
}

// 4. Verificar rutas en HTML generado
echo "4. SIMULACIÓN DE RUTA EN HTML:\n";
echo str_repeat("-", 80) . "\n";
if (!empty($proyectos)) {
    $primerProyecto = $proyectos[0];
    $rutaPlano = $primerProyecto['plano_imagen'];
    
    echo "Ruta en BD: {$rutaPlano}\n";
    echo "HTML generado: <img src=\"/{$rutaPlano}\" />\n";
    echo "URL completa: " . APP_URL . "/{$rutaPlano}\n\n";
    
    // Probar diferentes variantes de ruta
    echo "VARIANTES DE RUTA A PROBAR:\n";
    $variantes = [
        "/{$rutaPlano}",
        "{$rutaPlano}",
        APP_URL . "/{$rutaPlano}",
        str_replace('uploads/', '/uploads/', $rutaPlano),
        str_replace('uploads/', 'public/uploads/', $rutaPlano)
    ];
    
    foreach ($variantes as $i => $variante) {
        echo "  " . ($i + 1) . ". {$variante}\n";
    }
}
echo "\n";

// 5. Verificar lotes con coordenadas
echo "5. LOTES CON COORDENADAS EN EL PLANO:\n";
echo str_repeat("-", 80) . "\n";
$lotesConCoordenadas = $db->fetchAll("
    SELECT 
        l.id,
        l.codigo_lote,
        l.proyecto_id,
        l.plano_x,
        l.plano_y,
        p.nombre as proyecto_nombre
    FROM lotes l
    INNER JOIN proyectos p ON l.proyecto_id = p.id
    WHERE l.plano_x IS NOT NULL
    AND l.plano_y IS NOT NULL
    AND p.plano_imagen IS NOT NULL
    LIMIT 10
");

if (empty($lotesConCoordenadas)) {
    echo "❌ NO HAY LOTES CON COORDENADAS GUARDADAS\n";
    echo "   Esto puede ser normal si aún no se han posicionado lotes en el plano.\n\n";
} else {
    echo "✓ Se encontraron " . count($lotesConCoordenadas) . " lotes con coordenadas:\n";
    foreach ($lotesConCoordenadas as $lote) {
        echo "  - {$lote['codigo_lote']} ({$lote['proyecto_nombre']}): X={$lote['plano_x']}%, Y={$lote['plano_y']}%\n";
    }
    echo "\n";
}

// 6. Verificar permisos de archivos
echo "6. VERIFICAR PERMISOS Y ACCESO:\n";
echo str_repeat("-", 80) . "\n";
echo "Usuario PHP: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'N/A') . "\n";
echo "UID: " . (function_exists('posix_geteuid') ? posix_geteuid() : 'N/A') . "\n";
echo "GID: " . (function_exists('posix_getegid') ? posix_getegid() : 'N/A') . "\n";

if (!empty($proyectos)) {
    $rutaArchivo = __DIR__ . '/' . $proyectos[0]['plano_imagen'];
    if (file_exists($rutaArchivo)) {
        echo "Readable: " . (is_readable($rutaArchivo) ? 'SI' : 'NO') . "\n";
        echo "Writable: " . (is_writable($rutaArchivo) ? 'SI' : 'NO') . "\n";
    }
}
echo "\n";

// 7. RECOMENDACIONES
echo "=== RECOMENDACIONES ===\n";
echo str_repeat("-", 80) . "\n";

if (empty($proyectos)) {
    echo "❌ PROBLEMA 1: No hay proyectos con plano cargado\n";
    echo "   SOLUCIÓN:\n";
    echo "   1. Ir a /proyectos/edit/{id}\n";
    echo "   2. Subir una imagen del plano\n";
    echo "   3. Posicionar los lotes en el plano\n\n";
}

// Verificar si hay imágenes pero no se ven
foreach ($proyectos as $proy) {
    $rutaAbsoluta = __DIR__ . '/' . $proy['plano_imagen'];
    if (!file_exists($rutaAbsoluta)) {
        echo "❌ PROBLEMA 2: Imagen registrada pero archivo no existe\n";
        echo "   Proyecto: {$proy['nombre']}\n";
        echo "   Ruta esperada: {$rutaAbsoluta}\n";
        echo "   SOLUCIÓN:\n";
        echo "   1. Verificar que el archivo se subió correctamente\n";
        echo "   2. Verificar permisos del directorio uploads/planos/\n";
        echo "   3. Volver a subir la imagen desde /proyectos/edit/{$proy['id']}\n\n";
    }
}

// Verificar rutas
$hayRutasIncorrectas = false;
foreach ($proyectos as $proy) {
    if (strpos($proy['plano_imagen'], 'uploads/planos/') === false) {
        $hayRutasIncorrectas = true;
        echo "⚠️  ADVERTENCIA: Ruta de plano no estándar\n";
        echo "   Proyecto: {$proy['nombre']}\n";
        echo "   Ruta: {$proy['plano_imagen']}\n";
        echo "   Se esperaba: uploads/planos/nombrearchivo.ext\n\n";
    }
}

echo "\n✅ Diagnóstico completado\n";
echo "\nPARA PROBAR EN EL NAVEGADOR:\n";
if (!empty($proyectos)) {
    echo "1. Abrir: " . APP_URL . "/proyectos/show/{$proyectos[0]['id']}\n";
    echo "2. Buscar la sección 'Plano del Proyecto'\n";
    echo "3. Abrir consola del navegador (F12) y buscar errores de carga de imagen\n";
    echo "4. Verificar en Network tab si la imagen se solicita y qué respuesta da\n";
}
