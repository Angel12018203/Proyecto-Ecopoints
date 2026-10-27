<?php
/**
 * VISTA: SOLICITUDES DE RECOLECCIÓN CERCANAS (ROL RECICLADOR)
 * Basado fielmente en el diseño de Figma (Imagen 1)
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg pg-wide">

      <!-- Notificaciones Flash -->
      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success" style="margin-bottom:20px;display:flex;align-items:center;gap:10px;">
          <span style="font-size:1.2rem;">✅</span>
          <div><?= h($msg) ?></div>
        </div>
      <?php endif; ?>
      <?php if ($err = flash('error')): ?>
        <div class="alert alert-error" style="margin-bottom:20px;display:flex;align-items:center;gap:10px;">
          <span style="font-size:1.2rem;">⚠️</span>
          <div><?= h($err) ?></div>
        </div>
      <?php endif; ?>

      <!-- Encabezado de la Sección -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.55rem;font-weight:800;color:var(--g-800);margin:0 0 6px;">
            Solicitudes de recolección
          </h1>
          <p style="margin:0;font-size:0.9rem;color:var(--g-500);">
            Encuentra materiales disponibles en tu zona y coordina la recogida.
          </p>
        </div>

        <div style="display:flex;gap:10px;align-items:center;">
          <a href="<?= url('reciclador/recolecciones') ?>" class="btn btn-outline" style="font-size:0.85rem;padding:9px 18px;border-radius:10px;">
            📦 Mis recolecciones aceptadas
          </a>
        </div>
      </div>

      <!-- Filtros por Material (Filtros de pastilla según diseño) -->
      <?php 
        $categorias = ['Todas', 'Cartón', 'Plástico', 'Vidrio', 'Metales', 'Papel'];
      ?>
      <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:12px;margin-bottom:20px;">
        <?php foreach ($categorias as $cat): ?>
          <?php $isActive = (strtolower($materialSeleccionado) === strtolower($cat)); ?>
          <a href="<?= url('reciclador/solicitudes', ['material' => $cat]) ?>" 
             style="text-decoration:none;padding:8px 18px;border-radius:20px;font-size:0.86rem;font-weight:600;white-space:nowrap;transition:all 0.2s;<?= $isActive ? 'background:#087A3D;color:#fff;box-shadow:0 3px 10px rgba(8,122,61,0.25);' : 'background:#fff;color:var(--g-600);border:1px solid var(--g-200);' ?>">
            <?= h($cat) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Grid de Solicitudes Disponibles -->
      <?php if (empty($solicitudes)): ?>
        <div class="card" style="padding:48px 24px;text-align:center;background:#fff;border-radius:16px;">
          <div style="font-size:3rem;margin-bottom:12px;">🌱</div>
          <h3 style="color:var(--g-800);font-size:1.15rem;font-weight:700;margin-bottom:8px;">
            No hay solicitudes disponibles con este filtro
          </h3>
          <p style="color:var(--g-500);font-size:0.9rem;max-width:400px;margin:0 auto 20px;">
            Los ciudadanos de Bogotá registran nuevas solicitudes continuamente. Revisa en unos momentos o prueba con otra categoría.
          </p>
          <a href="<?= url('reciclador/solicitudes') ?>" class="btn btn-p" style="padding:10px 22px;">
            Ver todas las solicitudes
          </a>
        </div>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(360px, 1fr));gap:20px;">
          <?php foreach ($solicitudes as $sol): ?>
            <?php
              $badgeColor = '#087A3D';
              $badgeBg = '#E6F4EA';
              if ($sol['prioridad'] === 'Alta') {
                $prioColor = '#D9381E';
              } elseif ($sol['prioridad'] === 'Urgente') {
                $prioColor = '#B3261E';
              } else {
                $prioColor = '#087A3D';
              }
            ?>
            <div class="card" style="background:#fff;border-radius:16px;border:1px solid #E5EBE7;padding:22px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 2px 10px rgba(0,0,0,0.03);transition:transform 0.2s, box-shadow 0.2s;">
              
              <div>
                <!-- Top Row: ID y Badges -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                  <span style="font-size:0.8rem;font-weight:700;color:var(--g-400);">
                    Solicitud #<?= (int)$sol['id_solicitud'] ?>
                  </span>
                  <div style="display:flex;gap:6px;align-items:center;">
                    <span style="background:<?= $badgeBg ?>;color:<?= $badgeColor ?>;font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:12px;display:inline-flex;align-items:center;gap:5px;">
                      <span style="width:6px;height:6px;border-radius:50%;background:<?= $badgeColor ?>;"></span>
                      Disponible
                    </span>
                    <span style="background:#FFF3E0;color:<?= $prioColor ?>;font-size:0.72rem;font-weight:700;padding:3px 8px;border-radius:12px;">
                      ! <?= h($sol['prioridad'] ?? 'Normal') ?>
                    </span>
                  </div>
                </div>

                <!-- Material y Cantidad -->
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                  <div style="width:46px;height:46px;border-radius:12px;background:#F0F7F2;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                    <?= $sol['material_icono'] ? h($sol['material_icono']) : '📦' ?>
                  </div>
                  <div>
                    <h3 style="margin:0;font-size:1.15rem;font-weight:800;color:var(--g-800);">
                      <?= h($sol['material_nombre']) ?>
                    </h3>
                    <div style="font-size:0.85rem;color:var(--g-600);font-weight:600;margin-top:2px;">
                      ⚖️ <?= number_format($sol['cantidad_estimada'], 1) ?> kg aprox.
                      <span style="color:var(--g-400);font-weight:400;margin-left:6px;">(~<?= number_format($sol['cantidad_estimada'] * ($sol['puntos_por_kg'] ?? 10)) ?> pts)</span>
                    </div>
                  </div>
                </div>

                <!-- Info Grid -->
                <div style="background:#F9FCFA;border-radius:12px;padding:12px 14px;margin-bottom:18px;display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:0.83rem;">
                  <div>
                    <span style="color:var(--g-400);display:block;font-size:0.75rem;">Ubicación</span>
                    <strong style="color:var(--g-800);">📍 <?= h($sol['localidad_nombre']) ?></strong>
                    <div style="color:var(--g-600);font-size:0.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= h($sol['direccion']) ?>">
                      <?= h($sol['direccion']) ?>
                    </div>
                  </div>
                  <div>
                    <span style="color:var(--g-400);display:block;font-size:0.75rem;">Horario / Disponibilidad</span>
                    <strong style="color:var(--g-800);">🕒 <?= date('d M', strtotime($sol['fecha_disponibilidad'])) ?></strong>
                    <div style="color:var(--g-600);font-size:0.78rem;">
                      <?= substr($sol['hora_inicio'], 0, 5) ?> - <?= substr($sol['hora_fin'], 0, 5) ?>
                    </div>
                  </div>
                </div>

                <!-- Ciudadano Contacto -->
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:0.82rem;color:var(--g-600);">
                  <span style="color:#087A3D;font-weight:700;">👤 Ciudadano verificado:</span>
                  <span><?= h($sol['ciudadano_nombre']) ?> <?= h($sol['ciudadano_apellido']) ?></span>
                </div>
              </div>

              <!-- Botones de Acción (Ver solicitud / Aceptar recolección) -->
              <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:10px;padding-top:14px;border-top:1px solid #EEF3F0;">
                <button type="button" class="btn btn-outline" 
                        onclick="mostrarDetalleSolicitud(<?= htmlspecialchars(json_encode($sol), ENT_QUOTES, 'UTF-8') ?>)"
                        style="font-size:0.84rem;padding:10px;border-radius:10px;justify-content:center;">
                  Ver solicitud
                </button>

                <form action="<?= url('reciclador/solicitud/aceptar') ?>" method="POST" style="margin:0;">
                  <?= csrf_input() ?>
                  <input type="hidden" name="id_solicitud" value="<?= (int)$sol['id_solicitud'] ?>">
                  <button type="submit" class="btn btn-p" 
                          onclick="return confirm('¿Confirmas que deseas aceptar la recolección #<?= (int)$sol['id_solicitud'] ?>?')"
                          style="width:100%;font-size:0.84rem;padding:10px;border-radius:10px;justify-content:center;background:#087A3D;">
                    Aceptar recolección
                  </button>
                </form>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

<!-- Modal Detalle Solicitud -->
<div id="modal-solicitud" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:18px;max-width:520px;width:100%;padding:28px;box-shadow:0 12px 36px rgba(0,0,0,0.2);position:relative;">
    <button onclick="cerrarModalSolicitud()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--g-400);">&times;</button>
    <div id="modal-solicitud-content"></div>
  </div>
</div>

<script>
function mostrarDetalleSolicitud(sol) {
  var c = document.getElementById('modal-solicitud-content');
  c.innerHTML = `
    <div style="font-size:0.8rem;font-weight:700;color:var(--g-400);text-transform:uppercase;margin-bottom:6px;">Detalle de Oportunidad</div>
    <h2 style="margin:0 0 12px;font-size:1.35rem;font-weight:800;color:var(--g-800);">${sol.material_nombre} — Solicitud #${sol.id_solicitud}</h2>
    
    <div style="background:#F5F9F6;padding:16px;border-radius:12px;margin-bottom:16px;font-size:0.88rem;line-height:1.6;">
      <div><strong>Cantidad Estimada:</strong> ${sol.cantidad_estimada} kg</div>
      <div><strong>Localidad:</strong> ${sol.localidad_nombre} (${sol.barrio || 'Zona Urbana'})</div>
      <div><strong>Dirección Exacta:</strong> ${sol.direccion}</div>
      <div><strong>Fecha de Disponibilidad:</strong> ${sol.fecha_disponibilidad}</div>
      <div><strong>Franja Horaria:</strong> ${sol.hora_inicio} a ${sol.hora_fin}</div>
      <div><strong>Generador:</strong> ${sol.ciudadano_nombre} ${sol.ciudadano_apellido} (${sol.ciudadano_telefono || 'Teléfono en orden de recogida'})</div>
      ${sol.observaciones ? `<div style="margin-top:8px;padding-top:8px;border-top:1px dashed #d1e3d7;"><strong>Instrucciones:</strong> ${sol.observaciones}</div>` : ''}
    </div>

    <form action="<?= url('reciclador/solicitud/aceptar') ?>" method="POST" style="margin:0;">
      <?= csrf_input() ?>
      <input type="hidden" name="id_solicitud" value="${sol.id_solicitud}">
      <button type="submit" class="btn btn-p" style="width:100%;padding:12px;font-size:0.95rem;justify-content:center;background:#087A3D;">
        Aceptar esta recolección ahora
      </button>
    </form>
  `;
  document.getElementById('modal-solicitud').style.display = 'flex';
}

function cerrarModalSolicitud() {
  document.getElementById('modal-solicitud').style.display = 'none';
}

window.addEventListener('click', function(e) {
  var m = document.getElementById('modal-solicitud');
  if (e.target === m) cerrarModalSolicitud();
});
</script>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
