<?php
/**
 * VISTA: MIS RECOLECCIONES (ROL RECICLADOR)
 * Basado fielmente en el diseño de Figma (Imagen 2)
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

      <!-- Header -->
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:22px;">
        <div>
          <h1 style="font-size:1.6rem;font-weight:800;color:var(--g-800);margin:0 0 6px;">
            Mis recolecciones
          </h1>
          <p style="margin:0;font-size:0.9rem;color:var(--g-500);">
            Gestiona tus recolecciones aceptadas, activas y completadas.
          </p>
        </div>

        <div>
          <a href="<?= url('reciclador/solicitudes') ?>" class="btn btn-p" style="padding:11px 22px;border-radius:12px;font-size:0.88rem;background:#087A3D;">
            + Nueva recolección
          </a>
        </div>
      </div>

      <!-- Pestañas de Estado (Tabs según diseño de imagen 2) -->
      <div style="display:flex;gap:12px;border-bottom:1px solid #E5EBE7;padding-bottom:12px;margin-bottom:24px;overflow-x:auto;">
        <a href="<?= url('reciclador/recolecciones', ['tab' => 'activas']) ?>" 
           style="text-decoration:none;display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:700;padding:8px 16px;border-radius:10px;<?= $activeTab === 'activas' ? 'background:#E8F5E9;color:#087A3D;' : 'color:var(--g-500);' ?>">
          <span style="width:8px;height:8px;border-radius:50%;background:#087A3D;"></span>
          Activas
        </a>
        <a href="<?= url('reciclador/recolecciones', ['tab' => 'en_proceso']) ?>" 
           style="text-decoration:none;display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:700;padding:8px 16px;border-radius:10px;<?= $activeTab === 'en_proceso' ? 'background:#FFF8E1;color:#F57F17;' : 'color:var(--g-500);' ?>">
          <span style="width:8px;height:8px;border-radius:50%;background:#FBC02D;"></span>
          En proceso
        </a>
        <a href="<?= url('reciclador/recolecciones', ['tab' => 'completadas']) ?>" 
           style="text-decoration:none;display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:700;padding:8px 16px;border-radius:10px;<?= $activeTab === 'completadas' ? 'background:#E8F5E9;color:#2E7D32;' : 'color:var(--g-500);' ?>">
          <span>✓</span>
          Completadas
        </a>
        <a href="<?= url('reciclador/recolecciones', ['tab' => 'canceladas']) ?>" 
           style="text-decoration:none;display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:700;padding:8px 16px;border-radius:10px;<?= $activeTab === 'canceladas' ? 'background:#FFEBEE;color:#C62828;' : 'color:var(--g-500);' ?>">
          <span>✕</span>
          Canceladas
        </a>
      </div>

      <!-- Listado de Tarjetas de Recolección -->
      <?php if (empty($recolecciones)): ?>
        <div class="card" style="padding:48px 24px;text-align:center;background:#fff;border-radius:16px;">
          <div style="font-size:3rem;margin-bottom:12px;">🚲</div>
          <h3 style="color:var(--g-800);font-size:1.15rem;font-weight:700;margin-bottom:8px;">
            No tienes recolecciones en esta sección
          </h3>
          <p style="color:var(--g-500);font-size:0.9rem;max-width:420px;margin:0 auto 20px;">
            Acepta solicitudes disponibles en tu localidad para empezar a recolectar materiales y acumular Ecopoints.
          </p>
          <a href="<?= url('reciclador/solicitudes') ?>" class="btn btn-p" style="padding:10px 24px;background:#087A3D;">
            Explorar Solicitudes Cercanas
          </a>
        </div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:18px;">
          <?php foreach ($recolecciones as $rec): ?>
            <?php
              $badgeBg = '#E8F5E9';
              $badgeColor = '#087A3D';
              $badgeText = 'Aceptada';
              if ($rec['estado'] === 'En camino') {
                $badgeBg = '#FFF8E1';
                $badgeColor = '#F57F17';
                $badgeText = 'En curso';
              } elseif ($rec['estado'] === 'Recolectada') {
                $badgeBg = '#E8F5E9';
                $badgeColor = '#2E7D32';
                $badgeText = 'Recolectada';
              } elseif ($rec['estado'] === 'Cancelada') {
                $badgeBg = '#FFEBEE';
                $badgeColor = '#C62828';
                $badgeText = 'Cancelada';
              }
            ?>
            <div class="card" style="background:#fff;border-radius:18px;border:1px solid #E5EBE7;padding:24px;box-shadow:0 3px 12px rgba(0,0,0,0.03);">
              
              <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:16px;">
                <!-- Material e Info Principal -->
                <div style="display:flex;align-items:center;gap:16px;">
                  <div style="width:52px;height:52px;border-radius:14px;background:#F0F7F2;display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
                    <?= $rec['material_icono'] ? h($rec['material_icono']) : '♻️' ?>
                  </div>
                  <div>
                    <h3 style="margin:0;font-size:1.25rem;font-weight:800;color:var(--g-800);">
                      <?= h($rec['material_nombre']) ?>
                    </h3>
                    <div style="font-size:0.86rem;color:var(--g-500);font-weight:500;margin-top:2px;">
                      Solicitud #<?= (int)$rec['id_solicitud'] ?> · <strong><?= number_format($rec['cantidad_estimada'], 1) ?> kg aprox.</strong>
                    </div>
                  </div>
                </div>

                <!-- Badge Estado -->
                <div>
                  <span style="background:<?= $badgeBg ?>;color:<?= $badgeColor ?>;font-size:0.8rem;font-weight:700;padding:5px 14px;border-radius:20px;display:inline-flex;align-items:center;gap:6px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:<?= $badgeColor ?>;"></span>
                    <?= $badgeText ?>
                  </span>
                </div>
              </div>

              <!-- Fila de Detalles: Localidad, Centro de destino, Horario -->
              <div style="display:flex;flex-wrap:wrap;gap:24px;font-size:0.86rem;color:var(--g-600);margin-bottom:20px;padding:12px 16px;background:#F9FCFA;border-radius:12px;">
                <div>
                  <span style="color:var(--g-400);display:block;font-size:0.75rem;">Punto de recogida</span>
                  <strong>📍 <?= h($rec['localidad_nombre']) ?>, Bogotá</strong> — <?= h($rec['direccion']) ?>
                </div>
                <div>
                  <span style="color:var(--g-400);display:block;font-size:0.75rem;">Centro sugerido</span>
                  <strong>🏢 EcoCentro Suba Verde</strong>
                </div>
                <div>
                  <span style="color:var(--g-400);display:block;font-size:0.75rem;">Horario / Contacto</span>
                  <strong>🕒 <?= substr($rec['hora_inicio'], 0, 5) ?> - <?= substr($rec['hora_fin'], 0, 5) ?></strong> (<?= h($rec['ciudadano_nombre']) ?>)
                </div>
              </div>

              <!-- Botones de Acción (según imagen 2) -->
              <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <?php if ($rec['estado'] === 'Aceptada' || $rec['estado'] === 'En camino'): ?>
                  <!-- Botón Registrar Recolección -->
                  <button type="button" class="btn btn-p" 
                          onclick="abrirModalRegistrarRecoleccion(<?= (int)$rec['id_recoleccion'] ?>, '<?= h($rec['material_nombre']) ?>', <?= (float)$rec['cantidad_estimada'] ?>)"
                          style="flex:1;min-width:220px;justify-content:center;padding:12px;font-size:0.9rem;font-weight:700;background:#087A3D;border-radius:12px;">
                    📝 Registrar recolección
                  </button>

                  <?php if ($rec['estado'] === 'Aceptada'): ?>
                    <form action="<?= url('reciclador/recoleccion/actualizar') ?>" method="POST" style="margin:0;">
                      <?= csrf_input() ?>
                      <input type="hidden" name="id_recoleccion" value="<?= (int)$rec['id_recoleccion'] ?>">
                      <input type="hidden" name="nuevo_estado" value="En camino">
                      <button type="submit" class="btn btn-outline" style="padding:12px 20px;border-radius:12px;font-size:0.9rem;">
                        🚲 Voy en camino
                      </button>
                    </form>
                  <?php endif; ?>

                <?php elseif ($rec['estado'] === 'Recolectada'): ?>
                  <div style="font-size:0.86rem;color:#2E7D32;font-weight:700;display:flex;align-items:center;gap:6px;">
                    ✓ Material recolectado (<?= number_format($rec['cantidad_recolectada'], 1) ?> kg). Pendiente de validación y pesaje en Centro de Acopio.
                  </div>
                <?php endif; ?>

                <a href="<?= url('reciclador/centros') ?>" class="btn btn-outline" style="padding:12px 20px;border-radius:12px;font-size:0.9rem;display:flex;align-items:center;gap:6px;">
                  🗺️ Ver en ruta / Centro
                </a>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>

<!-- Modal Registrar Recolección Real -->
<div id="modal-registrar-rec" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:18px;max-width:480px;width:100%;padding:28px;box-shadow:0 12px 36px rgba(0,0,0,0.2);position:relative;">
    <button onclick="cerrarModalRegistrarRec()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--g-400);">&times;</button>
    
    <div style="font-size:0.8rem;font-weight:700;color:var(--g-400);text-transform:uppercase;margin-bottom:6px;">Completar Recogida</div>
    <h2 id="modal-rec-titulo" style="margin:0 0 16px;font-size:1.35rem;font-weight:800;color:var(--g-800);">Registrar Recolección</h2>
    
    <form action="<?= url('reciclador/recoleccion/actualizar') ?>" method="POST">
      <?= csrf_input() ?>
      <input type="hidden" name="id_recoleccion" id="modal-id-recoleccion" value="">
      <input type="hidden" name="nuevo_estado" value="Recolectada">

      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.86rem;font-weight:700;color:var(--g-700);margin-bottom:6px;">
          Kilogramos recolectados reales (kg)
        </label>
        <input type="number" step="0.1" min="0.1" name="cantidad_recolectada" id="modal-kg-input" required 
               style="width:100%;padding:12px 14px;border:1px solid #D1D5DB;border-radius:10px;font-size:1rem;font-weight:600;box-sizing:border-box;">
        <small style="color:var(--g-500);display:block;margin-top:4px;">
          Ingresa el peso verificado en la entrega con el ciudadano.
        </small>
      </div>

      <div style="background:#F0F7F2;border-radius:10px;padding:12px;margin-bottom:20px;font-size:0.82rem;color:var(--g-700);">
        💡 Al registrar esta recolección, se notificará al Centro de Acopio para realizar el pesaje oficial y abonar tus Ecopoints.
      </div>

      <div style="display:flex;gap:10px;">
        <button type="button" onclick="cerrarModalRegistrarRec()" class="btn btn-outline" style="flex:1;padding:12px;border-radius:10px;">
          Cancelar
        </button>
        <button type="submit" class="btn btn-p" style="flex:1.5;padding:12px;background:#087A3D;border-radius:10px;font-weight:700;">
          Confirmar recogida
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirModalRegistrarRec(id, material, kgEstimado) {
  document.getElementById('modal-id-recoleccion').value = id;
  document.getElementById('modal-rec-titulo').innerText = 'Recolección de ' + material;
  document.getElementById('modal-kg-input').value = kgEstimado;
  document.getElementById('modal-registrar-rec').style.display = 'flex';
}

function cerrarModalRegistrarRec() {
  document.getElementById('modal-registrar-rec').style.display = 'none';
}
</script>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
