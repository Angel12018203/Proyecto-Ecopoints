<?php
/**
 * ============================================================================
 * CONTROLADOR: CENTRO DE ACOPIO (Recepción y Validación Oficial)
 * ============================================================================
 * 
 * Administra el panel de operaciones del centro de acopio con métricas
 * reales de entregas validadas y pesaje en MySQL.
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
     * Muestra el panel principal del centro de acopio con datos reales de la BD.
     */
    public function dashboard(): void {
        $user = currentUser();
        $perfil = $this->centroModel->findByUserId((int)$user['id_usuario']);
        $idCentro = (int)($perfil['id_centro'] ?? 0);
        $metricas = $this->centroModel->getMetricas($idCentro);

        $this->view('centro_acopio/dashboard', [
            'titulo'    => 'Centro de Acopio — EcoPoints',
            'user'      => $user,
            'perfil'    => $perfil,
            'metricas'  => $metricas,
            'activeNav' => 'inicio'
        ]);
    }
}
