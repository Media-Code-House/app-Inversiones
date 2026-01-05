<?php
session_start();

echo "<pre>";
echo "=== DEBUG SESIÓN ===\n\n";
echo "Usuario logueado: " . (isset($_SESSION['user']) ? 'SÍ' : 'NO') . "\n";

if (isset($_SESSION['user'])) {
    echo "Email: " . $_SESSION['user']['email'] . "\n";
    echo "Nombre: " . $_SESSION['user']['nombre'] . "\n";
    echo "Rol: " . $_SESSION['user']['rol'] . "\n";
    echo "\nRol es 'administrador': " . ($_SESSION['user']['rol'] === 'administrador' ? 'SÍ' : 'NO') . "\n";
}

echo "\nSesión completa:\n";
print_r($_SESSION);
echo "</pre>";
