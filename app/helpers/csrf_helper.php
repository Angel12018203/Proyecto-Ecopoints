<?php
/**
 * ============================================================================
 * HELPER DE PROTECCIÓN CONTRA CSRF (Cross-Site Request Forgery)
 * ============================================================================
 * 
 * ¿QUÉ ES CSRF Y POR QUÉ ES PELIGROSO?
 * Un ataque CSRF ocurre cuando un sitio web malicioso o externo engaña al
 * navegador de un usuario autenticado para que envíe una petición no deseada
 * (como crear un usuario, cambiar una contraseña o transferir datos) a nuestro
 * servidor, aprovechando las cookies de sesión activas.
 * 
 * ¿CÓMO LO PREVENIMOS?
 * 1. Generamos un token secreto, criptográficamente seguro y único en la sesión.
 * 2. Incrustamos este token en un campo oculto dentro de cada formulario POST sensible.
 * 3. Al recibir la petición en el servidor, comparamos el token enviado con el guardado en la sesión.
 * 4. Usamos `hash_equals()` para evitar ataques de tiempo (timing attacks).
 * Si no coinciden o no existe el token, el servidor rechaza la petición inmediatamente con código 403.
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Obtiene el token CSRF activo de la sesión o genera uno nuevo si no existe.
 * 
 * @return string Token hexadecimal seguro de 64 caracteres
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes(32) genera 32 bytes aleatorios criptográficos
        // bin2hex los convierte a una cadena legible de 64 caracteres hexadecimales
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Genera el campo HTML oculto con el token CSRF listo para incluir en formularios.
 * 
 * @return string Código HTML <input type="hidden" ... />
 */
function csrf_input(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Valida si el token enviado por POST o por cabecera coincide exactamente con el de la sesión.
 * 
 * @param string|null $token Token recibido en la petición
 * @return bool True si es válido y legítimo, False si es fraudulento o inexistente
 */
function verify_csrf(?string $token = null): bool {
    // Si no se pasa el parámetro, se busca automáticamente en $_POST o en cabeceras HTTP
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    // hash_equals realiza una comparación de cadenas en tiempo constante,
    // previniendo ataques de análisis de tiempo de respuesta (timing attack).
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Exige obligatoriamente un token CSRF válido. Si la validación falla,
 * detiene la ejecución inmediatamente respondiendo error 403 Forbidden.
 */
function require_csrf(): void {
    if (!verify_csrf()) {
        http_response_code(403);
        die(
            "<div style='font-family:sans-serif;padding:30px;max-width:500px;margin:50px auto;background:#FFF5F5;border:2px solid #E53E3E;border-radius:12px;text-align:center;'>" .
            "<h2 style='color:#E53E3E;'>🚫 403 — Solicitud rechazada por seguridad</h2>" .
            "<p style='color:#4A6355;'>El token de seguridad (CSRF) expiró o es inválido. Por favor, recarga la página e intenta de nuevo.</p>" .
            "<a href='javascript:history.back()' style='display:inline-block;padding:10px 20px;background:#087A3D;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Volver</a>" .
            "</div>"
        );
    }
}
