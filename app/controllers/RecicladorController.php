<?php
/**
 * ============================================================================
 * CONTROLADOR: RECICLADOR (Actor Principal de EcoPoints)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE CONTROLADOR?
 * Controla el área de trabajo y panel principal del reciclador:
 * - Dashboard con saludo dinámico ("¡Hola, Carlos! 👋").
 * - Tarjetas de métricas oficiales conectadas a MySQL:
 *   1. Saldo de Ecopoints reales de la base de datos.
 *   2. Material recuperado (kg acumulados).
 *   3. Conteo de solicitudes reales pendientes y disponibles en el sistema.
 *   4. Nivel calculado con barra de progreso dinámico hacia el siguiente nivel.
 * - Listado de oportunidades reales de recolección registradas por ciudadanos.
 * - Centros de acopio activos disponibles en Bogotá.
 * 
 * CONTROL DE ACCESO:
 * Protegido estrictamente con `AuthMiddleware::recicladorOnly()`.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Reciclador.php';
require_once __DIR__ . '/../models/CentroAcopio.php';
require_once __DIR__ . '/../models/Localidad.php';

class RecicladorController extends Controller {

    private $recicladorModel;
    private $centroAcopioModel;

    public function __construct() {
        AuthMiddleware::recicladorOnly();
        $this->recicladorModel = new Reciclador();
        $this->centroAcopioModel = new CentroAcopio();
    }

    /**
     * Muestra el Dashboard principal del reciclador con datos 100% reales de la BD.
     */
    /**
     * Muestra el Dashboard principal del reciclador con datos 100% reales de la BD.
     */
    public function dashboard(): void {
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];

        // Consultar perfil completo y métricas de la base de datos
        $perfil = $this->recicladorModel->findByUserId($idUsuario);
        $metricas = $this->recicladorModel->getDashboardMetrics($idUsuario);
        $solicitudes = $this->recicladorModel->getSolicitudesDisponibles(null, 6);
        $centros = $this->centroAcopioModel->getAllActive();

        $this->view('reciclador/dashboard', [
            'titulo'      => 'Panel del Reciclador — EcoPoints',
            'user'        => $user,
            'perfil'      => $perfil,
            'metricas'    => $metricas,
            'solicitudes' => $solicitudes,
            'centros'     => $centros,
            'activeNav'   => 'inicio'
        ]);
    }

    /**
     * Módulo: Solicitudes de recolección cercanas (Filtros por material y aceptación).
     */
    public function solicitudes(): void {
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];
        $materialSeleccionado = $_GET['material'] ?? 'Todas';

        $perfil = $this->recicladorModel->findByUserId($idUsuario);
        $solicitudes = $this->recicladorModel->getSolicitudesDisponibles($materialSeleccionado);
        $metricas = $this->recicladorModel->getDashboardMetrics($idUsuario);

        $this->view('reciclador/solicitudes', [
            'titulo'              => 'Solicitudes Cercanas — EcoPoints',
            'user'                => $user,
            'perfil'              => $perfil,
            'metricas'            => $metricas,
            'solicitudes'         => $solicitudes,
            'materialSeleccionado'=> $materialSeleccionado,
            'activeNav'           => 'solicitudes'
        ]);
    }

    /**
     * Procesa la aceptación de una solicitud por parte del reciclador.
     */
    public function postAceptarSolicitud(): void {
        if (!verify_csrf()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            $this->redirect('reciclador/solicitudes');
            return;
        }

        $idSolicitud = (int)($_POST['id_solicitud'] ?? 0);
        $user = currentUser();
        $idReciclador = (int)$user['id_usuario'];

        if ($idSolicitud <= 0) {
            flash('error', 'Solicitud no válida.');
            $this->redirect('reciclador/solicitudes');
            return;
        }

        try {
            $idRecoleccion = $this->recicladorModel->aceptarSolicitud($idSolicitud, $idReciclador);
            flash('success', "¡Excelente! Has aceptado la Solicitud #{$idSolicitud}. Ya puedes coordinar la recogida en 'Mis recolecciones'.");
            $this->redirect('reciclador/recolecciones');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            $this->redirect('reciclador/solicitudes');
        }
    }

    /**
     * Módulo: Mis Recolecciones (Pestañas Activas, En proceso, Completadas, Canceladas).
     */
    public function recolecciones(): void {
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];
        $tab = $_GET['tab'] ?? 'activas';

        $perfil = $this->recicladorModel->findByUserId($idUsuario);
        $recolecciones = $this->recicladorModel->getMisRecolecciones($idUsuario, $tab);
        $metricas = $this->recicladorModel->getDashboardMetrics($idUsuario);

        $this->view('reciclador/recolecciones', [
            'titulo'        => 'Mis Recolecciones — EcoPoints',
            'user'          => $user,
            'perfil'        => $perfil,
            'metricas'      => $metricas,
            'recolecciones' => $recolecciones,
            'activeTab'     => $tab,
            'activeNav'     => 'mis_recolecciones'
        ]);
    }

    /**
     * Actualiza el estado de una recolección (En camino, Recolectada).
     */
    public function postActualizarRecoleccion(): void {
        if (!verify_csrf()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            $this->redirect('reciclador/recolecciones');
            return;
        }

        $idRecoleccion = (int)($_POST['id_recoleccion'] ?? 0);
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
        $kg = isset($_POST['cantidad_recolectada']) ? (float)$_POST['cantidad_recolectada'] : null;

        $user = currentUser();
        $idReciclador = (int)$user['id_usuario'];

        try {
            $this->recicladorModel->actualizarEstadoRecoleccion($idRecoleccion, $idReciclador, $nuevoEstado, $kg);
            if ($nuevoEstado === 'Recolectada') {
                flash('success', "¡Recolección completada con éxito! Se ha generado la entrega pendiente para pesaje en el Centro de Acopio.");
            } else {
                flash('success', "Estado de la recolección actualizado a '{$nuevoEstado}'.");
            }
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }

        $this->redirect('reciclador/recolecciones');
    }

    /**
     * Módulo: Centros de Acopio (Directorio oficial y mapa visual).
     */
    public function centros(): void {
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];

        $perfil = $this->recicladorModel->findByUserId($idUsuario);
        $centros = $this->centroAcopioModel->getAllActive();
        $metricas = $this->recicladorModel->getDashboardMetrics($idUsuario);

        $this->view('reciclador/centros', [
            'titulo'    => 'Centros de Acopio — EcoPoints',
            'user'      => $user,
            'perfil'    => $perfil,
            'metricas'  => $metricas,
            'centros'   => $centros,
            'activeNav' => 'centros'
        ]);
    }

    /**
     * Módulo: Beneficios (Catálogo por categorías y canje de Ecopuntos).
     */
    public function beneficios(): void {
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];
        $categoria = $_GET['categoria'] ?? 'Todos';

        $perfil = $this->recicladorModel->findByUserId($idUsuario);
        $beneficios = $this->recicladorModel->getAllBeneficios($categoria);
        $metricas = $this->recicladorModel->getDashboardMetrics($idUsuario);

        $this->view('reciclador/beneficios', [
            'titulo'            => 'Beneficios y Recompensas — EcoPoints',
            'user'              => $user,
            'perfil'            => $perfil,
            'metricas'          => $metricas,
            'beneficios'        => $beneficios,
            'categoriaActiva'   => $categoria,
            'activeNav'         => 'beneficios'
        ]);
    }

    /**
     * Procesa el canje de un beneficio.
     */
    public function postCanjearBeneficio(): void {
        if (!verify_csrf()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            $this->redirect('reciclador/beneficios');
            return;
        }

        $idBeneficio = (int)($_POST['id_beneficio'] ?? 0);
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];

        try {
            $resultado = $this->recicladorModel->canjearBeneficio($idBeneficio, $idUsuario);
            flash('success', "¡Canje exitoso! Bono generado: {$resultado['codigo_canje']} para '{$resultado['beneficio']}'. Nuevo saldo: " . number_format($resultado['nuevo_saldo']) . " pts.");
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }

        $this->redirect('reciclador/beneficios');
    }

    /**
     * Módulo: Mi Impacto Ambiental (Métricas, CO2 evitado, barras mensuales y materiales).
     */
    public function impacto(): void {
        $user = currentUser();
        $idUsuario = (int)$user['id_usuario'];

        $perfil = $this->recicladorModel->findByUserId($idUsuario);
        $impacto = $this->recicladorModel->getImpactoAmbiental($idUsuario);
        $metricas = $this->recicladorModel->getDashboardMetrics($idUsuario);

        $this->view('reciclador/impacto', [
            'titulo'    => 'Mi Impacto Ambiental — EcoPoints',
            'user'      => $user,
            'perfil'    => $perfil,
            'metricas'  => $metricas,
            'impacto'   => $impacto,
            'activeNav' => 'impacto'
        ]);
    }
}
