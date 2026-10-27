<?php
/**
 * ============================================================================
 * CONTROLADOR: SOLICITUDES DE RECOLECCIÓN (SolicitudController — Fase 2)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE CONTROLADOR?
 * Gestiona el ciclo completo de solicitudes de recolección desde la perspectiva
 * del ciudadano:
 * 1. Mostrar formulario para registrar una nueva solicitud de recolección.
 * 2. Procesar el POST con validación CSRF, validación estricta de cantidad, fechas y catálogo.
 * 3. Consultar y listar todas las solicitudes registradas por el ciudadano autenticado.
 * 4. Ver el detalle individual de una solicitud con verificación de propiedad.
 * 
 * MEDIDAS DE SEGURIDAD IMPLEMENTADAS:
 * - Protección por rol: Exclusivo para rol 'ciudadano' mediante AuthMiddleware::ciudadanoOnly().
 * - Validación obligatoria de token CSRF (verify_csrf()).
 * - Patrón Post/Redirect/Get para prevenir envíos duplicados al recargar pantalla.
 * - Validación de fechas: No se permiten fechas pasadas.
 * - Validación numérica: Cantidades mayores a cero en kilogramos.
 * - Escape de salida HTML mediante helper h() en las vistas.
 * - Prevención de IDOR: Un ciudadano solo puede ver o consultar sus propias solicitudes.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Solicitud.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/Localidad.php';
require_once __DIR__ . '/../models/Ciudadano.php';

class SolicitudController extends Controller {

    private $solicitudModel;
    private $materialModel;
    private $localidadModel;
    private $ciudadanoModel;

    public function __construct() {
        // Exclusivo para usuarios autenticados con rol ciudadano
        AuthMiddleware::ciudadanoOnly();

        $this->solicitudModel = new Solicitud();
        $this->materialModel = new Material();
        $this->localidadModel = new Localidad();
        $this->ciudadanoModel = new Ciudadano();
    }

    /**
     * Muestra el formulario para crear una nueva solicitud de recolección.
     */
    public function crear(): void {
        $user = currentUser();
        $perfil = $this->ciudadanoModel->findByUserId((int)$user['id_usuario']);

        $materiales = $this->materialModel->getAllActive();
        $localidades = $this->localidadModel->getAllActive();

        $error = flash('error');
        $old = $_SESSION['old_solicitud'] ?? [];
        unset($_SESSION['old_solicitud']);

        $this->view('ciudadano/crear_solicitud', [
            'titulo'      => 'Registrar Solicitud de Recolección — EcoPoints',
            'user'        => $user,
            'perfil'      => $perfil,
            'materiales'  => $materiales,
            'localidades' => $localidades,
            'error'       => $error,
            'old'         => $old,
            'activeNav'   => 'crear_solicitud'
        ]);
    }

    /**
     * Procesa la solicitud POST de creación de solicitud de recolección.
     */
    public function postCrear(): void {
        // 1. Verificación obligatoria de token CSRF
        if (!verify_csrf()) {
            flash('error', 'Token de seguridad inválido o expirado. Por favor, intenta de nuevo.');
            $this->redirect('ciudadano/solicitud/crear');
            return;
        }

        // 2. Extraer usuario autenticado de la sesión (nunca del POST)
        $user = currentUser();
        $idCiudadano = (int)$user['id_usuario'];

        // Guardar input anterior por si hay error
        $_SESSION['old_solicitud'] = $_POST;

        // 3. Sanitizar y extraer datos del formulario
        $idMaterial = (int)($_POST['id_material'] ?? 0);
        $cantidad = filter_input(INPUT_POST, 'cantidad_estimada', FILTER_VALIDATE_FLOAT);
        $idLocalidad = (int)($_POST['id_localidad'] ?? 0);
        $direccion = trim($_POST['direccion'] ?? '');
        $barrio = trim($_POST['barrio'] ?? '');
        $fechaDisp = trim($_POST['fecha_disponibilidad'] ?? '');
        $franjaHoraria = trim($_POST['franja_horaria'] ?? '08:00-14:00');
        $prioridad = trim($_POST['prioridad'] ?? 'Normal');
        $observaciones = trim($_POST['observaciones'] ?? '');

        // 4. Validaciones de negocio en el servidor
        $errores = [];

        // Validar material existente en catálogo
        $material = $this->materialModel->findById($idMaterial);
        if (!$material) {
            $errores[] = "Debes seleccionar un tipo de material válido del catálogo.";
        }

        // Validar cantidad numérica mayor a 0
        if ($cantidad === false || $cantidad === null || $cantidad <= 0) {
            $errores[] = "La cantidad estimada debe ser un número mayor a cero (ej: 5.5 kg).";
        } elseif ($cantidad > 10000) {
            $errores[] = "La cantidad registrada excede el límite razonable para recolección domiciliaria.";
        }

        // Validar localidad oficial existente
        $localidad = $this->localidadModel->findById($idLocalidad);
        if (!$localidad) {
            $errores[] = "Debes seleccionar una localidad oficial válida de Bogotá.";
        }

        // Validar dirección
        if (empty($direccion) || strlen($direccion) < 5) {
            $errores[] = "La dirección de recolección es obligatoria y debe ser detallada.";
        }

        // Validar fecha de disponibilidad (no permitir fechas pasadas)
        if (empty($fechaDisp)) {
            $errores[] = "Debes indicar la fecha en la que el material estará disponible.";
        } else {
            $timestampDisp = strtotime($fechaDisp);
            $hoy = strtotime(date('Y-m-d'));
            if (!$timestampDisp) {
                $errores[] = "El formato de la fecha de disponibilidad no es válido.";
            } elseif ($timestampDisp < $hoy) {
                $errores[] = "La fecha de disponibilidad no puede ser una fecha pasada.";
            }
        }

        // Desglosar franja horaria
        $horaInicio = '08:00:00';
        $horaFin = '14:00:00';
        if ($franjaHoraria === '08:00-12:00') {
            $horaInicio = '08:00:00';
            $horaFin = '12:00:00';
        } elseif ($franjaHoraria === '12:00-17:00') {
            $horaInicio = '12:00:00';
            $horaFin = '17:00:00';
        } elseif ($franjaHoraria === '07:00-11:00') {
            $horaInicio = '07:00:00';
            $horaFin = '11:00:00';
        } elseif ($franjaHoraria === '14:00-18:00') {
            $horaInicio = '14:00:00';
            $horaFin = '18:00:00';
        }

        // Prioridad permitida
        $prioridadesValidas = ['Normal', 'Alta', 'Urgente'];
        if (!in_array($prioridad, $prioridadesValidas, true)) {
            $prioridad = 'Normal';
        }

        if (!empty($errores)) {
            flash('error', implode('<br>', $errores));
            $this->redirect('ciudadano/solicitud/crear');
            return;
        }

        // 5. Inserción atómica en base de datos mediante el Modelo
        try {
            $idSolicitud = $this->solicitudModel->create([
                'id_ciudadano'         => $idCiudadano,
                'id_material'          => $idMaterial,
                'id_localidad'         => $idLocalidad,
                'cantidad_estimada'    => $cantidad,
                'direccion'            => $direccion,
                'barrio'               => $barrio,
                'fecha_disponibilidad' => $fechaDisp,
                'hora_inicio'          => $horaInicio,
                'hora_fin'             => $horaFin,
                'prioridad'            => $prioridad,
                'observaciones'        => $observaciones
            ]);

            // Limpiar datos previos
            unset($_SESSION['old_solicitud']);

            // Mensaje de éxito y Post-Redirect-Get
            flash('success', "¡Solicitud #{$idSolicitud} registrada con éxito! Los recicladores de {$localidad['nombre']} podrán atenderla pronto.");
            $this->redirect('ciudadano/solicitudes');

        } catch (Exception $e) {
            error_log("Error al crear solicitud de recolección: " . $e->getMessage());
            flash('error', "Ocurrió un error al guardar la solicitud: " . $e->getMessage());
            $this->redirect('ciudadano/solicitud/crear');
        }
    }

    /**
     * Muestra el listado de todas las solicitudes registradas por el ciudadano autenticado.
     */
    public function misSolicitudes(): void {
        $user = currentUser();
        $idCiudadano = (int)$user['id_usuario'];

        $solicitudes = $this->solicitudModel->getByCiudadano($idCiudadano);
        $metricas = $this->solicitudModel->getMetricasCiudadano($idCiudadano);

        $success = flash('success');
        $error = flash('error');

        $this->view('ciudadano/solicitudes', [
            'titulo'      => 'Mis Solicitudes de Recolección — EcoPoints',
            'user'        => $user,
            'solicitudes' => $solicitudes,
            'metricas'    => $metricas,
            'success'     => $success,
            'error'       => $error,
            'activeNav'   => 'mis_solicitudes'
        ]);
    }
}
