<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
$db = Database::getConnection();

foreach (['solicitudes', 'recolecciones', 'entregas', 'beneficios', 'canjes', 'ecopuntos'] as $tbl) {
    echo "=== $tbl ===\n";
    $cols = $db->query("DESCRIBE $tbl")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  {$c['Field']} ({$c['Type']})\n";
    }
}
