<?php
require 'config/config.php';
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);

echo "Estructura de tabla users:\n";
echo "==========================\n";
$stmt = $pdo->query('DESCRIBE users');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "{$col['Field']} - {$col['Type']}\n";
}

echo "\n\nUsuarios en la base de datos:\n";
echo "=============================\n";
$stmt = $pdo->query('SELECT * FROM users');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
