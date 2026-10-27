<?php
/**
 * ============================================================================
 * VISTA: MIS SOLICITUDES DE RECOLECCIÓN (solicitudes.php — Fase 2)
 * ============================================================================
 * 
 * Muestra el historial y estado de todas las solicitudes registradas
 * por el ciudadano autenticado, conectadas en tiempo real con MySQL.
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg">

      <!-- Breadcrumbs -->
      <div style="font-size:0.8rem;color:var(--muted);margin-bottom:14px;display:flex;align-items:center;gap:6px;">
        <a href="<?= url('ciudadano') ?>" style="color:var(--green);text-decoration:none;">Panel</a>
        <span>›</span>
        <span>Mis Solicitudes</span>
      </div>

      <!-- Encabezado y Acción Principal -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
          <h1 style="font-size:1.5rem;font-weight:800;color:var(--text);margin:0 0 4px 0;">
            📋 Mis Solicitudes de Recolección
          </h1>
          <p style="font-size:0.86rem;color:var(--muted);margin:0;">
            Seguimiento en tiempo real de los materiales que has solicitado reciclar.
          </p>
        </div>
        <a href="<?= url('ciudadano/solicitud/crear') ?>" class="btn btn-p" style="padding:10px 18px;font-size:0.88rem;font-weight:700;">
          ➕ Nueva Solicitud
        </a>
      </div>

      <!-- Mensajes Flash de Éxito o Error -->
      <?php if (!empty($success)): ?>
        <div class="alert-success" style="margin-bottom:20px;">
          <?= h($success) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert-error" style="margin-bottom:20px;">
          <?= $error ?>
        </div>
      <?php endif; ?>

      <!-- Tarjetas de Métricas de Solicitudes -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;margin-bottom:26px;">
        <div class="card" style="padding:16px 20px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;">Total Solicitudes</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--text);margin-top:4px;">
            <?= (int)($metricas['total'] ?? 0) ?>
          </div>
        </div>

        <div class="card" style="padding:16px 20px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:#B45309;font-weight:600;text-transform:uppercase;">Pendientes</div>
          <div style="font-size:1.8rem;font-weight:800;color:#B45309;margin-top:4px;">
            <?= (int)($metricas['pendientes'] ?? 0) ?>
          </div>
        </div>

        <div class="card" style="padding:16px 20px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:#1D4ED8;font-weight:600;text-transform:uppercase;">En Proceso</div>
          <div style="font-size:1.8rem;font-weight:800;color:#1D4ED8;margin-top:4px;">
            <?= (int)($metricas['en_proceso'] ?? 0) ?>
          </div>
        </div>

        <div class="card" style="padding:16px 20px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:#065F46;font-weight:600;text-transform:uppercase;">Completadas</div>
          <div style="font-size:1.8rem;font-weight:800;color:#065F46;margin-top:4px;">
            <?= (int)($metricas['completadas'] ?? 0) ?>
          </div>
        </div>

        <div class="card" style="padding:16px 20px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:var(--green);font-weight:600;text-transform:uppercase;">Total Estimado</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--green);margin-top:4px;">
            <?= number_format((float)($metricas['total_kg_estimados'] ?? 0), 1, ',', '.') ?> <span style="font-size:1rem;font-weight:600;">kg</span>
          </div>
        </div>
      </div>

      <!-- LISTADO DE SOLICITUDES -->
      <?php if (empty($solicitudes)): ?>
        <!-- Estado Vacío Informativo -->
        <div class="card" style="padding:48px 24px;text-align:center;background:#fff;border-radius:var(--r-xl);border:2px dashed var(--border);">
          <div style="font-size:3.5rem;margin-bottom:12px;">📦</div>
          <h3 style="font-size:1.2rem;font-weight:700;color:var(--text);margin-bottom:8px;">
            Aún no tienes solicitudes registradas
          </h3>
          <p style="font-size:0.86rem;color:var(--muted);max-width:440px;margin:0 auto 20px auto;line-height:1.5;">
            Separa tus botellas, cartón o plásticos y solicita una recolección en tu casa para que los recicladores cercanos te atiendan.
          </p>
          <a href="<?= url('ciudadano/solicitud/crear') ?>" class="btn btn-p" style="padding:11px 24px;font-size:0.92rem;">
            ➕ Registrar mi primera solicitud
          </a>
        </div>
      <?php else: ?>
        <!-- Listado en Tarjetas Elegantes -->
        <div style="display:flex;flex-direction:column;gap:14px;">
          <?php foreach ($solicitudes as $sol): ?>
            <?php
              $estado = $sol['estado'] ?? 'Pendiente';
              $badgeBg = '#FEF3C7';
              $badgeColor = '#B45309';
              $badgeIcon = '⏳';

              if (in_array($estado, ['Aceptada', 'En proceso'])) {
                $badgeBg = '#DBEAFE';
                $badgeColor = '#1D4ED8';
                $badgeIcon = '🚲';
              } elseif (in_array($estado, ['Recolectada', 'Recogida', 'Entregada', 'Validada', 'Completada'])) {
                $badgeBg = '#D1FAE5';
                $badgeColor = '#065F46';
                $badgeIcon = '✅';
              } elseif ($estado === 'Cancelada') {
                $badgeBg = '#FEE2E2';
                $badgeColor = '#B91C1C';
                $badgeIcon = '❌';
              }

              $iconoMap = [
                'box' => '📦',
                'file-text' => '📄',
                'disc' => '🧴',
                'shield' => '🍾',
                'layers' => '🥫',
                'package' => '📦'
              ];
              $emojiMat = $iconoMap[$sol['material_icono'] ?? 'box'] ?? '♻️';
            ?>
            <div class="card" style="padding:20px;background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 4px rgba(0,0,0,0.03);display:flex;flex-direction:column;gap:12px;transition:all 0.15s ease;">
              
              <!-- Cabecera de la Tarjeta -->
              <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:12px;">
                  <span style="font-size:2rem;background:#F0FDF4;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid #DCFCE7;">
                    <?= $emojiMat ?>
                  </span>
                  <div>
                    <div style="font-size:1.05rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px;">
                      <span><?= h($sol['material_nombre']) ?></span>
                      <span style="font-size:0.75rem;font-weight:600;color:var(--muted);background:#F3F4F6;padding:2px 8px;border-radius:6px;">
                        #<?= str_pad((string)$sol['id_solicitud'], 5, '0', STR_PAD_LEFT) ?>
                      </span>
                    </div>
                    <div style="font-size:0.78rem;color:var(--muted);margin-top:2px;">
                      Registrada el <?= date('d/m/Y h:i A', strtotime($sol['fecha_solicitud'])) ?>
                    </div>
                  </div>
                </div>

                <!-- Insignia de Estado -->
                <div>
                  <span style="display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;font-weight:700;padding:6px 14px;border-radius:999px;background:<?= $badgeBg ?>;color:<?= $badgeColor ?>;">
                    <span><?= $badgeIcon ?></span>
                    <span><?= h($estado) ?></span>
                  </span>
                </div>
              </div>

              <hr style="border:none;border-top:1px solid #F3F4F6;margin:2px 0;">

              <!-- Datos de la Recolección -->
              <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;font-size:0.84rem;color:var(--text);">
                <div>
                  <span style="color:var(--muted);display:block;font-size:0.74rem;">CANTIDAD ESTIMADA</span>
                  <strong><?= number_format((float)$sol['cantidad_estimada'], 1, ',', '.') ?> kg</strong>
                  <span style="font-size:0.72rem;color:var(--muted);">(Aprox. <?= round($sol['cantidad_estimada'] * ($sol['puntos_por_kg'] ?? 10)) ?> pts potenciales)</span>
                </div>

                <div>
                  <span style="color:var(--muted);display:block;font-size:0.74rem;">FECHA DE DISPONIBILIDAD</span>
                  <strong>📅 <?= date('d/m/Y', strtotime($sol['fecha_disponibilidad'])) ?></strong>
                  <span style="font-size:0.75rem;color:var(--muted);display:block;">
                    ⏰ <?= date('h:i A', strtotime($sol['hora_inicio'])) ?> - <?= date('h:i A', strtotime($sol['hora_fin'])) ?>
                  </span>
                </div>

                <div>
                  <span style="color:var(--muted);display:block;font-size:0.74rem;">LOCALIDAD Y BARRIO</span>
                  <strong>📍 <?= h($sol['localidad_nombre']) ?></strong>
                  <span style="font-size:0.75rem;color:var(--muted);display:block;">
                    <?= h($sol['barrio'] ?? 'Sector residencial') ?>
                  </span>
                </div>

                <div>
                  <span style="color:var(--muted);display:block;font-size:0.74rem;">DIRECCIÓN PRIVADA</span>
                  <span style="font-size:0.82rem;font-weight:600;color:var(--text);">
                    🏠 <?= h($sol['direccion']) ?>
                  </span>
                </div>
              </div>

              <!-- Observaciones si existen -->
              <?php if (!empty($sol['observaciones'])): ?>
                <div style="background:#F9FAFB;border-radius:8px;padding:8px 12px;font-size:0.78rem;color:var(--muted);border-left:3px solid var(--green);">
                  <strong>Indicaciones:</strong> <?= h($sol['observaciones']) ?>
                </div>
              <?php endif; ?>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <?php require APP_PATH . 'views/layouts/footer.php'; ?>
