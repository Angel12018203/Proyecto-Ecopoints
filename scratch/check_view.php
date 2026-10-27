<?php
require_once __DIR__ . '/../app/config/database.php';

$db = Database::getConnection();
$stmt = $db->query("DESCRIBE vw_diccionario_ecopoints_mensual");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "COLUMNS IN vw_diccionario_ecopoints_mensual:\n";
foreach ($cols as $c) {
    echo " - " . $c['Field'] . " (" . $c['Type'] . ")\n";
}

$stmt2 = $db->query("SELECT COUNT(*) as total, COUNT(DISTINCT reciclador_id) as total_recicladores FROM vw_diccionario_ecopoints_mensual");
$cnt = $stmt2->fetch(PDO::FETCH_ASSOC);
echo "\nTotal monthly rows: " . $cnt['total'] . " across " . $cnt['total_recicladores'] . " recyclers.\n";

$stmt3 = $db->query("SELECT * FROM vw_diccionario_ecopoints_mensual ORDER BY mes DESC LIMIT 3");
$samples = $stmt3->fetchAll(PDO::FETCH_ASSOC);
echo "\nSample rows:\n";
print_r($samples);
