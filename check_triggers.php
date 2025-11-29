<?php
require 'config/config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

echo "=== ESTRUCTURA DE LA TABLA AMORTIZACIONES ===\n\n";
$stmt = $pdo->query('SHOW CREATE TABLE amortizaciones');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo $result['Create Table'];
echo "\n\n";

echo "=== TRIGGERS DE AMORTIZACIONES ===\n\n";
$stmt = $pdo->query("SHOW TRIGGERS WHERE `Table` = 'amortizaciones'");
$triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($triggers)) {
    echo "No hay triggers en esta tabla.\n";
} else {
    foreach ($triggers as $trigger) {
        echo "Trigger: " . $trigger['Trigger'] . "\n";
        echo "Evento: " . $trigger['Event'] . "\n";
        echo "Timing: " . $trigger['Timing'] . "\n\n";
        
        // Obtener el código del trigger
        $stmt2 = $pdo->query("SHOW CREATE TRIGGER " . $trigger['Trigger']);
        $triggerDef = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo "SQL:\n" . $triggerDef['SQL Original Statement'] . "\n";
        echo "\n" . str_repeat("=", 80) . "\n\n";
    }
}
