<?php
require_once __DIR__ . '/../app/config/database.php';
$db = Database::getConnection();

echo "USUARIOS:\n";
foreach ($db->query("DESCRIBE usuarios")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo " - " . $c['Field'] . "\n";
}

echo "\nPERFIL_RECICLADOR:\n";
foreach ($db->query("DESCRIBE perfil_reciclador")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo " - " . $c['Field'] . "\n";
}
