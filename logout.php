<?php
// =====================================================================
// ECOPOINT — CIERRE SEGURO DE SESIÓN
// =====================================================================

require_once __DIR__ . '/auth/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar todas las variables de sesión
$_SESSION = [];

// Invalidar la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
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

// Destruir la sesión completamente
session_destroy();

// Iniciar nueva sesión limpia para dejar mensaje flash de despedida
session_start();
setFlash('info', 'Has cerrado sesión exitosamente. ¡Hasta pronto!');

header('Location: /login.php');
exit;
