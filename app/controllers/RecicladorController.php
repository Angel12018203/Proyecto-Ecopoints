<?php
/**
 * ============================================================================
 * CONTROLADOR: RECICLADOR (Actor Principal de EcoPoints)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE CONTROLADOR?
 * Controla el área de trabajo y panel principal del reciclador:
 * - Dashboard con saludo dinámico ("¡Hola, Carlos! 👋").
 * - Tarjetas de métricas oficiales:
 *   1. Saldo de Ecopoints reales de la base de datos.
 *   2. Material recuperado (kg acumulados).
 *   3. Conteo de solicitudes cercanas disponibles en la zona.
 *   4. Nivel calculado con barra de progreso dinámico hacia el siguiente nivel.
 * - Enlaces directos hacia Solicitudes, Mi ruta, Recolecciones, Centros, Ecopoints, etc.
 * 
 * CONTROL DE ACCESO:
 * Protegido estrictamente con `AuthMiddleware::recicladorOnly()`. Si un usuario
 * con otro rol intenta ingresar, el middleware lo desviará a su panel respectivo.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Reciclador.php';
require_once __DIR__ . '/../models/Localidad.php';

class RecicladorController extends Controller {

    private $recicladorModel;

    public function __construct() {
        // Garantizar que solo los recicladores autenticados ejecuten este controlador
        AuthMiddleware::recicladorOnly();
        $this->recicladorModel = new Reciclador();
    }

    /**
     * Muestra el Dashboard principal del reciclador (fiel al diseño aprobado de Figma).
     */
    public function dashboard(): void {
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];

        // Consultar perfil completo y métricas de la base de datos
        $perfil = $this->recicladorModel->findByUserId($idUsuario);
        $metricas = $this->recicladorModel->getDashboardMetrics($idUsuario);

        $this->view('reciclador/dashboard', [
            'titulo'   => 'Panel del Reciclador — EcoPoints',
            'user'     => $user,
            'perfil'   => $perfil,
            'metricas' => $metricas,
            'activeNav'=> 'inicio'
        ]);
    }
}
