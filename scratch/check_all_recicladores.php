<?php
require_once __DIR__ . '/../app/config/database.php';

$db = Database::getConnection();
$stmt = $db->query("
    SELECT 
        u.id_usuario,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
        u.correo,
        u.numero_documento,
        u.telefono,
        l.nombre AS localidad,
        pr.ecopuntos,
        pr.material_recuperado,
        pr.nivel,
        (SELECT COUNT(*) FROM entregas e JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion WHERE r.id_reciclador = u.id_usuario AND e.estado = 'Validada') AS total_entregas,
        (SELECT COUNT(*) FROM canjes c WHERE c.id_usuario = u.id_usuario) AS total_canjes
    FROM usuarios u
    JOIN roles r ON u.id_rol = r.id_rol
    LEFT JOIN perfil_reciclador pr ON u.id_usuario = pr.id_usuario
    LEFT JOIN localidades l ON pr.id_localidad = l.id_localidad
    WHERE r.nombre = 'reciclador' AND u.estado = 'activo'
    ORDER BY u.id_usuario ASC
");

$recs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total recicladores activos: " . count($recs) . "\n";
echo "Muestra de recicladores:\n";
foreach (array_slice($recs, 0, 10) as $rec) {
    echo " - ID: " . $rec['id_usuario'] . " | " . $rec['nombre_completo'] . " | " . $rec['localidad'] . " | Pts: " . $rec['ecopuntos'] . " | Entregas: " . $rec['total_entregas'] . "\n";
}
