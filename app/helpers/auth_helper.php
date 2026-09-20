<?php
/**
 * ============================================================================
 * HELPER DE AUTENTICACIÓN, SESIONES Y CONTROL DE ACCESO
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE ARCHIVO?
 * Centraliza la verificación del estado de inicio de sesión, el control
 * de roles y permisos, y la protección de rutas privadas.
 * 
 * CONCEPTOS CLAVE DE SEGURIDAD IMPLEMENTADOS:
 * 1. Prevención de Session Fixation: Al autenticarse exitosamente, se ejecuta
 *    `session_regenerate_id(true)` para cambiar el ID de la sesión y descartar
 *    la cookie previa.
 * 2. Control de Acceso Basado en Roles (RBAC): Cada usuario posee un rol
 *    específico ('reciclador', 'ciudadano', 'centro_acopio', 'administrador').
 *    Las funciones `requireRole()` impiden que un ciudadano o atacante acceda a paneles
 *    privados de reciclador simplemente escribiendo la URL.
 * 3. Cierre de sesión seguro: Limpia el arreglo `$_SESSION`, destruye la sesión
 *    en disco y expira la cookie en el navegador.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/view_helper.php';

/**
 * Comprueba si el visitante actual tiene una sesión activa y autenticada.
 * 
 * @return bool True si está autenticado
 */
function isAuthenticated(): bool {
    return !empty($_SESSION['usuario']) && !empty($_SESSION['usuario']['id_usuario']);
}

/**
 * Devuelve la información del usuario autenticado actualmente.
 * 
 * @return array|null Datos del usuario o null si no ha iniciado sesión
 */
function currentUser(): ?array {
    return $_SESSION['usuario'] ?? null;
}

/**
 * Devuelve el rol del usuario autenticado (ej: 'reciclador', 'ciudadano', 'centro_acopio').
 * 
 * @return string|null Nombre del rol o null si no está autenticado
 */
function currentRole(): ?string {
    return $_SESSION['usuario']['rol'] ?? null;
}

/**
 * Verifica si el usuario autenticado tiene un rol específico.
 * 
 * @param string|array $roles Rol o lista de roles permitidos
 * @return bool True si cumple con el rol requerido
 */
function hasRole($roles): bool {
    if (!isAuthenticated()) {
        return false;
    }
    
    $userRole = currentRole();
    if (is_array($roles)) {
        return in_array($userRole, $roles, true);
    }
    
    return $userRole === $roles;
}

/**
 * Registra formalmente la sesión de un usuario tras una autenticación exitosa.
 * 
 * @param array $usuario Datos provenientes de la tabla 'usuarios'
 * @param array|null $perfil Datos extendidos del perfil (reciclador, ciudadano, etc.)
 */
function loginUser(array $usuario, ?array $perfil = null): void {
    // 1. REGENERACIÓN DE ID DE SESIÓN (Protección contra fijación de sesión)
    // El parámetro true elimina el archivo de sesión anterior del servidor.
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        session_regenerate_id(true);
    }

    // 2. Mapeo de nombre de rol si viene como id_rol
    $rolNombre = $usuario['rol_nombre'] ?? null;
    if (!$rolNombre) {
        $rolesMap = [
            1 => 'reciclador',
            2 => 'ciudadano',
            3 => 'centro_acopio',
            4 => 'administrador'
        ];
        $rolNombre = $rolesMap[(int)$usuario['id_rol']] ?? 'reciclador';
    }

    // 3. Guardar en $_SESSION los datos necesarios para la navegación
    $_SESSION['usuario'] = [
        'id_usuario'     => (int)$usuario['id_usuario'],
        'nombre'         => $usuario['nombre'],
        'apellido'       => $usuario['apellido'],
        'nombre_completo'=> trim($usuario['nombre'] . ' ' . $usuario['apellido']),
        'correo'         => $usuario['correo'],
        'telefono'       => $usuario['telefono'] ?? '',
        'id_rol'         => (int)$usuario['id_rol'],
        'rol'            => $rolNombre,
        'perfil'         => $perfil ?? []
    ];
}

/**
 * Cierra la sesión activa de forma completa y segura.
 */
function logoutUser(): void {
    // 1. Vaciar el arreglo de variables de sesión
    $_SESSION = [];

    // 2. Expirar la cookie de sesión en el navegador cliente
    if (ini_get("session.use_cookies") && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // 3. Destruir la sesión en el servidor si está activa
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Middleware: Exige que el usuario esté autenticado para ver la página.
 * Si no ha iniciado sesión, guarda un mensaje flash y lo redirige al Login.
 */
function requireLogin(): void {
    if (!isAuthenticated()) {
        flash('error', 'Debes iniciar sesión para acceder a esta sección.');
        header('Location: ' . url('login'));
        exit;
    }
}

/**
 * Middleware: Exige que el usuario pertenezca a uno o más roles autorizados.
 * Si no cumple el rol, se le deniega el acceso y se le redirige a su panel respectivo.
 * 
 * @param string|array $roles Rol o lista de roles autorizados
 */
function requireRole($roles): void {
    requireLogin(); // Primero debe tener sesión activa

    if (!hasRole($roles)) {
        // Redirigir al dashboard adecuado según su rol real para evitar bloqueos ciegos
        $userRole = currentRole();
        $targetRoute = 'login';
        
        if ($userRole === 'reciclador') {
            $targetRoute = 'reciclador';
        } elseif ($userRole === 'ciudadano') {
            $targetRoute = 'ciudadano';
        } elseif ($userRole === 'centro_acopio') {
            $targetRoute = 'centro-acopio';
        }
        
        flash('error', 'No tienes permisos para acceder al área solicitada.');
        header('Location: ' . url($targetRoute));
        exit;
    }
}
