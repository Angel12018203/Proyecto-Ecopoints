<?php
require_once __DIR__ . '/../app/config/database.php';

$db = Database::getConnection();
$stmt = $db->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Tables in database:\n";
foreach ($tables as $t) {
    echo " - " . $t . "\n";
}
