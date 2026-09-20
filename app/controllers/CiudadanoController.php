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
 * - Estadísticas de su contribución ecológica.
 * 
 * CONTROL DE ACCESO:
 * Protegido con `AuthMiddleware::ciudadanoOnly()`.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Ciudadano.php';

class CiudadanoController extends Controller {

    private $ciudadanoModel;

    public function __construct() {
        AuthMiddleware::ciudadanoOnly();
        $this->ciudadanoModel = new Ciudadano();
    }

    /**
     * Muestra el panel principal del ciudadano.
     */
    public function dashboard(): void {
        $user = currentUser();
        $perfil = $this->ciudadanoModel->findByUserId((int)$user['id_usuario']);

        $this->view('ciudadano/dashboard', [
            'titulo'    => 'Panel del Ciudadano — EcoPoints',
            'user'      => $user,
            'perfil'    => $perfil,
            'activeNav' => 'inicio'
        ]);
    }
}
