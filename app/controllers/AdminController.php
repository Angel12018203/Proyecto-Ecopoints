<?php
/**
 * ============================================================================
 * CONTROLADOR: ADMIN CONTROLLER (Panel de Control y Analítica Administrativa)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE CONTROLADOR?
 * Procesa la lógica de negocio y consultas estadísticas para el Administrador
 * General de EcoPoints. Provee KPIs consolidados del ecosistema, distribución
 * de materiales, rendimiento de recicladores y acceso a la vista analítica
 * del Diccionario de Datos para minería.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/database.php';

class AdminController extends Controller {

    private $db;

    public function __construct() {
        // Exigir autenticación y rol de administrador
        AuthMiddleware::adminOnly();
        $this->db = Database::getConnection();
    }

    /**
     * Muestra el Dashboard principal del Administrador con KPIs y analítica.
     */
    public function dashboard(): void {
        $user = currentUser();

        // 1. KPIs Globales
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

        // 2. Desglose por Material Reciclado
        $stmtMateriales = $this->db->query("
            SELECT 
                m.nombre AS material,
                COUNT(e.id_entrega) AS total_entregas,
                ROUND(SUM(e.cantidad_validada), 2) AS total_kg,
                ROUND((SUM(e.cantidad_validada) / (SELECT SUM(cantidad_validada) FROM entregas WHERE estado = 'Validada')) * 100, 1) AS porcentaje
            FROM entregas e
            JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
            JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
            JOIN materiales m ON s.id_material = m.id_material
            WHERE e.estado = 'Validada'
            GROUP BY m.id_material, m.nombre
            ORDER BY total_kg DESC
        ");
        $materialesData = $stmtMateriales->fetchAll(PDO::FETCH_ASSOC);

        // Colores distintivos para las barras de progreso en el dashboard
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

        // 3. Top 8 Recicladores Destacados
        $stmtTopRecicladores = $this->db->query("
            SELECT 
                u.id_usuario,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                u.correo,
                pr.ecopuntos,
                pr.material_recuperado,
                pr.nivel,
                l.nombre AS localidad
            FROM perfil_reciclador pr
            JOIN usuarios u ON pr.id_usuario = u.id_usuario
            JOIN localidades l ON pr.id_localidad = l.id_localidad
            ORDER BY pr.ecopuntos DESC
            LIMIT 8
        ");
        $topRecicladores = $stmtTopRecicladores->fetchAll(PDO::FETCH_ASSOC);

        // 4. Muestra de la Vista Analítica del Diccionario de Datos (para Minería)
        $stmtDiccionario = $this->db->query("
            SELECT 
                reciclador_id,
                DATE_FORMAT(mes, '%Y-%m') AS periodo,
                material_principal,
                kg_recolectados,
                entregas,
                diversidad_materiales,
                beneficios_redimidos,
                puntos_obtenidos,
                COALESCE(puntos_mes_siguiente, '—') AS puntos_mes_siguiente
            FROM vw_diccionario_ecopoints_mensual
            ORDER BY reciclador_id ASC, mes ASC
            LIMIT 12
        ");
        $diccionarioMuestra = $stmtDiccionario->fetchAll(PDO::FETCH_ASSOC);

        // 5. Renderizar vista con datos estructurados
        $this->view('admin/dashboard', [
            'titulo'             => 'Panel Administrativo — EcoPoints',
            'user'               => $user,
            'activeNav'          => 'admin_dashboard',
            'kpis'               => [
                'total_recicladores' => $totalRecicladores,
                'total_entregas'     => (int)$totalEntregas,
                'total_kg'           => (float)$totalKg,
                'total_puntos'       => $totalPuntos,
                'total_canjes'       => $totalCanjes,
                'total_centros'      => $totalCentros,
            ],
            'materiales'         => $materialesData,
            'topRecicladores'    => $topRecicladores,
            'diccionarioMuestra' => $diccionarioMuestra
        ]);
    }
}
