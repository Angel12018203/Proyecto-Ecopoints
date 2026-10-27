<?php
/**
 * ============================================================================
 * CONTROLADOR: ADMIN CONTROLLER (Panel de Control y Analítica Administrativa)
 * ============================================================================
 * 
 * Procesa la lógica de negocio y consultas estadísticas para el Administrador
 * General de EcoPoints:
 * - KPIs consolidados del ecosistema y distribución de materiales.
 * - Buscador universal y ficha individual de cualquier reciclador.
 * - Historial completo de entregas, pesajes y canjes con filtros.
 * - Integración de los 4 modelos de minería de datos (Joblib / Python).
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/InferenciaService.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';

class AdminController extends Controller {

    private $db;
    private $inferenciaService;

    public function __construct() {
        // Exigir autenticación y rol de administrador
        AuthMiddleware::adminOnly();
        $this->db = Database::getConnection();
        $this->inferenciaService = new InferenciaService();
    }

    /**
     * Muestra el Dashboard principal del Administrador con KPIs, selector y analítica.
     */
    public function dashboard(): void {
        $user = currentUser();

        // 1. KPIs Globales de la plataforma
        $stmtRecicladores = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 1 AND estado = 'activo'");
        $totalRecicladores = (int)$stmtRecicladores->fetchColumn();

        $stmtEntregas = $this->db->query("SELECT COUNT(*), COALESCE(SUM(cantidad_validada), 0) FROM entregas WHERE estado = 'Validada'");
        [$totalEntregas, $totalKg] = $stmtEntregas->fetch(PDO::FETCH_NUM);

        $stmtPuntos = $this->db->query("SELECT COALESCE(SUM(cantidad_puntos), 0) FROM ecopuntos WHERE estado = 'activo'");
        $totalPuntos = (int)$stmtPuntos->fetchColumn();

        $stmtCanjes = $this->db->query("SELECT COUNT(*) FROM canjes");
        $totalCanjes = (int)$stmtCanjes->fetchColumn();

        $stmtCentros = $this->db->query("SELECT COUNT(*) FROM centros_acopio WHERE estado = 'activo'");
        $totalCentros = (int)$stmtCentros->fetchColumn();

        // 2. Desglose global por Material Reciclado
        $stmtMateriales = $this->db->query("
            SELECT 
                m.nombre AS material,
                COUNT(e.id_entrega) AS total_entregas,
                ROUND(SUM(e.cantidad_validada), 2) AS total_kg,
                COALESCE(ROUND(
                    SUM(e.cantidad_validada) /
                    NULLIF((SELECT SUM(cantidad_validada) FROM entregas WHERE estado = 'Validada'), 0) * 100,
                    1
                ), 0) AS porcentaje
            FROM entregas e
            JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
            JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
            JOIN materiales m ON s.id_material = m.id_material
            WHERE e.estado = 'Validada'
            GROUP BY m.id_material, m.nombre
            ORDER BY total_kg DESC
        ");
        $materialesData = $stmtMateriales->fetchAll(PDO::FETCH_ASSOC);

        $coloresMap = [
            'Cartón'   => '#F59E0B',
            'Papel'    => '#06B6D4',
            'Plástico' => '#3B82F6',
            'Vidrio'   => '#10B981',
            'Metales'  => '#8B5CF6',
            'Otros'    => '#6B7280'
        ];
        foreach ($materialesData as &$mat) {
            $mat['color_hex'] = $coloresMap[$mat['material']] ?? '#087A3D';
        }
        unset($mat);

        // 3. Localidades disponibles para filtros
        $stmtLocalidades = $this->db->query("SELECT id_localidad, nombre FROM localidades ORDER BY nombre ASC");
        $localidades = $stmtLocalidades->fetchAll(PDO::FETCH_ASSOC);

        // 4. Lista inicial de recicladores para el selector
        $stmtRecicladoresList = $this->db->query("
            SELECT 
                u.id_usuario,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                u.correo,
                u.telefono,
                u.estado,
                COALESCE(pr.ecopuntos, 0) AS ecopuntos,
                COALESCE(pr.material_recuperado, 0) AS material_recuperado,
                COALESCE(pr.nivel, 'Bronce') AS nivel,
                COALESCE(l.nombre, 'Bogotá D.C.') AS localidad,
                (SELECT COUNT(*) FROM entregas e WHERE e.id_reciclador = u.id_usuario AND e.estado = 'Validada') AS total_entregas
            FROM usuarios u
            JOIN roles rol ON u.id_rol = rol.id_rol
            LEFT JOIN perfil_reciclador pr ON u.id_usuario = pr.id_usuario
            LEFT JOIN localidades l ON pr.id_localidad = l.id_localidad
            WHERE rol.nombre = 'reciclador'
            ORDER BY pr.ecopuntos DESC, u.id_usuario ASC
            LIMIT 40
        ");
        $recicladoresIniciales = $stmtRecicladoresList->fetchAll(PDO::FETCH_ASSOC);

        // 5. Metadatos de los modelos de Machine Learning
        $metadatosML = $this->inferenciaService->getMetadatosModelos();

        // 6. Reglas de asociación Apriori destacadas
        $reglasAsociacion = $this->inferenciaService->getReglasAsociacion('', 8);

        // 7. Renderizar vista completa
        $this->view('admin/dashboard', [
            'titulo'                 => 'Panel Administrativo — EcoPoints',
            'user'                   => $user,
            'activeNav'              => 'admin_dashboard',
            'kpis'                   => [
                'total_recicladores' => $totalRecicladores,
                'total_entregas'     => (int)$totalEntregas,
                'total_kg'           => (float)$totalKg,
                'total_puntos'       => $totalPuntos,
                'total_canjes'       => $totalCanjes,
                'total_centros'      => $totalCentros,
            ],
            'materiales'             => $materialesData,
            'localidades'            => $localidades,
            'recicladoresIniciales'  => $recicladoresIniciales,
            'primerRecicladorId'     => !empty($recicladoresIniciales) ? (int)$recicladoresIniciales[0]['id_usuario'] : 0,
            'metadatosML'            => $metadatosML,
            'reglasAsociacion'       => $reglasAsociacion
        ]);
    }

    /**
     * Endpoint AJAX: Busca recicladores por término de texto (ID, nombre, correo, teléfono).
     * Devuelve JSON con los candidatos coincidentes para el autocompletado en tiempo real.
     */
    public function buscarRecicladores(): void {
        $q = trim($_GET['q'] ?? '');

        try {
            if ($q === '') {
                $stmt = $this->db->prepare("
                    SELECT 
                        u.id_usuario,
                        CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                        u.correo,
                        u.telefono,
                        u.estado,
                        COALESCE(pr.ecopuntos, 0) AS ecopuntos,
                        COALESCE(pr.material_recuperado, 0) AS material_recuperado,
                        COALESCE(pr.nivel, 'Bronce') AS nivel,
                        COALESCE(l.nombre, 'Bogotá D.C.') AS localidad,
                        (SELECT COUNT(*) FROM entregas e WHERE e.id_reciclador = u.id_usuario AND e.estado = 'Validada') AS total_entregas
                    FROM usuarios u
                    JOIN roles rol ON u.id_rol = rol.id_rol
                    LEFT JOIN perfil_reciclador pr ON u.id_usuario = pr.id_usuario
                    LEFT JOIN localidades l ON pr.id_localidad = l.id_localidad
                    WHERE rol.nombre = 'reciclador'
                    ORDER BY pr.ecopuntos DESC, u.id_usuario ASC
                    LIMIT 25
                ");
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare("
                    SELECT 
                        u.id_usuario,
                        CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                        u.correo,
                        u.telefono,
                        u.estado,
                        COALESCE(pr.ecopuntos, 0) AS ecopuntos,
                        COALESCE(pr.material_recuperado, 0) AS material_recuperado,
                        COALESCE(pr.nivel, 'Bronce') AS nivel,
                        COALESCE(l.nombre, 'Bogotá D.C.') AS localidad,
                        (SELECT COUNT(*) FROM entregas e WHERE e.id_reciclador = u.id_usuario AND e.estado = 'Validada') AS total_entregas
                    FROM usuarios u
                    JOIN roles rol ON u.id_rol = rol.id_rol
                    LEFT JOIN perfil_reciclador pr ON u.id_usuario = pr.id_usuario
                    LEFT JOIN localidades l ON pr.id_localidad = l.id_localidad
                    WHERE rol.nombre = 'reciclador'
                      AND (
                          u.id_usuario = ?
                          OR u.nombre LIKE ?
                          OR u.apellido LIKE ?
                          OR CONCAT(u.nombre, ' ', u.apellido) LIKE ?
                          OR u.correo LIKE ?
                          OR u.telefono LIKE ?
                      )
                    ORDER BY (u.id_usuario = ?) DESC, pr.ecopuntos DESC
                    LIMIT 20
                ");
                $idExacto = is_numeric($q) ? (int)$q : -1;
                $term = "%{$q}%";
                $stmt->execute([
                    $idExacto,
                    $term,
                    $term,
                    $term,
                    $term,
                    $term,
                    $idExacto
                ]);
            }

            $recicladores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->json([
                'success'      => true,
                'total'        => count($recicladores),
                'recicladores' => $recicladores
            ]);
        } catch (PDOException $e) {
            error_log('Error en buscarRecicladores: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Error al consultar recicladores.'], 500);
        }
    }

    /**
     * Endpoint AJAX: Obtiene la ficha individual completa de un reciclador específico.
     * Incluye datos personales, perfil, métricas, historial de entregas, canjes y análisis ML.
     */
    public function detalleReciclador(): void {
        $idReciclador = (int)($_GET['id'] ?? 0);
        if ($idReciclador <= 0) {
            $this->json(['success' => false, 'error' => 'ID de reciclador no válido.'], 400);
            return;
        }

        try {
            // 1. Datos personales y perfil del reciclador
            $stmtUser = $this->db->prepare("
                SELECT 
                    u.id_usuario,
                    u.nombre,
                    u.apellido,
                    CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                    u.correo,
                    u.telefono,
                    u.estado,
                    u.fecha_registro,
                    u.ultimo_acceso,
                    pr.id_perfil,
                    pr.zona_trabajo,
                    pr.medio_transporte,
                    pr.disponibilidad,
                    COALESCE(pr.ecopuntos, 0) AS ecopuntos,
                    COALESCE(pr.material_recuperado, 0) AS material_recuperado,
                    COALESCE(pr.nivel, 'Bronce') AS nivel,
                    COALESCE(l.nombre, 'Sin asignar') AS localidad
                FROM usuarios u
                LEFT JOIN perfil_reciclador pr ON u.id_usuario = pr.id_usuario
                LEFT JOIN localidades l ON pr.id_localidad = l.id_localidad
                WHERE u.id_usuario = ?
            ");
            $stmtUser->execute([$idReciclador]);
            $perfil = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$perfil) {
                $this->json(['success' => false, 'error' => 'Reciclador no encontrado.'], 404);
                return;
            }

            // 2. Métricas consolidadas de entregas del reciclador
            $stmtStats = $this->db->prepare("
                SELECT 
                    COUNT(e.id_entrega) AS total_entregas,
                    COALESCE(SUM(e.cantidad_validada), 0) AS total_kg_validados,
                    (SELECT COALESCE(SUM(ep.cantidad_puntos), 0) 
                     FROM ecopuntos ep 
                     WHERE ep.id_reciclador = ? AND ep.estado = 'activo') AS total_puntos_activos
                FROM entregas e
                WHERE e.id_reciclador = ? AND e.estado = 'Validada'
            ");
            $stmtStats->execute([$idReciclador, $idReciclador]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

            // 3. Desglose de materiales recolectados por este reciclador
            $stmtMat = $this->db->prepare("
                SELECT 
                    m.nombre AS material,
                    COUNT(e.id_entrega) AS entregas_material,
                    ROUND(SUM(e.cantidad_validada), 2) AS kg_material,
                    COALESCE(ROUND(
                        SUM(e.cantidad_validada) / 
                        NULLIF((SELECT SUM(e2.cantidad_validada) FROM entregas e2 WHERE e2.id_reciclador = ? AND e2.estado = 'Validada'), 0) * 100,
                        1
                    ), 0) AS porcentaje
                FROM entregas e
                JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
                JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
                JOIN materiales m ON s.id_material = m.id_material
                WHERE e.id_reciclador = ? AND e.estado = 'Validada'
                GROUP BY m.id_material, m.nombre
                ORDER BY kg_material DESC
            ");
            $stmtMat->execute([$idReciclador, $idReciclador]);
            $materiales = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

            // 4. Historial detallado de entregas validadas y pendientes
            $stmtHistEntregas = $this->db->prepare("
                SELECT 
                    e.id_entrega,
                    DATE_FORMAT(e.fecha_entrega, '%Y-%m-%d %H:%i') AS fecha_entrega,
                    e.cantidad_declarada,
                    e.cantidad_validada,
                    e.estado,
                    e.observaciones,
                    m.nombre AS material,
                    COALESCE(ca.nombre, 'Centro Principal') AS centro_acopio,
                    COALESCE(ep.cantidad_puntos, 0) AS ecopuntos_otorgados
                FROM entregas e
                JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
                JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
                JOIN materiales m ON s.id_material = m.id_material
                LEFT JOIN centros_acopio ca ON e.id_centro = ca.id_centro
                LEFT JOIN ecopuntos ep ON e.id_entrega = ep.id_entrega AND ep.estado = 'activo'
                WHERE e.id_reciclador = ?
                ORDER BY e.fecha_entrega DESC
                LIMIT 50
            ");
            $stmtHistEntregas->execute([$idReciclador]);
            $historialEntregas = $stmtHistEntregas->fetchAll(PDO::FETCH_ASSOC);

            // 5. Historial de beneficios canjeados
            $stmtHistCanjes = $this->db->prepare("
                SELECT 
                    c.id_canje,
                    DATE_FORMAT(c.fecha_canje, '%Y-%m-%d %H:%i') AS fecha_canje,
                    c.ecopuntos_utilizados,
                    c.codigo_canje,
                    c.estado,
                    b.nombre AS beneficio,
                    b.categoria,
                    b.icono
                FROM canjes c
                JOIN beneficios b ON c.id_beneficio = b.id_beneficio
                WHERE c.id_usuario = ?
                ORDER BY c.fecha_canje DESC
                LIMIT 20
            ");
            $stmtHistCanjes->execute([$idReciclador]);
            $historialCanjes = $stmtHistCanjes->fetchAll(PDO::FETCH_ASSOC);

            // 6. Evolución histórica mensual (hasta 12 meses observados)
            $stmtEvolucion = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(mes, '%Y-%m') AS periodo,
                    kg_recolectados,
                    entregas,
                    puntos_obtenidos,
                    diversidad_materiales,
                    material_principal
                FROM vw_diccionario_ecopoints_mensual
                WHERE reciclador_id = ?
                ORDER BY mes ASC
                LIMIT 12
            ");
            $stmtEvolucion->execute([$idReciclador]);
            $evolucionMensual = $stmtEvolucion->fetchAll(PDO::FETCH_ASSOC);

            // 7. Inferencia y análisis de Machine Learning con los 4 modelos
            $tieneActividad = ((int)$stats['total_entregas'] > 0 || !empty($evolucionMensual));
            $analisisML = null;

            if ($tieneActividad) {
                try {
                    $analisisML = $this->inferenciaService->obtenerOEjecutarAnalisis($idReciclador, false);
                } catch (Exception $ex) {
                    $analisisML = [
                        'estado'  => 'error',
                        'mensaje' => 'No fue posible completar la inferencia analítica: ' . $ex->getMessage()
                    ];
                }
            } else {
                $analisisML = [
                    'estado'  => 'sin_actividad',
                    'mensaje' => 'Este reciclador está registrado pero aún no cuenta con entregas validadas. Los modelos de Machine Learning requieren datos operativos históricos para generar estimaciones confiables.'
                ];
            }

            $this->json([
                'success'           => true,
                'perfil'            => $perfil,
                'metricas'          => [
                    'total_entregas'       => (int)$stats['total_entregas'],
                    'total_kg'             => (float)$stats['total_kg_validados'],
                    'ecopuntos_activos'    => (int)$stats['total_puntos_activos'],
                    'total_canjes'         => count($historialCanjes),
                    'tiene_actividad'      => $tieneActividad
                ],
                'materiales'        => $materiales,
                'historialEntregas' => $historialEntregas,
                'historialCanjes'   => $historialCanjes,
                'evolucionMensual'  => $evolucionMensual,
                'analisisML'        => $analisisML
            ]);

        } catch (PDOException $e) {
            error_log('Error en detalleReciclador: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Error al consultar la ficha del reciclador.'], 500);
        }
    }

    /**
     * Endpoint AJAX: Fuerza la re-ejecución en tiempo real de los modelos ML para el reciclador
     * y actualiza el registro en analitica_predicciones.
     */
    public function ejecutarAnalisisReciclador(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
            return;
        }

        if (!verify_csrf()) {
            $this->json(['success' => false, 'error' => 'Token CSRF inválido o sesión caducada.'], 403);
            return;
        }

        $body = file_get_contents('php://input');
        $payload = json_decode($body, true) ?? $_POST;
        $idReciclador = (int)($payload['id_reciclador'] ?? 0);

        if ($idReciclador <= 0) {
            $this->json(['success' => false, 'error' => 'ID de reciclador requerido.'], 400);
            return;
        }

        try {
            $resultado = $this->inferenciaService->obtenerOEjecutarAnalisis($idReciclador, true);
            $this->json([
                'success'   => true,
                'analisis'  => $resultado,
                'mensaje'   => 'Análisis de Machine Learning ejecutado y registrado con éxito.'
            ]);
        } catch (Exception $e) {
            error_log('Error al ejecutar modelos ML: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error'   => 'Fallo en la ejecución de los modelos: ' . $e->getMessage()
            ], 500);
        }
    }
}

