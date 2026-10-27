<?php
/**
 * ============================================================================
 * CONTROLADOR: CIUDADANO (Generador de Recolecciones)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE CONTROLADOR?
 * Controla el área inicial del ciudadano:
 * - Panel de bienvenida y visualización de sus datos de perfil.
 * - Información sobre cómo preparar el material y solicitar recolección.
 * - Resumen de solicitudes activas y botón de acceso a nueva solicitud.
 * 
 * CONTROL DE ACCESO:
 * Protegido con `AuthMiddleware::ciudadanoOnly()`.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Ciudadano.php';
require_once __DIR__ . '/../models/Solicitud.php';

class CiudadanoController extends Controller {

    private $ciudadanoModel;
    private $solicitudModel;

    public function __construct() {
        AuthMiddleware::ciudadanoOnly();
        $this->ciudadanoModel = new Ciudadano();
        $this->solicitudModel = new Solicitud();
    }

    /**
     * Muestra el panel principal del ciudadano con estadísticas reales.
     */
    public function dashboard(): void {
        $user = currentUser();
        $idCiudadano = (int)$user['id_usuario'];

        $perfil = $this->ciudadanoModel->findByUserId($idCiudadano);
        $metricas = $this->solicitudModel->getMetricasCiudadano($idCiudadano);
        $solicitudes = $this->solicitudModel->getByCiudadano($idCiudadano);

        $this->view('ciudadano/dashboard', [
            'titulo'      => 'Panel del Ciudadano — EcoPoints',
            'user'        => $user,
            'perfil'      => $perfil,
            'metricas'    => $metricas,
            'solicitudes' => $solicitudes,
            'activeNav'   => 'inicio'
        ]);
    }
}
