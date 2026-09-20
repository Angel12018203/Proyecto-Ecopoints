<?php
/**
 * ============================================================================
 * CONTROLADOR: AUTENTICACIÓN (AuthController)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE CONTROLADOR?
 * Gestiona el ciclo completo de identidad y acceso de los usuarios:
 * 1. Mostrar formulario de Login (diseño aprobado de Figma).
 * 2. Procesar inicio de sesión seguro con verificación BCRYPT y prevención de fijación de sesión.
 * 3. Mostrar formulario de Registro adaptativo por roles (Ciudadano, Reciclador, Centro de Acopio).
 * 4. Procesar registro transaccional con validaciones frontend y backend simultáneas.
 * 5. Cierre de sesión seguro.
 * 
 * FLUJO DE SEGURIDAD EN EL LOGIN (Punto 21 de requisitos):
 * 1. El usuario introduce correo y contraseña.
 * 2. Validación básica en JavaScript antes del envío.
 * 3. Envío por POST al servidor.
 * 4. Validación de token CSRF (`verify_csrf`).
 * 5. Sanitización y validación en PHP.
 * 6. Consulta segura con PDO y parámetros preparados.
 * 7. Verificación de hash con `password_verify()`.
 * 8. Regeneración de identificador de sesión con `session_regenerate_id(true)`.
 * 9. Almacenamiento de datos del usuario y perfil en `$_SESSION`.
 * 10. Identificación de rol y redirección a su panel correspondiente.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Localidad.php';
require_once __DIR__ . '/../models/Reciclador.php';
require_once __DIR__ . '/../models/Ciudadano.php';
require_once __DIR__ . '/../models/CentroAcopio.php';

class AuthController extends Controller {

    private $usuarioModel;
    private $localidadModel;
    private $recicladorModel;
    private $ciudadanoModel;
    private $centroAcopioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->localidadModel = new Localidad();
        $this->recicladorModel = new Reciclador();
        $this->ciudadanoModel = new Ciudadano();
        $this->centroAcopioModel = new CentroAcopio();
    }

    /**
     * Muestra la pantalla de inicio de sesión.
     * Si el usuario ya está autenticado, lo redirige directamente a su panel.
     */
    public function login(): void {
        if (isAuthenticated()) {
            $this->redirectByRole(currentRole());
            return;
        }

        $error = flash('error');
        $success = flash('success');

        $this->view('auth/login', [
            'titulo'  => 'Iniciar sesión — EcoPoints',
            'error'   => $error,
            'success' => $success
        ]);
    }

    /**
     * Procesa la solicitud POST de inicio de sesión.
     */
    public function postLogin(): void {
        // 1. Verificación obligatoria de token CSRF
        if (!verify_csrf()) {
            flash('error', 'Token de seguridad inválido o expirado. Por favor, intenta de nuevo.');
            $this->redirect('login');
            return;
        }

        // 2. Sanitización básica y extracción de datos
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // 3. Validación de campos obligatorios en el servidor
        if (empty($correo) || empty($password)) {
            flash('error', 'Por favor ingresa tu correo electrónico y contraseña.');
            $this->redirect('login');
            return;
        }

        // 4. Búsqueda del usuario por correo electrónico mediante PDO
        $usuario = $this->usuarioModel->findByEmail($correo);

        // 5. Verificación de existencia y hash de contraseña
        if (!$usuario || !Usuario::verifyPassword($password, $usuario['password'])) {
            flash('error', 'Credenciales incorrectas. Verifica tu correo y contraseña.');
            $this->redirect('login');
            return;
        }

        // 6. Verificación del estado de la cuenta
        if ($usuario['estado'] !== 'activo') {
            flash('error', 'Tu cuenta se encuentra inactiva o suspendida. Comunícate con soporte.');
            $this->redirect('login');
            return;
        }

        // 7. Actualización de fecha y hora del último acceso
        $this->usuarioModel->updateLastLogin((int)$usuario['id_usuario']);

        // 8. Cargar perfil correspondiente según el rol
        $perfil = null;
        $idUsuario = (int)$usuario['id_usuario'];
        $idRol = (int)$usuario['id_rol'];

        if ($idRol === 1) { // Reciclador
            $perfil = $this->recicladorModel->findByUserId($idUsuario);
        } elseif ($idRol === 2) { // Ciudadano
            $perfil = $this->ciudadanoModel->findByUserId($idUsuario);
        } elseif ($idRol === 3) { // Centro de Acopio
            $perfil = $this->centroAcopioModel->findByUserId($idUsuario);
        }

        // 9. Crear la sesión segura (con session_regenerate_id)
        loginUser($usuario, $perfil);

        // 10. Redirigir al panel correspondiente según el rol
        $this->redirectByRole($usuario['rol_nombre']);
    }

    /**
     * Muestra la pantalla de registro adaptativo según el rol.
     */
    public function registro(): void {
        if (isAuthenticated()) {
            $this->redirectByRole(currentRole());
            return;
        }

        // Cargar el catálogo oficial de localidades de Bogotá para los desplegables
        $localidades = $this->localidadModel->getAllActive();

        $error = flash('error');
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $this->view('auth/registro', [
            'titulo'      => 'Crear cuenta — EcoPoints',
            'localidades' => $localidades,
            'error'       => $error,
            'old'         => $old
        ]);
    }

    /**
     * Procesa la solicitud POST de registro de nuevo usuario.
     */
    public function postRegistro(): void {
        // 1. Verificación CSRF
        if (!verify_csrf()) {
            flash('error', 'Token de seguridad inválido. Recarga el formulario e intenta de nuevo.');
            $this->redirect('registro');
            return;
        }

        // 2. Extracción y sanitización de datos generales
        $rol = trim($_POST['rol'] ?? 'reciclador');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $idLocalidad = (int)($_POST['id_localidad'] ?? 0);
        $terminos = isset($_POST['terminos']);

        // Guardar valores previos en sesión por si hay error y debe rellenarse el formulario
        $_SESSION['old_input'] = $_POST;

        // 3. Validaciones generales en el servidor
        $errores = [];

        if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
        if (empty($apellido)) $errores[] = "El apellido es obligatorio.";
        
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "Debes ingresar un correo electrónico válido.";
        }

        if (empty($telefono)) $errores[] = "El número de teléfono es obligatorio.";
        
        if (strlen($password) < 6) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres.";
        }

        if ($password !== $passwordConfirm) {
            $errores[] = "Las contraseñas no coinciden.";
        }

        if (!$terminos) {
            $errores[] = "Debes aceptar la política de tratamiento de datos personales.";
        }

        // Validar que la localidad exista en el catálogo oficial
        $localidad = $this->localidadModel->findById($idLocalidad);
        if (!$localidad) {
            $errores[] = "Debes seleccionar una localidad válida de Bogotá.";
        }

        // Mapeo de rol permitido
        $rolesValidos = [
            'reciclador'    => 1,
            'ciudadano'     => 2,
            'centro_acopio' => 3
        ];

        if (!array_key_exists($rol, $rolesValidos)) {
            $errores[] = "El tipo de cuenta seleccionado no es válido.";
        }

        // Validar si el correo ya está en uso
        if (empty($errores) && $this->usuarioModel->findByEmail($correo)) {
            $errores[] = "El correo electrónico '{$correo}' ya se encuentra registrado en EcoPoints.";
        }

        // 4. Validaciones específicas por cada rol
        if ($rol === 'ciudadano') {
            $direccion = trim($_POST['direccion'] ?? '');
            $barrio = trim($_POST['barrio'] ?? '');
            if (empty($direccion)) $errores[] = "La dirección de residencia es obligatoria.";
            if (empty($barrio)) $errores[] = "El barrio es obligatorio.";
        } elseif ($rol === 'reciclador') {
            $zonaTrabajo = trim($_POST['zona_trabajo'] ?? '');
            $medioTransporte = trim($_POST['medio_transporte'] ?? '');
            if (empty($zonaTrabajo)) $errores[] = "La zona habitual de trabajo es obligatoria.";
            if (empty($medioTransporte)) $errores[] = "El medio de transporte es obligatorio.";
        } elseif ($rol === 'centro_acopio') {
            $nombreCentro = trim($_POST['nombre_centro'] ?? '');
            $direccionCentro = trim($_POST['direccion_centro'] ?? '');
            if (empty($nombreCentro)) $errores[] = "El nombre del centro de acopio es obligatorio.";
            if (empty($direccionCentro)) $errores[] = "La dirección del establecimiento es obligatoria.";
        }

        // Si se encontraron errores, redirigir con el primer mensaje
        if (!empty($errores)) {
            flash('error', implode('<br>', $errores));
            $this->redirect('registro');
            return;
        }

        // 5. REGISTRO TRANSACCIONAL (Garantiza consistencia en Base de Datos)
        try {
            $this->usuarioModel->beginTransaction();

            $idRol = $rolesValidos[$rol];

            // A. Insertar en tabla principal 'usuarios'
            $idUsuario = $this->usuarioModel->create([
                'nombre'   => $nombre,
                'apellido' => $apellido,
                'correo'   => $correo,
                'telefono' => $telefono,
                'password' => $password,
                'id_rol'   => $idRol
            ]);

            // B. Insertar en tabla de perfil respectiva
            $perfil = null;

            if ($rol === 'reciclador') {
                $this->recicladorModel->createProfile([
                    'id_usuario'       => $idUsuario,
                    'id_localidad'     => $idLocalidad,
                    'zona_trabajo'     => $_POST['zona_trabajo'] ?? $localidad['nombre'],
                    'medio_transporte' => $_POST['medio_transporte'] ?? 'Bicicleta',
                    'disponibilidad'   => $_POST['disponibilidad'] ?? 'Lunes a Sábado 7:00 a.m. - 4:00 p.m.'
                ]);
                $perfil = $this->recicladorModel->findByUserId($idUsuario);

            } elseif ($rol === 'ciudadano') {
                $this->ciudadanoModel->createProfile([
                    'id_usuario'   => $idUsuario,
                    'id_localidad' => $idLocalidad,
                    'direccion'    => $_POST['direccion'] ?? '',
                    'barrio'       => $_POST['barrio'] ?? '',
                    'preferencias' => $_POST['preferencias'] ?? 'Material clasificado y seco'
                ]);
                $perfil = $this->ciudadanoModel->findByUserId($idUsuario);

            } elseif ($rol === 'centro_acopio') {
                $this->centroAcopioModel->createProfile([
                    'id_usuario'    => $idUsuario,
                    'id_localidad'  => $idLocalidad,
                    'nombre_centro' => $_POST['nombre_centro'] ?? "EcoCentro {$localidad['nombre']}",
                    'responsable'   => trim($nombre . ' ' . $apellido),
                    'direccion'     => $_POST['direccion_centro'] ?? '',
                    'telefono'      => $telefono
                ]);
                $perfil = $this->centroAcopioModel->findByUserId($idUsuario);
            }

            // Confirmar transacción en MySQL
            $this->usuarioModel->commit();

            // Limpiar datos previos
            unset($_SESSION['old_input']);

            // Iniciar sesión automáticamente tras el registro
            $usuarioNuevo = $this->usuarioModel->findById($idUsuario);
            loginUser($usuarioNuevo, $perfil);

            flash('success', "¡Bienvenido a EcoPoints, {$nombre}! Tu cuenta fue creada exitosamente.");
            $this->redirectByRole($rol);

        } catch (Exception $e) {
            // Revertir todo en caso de error
            $this->usuarioModel->rollBack();
            error_log("Error al registrar usuario: " . $e->getMessage());
            flash('error', "No fue posible crear la cuenta: " . $e->getMessage());
            $this->redirect('registro');
        }
    }

    /**
     * Cierra la sesión activa del usuario y redirige al Login.
     */
    public function logout(): void {
        logoutUser();
        flash('success', 'Has cerrado sesión exitosamente. ¡Hasta pronto!');
        $this->redirect('login');
    }

    /**
     * Redirige al panel correspondiente según el rol del usuario.
     */
    private function redirectByRole(?string $rol): void {
        switch ($rol) {
            case 'ciudadano':
                $this->redirect('ciudadano');
                break;
            case 'centro_acopio':
                $this->redirect('centro-acopio');
                break;
            case 'reciclador':
            default:
                $this->redirect('reciclador');
                break;
        }
    }
}
