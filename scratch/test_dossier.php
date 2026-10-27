<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/services/InferenciaService.php';

$db = Database::getConnection();
$service = new InferenciaService();

$idRec = 6;
$stmtRec = $db->prepare("
    SELECT 
        u.id_usuario,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
        u.correo,
        u.telefono,
        u.fecha_registro,
        pr.zona_trabajo,
        pr.medio_transporte,
        COALESCE(pr.ecopuntos, 0) AS ecopuntos,
        COALESCE(pr.material_recuperado, 0) AS material_recuperado,
        pr.nivel,
        l.nombre AS localidad
    FROM usuarios u
    LEFT JOIN perfil_reciclador pr ON u.id_usuario = pr.id_usuario
    LEFT JOIN localidades l ON pr.id_localidad = l.id_localidad
    WHERE u.id_usuario = ?
");
$stmtRec->execute([$idRec]);
$info = $stmtRec->fetch(PDO::FETCH_ASSOC);

$stmtEnt = $db->prepare("
    SELECT 
        e.id_entrega,
        e.fecha_entrega,
        e.cantidad_validada,
        e.puntos_otorgados,
        e.estado,
        m.nombre AS material,
        ca.nombre AS centro_acopio
    FROM entregas e
    JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
    JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN centros_acopio ca ON e.id_centro_acopio = ca.id_centro_acopio
    WHERE r.id_reciclador = ?
    ORDER BY e.fecha_entrega DESC
    LIMIT 5
");
$stmtEnt->execute([$idRec]);
$entregas = $stmtEnt->fetchAll(PDO::FETCH_ASSOC);

$stmtCan = $db->prepare("
    SELECT 
        c.id_canje,
        c.fecha_canje,
        c.puntos_canjeados,
        c.codigo_canje,
        c.estado,
        b.nombre AS beneficio,
        b.categoria
    FROM canjes c
    JOIN beneficios b ON c.id_beneficio = b.id_beneficio
    WHERE c.id_usuario = ?
    ORDER BY c.fecha_canje DESC
    LIMIT 5
");
$stmtCan->execute([$idRec]);
$canjes = $stmtCan->fetchAll(PDO::FETCH_ASSOC);

$analitica = $service->obtenerOEjecutarAnalisis($idRec, false);

echo "Dossier Reciclador #$idRec:\n";
print_r($info);
echo "Total entregas sample: " . count($entregas) . "\n";
echo "Total canjes sample: " . count($canjes) . "\n";
echo "Analítica ML:\n";
echo " - Puntos estimados: " . ($analitica['regresion']['puntos_estimados'] ?? 'N/A') . "\n";
echo " - Nivel actividad: " . ($analitica['clasificacion']['nivel_actividad'] ?? 'N/A') . "\n";
echo " - Segmento: " . ($analitica['segmentacion']['segmento'] ?? 'N/A') . "\n";
echo " - Estado operativo: " . ($analitica['anomalias']['estado_operativo'] ?? 'N/A') . "\n";
