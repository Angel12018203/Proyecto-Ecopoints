<?php
/**
 * ============================================================================
 * MIDDLEWARE DE AUTENTICACIÓN Y AUTORIZACIÓN
 * ============================================================================
 * 
 * ¿QUÉ HACE UN MIDDLEWARE?
 * Actúa como una capa intermedia (un "portero") entre la petición entrante
 * del navegador y el controlador que procesa la respuesta.
 * 
 * Si el usuario no cumple las condiciones de seguridad requeridas
 * (por ejemplo, estar logueado o tener el rol de reciclador), el middleware
 * intercepta la solicitud y lo desvía antes de que se ejecute la lógica de negocio.
 */

require_once __DIR__ . '/../helpers/auth_helper.php';

class AuthMiddleware {
    
    /**
     * Interceptor para rutas que requieren inicio de sesión obligatorio.
     */
    public static function auth(): void {
        requireLogin();
    }

    /**
     * Interceptor para rutas exclusivas de recicladores.
     */
    public static function recicladorOnly(): void {
        requireRole('reciclador');
    }

    /**
     * Interceptor para rutas exclusivas de ciudadanos.
     */
    public static function ciudadanoOnly(): void {
        requireRole('ciudadano');
    }

    /**
     * Interceptor para rutas exclusivas de centros de acopio.
     */
    public static function centroAcopioOnly(): void {
        requireRole('centro_acopio');
    }

    /**
     * Interceptor para rutas accesibles solo por invitados (usuarios NO autenticados),
     * como por ejemplo el Login o el Registro. Si ya están autenticados, se les
     * redirige directamente a su panel.
     */
    public static function guestOnly(): void {
        if (isAuthenticated()) {
            $rol = currentRole();
            $route = 'reciclador';
            if ($rol === 'ciudadano') {
                $route = 'ciudadano';
            } elseif ($rol === 'centro_acopio') {
                $route = 'centro-acopio';
            }
            header('Location: ' . url($route));
            exit;
        }
    }
}
