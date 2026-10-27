<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL CIUDADANO (Fase 2 Integrada)
 * ============================================================================
 * 
 * Muestra el panel principal del ciudadano con estadísticas reales de sus
 * solicitudes, acceso directo al registro de recolecciones y estado de su cuenta.
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';

$totalSolicitudes = (int)($metricas['total'] ?? 0);
$pendientes = (int)($metricas['pendientes'] ?? 0);
$completadas = (int)($metricas['completadas'] ?? 0);
$kgEstimados = (float)($metricas['total_kg_estimados'] ?? 0);
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

      <!-- Banner de Bienvenida -->
      <div style="background:linear-gradient(135deg,#065C2D,#087A3D,#2E9B50);border-radius:var(--r-xl);padding:26px 30px;margin-bottom:24px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
          <div style="font-size:1.5rem;font-weight:800;margin-bottom:6px;">
            ¡Bienvenido, <?= h($user['nombre']) ?>! 🏡
          </div>
          <div style="font-size:0.92rem;opacity:0.92;max-width:560px;line-height:1.55;">
            Gracias por separar tus residuos y apoyar a los recicladores de tu localidad en <strong><?= h($perfil['localidad_nombre'] ?? 'Bogotá') ?></strong>.
          </div>
        </div>
        <a href="<?= url('ciudadano/solicitud/crear') ?>" class="btn" style="background:#fff;color:#087A3D;font-weight:700;padding:12px 22px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.12);text-decoration:none;">
          ➕ Registrar Solicitud
        </a>
      </div>

      <!-- Tarjetas de Resumen de Impacto y Solicitudes -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;margin-bottom:24px;">
        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;">Solicitudes Registradas</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--text);margin-top:4px;">
            <?= $totalSolicitudes ?>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">En tu historial</div>
        </div>

        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:#B45309;font-weight:600;text-transform:uppercase;">Pendientes de Recogida</div>
          <div style="font-size:1.8rem;font-weight:800;color:#B45309;margin-top:4px;">
            <?= $pendientes ?>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">Por asignar a reciclador</div>
        </div>

        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:#065F46;font-weight:600;text-transform:uppercase;">Recolecciones Completadas</div>
          <div style="font-size:1.8rem;font-weight:800;color:#065F46;margin-top:4px;">
            <?= $completadas ?>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">Entregadas con éxito</div>
        </div>

        <div class="card" style="padding:18px;background:#fff;border-radius:14px;border:1px solid var(--border);">
          <div style="font-size:0.75rem;color:var(--green);font-weight:600;text-transform:uppercase;">Material Entregado</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--green);margin-top:4px;">
            <?= number_format($kgEstimados, 1, ',', '.') ?> <span style="font-size:0.95rem;">kg</span>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">Estimación total</div>
        </div>
      </div>

      <!-- Cuadrícula 2 Columnas: Datos y Acciones -->
      <div class="g2" style="margin-bottom:24px;">
        
        <!-- Tarjeta de Datos de Recolección -->
        <div class="card cp">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="font-size:1.05rem;font-weight:700;">📍 Tus datos de recolección</div>
            <span style="font-size:0.75rem;color:var(--green);font-weight:600;background:#EEF8EE;padding:3px 8px;border-radius:6px;">Perfil verificado</span>
          </div>
          <p style="font-size:0.8rem;color:var(--muted);margin-bottom:14px;">
            Ubicación predeterminada para las visitas de los recicladores autorizados:
          </p>
          <div style="font-size:0.84rem;line-height:2.1;">
            <div>🏠 <strong>Dirección:</strong> <?= h($perfil['direccion'] ?? 'Sin registrar') ?></div>
            <div>🏘️ <strong>Barrio:</strong> <?= h($perfil['barrio'] ?? 'Sin registrar') ?></div>
            <div>🗺️ <strong>Localidad:</strong> <?= h($perfil['localidad_nombre'] ?? 'Bogotá') ?> (#<?= h($perfil['localidad_numero'] ?? '') ?>)</div>
            <div>📝 <strong>Preferencias:</strong> <?= h($perfil['preferencias'] ?? 'Material limpio y seco') ?></div>
          </div>
        </div>

        <!-- Tarjeta de Llamado a la Acción -->
        <div class="card cp" style="text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:28px 24px;">
          <div style="font-size:3.2rem;margin-bottom:10px;">📦</div>
          <div style="font-size:1.15rem;font-weight:700;margin-bottom:6px;color:var(--text);">¿Tienes material reciclable listo?</div>
          <p style="font-size:0.82rem;color:var(--muted);max-width:340px;margin-bottom:20px;line-height:1.45;">
            Registra una solicitud indicando qué tipo de residuo tienes y cuándo pueden pasar a recogerlo a tu domicilio.
          </p>
          <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
            <a href="<?= url('ciudadano/solicitud/crear') ?>" class="btn btn-p" style="padding:10px 20px;font-size:0.88rem;font-weight:700;">
              ➕ Crear nueva solicitud
            </a>
            <a href="<?= url('ciudadano/solicitudes') ?>" class="btn btn-outline" style="padding:10px 18px;font-size:0.88rem;">
              📋 Ver mis solicitudes
            </a>
          </div>
        </div>

      </div>

      <!-- Resumen de Solicitudes Recientes (Si existen) -->
      <?php if (!empty($solicitudes)): ?>
        <div class="card" style="padding:22px;background:#fff;border-radius:16px;border:1px solid var(--border);">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="font-size:1rem;font-weight:700;margin:0;color:var(--text);">
              🕒 Últimas solicitudes registradas
            </h3>
            <a href="<?= url('ciudadano/solicitudes') ?>" style="font-size:0.8rem;color:var(--green);font-weight:600;text-decoration:none;">
              Ver todas (<?= count($solicitudes) ?>) →
            </a>
          </div>

          <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.83rem;">
              <thead>
                <tr style="border-bottom:2px solid #F3F4F6;text-align:left;color:var(--muted);">
                  <th style="padding:8px 10px;">ID</th>
                  <th style="padding:8px 10px;">Material</th>
                  <th style="padding:8px 10px;">Cantidad</th>
                  <th style="padding:8px 10px;">Fecha disp.</th>
                  <th style="padding:8px 10px;">Localidad</th>
                  <th style="padding:8px 10px;">Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice($solicitudes, 0, 5) as $s): ?>
                  <?php
                    $st = $s['estado'] ?? 'Pendiente';
                    $bg = '#FEF3C7';
                    $tc = '#B45309';
                    if (in_array($st, ['Aceptada', 'En proceso'])) { $bg = '#DBEAFE'; $tc = '#1D4ED8'; }
                    elseif (in_array($st, ['Recolectada', 'Validada', 'Completada'])) { $bg = '#D1FAE5'; $tc = '#065F46'; }
                  ?>
                  <tr style="border-bottom:1px solid #F9FAFB;">
                    <td style="padding:10px;font-weight:600;color:var(--muted);">
                      #<?= str_pad((string)$s['id_solicitud'], 5, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td style="padding:10px;font-weight:700;">
                      <?= h($s['material_nombre']) ?>
                    </td>
                    <td style="padding:10px;">
                      <?= number_format((float)$s['cantidad_estimada'], 1, ',', '.') ?> kg
                    </td>
                    <td style="padding:10px;">
                      <?= date('d/m/Y', strtotime($s['fecha_disponibilidad'])) ?>
                    </td>
                    <td style="padding:10px;">
                      <?= h($s['localidad_nombre']) ?>
                    </td>
                    <td style="padding:10px;">
                      <span style="font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:999px;background:<?= $bg ?>;color:<?= $tc ?>;">
                        <?= h($st) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <?php require APP_PATH . 'views/layouts/footer.php'; ?>
