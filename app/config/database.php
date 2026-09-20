<?php
/**
 * ============================================================================
 * GESTOR DE CONEXIÓN A LA BASE DE DATOS (PDO SINGLETON)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE ARCHIVO?
 * Establece y administra la conexión con MySQL/MariaDB mediante la extensión
 * nativa PDO (PHP Data Objects).
 * 
 * ¿POR QUÉ SE DISEÑÓ CON EL PATRÓN SINGLETON?
 * En una aplicación web pueden ejecutarse múltiples consultas en una sola
 * petición HTTP (ej: validar sesión, consultar usuario, listar localidades).
 * Si abriéramos una conexión nueva en cada consulta, saturaríamos los recursos
 * de MySQL en XAMPP. El patrón Singleton garantiza que exista UNA SOLA
 * instancia de conexión abierta durante todo el ciclo de vida de la petición.
 * 
 * MEDIDAS DE SEGURIDAD IMPLEMENTADAS:
 * 1. PDO::ERRMODE_EXCEPTION: Convierte errores de SQL en excepciones PHP para
 *    atraparlos limpiamente sin exponer datos sensibles ni credenciales.
 * 2. PDO::ATTR_DEFAULT_FETCH_MODE: Devuelve arreglos asociativos por defecto
 *    (ej: $fila['correo']), haciendo el código más legible y estándar.
 * 3. PDO::ATTR_EMULATE_PREPARES => false: Obliga a MySQL a usar sentencias
 *    preparadas reales a nivel de motor de base de datos, lo cual previene
 *    completamente cualquier intento de inyección SQL.
 */

require_once __DIR__ . '/config.php';

class Database {
    // Variable estática que guardará la única instancia de la conexión PDO
    private static $instance = null;

    /**
     * El constructor se declara privado para evitar que alguien cree
     * nuevas instancias con 'new Database()' fuera de esta clase.
     */
    private function __construct() {}

    /**
     * Clonación privada para evitar duplicación de la conexión
     */
    private function __clone() {}

    /**
     * Obtiene la conexión PDO activa o crea una nueva si aún no existe.
     * 
     * @return PDO Objeto de conexión listo para preparar consultas
     * @throws PDOException Si las credenciales o el servicio MySQL fallan
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // Data Source Name: define motor, host, base de datos y juego de caracteres
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                // Opciones avanzadas de seguridad y rendimiento para PDO
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Arreglos asociativos
                    PDO::ATTR_EMULATE_PREPARES   => false,                  // Consultas preparadas reales nativas
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                // Instanciación de la conexión PDO
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);

            } catch (PDOException $e) {
                // Registro del error y mensaje amigable en caso de falla
                error_log("Error de conexión PDO en EcoPoints: " . $e->getMessage());
                
                // Si estamos en entorno de pruebas/desarrollo local, mostramos orientación clara
                die(
                    "<div style='font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border:2px solid #E53E3E;border-radius:12px;background:#FFF5F5;'>" .
                    "<h2 style='color:#E53E3E;margin-top:0;'>⚠️ Error de conexión con MySQL en XAMPP</h2>" .
                    "<p>No fue posible conectar con la base de datos <strong>'" . htmlspecialchars(DB_NAME) . "'</strong>.</p>" .
                    "<ul style='color:#555;'>" .
                    "<li>Verifica que el módulo <strong>MySQL</strong> esté iniciado en el panel de control de XAMPP (botón 'Start' verde).</li>" .
                    "<li>Verifica que hayas importado el archivo <code>database/ecopoint.sql</code> en phpMyAdmin.</li>" .
                    "<li>Detalle técnico: " . htmlspecialchars($e->getMessage()) . "</li>" .
                    "</ul>" .
                    "</div>"
                );
            }
        }

        return self::$instance;
    }
}
