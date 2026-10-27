<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
$db = Database::getConnection();

echo "=== COLUMNS OF centros_acopio ===\n";
$cols = $db->query("DESCRIBE centros_acopio")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}



