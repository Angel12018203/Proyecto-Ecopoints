<?php
require_once __DIR__ . '/../app/config/database.php';
$db = Database::getConnection();

echo "ENTREGAS:\n";
foreach ($db->query("DESCRIBE entregas")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo " - " . $c['Field'] . " (" . $c['Type'] . ")\n";
}

echo "\nECOPUNTOS:\n";
foreach ($db->query("DESCRIBE ecopuntos")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo " - " . $c['Field'] . " (" . $c['Type'] . ")\n";
}
