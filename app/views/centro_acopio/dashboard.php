<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL CENTRO DE ACOPIO
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA VISTA?
 * Muestra el panel de control oficial para el pesaje, recepción y validación
 * de materiales entregados por los recicladores.
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
          Centro de Acopio: <?= h($perfil['nombre'] ?? 'EcoCentro Oficial') ?> 🏢
        </div>
        <div style="font-size:0.92rem;opacity:0.92;max-width:560px;line-height:1.55;">
          Responsable: <strong><?= h($perfil['responsable'] ?? $user['nombre_completo']) ?></strong> | Localidad: <strong><?= h($perfil['localidad_nombre'] ?? 'Bogotá') ?></strong>
        </div>
      </div>

      <div class="g2">
        <div class="card cp">
          <div style="font-size:1rem;font-weight:700;margin-bottom:8px;">Ficha del Establecimiento</div>
          <div style="font-size:0.82rem;line-height:1.8;">
            <div>📍 <strong>Dirección:</strong> <?= h($perfil['direccion'] ?? 'Sin registrar') ?></div>
            <div>📱 <strong>Teléfono:</strong> <?= h($perfil['telefono'] ?? $user['telefono']) ?></div>
            <div>🕐 <strong>Horario:</strong> <?= h($perfil['hora_apertura'] ?? '08:00') ?> a <?= h($perfil['hora_cierre'] ?? '17:30') ?></div>
            <div>📦 <strong>Materiales autorizados:</strong> <?= h($perfil['materiales_recibidos'] ?? 'Cartón, Plástico, Vidrio, Metales') ?></div>
          </div>
        </div>

        <div class="card cp" style="text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;">
          <div style="font-size:3rem;margin-bottom:10px;">⚖️</div>
          <div style="font-size:1.1rem;font-weight:700;margin-bottom:6px;">Recepción y Pesaje Oficial</div>
          <p style="font-size:0.8rem;color:var(--muted);max-width:320px;margin-bottom:16px;">
            El flujo oficial de registro de entregas, pesaje en báscula y emisión de Ecopoints se implementará en la fase de validación.
          </p>
          <button class="btn btn-p" onclick="alert('Módulo de validación de entregas en preparación para la Fase 6.');">
            ⚖️ Iniciar Pesaje y Validación
          </button>
        </div>
      </div>

    </div>
  </div>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
