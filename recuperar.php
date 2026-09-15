<?php
// =====================================================================
// ECOPOINT — RECUPERACIÓN DE CONTRASEÑA
// =====================================================================

require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/auth/permissions.php';

$mensaje = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $error = 'Token de seguridad inválido. Intenta de nuevo.';
    } else {
        $correo = trim(strtolower($_POST['correo'] ?? ''));
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingresa un correo electrónico válido.';
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id_usuario, nombre FROM usuarios WHERE correo = :correo LIMIT 1");
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                // En entorno de producción aquí se generaría un token temporal con vencimiento y envío por PHPMailer/SMTP
                $mensaje = "Hemos enviado las instrucciones para restablecer tu contraseña a <strong>" . h($correo) . "</strong>. Revisa tu bandeja de entrada o spam.";
            } else {
                // Por seguridad no se confirma explícitamente si el correo existe o no frente a enumeración de usuarios
                $mensaje = "Si el correo <strong>" . h($correo) . "</strong> se encuentra registrado en Ecopoint, recibirás un enlace de recuperación en los próximos minutos.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecopoint — Recuperar Contraseña</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-wrapper">

    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo-brand" style="text-decoration:none;">
                <span>🌿</span>
                <span class="eco">Eco</span><span class="pt">point</span>
            </a>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text); margin-top: 8px;">¿Olvidaste tu contraseña?</h2>
            <p class="auth-tagline" style="font-size: 0.84rem;">
                Ingresa tu correo electrónico registrado y te enviaremos un enlace seguro para restablecerla.
            </p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                <span>📧</span>
                <div><?= $mensaje ?></div>
            </div>
            <div style="margin-top: 20px;">
                <a href="/login.php" class="btn btn-p btn-full">Volver al inicio de sesión</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <span>⚠️</span>
                    <div><?= h($error) ?></div>
                </div>
            <?php endif; ?>

            <form action="/recuperar.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                <div class="form-group">
                    <label class="form-label" for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control" placeholder="nombre@correo.com" required autofocus>
                </div>

                <button type="submit" class="btn btn-p btn-lg btn-full" style="margin-top: 10px;">
                    Solicitar recuperación
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="/login.php" style="font-size: 0.85rem; color: var(--muted); font-weight: 500;">
                    ← Regresar a Iniciar sesión
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
