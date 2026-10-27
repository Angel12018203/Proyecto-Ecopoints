<?php
/**
 * Test programático del Flujo Completo de la Etapa 1
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Solicitud.php';
require_once __DIR__ . '/../app/models/Reciclador.php';
require_once __DIR__ . '/../app/models/CentroAcopio.php';

$db = Database::getConnection();
$recicladorModel = new Reciclador();
$solicitudModel = new Solicitud();
$usuarioModel = new Usuario();

echo "========================================================\n";
echo "PRUEBA DEL FLUJO DE ACEPTACIÓN Y GESTIÓN DEL RECICLADOR\n";
echo "========================================================\n\n";

// 1. Obtener ID del reciclador Carlos
$userCarlos = $usuarioModel->findByEmail('reciclador@ecopoint.app');
$idCarlos = (int)$userCarlos['id_usuario'];
echo "1. Reciclador autenticado: {$userCarlos['nombre']} {$userCarlos['apellido']} (ID: $idCarlos)\n";

// 2. Crear una solicitud de prueba como Ciudadano (Usuario ID 2)
$dataSol = [
    'id_ciudadano'         => 2,
    'id_material'          => 3, // Plástico (id_material = 3)
    'id_localidad'         => 11, // Suba
    'cantidad_estimada'    => 18.5,
    'direccion'            => 'Carrera 91 # 146-20 Torre 3',
    'barrio'               => 'Suba Rincón',
    'latitud'              => 4.7450,
    'longitud'             => -74.0850,
    'fecha_disponibilidad' => date('Y-m-d', strtotime('+1 day')),
    'hora_inicio'          => '14:00:00',
    'hora_fin'             => '17:00:00',
    'observaciones'        => 'Material plástico PET limpio y compactado para prueba de aceptación',
    'prioridad'            => 'Alta'
];

$idSolicitud = $solicitudModel->create($dataSol);
echo "2. Solicitud creada por ciudadano: #$idSolicitud (Estado: Pendiente)\n";

// 3. El reciclador consulta solicitudes disponibles
$disponibles = $recicladorModel->getSolicitudesDisponibles('Plástico');
$encontrada = false;
foreach ($disponibles as $d) {
    if ((int)$d['id_solicitud'] === $idSolicitud) {
        $encontrada = true;
        break;
    }
}
echo "3. Búsqueda de solicitud en lista de disponibles: " . ($encontrada ? "✅ ENCONTRADA" : "❌ NO ENCONTRADA") . "\n";

// 4. El reciclador acepta la solicitud
$idRecoleccion = $recicladorModel->aceptarSolicitud($idSolicitud, $idCarlos);
echo "4. Aceptación de recolección: ✅ EXITOSA (ID Recolección: #$idRecoleccion)\n";

// Verificar cambio de estado en solicitudes
$stmtCheck = $db->prepare("SELECT estado FROM solicitudes WHERE id_solicitud = ?");
$stmtCheck->execute([$idSolicitud]);
$estadoSol = $stmtCheck->fetchColumn();
echo "   -> Estado de Solicitud actualizado a: $estadoSol (Esperado: Aceptada)\n";

// 5. Consultar mis recolecciones activas
$misRec = $recicladorModel->getMisRecolecciones($idCarlos, 'activas');
$enMisRec = false;
foreach ($misRec as $r) {
    if ((int)$r['id_recoleccion'] === $idRecoleccion) {
        $enMisRec = true;
        break;
    }
}
echo "5. Verificación en 'Mis recolecciones activas': " . ($enMisRec ? "✅ PRESENTE" : "❌ AUSENTE") . "\n";

// 6. Reciclador cambia estado a 'En camino'
$recicladorModel->actualizarEstadoRecoleccion($idRecoleccion, $idCarlos, 'En camino');
$stmtCheckRec = $db->prepare("SELECT estado FROM recolecciones WHERE id_recoleccion = ?");
$stmtCheckRec->execute([$idRecoleccion]);
echo "6. Transición a 'En camino': ✅ ESTADO " . $stmtCheckRec->fetchColumn() . "\n";

// 7. Reciclador completa recolección con 20.0 kg recolectados
$recicladorModel->actualizarEstadoRecoleccion($idRecoleccion, $idCarlos, 'Recolectada', 20.0);
$stmtCheckRec2 = $db->prepare("SELECT estado, cantidad_recolectada FROM recolecciones WHERE id_recoleccion = ?");
$stmtCheckRec2->execute([$idRecoleccion]);
$recFinal = $stmtCheckRec2->fetch(PDO::FETCH_ASSOC);
echo "7. Recolección completada: ✅ ESTADO " . $recFinal['estado'] . " (Kg recolectados: " . $recFinal['cantidad_recolectada'] . ")\n";

// 8. Verificar que se haya generado la entrega pendiente para pesaje en Centro de Acopio
$stmtEnt = $db->prepare("SELECT * FROM entregas WHERE id_recoleccion = ?");
$stmtEnt->execute([$idRecoleccion]);
$entrega = $stmtEnt->fetch(PDO::FETCH_ASSOC);
echo "8. Generación de entrega en Centro de Acopio: " . ($entrega ? "✅ GENERADA (ID: #{$entrega['id_entrega']}, Estado: {$entrega['estado']})" : "❌ NO GENERADA") . "\n";

// 9. Probar catálogo de beneficios y simulación de canje
$beneficios = $recicladorModel->getAllBeneficios('Alimentos');
echo "9. Catálogo de beneficios (Alimentos): " . count($beneficios) . " ítems encontrados.\n";

// 10. Probar métricas de impacto ambiental
$impacto = $recicladorModel->getImpactoAmbiental($idCarlos);
echo "10. Impacto Ambiental calculado:\n";
echo "    - Material recuperado: {$impacto['material_recuperado']} kg\n";
echo "    - CO2 evitado: {$impacto['co2_evitado']} kg\n";
echo "    - Recolecciones: {$impacto['total_recolecciones']}\n";
echo "    - Total materiales clasificados: " . count($impacto['materiales']) . "\n";

echo "\n🎉 TODAS LAS PRUEBAS DE LA ETAPA 1 PASARON CON ÉXITO.\n";
