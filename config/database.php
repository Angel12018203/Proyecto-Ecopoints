<?php
// =====================================================================
// ECOPOINT — CONFIGURACIÓN Y CONEXIÓN PDO A BASE DE DATOS
// =====================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private static ?PDO $instance = null;

    // Configuración predeterminada para servidores locales (XAMPP, WAMP, Laragon)
    private static string $host = '127.0.0.1';
    private static string $dbname = 'ecopoint';
    private static string $user = 'root';
    private static string $pass = '';
    private static string $charset = 'utf8mb4';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=" . self::$charset;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                self::$instance = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                // Mensaje amigable con instrucciones si la BD aún no ha sido importada
                die("
                <div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:24px;border-radius:12px;background:#FFF0F0;border:1px solid #FFC9C9;color:#8F1D1D;'>
                    <h3 style='margin-top:0;'>⚠️ Error de Conexión a la Base de Datos</h3>
                    <p>No se pudo conectar a MySQL con la base de datos <strong>" . htmlspecialchars(self::$dbname) . "</strong> en <code>" . htmlspecialchars(self::$host) . "</code>.</p>
                    <p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                    <hr style='border:0;border-top:1px solid #FFC9C9;margin:15px 0;'>
                    <p><strong>Pasos para solucionar:</strong></p>
                    <ol style='line-height:1.6;'>
                        <li>Asegúrate de que MySQL esté activo en tu panel de control (XAMPP / Laragon / WAMP).</li>
                        <li>Abre phpMyAdmin (generalmente en <code>http://localhost/phpmyadmin</code>).</li>
                        <li>Crea la base de datos <code>ecopoint</code> o importa el archivo <code>database/ecopoint.sql</code>.</li>
                        <li>Si tu MySQL tiene contraseña para 'root', configúrala en <code>config/database.php</code>.</li>
                    </ol>
                </div>
                ");
            }
        }
        return self::$instance;
    }
}
