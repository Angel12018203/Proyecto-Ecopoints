<?php
/**
 * ============================================================================
 * CONTROLADOR: SIMULACIÓN CONTROLLER (Módulo de Modelado y Simulación)
 * ============================================================================
 * 
 * Gestiona la lógica web del módulo de Modelado y Simulación para el Administrador:
 * - Renderizado de la interfaz interactiva en 5 apartados.
 * - Ejecución de simulaciones SimPy y Monte Carlo vía AJAX.
 * - Exportación de resultados de experimentos a CSV.
 * - Aislamiento ético: sin modificar datos de producción.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/SimulacionService.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';

class SimulacionController extends Controller {

    private $simulacionService;

    public function __construct() {
        // Exigir autenticación y rol exclusivo de Administrador
        AuthMiddleware::adminOnly();
        $this->simulacionService = new SimulacionService();
    }

    /**
     * Muestra la interfaz principal de Modelado y Simulación
     */
    public function index(): void {
        $user = currentUser();
        $escenarios = $this->simulacionService->getEscenariosPredefinidos();

        // Ejecutar simulación inicial de referencia (Escenario A) para arranque en vivo
        $simulacionInicial = null;
        try {
            $simulacionInicial = $this->simulacionService->ejecutarSimulacion([
                'escenario'        => 'A',
                'solicitudes_dia'  => 40,
                'num_recicladores' => 30,
                'puestos_pesaje'   => 2,
                'duracion_dias'    => 7,
                'num_replicas'     => 10,
                'semilla'          => 42
            ]);
            // Guardar en sesión para permitir exportación inmediata
            $_SESSION['ultima_simulacion'] = $simulacionInicial;
        } catch (Exception $e) {
            error_log("Aviso: No fue posible ejecutar la simulación inicial: " . $e->getMessage());
        }

        $this->view('admin/simulacion', [
            'titulo'            => 'Modelado y Simulación — EcoPoints',
            'user'              => $user,
            'activeNav'         => 'admin_simulacion',
            'escenarios'        => $escenarios,
            'simulacionInicial' => $simulacionInicial,
            'csrfToken'         => csrf_token()
        ]);
    }

    /**
     * Endpoint AJAX: Ejecuta una nueva simulación con parámetros personalizados
     */
    public function ejecutar(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
            return;
        }

        if (!verify_csrf()) {
            $this->json(['success' => false, 'error' => 'Token CSRF inválido o sesión expirada.'], 403);
            return;
        }

        $body = file_get_contents('php://input');
        $params = json_decode($body, true) ?? $_POST;

        try {
            $resultado = $this->simulacionService->ejecutarSimulacion($params);
            
            // Guardar en sesión para exportación
            $_SESSION['ultima_simulacion'] = $resultado;

            $this->json($resultado);
        } catch (Exception $e) {
            error_log("Error en SimulacionController::ejecutar: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error'   => 'Fallo al procesar la simulación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporta los resultados de la última simulación a un archivo CSV descargable
     */
    public function exportar(): void {
        $simulacion = $_SESSION['ultima_simulacion'] ?? null;

        if (!$simulacion || empty($simulacion['success'])) {
            // Si no está en sesión, ejecutar una corrida de referencia para exportar
            try {
                $simulacion = $this->simulacionService->ejecutarSimulacion([
                    'escenario'        => 'A',
                    'solicitudes_dia'  => 40,
                    'num_recicladores' => 30,
                    'puestos_pesaje'   => 2,
                    'duracion_dias'    => 7,
                    'num_replicas'     => 10,
                    'semilla'          => 42
                ]);
            } catch (Exception $e) {
                die("No hay datos de simulación disponibles para exportar.");
            }
        }

        $csvData = $this->simulacionService->exportarResultadosCSV($simulacion);
        $filename = "simulacion_ecopoints_escenario_" . strtolower($simulacion['escenario'] ?? 'a') . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // BOM UTF-8 para visualización en Excel
        echo $csvData;
        exit;
    }
}
