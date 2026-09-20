<?php
/**
 * ============================================================================
 * ENRUTADOR PRINCIPAL / FRONT CONTROLLER (index.php)
 * ============================================================================
 * 
 * ¿QUÉ ES UN FRONT CONTROLLER?
 * Es el punto único de entrada de toda la aplicación web. En lugar de tener
 * archivos PHP desorganizados con SQL y HTML mezclado, todas las solicitudes
 * llegan primero a este archivo, el cual:
 * 1. Carga la configuración global y las medidas de seguridad.
 * 2. Analiza la URL solicitada (ej: '/login', '/registro', '/reciclador').
 * 3. Delega la petición al Controlador y Método específico correspondiente.
 * 
 * ¿CÓMO FUNCIONA EL ENRUTAMIENTO?
 * Soporta dos modalidades para máxima compatibilidad con XAMPP:
 * - Parámetro GET explícito: `index.php?route=login`
 * - URL amigable reescrita: `http://localhost/Proyecto_Ecopoints/login` (vía .htaccess o PATH_INFO)
 */

// 1. Cargar el entorno de configuración global
require_once __DIR__ . '/app/config/config.php';

// 2. Cargar funciones auxiliares de seguridad, vistas y autenticación
require_once __DIR__ . '/app/helpers/view_helper.php';
require_once __DIR__ . '/app/helpers/csrf_helper.php';
require_once __DIR__ . '/app/helpers/auth_helper.php';
require_once __DIR__ . '/app/middleware/AuthMiddleware.php';

// 3. Cargar los controladores del sistema
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/RecicladorController.php';
require_once __DIR__ . '/app/controllers/CiudadanoController.php';
require_once __DIR__ . '/app/controllers/CentroAcopioController.php';

// 4. Determinar la ruta solicitada
$route = $_GET['route'] ?? $_GET['url'] ?? '';

// Si no se pasó por parámetro GET, intentar extraer desde PATH_INFO o REQUEST_URI
if ($route === '') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $baseFolder = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    
    // Remover la carpeta base si existe en la URI
    $path = trim($requestUri, '/');
    if ($baseFolder !== '' && strpos($path, $baseFolder) === 0) {
        $path = trim(substr($path, strlen($baseFolder)), '/');
    }
    
    // Limpiar 'index.php' si viene en la ruta
    if (strpos($path, 'index.php') === 0) {
        $path = trim(substr($path, strlen('index.php')), '/');
    }
    
    $route = $path;
}

// Normalizar la ruta eliminando barras sobrantes y caracteres extraños
$route = trim($route, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 5. Ruta por defecto cuando se accede a la raíz del proyecto
if ($route === '') {
    if (isAuthenticated()) {
        $rol = currentRole();
        $route = 'reciclador';
        if ($rol === 'ciudadano') {
            $route = 'ciudadano';
        } elseif ($rol === 'centro_acopio') {
            $route = 'centro-acopio';
        }
    } else {
        $route = 'login';
    }
}

// 6. Despacho y enrutamiento hacia los controladores
switch ($route) {

    // ── Módulo de Autenticación ──
    case 'login':
        $auth = new AuthController();
        if ($method === 'POST') {
            $auth->postLogin();
        } else {
            $auth->login();
        }
        break;

    case 'registro':
        $auth = new AuthController();
        if ($method === 'POST') {
            $auth->postRegistro();
        } else {
            $auth->registro();
        }
        break;

    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        break;

    // ── Módulo del Reciclador (Actor Principal) ──
    case 'reciclador':
    case 'reciclador/dashboard':
        $controller = new RecicladorController();
        $controller->dashboard();
        break;

    // ── Módulo del Ciudadano ──
    case 'ciudadano':
    case 'ciudadano/dashboard':
        $controller = new CiudadanoController();
        $controller->dashboard();
        break;

    // ── Módulo del Centro de Acopio ──
    case 'centro-acopio':
    case 'centro_acopio':
    case 'centro-acopio/dashboard':
        $controller = new CentroAcopioController();
        $controller->dashboard();
        break;

    // ── Ruta no encontrada (404) ──
    default:
        http_response_code(404);
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>404 — No Encontrado</title>";
        echo "<link rel='stylesheet' href='" . asset('css/styles.css') . "'>";
        echo "</head><body style='display:flex;align-items:center;justify-content:center;height:100vh;background:#FAFCFA;font-family:sans-serif;'>";
        echo "<div style='text-align:center;padding:32px;background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:440px;'>";
        echo "<div style='font-size:3.5rem;margin-bottom:10px;'>🍃</div>";
        echo "<h2 style='color:#087A3D;margin-bottom:8px;'>404 — Página no encontrada</h2>";
        echo "<p style='color:#4A6355;font-size:0.9rem;margin-bottom:20px;'>La sección que buscas ('" . htmlspecialchars($route) . "') no existe o fue movida.</p>";
        echo "<a href='" . url('') . "' class='btn btn-p' style='display:inline-block;'>Volver al inicio</a>";
        echo "</div></body></html>";
        break;
}
