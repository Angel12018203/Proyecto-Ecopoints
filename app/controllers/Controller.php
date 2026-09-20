<?php
/**
 * ============================================================================
 * CLASE BASE CONTROLADOR (CONTROLLER)
 * ============================================================================
 * 
 * ¿QUÉ HACE UN CONTROLADOR EN LA ARQUITECTURA MVC?
 * El Controlador es el director de orquesta:
 * 1. Recibe las peticiones HTTP del usuario a través de las rutas.
 * 2. Solicita datos o ejecuta acciones en los Modelos.
 * 3. Prepara la información y la envía a la Vista adecuada para renderizarla.
 * 
 * ¿QUÉ FACILIDADES OFRECE ESTA CLASE BASE?
 * - `view()`: Carga una vista PHP extrayendo automáticamente variables seguras.
 * - `redirect()`: Redirige al navegador hacia otra ruta del sistema.
 * - `json()`: Responde datos en formato JSON para peticiones asíncronas / AJAX.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/view_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/csrf_helper.php';

abstract class Controller {

    /**
     * Renderiza una vista PHP pasando variables desde el controlador.
     * 
     * @param string $view Nombre de la vista en notación de carpetas (ej: 'auth/login', 'reciclador/dashboard')
     * @param array $data Variables que estarán disponibles dentro de la plantilla HTML
     */
    protected function view(string $view, array $data = []): void {
        // extract() convierte las claves de un arreglo en variables independientes:
        // ['titulo' => 'Inicio'] se convierte en $titulo = 'Inicio' dentro de la vista
        extract($data);

        $viewFile = APP_PATH . 'views' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $view) . '.php';

        if (!file_exists($viewFile)) {
            die("Error 500: La vista '{$view}' no existe en el sistema.");
        }

        require $viewFile;
    }

    /**
     * Redirige a otra ruta del sistema y detiene la ejecución inmediatamente.
     * 
     * @param string $route Ruta destino (ej: 'login', 'reciclador')
     * @param array $params Parámetros adicionales en query string
     */
    protected function redirect(string $route, array $params = []): void {
        header('Location: ' . url($route, $params));
        exit;
    }

    /**
     * Envía una respuesta estructurada en formato JSON (útil para APIs o JS).
     * 
     * @param mixed $data Datos a serializar en JSON
     * @param int $statusCode Código HTTP (200, 400, 404, 500, etc.)
     */
    protected function json($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
