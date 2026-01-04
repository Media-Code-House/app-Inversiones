<?php
require 'config/config.php';
require 'core/Database.php';

$db = Database::getInstance();

echo "=== ESTRUCTURA DE LA TABLA proyectos ===\n\n";
$columns = $db->fetchAll("SHOW COLUMNS FROM proyectos");
foreach ($columns as $col) {
    echo "{$col['Field']} - {$col['Type']} - {$col['Null']} - {$col['Key']}\n";
}
