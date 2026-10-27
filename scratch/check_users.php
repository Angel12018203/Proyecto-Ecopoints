<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
$db = Database::getConnection();

echo "--- SOLICITUDES ---\n";
print_r($db->query("SELECT id_solicitud, id_ciudadano, estado, fecha_solicitud, direccion FROM solicitudes LIMIT 5")->fetchAll(PDO::FETCH_ASSOC));











