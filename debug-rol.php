<?php
/**
 * Script de depuración para verificar rol y permisos del usuario actual
 */

session_start();

echo "=== DEBUG: ROL Y PERMISOS DEL USUARIO ===\n\n";

// Verificar si hay sesión
if (!isset($_SESSION['user'])) {
    echo "❌ NO HAY SESIÓN ACTIVA\n";
    echo "El usuario no está logueado.\n";
    exit;
}

echo "✓ Usuario logueado\n\n";

// Mostrar información del usuario
echo "INFORMACIÓN DEL USUARIO:\n";
echo "  ID: " . ($_SESSION['user']['id'] ?? 'no definido') . "\n";
echo "  Username: " . ($_SESSION['user']['username'] ?? 'no definido') . "\n";
echo "  Nombre: " . ($_SESSION['user']['nombre'] ?? 'no definido') . "\n";
echo "  Email: " . ($_SESSION['user']['email'] ?? 'no definido') . "\n";
echo "  Rol: '" . ($_SESSION['user']['rol'] ?? 'no definido') . "'\n";
echo "  Rol (longitud): " . strlen($_SESSION['user']['rol'] ?? '') . " caracteres\n";
echo "  Rol (hex): " . bin2hex($_SESSION['user']['rol'] ?? '') . "\n\n";

// Verificar el rol específico
$rol = $_SESSION['user']['rol'] ?? '';

echo "VERIFICACIÓN DE ROL:\n";
echo "  ¿Es 'vendedor'? " . ($rol === 'vendedor' ? '✓ SÍ' : '❌ NO') . "\n";
echo "  ¿Es 'administrador'? " . ($rol === 'administrador' ? '✓ SÍ' : '❌ NO') . "\n";
echo "  ¿Es 'consulta'? " . ($rol === 'consulta' ? '✓ SÍ' : '❌ NO') . "\n\n";

// Cargar helpers para probar can()
require_once 'core/helpers.php';

echo "PERMISOS (usando función can()):\n";
$permisos = [
    'ver_clientes',
    'crear_clientes',
    'editar_clientes',
    'eliminar_clientes',
    'ver_lotes',
    'crear_lotes',
    'ver_proyectos',
    'crear_proyectos'
];

foreach ($permisos as $permiso) {
    $tiene = can($permiso);
    echo "  $permiso: " . ($tiene ? '✓ TIENE' : '❌ NO TIENE') . "\n";
}

echo "\n";

// Mapeo directo de permisos
$rolePermissions = [
    'administrador' => [
        'ver_lotes', 'crear_lotes', 'editar_lotes', 'eliminar_lotes',
        'ver_clientes', 'crear_clientes', 'editar_clientes', 'eliminar_clientes',
        'ver_proyectos', 'crear_proyectos', 'editar_proyectos', 'eliminar_proyectos'
    ],
    'vendedor' => [
        'ver_lotes', 'crear_lotes', 'editar_lotes', 'eliminar_lotes',
        'ver_clientes', 'crear_clientes',
        'ver_proyectos', 'crear_proyectos', 'editar_proyectos'
    ],
    'consulta' => [
        'ver_lotes', 'ver_clientes', 'crear_clientes', 'ver_proyectos'
    ]
];

echo "PERMISOS ESPERADOS PARA EL ROL '$rol':\n";
if (isset($rolePermissions[$rol])) {
    foreach ($rolePermissions[$rol] as $p) {
        echo "  - $p\n";
    }
} else {
    echo "  ❌ Rol no encontrado en el mapeo de permisos\n";
}

echo "\n=== DIAGNÓSTICO ===\n";
if ($rol === 'vendedor' && !can('crear_clientes')) {
    echo "⚠️  PROBLEMA ENCONTRADO:\n";
    echo "El rol es 'vendedor' pero can('crear_clientes') retorna false\n";
    echo "Esto indica un problema en la función can() o en el mapeo de permisos\n";
} elseif ($rol !== 'vendedor') {
    echo "⚠️  El rol actual es '$rol', no 'vendedor'\n";
    echo "Verifica que el usuario en la base de datos tenga el rol correcto\n";
} elseif (can('crear_clientes')) {
    echo "✅ Todo está correcto\n";
    echo "El usuario tiene el permiso 'crear_clientes'\n";
    echo "El botón debería aparecer en /clientes\n";
}
