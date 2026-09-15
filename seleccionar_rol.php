<?php
// =====================================================================
// ECOPOINT — PASO 1: SELECCIÓN DE ROL DE REGISTRO
// =====================================================================

require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/auth/permissions.php';

redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecopoint — Selecciona tu tipo de usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-wrapper">

    <div class="auth-card auth-card-wide">
        <div class="auth-header">
            <a href="/" class="auth-logo-brand" style="text-decoration:none;">
                <span>🌿</span>
                <span class="eco">Eco</span><span class="pt">point</span>
            </a>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 6px;">¿Cómo quieres registrarte?</h2>
            <p class="auth-tagline">Selecciona tu perfil en la plataforma para personalizar tu experiencia de reciclaje</p>
        </div>

        <!-- Alertas Flash si existen -->
        <?php require_once __DIR__ . '/includes/alerts.php'; ?>

        <div class="role-cards-grid">
            <!-- Tarjeta 1: Reciclador -->
            <div class="role-choice-card reciclador">
                <div>
                    <div class="role-choice-icon">♻️</div>
                    <h3 class="role-choice-title">Soy reciclador</h3>
                    <p class="role-choice-desc">
                        Encuentra oportunidades de recolección, organiza tus recorridos, entrega materiales y recibe Ecopuntos.
                    </p>
                </div>
                <a href="/registro.php?rol=reciclador" class="btn btn-p btn-full" style="background: #087A3D;">
                    Registrarme como reciclador →
                </a>
            </div>

            <!-- Tarjeta 2: Ciudadano -->
            <div class="role-choice-card">
                <div>
                    <div class="role-choice-icon">👤</div>
                    <h3 class="role-choice-title">Soy ciudadano</h3>
                    <p class="role-choice-desc">
                        Solicita la recolección de tus materiales aprovechables y contribuye a la economía circular.
                    </p>
                </div>
                <a href="/registro.php?rol=ciudadano" class="btn btn-sec btn-full">
                    Registrarme como ciudadano →
                </a>
            </div>
        </div>

        <div style="text-align: center; border-top: 1px solid var(--border); padding-top: 20px; font-size: 0.86rem; color: var(--muted);">
            ¿Ya tienes una cuenta registrada? 
            <a href="/login.php" style="font-weight: 600; color: var(--green);">Iniciar sesión</a>
        </div>
    </div>

</body>
</html>
