<?php
/**
 * Script para generar hashes de contraseñas para usuarios de prueba RBAC
 * Ejecutar: php database/generar_passwords_rbac.php
 */

// Contraseñas de prueba
$passwords = [
    'Admin123' => 'Contraseña para usuario Administrador (ya existe)',
    'Consulta123' => 'Contraseña para usuario Consulta',
    'Vendedor123' => 'Contraseña para usuario Vendedor'
];

echo "========================================\n";
echo "GENERADOR DE PASSWORDS PARA USUARIOS RBAC\n";
echo "========================================\n\n";

foreach ($passwords as $password => $descripcion) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    echo "Password: {$password}\n";
    echo "Descripción: {$descripcion}\n";
    echo "Hash: {$hash}\n";
    echo "----------------------------------------\n\n";
}

echo "========================================\n";
echo "INSTRUCCIONES:\n";
echo "========================================\n";
echo "1. Copia los hashes generados arriba\n";
echo "2. Abre el archivo: database/crear_usuarios_prueba_rbac.sql\n";
echo "3. Reemplaza los hashes placeholder con los reales\n";
echo "4. Ejecuta el script SQL en phpMyAdmin\n";
echo "========================================\n";
