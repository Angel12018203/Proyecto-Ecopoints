<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL CENTRO DE ACOPIO (Datos 100% Reales de MySQL)
 * ============================================================================
 * 
 * Muestra el panel de control oficial para la recepción y pesaje de materiales
 * entregados por recicladores, con datos reales de la base de datos.
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';

$totalEntregas = (int)($metricas['total_entregas'] ?? 0);
$totalKg = (float)($metricas['total_kg'] ?? 0);
$entregasPendientes = (int)($metricas['entregas_pendientes'] ?? 0);
$entregasValidadas = (int)($metricas['entregas_validadas'] ?? 0);
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

      <!-- Banner del Centro -->
      <div style="background:linear-gradient(135deg,#065C2D,#087A3D,#2E9B50);border-radius:var(--r-xl);padding:26px 30px;margin-bottom:24px;color:#fff;">
        <div style="font-size:1.5rem;font-weight:800;margin-bottom:6px;">
          🏢 <?= h($perfil['nombre'] ?? 'Centro de Acopio Autorizado') ?>
        </div>
        <div style="font-size:0.92rem;opacity:0.92;max-width:580px;line-height:1.55;">
          Responsable: <strong><?= h($perfil['responsable'] ?? $user['nombre']) ?></strong> | Localidad: <strong><?= h($perfil['localidad_nombre'] ?? 'Bogotá') ?></strong>
        </div>
      </div>

      <!-- Métricas Reales en Base de Datos -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;margin-bottom:24px;">
        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;">Entregas Totales</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--text);margin-top:4px;">
            <?= number_format($totalEntregas, 0, ',', '.') ?>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">Histórico en sistema</div>
        </div>

        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:var(--green);font-weight:600;text-transform:uppercase;">Material Pesado y Validado</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--green);margin-top:4px;">
            <?= number_format($totalKg, 1, ',', '.') ?> <span style="font-size:0.95rem;">kg</span>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">Total verificado en báscula</div>
        </div>

        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:#065F46;font-weight:600;text-transform:uppercase;">Entregas Certificadas</div>
          <div style="font-size:1.8rem;font-weight:800;color:#065F46;margin-top:4px;">
            <?= number_format($entregasValidadas, 0, ',', '.') ?>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">Con Ecopoints asignados</div>
        </div>

        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:#B45309;font-weight:600;text-transform:uppercase;">Pendientes de Pesaje</div>
          <div style="font-size:1.8rem;font-weight:800;color:#B45309;margin-top:4px;">
            <?= $entregasPendientes ?>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">En recepción hoy</div>
        </div>
      </div>

      <div class="g2">
        <div class="card cp">
          <div style="font-size:1rem;font-weight:700;margin-bottom:8px;">Ficha del Establecimiento Comercial</div>
          <div style="font-size:0.84rem;line-height:2;">
            <div>📍 <strong>Dirección:</strong> <?= h($perfil['direccion'] ?? 'Sin registrar') ?></div>
            <div>📱 <strong>Teléfono de contacto:</strong> <?= h($perfil['telefono'] ?? $user['telefono']) ?></div>
            <div>🕐 <strong>Horario de atención:</strong> <?= h($perfil['hora_apertura'] ?? '08:00') ?> a <?= h($perfil['hora_cierre'] ?? '17:30') ?></div>
            <div>📦 <strong>Materiales recibidos:</strong> <?= h($perfil['materiales_recibidos'] ?? 'Cartón, Plástico, Vidrio, Metales') ?></div>
          </div>
        </div>

        <div class="card cp" style="display:flex;flex-direction:column;justify-content:center;padding:24px;">
          <div style="font-size:2rem;margin-bottom:6px;">⚖️</div>
          <div style="font-size:1.05rem;font-weight:700;margin-bottom:6px;">Punto de Pesaje y Control</div>
          <p style="font-size:0.82rem;color:var(--muted);line-height:1.5;margin:0 0 14px 0;">
            Cada entrega efectuada por un reciclador en este centro debe pesarse y clasificarse antes de emitir los Ecopoints correspondientes en la plataforma.
          </p>
          <div style="font-size:0.8rem;background:#EEF8EE;color:#087A3D;padding:10px 14px;border-radius:10px;font-weight:600;">
            ✅ Estado del centro: Activo y habilitado para recepción en <?= h($perfil['localidad_nombre'] ?? 'Bogotá') ?>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php require APP_PATH . 'views/layouts/footer.php'; ?>
