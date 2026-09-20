<?php
/**
 * ============================================================================
 * CONTROLADOR: CENTRO DE ACOPIO (Recepción y Validación Oficial)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE CONTROLADOR?
 * Administra el panel de operaciones del centro de acopio:
 * - Panel de bienvenida con los datos del centro y responsable.
 * - Información sobre entregas pendientes de pesaje y validación oficial.
 * - Registro de pesaje certificado y acreditación de Ecopoints.
 * 
 * CONTROL DE ACCESO:
 * Protegido con `AuthMiddleware::centroAcopioOnly()`.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/CentroAcopio.php';

class CentroAcopioController extends Controller {

    private $centroModel;

    public function __construct() {
        AuthMiddleware::centroAcopioOnly();
        $this->centroModel = new CentroAcopio();
    }

    /**
     * Muestra el panel principal del centro de acopio.
     */
    public function dashboard(): void {
        $user = currentUser();
        $perfil = $this->centroModel->findByUserId((int)$user['id_usuario']);

        $this->view('centro_acopio/dashboard', [
            'titulo'    => 'Centro de Acopio — EcoPoints',
            'user'      => $user,
            'perfil'    => $perfil,
            'activeNav' => 'inicio'
        ]);
    }
}
