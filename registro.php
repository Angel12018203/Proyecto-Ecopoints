<?php
// =====================================================================
// ECOPOINT — PASO 2: FORMULARIO DE REGISTRO DIFERENCIADO POR ROL
// =====================================================================

require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/auth/permissions.php';

redirectIfLoggedIn();

$rol = $_GET['rol'] ?? 'ciudadano';
if (!in_array($rol, ['reciclador', 'ciudadano'], true)) {
    header('Location: /seleccionar_rol.php');
    exit;
}

$errores = [];
$datos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errores[] = 'Error de validación de seguridad (CSRF). Intenta nuevamente.';
    } else {
        $datos = [
            'nombre'           => trim($_POST['nombre'] ?? ''),
            'apellido'         => trim($_POST['apellido'] ?? ''),
            'correo'           => trim($_POST['correo'] ?? ''),
            'telefono'         => trim($_POST['telefono'] ?? ''),
            'password'         => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'acepta_terminos'  => !empty($_POST['acepta_terminos']),
            // Campos específicos de reciclador
            'zona_trabajo'     => trim($_POST['zona_trabajo'] ?? 'Suba'),
            'medio_transporte' => trim($_POST['medio_transporte'] ?? ''),
            'disponibilidad'   => trim($_POST['disponibilidad'] ?? ''),
            // Campos específicos de ciudadano
            'direccion'        => trim($_POST['direccion'] ?? ''),
            'localidad'        => trim($_POST['localidad'] ?? 'Suba'),
            'barrio'           => trim($_POST['barrio'] ?? ''),
            'preferencias'     => trim($_POST['preferencias'] ?? '')
        ];

        // Validaciones obligatorias generales (Sección 30)
        if (empty($datos['nombre'])) $errores[] = 'El nombre es obligatorio.';
        if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
        if (empty($datos['correo']) || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electrónico válido.';
        }
        if (empty($datos['telefono'])) $errores[] = 'El número de teléfono es obligatorio.';
        if (strlen($datos['password']) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        if ($datos['password'] !== $datos['password_confirm']) $errores[] = 'Las contraseñas no coinciden.';
        if (!$datos['acepta_terminos']) $errores[] = 'Debes aceptar el tratamiento de datos personales.';

        // Validaciones por rol
        if ($rol === 'reciclador') {
            if (empty($datos['zona_trabajo'])) $errores[] = 'Debes indicar tu zona habitual de trabajo.';
            if (empty($datos['medio_transporte'])) $errores[] = 'Debes indicar tu medio de transporte de recolección.';
        } else {
            if (empty($datos['direccion'])) $errores[] = 'La dirección de residencia es obligatoria.';
            if (empty($datos['barrio'])) $errores[] = 'El barrio es obligatorio.';
        }

        if (empty($errores)) {
            $res = registerUser($datos, $rol);
            if ($res['success']) {
                $mensajeExito = ($rol === 'reciclador') 
                    ? '¡Cuenta creada correctamente! Ahora puedes iniciar sesión en Ecopoint como reciclador.'
                    : '¡Tu cuenta Ecopoint fue creada correctamente! Inicia sesión para solicitar tu primera recolección.';
                
                setFlash('success', $mensajeExito);
                header('Location: /login.php');
                exit;
            } else {
                $errores[] = $res['message'];
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
    <title>Ecopoint — Registro de <?= $rol === 'reciclador' ? 'Reciclador' : 'Ciudadano' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-wrapper">

    <div class="auth-card" style="max-width: 620px;">
        <div class="auth-header">
            <a href="/" class="auth-logo-brand" style="text-decoration:none;">
                <span>🌿</span>
                <span class="eco">Eco</span><span class="pt">point</span>
            </a>
            <div style="margin-top: 4px;">
                <span class="bdg <?= $rol === 'reciclador' ? 'bdg-proc' : 'bdg-disp' ?>" style="font-size: 0.8rem; padding: 4px 12px;">
                    <?= $rol === 'reciclador' ? '♻️ Registro de Reciclador' : '👤 Registro de Ciudadano' ?>
                </span>
            </div>
            <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--text); margin-top: 12px;">
                <?= $rol === 'reciclador' ? 'Crear cuenta de reciclador' : 'Crear cuenta de ciudadano' ?>
            </h2>
            <p class="auth-tagline" style="font-size: 0.82rem;">
                <?= $rol === 'reciclador' 
                    ? 'Únete a la red formal de recicladores urbanos y gana Ecopuntos por tus entregas validadas.' 
                    : 'Solicita recolecciones puerta a puerta de tus residuos aprovechables y cuida el planeta.' ?>
            </p>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger" style="display: block;">
                <strong>⚠️ Por favor corrige los siguientes errores:</strong>
                <ul style="margin: 8px 0 0 18px; padding: 0;">
                    <?php foreach ($errores as $err): ?>
                        <li><?= h($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/registro.php?rol=<?= urlencode($rol) ?>" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

            <!-- Datos Personales Básicos -->
            <div class="g2">
                <div class="form-group">
                    <label class="form-label">Nombre <span style="color:red;">*</span></label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Carlos" value="<?= h($datos['nombre'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido <span style="color:red;">*</span></label>
                    <input type="text" name="apellido" class="form-control" placeholder="Ej. Rodríguez" value="<?= h($datos['apellido'] ?? '') ?>" required>
                </div>
            </div>

            <div class="g2">
                <div class="form-group">
                    <label class="form-label">Correo Electrónico <span style="color:red;">*</span></label>
                    <input type="email" name="correo" class="form-control" placeholder="nombre@correo.com" value="<?= h($datos['correo'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono / WhatsApp <span style="color:red;">*</span></label>
                    <input type="tel" name="telefono" class="form-control" placeholder="Ej. 3115554321" value="<?= h($datos['telefono'] ?? '') ?>" required>
                </div>
            </div>

            <div class="g2">
                <div class="form-group">
                    <label class="form-label">Contraseña <span style="color:red;">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña <span style="color:red;">*</span></label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="Repite la contraseña" required>
                </div>
            </div>

            <!-- Campos Exclusivos de Reciclador -->
            <?php if ($rol === 'reciclador'): ?>
                <div style="background: #F9FCFA; border: 1px solid var(--border); border-radius: var(--r); padding: 16px; margin-bottom: 18px;">
                    <div style="font-weight: 700; color: var(--green); font-size: 0.85rem; margin-bottom: 12px;">
                        ♻️ Perfil Operativo del Reciclador
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Zona habitual de trabajo <span style="color:red;">*</span></label>
                        <select name="zona_trabajo" class="form-select" required>
                            <option value="Suba" <?= ($datos['zona_trabajo'] ?? '') === 'Suba' ? 'selected' : '' ?>>Suba</option>
                            <option value="Engativá" <?= ($datos['zona_trabajo'] ?? '') === 'Engativá' ? 'selected' : '' ?>>Engativá</option>
                            <option value="Usaquén" <?= ($datos['zona_trabajo'] ?? '') === 'Usaquén' ? 'selected' : '' ?>>Usaquén</option>
                            <option value="Chapinero" <?= ($datos['zona_trabajo'] ?? '') === 'Chapinero' ? 'selected' : '' ?>>Chapinero</option>
                            <option value="Kennedy" <?= ($datos['zona_trabajo'] ?? '') === 'Kennedy' ? 'selected' : '' ?>>Kennedy</option>
                            <option value="Fontibón" <?= ($datos['zona_trabajo'] ?? '') === 'Fontibón' ? 'selected' : '' ?>>Fontibón</option>
                        </select>
                    </div>

                    <div class="g2">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Medio de transporte <span style="color:red;">*</span></label>
                            <select name="medio_transporte" class="form-select" required>
                                <option value="Carreta de reciclaje manual">Carreta de reciclaje manual</option>
                                <option value="Bicitriciclo de carga">Bicitriciclo de carga</option>
                                <option value="Motocarro / Vehículo ligero">Motocarro / Vehículo ligero</option>
                                <option value="A pie con costal">A pie con costal</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Disponibilidad horaria</label>
                            <input type="text" name="disponibilidad" class="form-control" placeholder="Ej. Lunes a Sábado 7am - 4pm" value="<?= h($datos['disponibilidad'] ?? 'Lunes a Sábado 7am - 4pm') ?>">
                        </div>
                    </div>
                </div>

            <!-- Campos Exclusivos de Ciudadano -->
            <?php else: ?>
                <div style="background: #F9FCFA; border: 1px solid var(--border); border-radius: var(--r); padding: 16px; margin-bottom: 18px;">
                    <div style="font-weight: 700; color: var(--green); font-size: 0.85rem; margin-bottom: 12px;">
                        📍 Ubicación de Recolección del Ciudadano
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dirección exacta <span style="color:red;">*</span></label>
                        <input type="text" name="direccion" class="form-control" placeholder="Ej. Calle 145 # 92-30 Torre 2 Apto 402" value="<?= h($datos['direccion'] ?? '') ?>" required>
                    </div>

                    <div class="g2">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Localidad <span style="color:red;">*</span></label>
                            <select name="localidad" class="form-select" required>
                                <option value="Suba" <?= ($datos['localidad'] ?? '') === 'Suba' ? 'selected' : '' ?>>Suba</option>
                                <option value="Engativá" <?= ($datos['localidad'] ?? '') === 'Engativá' ? 'selected' : '' ?>>Engativá</option>
                                <option value="Usaquén" <?= ($datos['localidad'] ?? '') === 'Usaquén' ? 'selected' : '' ?>>Usaquén</option>
                                <option value="Chapinero" <?= ($datos['localidad'] ?? '') === 'Chapinero' ? 'selected' : '' ?>>Chapinero</option>
                                <option value="Kennedy" <?= ($datos['localidad'] ?? '') === 'Kennedy' ? 'selected' : '' ?>>Kennedy</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Barrio <span style="color:red;">*</span></label>
                            <input type="text" name="barrio" class="form-control" placeholder="Ej. Suba Centro" value="<?= h($datos['barrio'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Aceptación de Tratamiento de Datos -->
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="acepta_terminos" value="1" <?= !empty($datos['acepta_terminos']) ? 'checked' : '' ?> required>
                    <span>Acepto el <a href="#" onclick="alert('Tratamiento de datos personales conforme a la Ley 1581 de 2012 de Colombia.'); return false;" style="text-decoration: underline;">tratamiento de mis datos personales</a> y las políticas de la plataforma Ecopoint.</span>
                </label>
            </div>

            <button type="submit" class="btn btn-p btn-lg btn-full" style="margin-top: 10px;">
                Crear cuenta
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 0.85rem; color: var(--muted);">
            ¿Deseas cambiar el tipo de usuario? 
            <a href="/seleccionar_rol.php" style="color: var(--green); font-weight: 600;">Elegir otro rol</a>
            <span style="margin: 0 8px;">•</span>
            <a href="/login.php" style="color: var(--green); font-weight: 600;">Iniciar sesión</a>
        </div>
    </div>

</body>
</html>
