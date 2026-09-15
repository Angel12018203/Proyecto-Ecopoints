<?php
// =====================================================================
// ECOPOINT — INICIO DE SESIÓN
// =====================================================================

require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/auth/permissions.php';

redirectIfLoggedIn();

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $error = 'Error de seguridad en el formulario (CSRF). Intenta nuevamente.';
    } else {
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($correo) || empty($password)) {
            $error = 'Por favor completa todos los campos.';
        } else {
            $res = loginUser($correo, $password);
            if ($res['success']) {
                setFlash('success', '¡Bienvenido(a) de nuevo, ' . $_SESSION['nombre'] . '!');
                if ($res['rol'] === 'reciclador') {
                    header('Location: /reciclador/dashboard.php');
                } elseif ($res['rol'] === 'ciudadano') {
                    header('Location: /ciudadano/dashboard.php');
                } else {
                    header('Location: /index.php');
                }
                exit;
            } else {
                $error = $res['message'];
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
    <title>Ecopoint — Iniciar Sesión</title>
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
            <p class="auth-tagline" style="font-weight: 500; color: var(--green-dark); font-size: 0.92rem; margin-top: 6px;">
                Tu trabajo transforma residuos en oportunidades.
            </p>
        </div>

        <!-- Alertas Flash del Sistema -->
        <?php require_once __DIR__ . '/includes/alerts.php'; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span>
                <div><?= h($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="/login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" placeholder="tu@correo.com" value="<?= h($correo) ?>" required autofocus>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" for="password" style="margin-bottom: 0;">Contraseña</label>
                    <a href="/recuperar.php" style="font-size: 0.78rem; color: var(--muted);">¿Olvidaste tu contraseña?</a>
                </div>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-p btn-lg btn-full" style="margin-top: 10px;">
                Iniciar sesión
            </button>
        </form>

        <div style="text-align: center; margin-top: 26px; padding-top: 20px; border-top: 1px solid var(--border);">
            <div style="font-size: 0.88rem; color: var(--muted); margin-bottom: 12px;">¿Aún no tienes una cuenta?</div>
            <a href="/seleccionar_rol.php" class="btn btn-sec btn-full">
                Crear cuenta
            </a>
        </div>

        <!-- Cajas de ayuda rápida para pruebas locales -->
        <div style="margin-top: 24px; padding: 14px; background: #F7FAF8; border-radius: var(--r); border: 1px dashed #CDE0D4; font-size: 0.78rem; color: var(--muted);">
            <div style="font-weight: 700; color: var(--green); margin-bottom: 4px;">👥 Cuentas de demostración:</div>
            <div><strong>♻️ Reciclador:</strong> <code>reciclador@ecopoint.app</code> / <code>Carlos123*</code></div>
            <div><strong>👤 Ciudadano:</strong> <code>ciudadano@ecopoint.app</code> / <code>Maria123*</code></div>
        </div>
    </div>

</body>
</html>
