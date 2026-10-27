<?php
/**
 * ============================================================================
 * ARCHIVO DE CONFIGURACIÓN GLOBAL — ECOPOINTS
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE ARCHIVO?
 * Este archivo centraliza todas las constantes y configuraciones globales
 * del proyecto: conexión a base de datos, rutas base del sistema, manejo
 * de sesiones y zona horaria.
 * 
 * ¿POR QUÉ SE DISEÑÓ ASÍ?
 * Centralizar la configuración evita repetir credenciales o rutas absolutas
 * en diferentes archivos. Si en el futuro cambias de servidor, de base de datos
 * o de carpeta en XAMPP, solo debes modificar los valores de este archivo.
 * 
 * ¿CÓMO FUNCIONA?
 * Es el primer archivo requerido por el enrutador principal (index.php).
 * Al cargarse, inicia la sesión segura de PHP (si no estaba iniciada), define
 * constantes inmutables y establece la zona horaria oficial de Colombia.
 */

// 1. Manejo y visualización de errores (Modo desarrollo local en XAMPP)
// En producción estos valores se deben apagar (display_errors = 0)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Zona horaria oficial para Colombia (Bogotá D.C.) y codificación UTF-8
date_default_timezone_set('America/Bogota');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
}
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// 3. Inicio seguro de sesión PHP
// Se verifica primero si ya existe una sesión activa para evitar avisos 'session already started'
if (session_status() === PHP_SESSION_NONE) {
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        // Firma compatible con PHP 7.2 y anteriores: lifetime, path, domain, secure, httponly
        session_set_cookie_params(0, '/', '', false, true);
    }
    if (!headers_sent()) {
        session_start();
    }
}

// 4. Parámetros de conexión a la Base de Datos MySQL/MariaDB en XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecopoint');
define('DB_USER', 'root');
define('DB_PASS', '');            // Por defecto en XAMPP el usuario root no tiene contraseña
define('DB_CHARSET', 'utf8mb4');

// 5. Constantes generales de la aplicación
define('APP_NAME', 'EcoPoints');
define('APP_VERSION', '1.0.0');

// 6. Detección automática de la URL Base del proyecto (BASE_URL)
// Permite que el sistema funcione automáticamente tanto en 'http://localhost/Proyecto_Ecopoints/'
// como en VirtualHosts o puertos personalizados sin tener que reescribir código.
if (!defined('BASE_URL')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['https']) && $_SERVER['https'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        
        // Detectar directorio base a partir de la ruta del script actual
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $scriptDir = rtrim($scriptDir, '/');
        
        define('BASE_URL', $protocol . $host . $scriptDir . '/');
    } else {
        // Fallback para ejecución desde consola (CLI / pruebas PHPUnit / scripts de prueba)
        define('BASE_URL', 'http://localhost/Proyecto_Ecopoints/');
    }
}

// 7. Rutas internas del servidor para inclusión de archivos PHP
define('ROOT_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR);

// 8. Configuración del Módulo de Minería de Datos y Modelos ML
define('PYTHON_BIN', getenv('ECOPOINTS_PYTHON_BIN') ?: (
    file_exists('C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\.venv\\Scripts\\python.exe')
        ? 'C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\.venv\\Scripts\\python.exe'
        : 'python'
));

define('ML_MODELS_PATH', getenv('ECOPOINTS_MODELOS_DIR') ?: (
    file_exists('C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\11_modelos_guardados')
        ? 'C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\11_modelos_guardados'
        : ROOT_PATH . 'ml_models'
));

define('ML_REGLAS_PATH', getenv('ECOPOINTS_REGLAS_PATH') ?: (
    file_exists('C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\09_reglas_asociacion\\reglas_asociacion_descubiertas.csv')
        ? 'C:\\Users\\rueda\\Desktop\\Proyecto_Integrador_X\\Ecopoints_Estructura\\09_reglas_asociacion\\reglas_asociacion_descubiertas.csv'
        : ''
));

