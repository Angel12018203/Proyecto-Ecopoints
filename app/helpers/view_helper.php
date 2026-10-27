<?php
/**
 * ============================================================================
 * HELPER DE VISTAS, ESCAPE XSS Y UTILIDADES
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE ARCHIVO?
 * Proporciona funciones auxiliares para el renderizado seguro de plantillas:
 * 1. `h()`: Escapa caracteres especiales HTML para prevenir ataques XSS.
 * 2. `url()`: Genera URLs absolutas o compatibles para el enrutador.
 * 3. `asset()`: Resuelve rutas para hojas de estilo, scripts e imágenes.
 * 4. `flash()`: Administra mensajes temporales entre redirecciones (éxito, error).
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Escapa cadenas de texto antes de imprimirlas en HTML para prevenir Cross-Site Scripting (XSS).
 * 
 * ¿POR QUÉ ES VITAL?
 * Si un usuario introduce un nombre malicioso como `<script>alert(1)</script>`, imprimirlo
 * directamente ejecutaría código en el navegador de otros usuarios.
 * `htmlspecialchars` transforma los caracteres peligrosos:
 *   < se convierte en &lt;
 *   > se convierte en &gt;
 *   " se convierte en &quot;
 *   ' se convierte en &#039;
 * 
 * @param mixed $value Cadena o valor a escapar
 * @return string Cadena limpia e inocua para el navegador
 */
/**
 * Normaliza y repara cadenas con posible doble codificación UTF-8 (Mojibake).
 * Garantiza que caracteres especiales (tildes, eñes, diéresis) se vean perfectos.
 */
function fix_utf8(string $str): string {
    if (strpos($str, 'Ã') !== false) {
        $repaired = @mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
        if ($repaired !== false && mb_check_encoding($repaired, 'UTF-8')) {
            return $repaired;
        }
    }
    return $str;
}

function h($value): string {
    if ($value === null) {
        return '';
    }
    $str = fix_utf8((string)$value);
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Construye una URL compatible con el enrutador de EcoPoints.
 * 
 * Soporta dos formatos:
 * - Con mod_rewrite amigable: BASE_URL . $route (ej: 'http://localhost/Proyecto_Ecopoints/login')
 * - Fallback por parámetro: BASE_URL . 'index.php?route=' . $route
 * 
 * @param string $route Nombre de la ruta (ej: 'login', 'registro', 'reciclador')
 * @param array $params Parámetros adicionales en query string
 * @return string URL completa lista para usar en href o action
 */
function url(string $route = '', array $params = []): string {
    $route = ltrim($route, '/');
    
    // Si la ruta contiene un fragmento ancla (ej: 'reciclador#recompensas'), separarlo
    $fragment = '';
    if (strpos($route, '#') !== false) {
        list($route, $fragment) = explode('#', $route, 2);
    }
    
    // Si la ruta está vacía, devuelve la URL raíz
    if ($route === '') {
        $url = BASE_URL;
    } else {
        // Formato estándar compatible universalmente en cualquier Apache/XAMPP
        $url = BASE_URL . 'index.php?route=' . urlencode($route);
    }
    
    if (!empty($params)) {
        $separator = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $separator . http_build_query($params);
    }
    
    if ($fragment !== '') {
        $url .= '#' . $fragment;
    }
    
    return $url;
}

/**
 * Resuelve la ruta web hacia archivos estáticos en la carpeta `public/` (CSS, JS, imágenes).
 * 
 * @param string $path Ruta relativa dentro de public (ej: 'css/styles.css', 'img/logo.jpg')
 * @return string URL absoluta directa al recurso
 */
function asset(string $path): string {
    $path = ltrim($path, '/');
    return BASE_URL . 'public/' . $path;
}

/**
 * Gestiona mensajes "Flash" (mensajes que se muestran una sola vez después de redirigir).
 * 
 * Uso:
 * 1. Para guardar un mensaje: flash('error', 'Credenciales incorrectas');
 * 2. Para leerlo y borrarlo: $msg = flash('error');
 * 
 * @param string $key Clave del mensaje (ej: 'success', 'error', 'info')
 * @param string|null $message Mensaje a almacenar (si es null, se consulta el existente)
 * @return string|null Mensaje obtenido o null si no existe
 */
function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        // Guardar mensaje en sesión
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    // Consultar y eliminar el mensaje para que no se repita
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    return null;
}

/**
 * Verifica si existe un mensaje flash pendiente sin consumirlo.
 * 
 * @param string $key Clave del mensaje
 * @return bool True si existe
 */
function has_flash(string $key): bool {
    return !empty($_SESSION['flash'][$key]);
}
