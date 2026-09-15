<?php
// =====================================================================
// ECOPOINT — PÁGINA PRINCIPAL / LANDING
// =====================================================================

require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/auth/permissions.php';

// Si ya tiene sesión, redirigir a su panel correspondiente
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecopoint — Plataforma de Reciclaje y Economía Circular</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .hero-banner {
            text-align: center;
            padding: 80px 20px 60px;
            max-width: 860px;
            margin: 0 auto;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--green-light);
            color: var(--green);
            padding: 6px 16px;
            border-radius: var(--r-full);
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 20px;
            border: 1px solid #CBEACF;
        }
        .hero-title {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--text);
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }
        .hero-desc {
            font-size: 1.15rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 34px;
        }
        .flow-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 980px;
            margin: 40px auto;
            text-align: left;
        }
        @media (max-width: 768px) {
            .flow-cards { grid-template-columns: 1fr; }
            .hero-title { font-size: 2.1rem; }
        }
    </style>
</head>
<body style="background: radial-gradient(circle at 50% 0%, #EEF8EE 0%, #FAFCFA 75%);">

    <!-- Header Público -->
    <header style="padding: 20px 40px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.85); backdrop-filter: blur(8px); position: sticky; top: 0; z-index: 100;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: var(--green); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">🌿</div>
            <div style="font-size: 1.4rem; font-weight: 800;"><span style="color: var(--green);">Eco</span><span>point</span></div>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="/login.php" class="btn btn-o">Iniciar Sesión</a>
            <a href="/seleccionar_rol.php" class="btn btn-p">Crear Cuenta</a>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main>
        <div class="hero-banner">
            <div class="hero-badge">🌱 Economía Circular y Reciclaje Inclusivo</div>
            <h1 class="hero-title">Tu trabajo transforma residuos en oportunidades</h1>
            <p class="hero-desc">Conectamos a ciudadanos conscientes con recicladores de oficio y centros de acopio autorizados para garantizar la trazabilidad real del material y recompensas justas con Ecopuntos.</p>
            
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="/seleccionar_rol.php" class="btn btn-p btn-lg">Comenzar Ahora — Registrarme</a>
                <a href="/login.php" class="btn btn-o btn-lg">Acceder a mi cuenta</a>
            </div>
        </div>

        <!-- Diagrama del Flujo Trilateral -->
        <div class="flow-cards">
            <div class="card cp" style="border-top: 4px solid var(--green);">
                <div style="font-size: 2.2rem; margin-bottom: 12px;">👤</div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">1. Ciudadanos</h3>
                <p style="color: var(--muted); font-size: 0.86rem; line-height: 1.5;">Separan sus materiales aprovechables (cartón, plástico, metal, papel) y publican solicitudes de recolección en su barrio.</p>
            </div>

            <div class="card cp" style="border-top: 4px solid var(--gold);">
                <div style="font-size: 2.2rem; margin-bottom: 12px;">♻️</div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">2. Recicladores</h3>
                <p style="color: var(--muted); font-size: 0.86rem; line-height: 1.5;">Consultan oportunidades cercanas, organizan sus rutas diarias, recogen el material y lo trasladan con seguridad.</p>
            </div>

            <div class="card cp" style="border-top: 4px solid #1E6091;">
                <div style="font-size: 2.2rem; margin-bottom: 12px;">⚖️</div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">3. Centros de Acopio</h3>
                <p style="color: var(--muted); font-size: 0.86rem; line-height: 1.5;">Reciben, pesan y validan oficialmente el material. <strong>Solo tras la validación certificada se emiten los Ecopuntos</strong>.</p>
            </div>
        </div>

        <!-- Cuentas de Prueba Destacadas -->
        <div style="max-width: 600px; margin: 50px auto; text-align: center; padding: 20px; background: #FFF; border: 1px dashed var(--border); border-radius: var(--r-lg);">
            <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 8px;">🔑 Accesos Rápidos de Prueba (Entorno Local)</div>
            <div style="font-size: 0.84rem; color: var(--muted); line-height: 1.6;">
                <strong>Reciclador:</strong> <code>reciclador@ecopoint.app</code> / Contraseña: <code>Carlos123*</code><br>
                <strong>Ciudadano:</strong> <code>ciudadano@ecopoint.app</code> / Contraseña: <code>Maria123*</code>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 24px; color: var(--muted); font-size: 0.8rem; border-top: 1px solid var(--border); background: #FFF;">
        Ecopoint © <?= date('Y') ?> — Plataforma integral de gestión de reciclaje y recompensas sostenibles.
    </footer>
</body>
</html>
