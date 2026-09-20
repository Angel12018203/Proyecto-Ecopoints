<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL CIUDADANO
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA VISTA?
 * Muestra el panel inicial para los ciudadanos que solicitan recolección.
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg">

      <?php if ($success = flash('success')): ?>
        <div class="alert-success" style="margin-bottom:20px;">
          <?= h($success) ?>
        </div>
      <?php endif; ?>

      <div style="background:linear-gradient(135deg,#087A3D,#2E9B50);border-radius:var(--r-xl);padding:24px 28px;margin-bottom:24px;color:#fff;">
        <div style="font-size:1.45rem;font-weight:800;margin-bottom:6px;">
          ¡Bienvenido, <?= h($user['nombre']) ?>! 🏡
        </div>
        <div style="font-size:0.92rem;opacity:0.92;max-width:560px;line-height:1.55;">
          Gracias por separar tus residuos y apoyar a los recicladores de tu localidad en <strong><?= h($perfil['localidad_nombre'] ?? 'Bogotá') ?></strong>.
        </div>
      </div>

      <div class="g2">
        <div class="card cp">
          <div style="font-size:1rem;font-weight:700;margin-bottom:8px;">Tus datos de recolección</div>
          <p style="font-size:0.8rem;color:var(--muted);margin-bottom:12px;">Información registrada para las recolecciones en tu domicilio:</p>
          <div style="font-size:0.82rem;line-height:1.8;">
            <div>📍 <strong>Dirección:</strong> <?= h($perfil['direccion'] ?? 'Sin registrar') ?></div>
            <div>🏘️ <strong>Barrio:</strong> <?= h($perfil['barrio'] ?? 'Sin registrar') ?></div>
            <div>🗺️ <strong>Localidad:</strong> <?= h($perfil['localidad_nombre'] ?? 'Suba') ?></div>
            <div>📝 <strong>Preferencias:</strong> <?= h($perfil['preferencias'] ?? 'Material limpio y seco') ?></div>
          </div>
        </div>

        <div class="card cp" style="text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;">
          <div style="font-size:3rem;margin-bottom:10px;">📦</div>
          <div style="font-size:1.1rem;font-weight:700;margin-bottom:6px;">¿Tienes material listo?</div>
          <p style="font-size:0.8rem;color:var(--muted);max-width:320px;margin-bottom:16px;">
            El módulo de publicación de nuevas solicitudes estará disponible en la Fase 2 para que los recicladores cercanos puedan atenderte.
          </p>
          <button class="btn btn-p" onclick="alert('Módulo de creación de solicitudes en preparación para la Fase 2.');">
            ➕ Crear nueva solicitud
          </button>
        </div>
      </div>

    </div>
  </div>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
