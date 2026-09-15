<?php
// =====================================================================
// ECOPOINT — CONTROL DE ACCESO, PERMISOS Y PROTECCIÓN DE RUTAS
// =====================================================================

require_once __DIR__ . '/auth.php';

/**
 * Exigir inicio de sesión para acceder a la página
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Debes iniciar sesión para acceder a esta sección.');
        header('Location: /login.php');
        exit;
    }
}

/**
 * Exigir rol específico (ej: 'reciclador' o 'ciudadano')
 */
function requireRole(string ...$allowedRoles): void {
    requireLogin();

    $userRole = getCurrentRole();
    if (!in_array($userRole, $allowedRoles, true)) {
        setFlash('danger', 'Acceso no autorizado. Tu tipo de cuenta no tiene permiso para ingresar a esta sección.');

        // Redirigir a su propio dashboard correspondiente
        if ($userRole === 'reciclador') {
            header('Location: /reciclador/dashboard.php');
        } elseif ($userRole === 'ciudadano') {
            header('Location: /ciudadano/dashboard.php');
        } else {
            header('Location: /login.php');
        }
        exit;
    }
}

/**
 * Si ya tiene sesión activa, redirigir directo a su dashboard
 */
function redirectIfLoggedIn(): void {
    if (isLoggedIn()) {
        $userRole = getCurrentRole();
        if ($userRole === 'reciclador') {
            header('Location: /reciclador/dashboard.php');
            exit;
        } elseif ($userRole === 'ciudadano') {
            header('Location: /ciudadano/dashboard.php');
            exit;
        }
    }
}
