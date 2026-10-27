<?php
/**
 * ============================================================================
 * VISTA: REGISTRAR SOLICITUD DE RECOLECCIÓN (crear_solicitud.php — Fase 2)
 * ============================================================================
 * 
 * Permite a los ciudadanos registrar una solicitud para que un reciclador
 * recoja materiales reciclables en su domicilio u oficina.
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';

$idLocalidadPerfil = (int)($perfil['id_localidad'] ?? 11);
$direccionPerfil = $perfil['direccion'] ?? '';
$barrioPerfil = $perfil['barrio'] ?? '';
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg" style="max-width:860px;margin:0 auto;">

      <!-- Navegación de migas de pan (Breadcrumb) -->
      <div style="font-size:0.8rem;color:var(--muted);margin-bottom:14px;display:flex;align-items:center;gap:6px;">
        <a href="<?= url('ciudadano') ?>" style="color:var(--green);text-decoration:none;">Panel</a>
        <span>›</span>
        <span>Registrar Solicitud</span>
      </div>

      <!-- Encabezado de la Sección -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
          <h1 style="font-size:1.5rem;font-weight:800;color:var(--text);margin:0 0 4px 0;">
            📦 Nueva Solicitud de Recolección
          </h1>
          <p style="font-size:0.86rem;color:var(--muted);margin:0;">
            Conecta tus materiales reciclables clasificados con los recicladores profesionales de tu localidad.
          </p>
        </div>
        <a href="<?= url('ciudadano/solicitudes') ?>" class="btn btn-outline" style="font-size:0.82rem;padding:8px 14px;">
          📋 Ver mis solicitudes
        </a>
      </div>

      <!-- Alertas de Error y Validación -->
      <?php if (!empty($error)): ?>
        <div class="alert-error" style="margin-bottom:20px;">
          <?= $error ?>
        </div>
      <?php endif; ?>

      <div id="js-solicitud-error" class="alert-error" style="display:none;margin-bottom:20px;"></div>

      <!-- Tarjeta Principal del Formulario -->
      <div class="card" style="padding:30px;background:#fff;border-radius:var(--r-xl);box-shadow:var(--shadow-sm);border:1px solid var(--border);">

        <form id="form-crear-solicitud" action="<?= url('ciudadano/solicitud/crear') ?>" method="POST">
          <?= csrf_input() ?>

          <!-- SECCIÓN 1: TIPO DE MATERIAL -->
          <div style="margin-bottom:24px;">
            <label class="form-label" style="font-size:0.95rem;font-weight:700;display:flex;align-items:center;gap:8px;margin-bottom:10px;">
              <span>♻️ 1. Tipo de material a entregar</span>
              <span style="color:var(--red);font-size:0.8rem;">*</span>
            </label>
            <p style="font-size:0.78rem;color:var(--muted);margin:-4px 0 12px 0;">
              Selecciona el residuo aprovechable principal que tienes listo y clasificado:
            </p>

            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:10px;">
              <?php foreach ($materiales as $mat): ?>
                <?php 
                  $isSelected = ((int)($old['id_material'] ?? 1) === (int)$mat['id_material']);
                  $iconoMap = [
                    'box' => '📦',
                    'file-text' => '📄',
                    'disc' => '🧴',
                    'shield' => '🍾',
                    'layers' => '🥫',
                    'package' => '📦'
                  ];
                  $emoji = $iconoMap[$mat['icono']] ?? '♻️';
                ?>
                <label class="material-card-label" style="display:flex;flex-direction:column;align-items:center;padding:12px 8px;border:2px solid <?= $isSelected ? '#087A3D' : '#E0EBE4' ?>;background:<?= $isSelected ? '#EEF8EE' : '#fff' ?>;border-radius:12px;cursor:pointer;text-align:center;transition:all 0.18s ease;">
                  <input type="radio" name="id_material" value="<?= h($mat['id_material']) ?>" style="display:none;" <?= $isSelected ? 'checked' : '' ?> required>
                  <span style="font-size:1.8rem;margin-bottom:4px;"><?= $emoji ?></span>
                  <strong style="font-size:0.82rem;color:var(--text);"><?= h($mat['nombre']) ?></strong>
                  <span style="font-size:0.68rem;color:var(--muted);margin-top:2px;">~<?= h($mat['puntos_por_kg']) ?> pts/kg</span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <hr style="border:none;border-top:1px solid #EFEFEF;margin:24px 0;">

          <!-- SECCIÓN 2: CANTIDAD ESTIMADA -->
          <div style="margin-bottom:24px;">
            <label class="form-label" for="cantidad_estimada" style="font-size:0.95rem;font-weight:700;display:flex;align-items:center;gap:8px;margin-bottom:6px;">
              <span>⚖️ 2. Cantidad aproximada</span>
              <span style="color:var(--red);font-size:0.8rem;">*</span>
            </label>
            <p style="font-size:0.78rem;color:var(--muted);margin:0 0 10px 0;">
              Ingresa una estimación en kilogramos. No te preocupes por la exactitud: el pesaje formal se valida en el Centro de Acopio.
            </p>

            <div style="display:flex;align-items:center;max-width:320px;gap:8px;">
              <input 
                type="number" 
                id="cantidad_estimada" 
                name="cantidad_estimada" 
                class="form-input" 
                step="0.1" 
                min="0.1" 
                max="5000" 
                placeholder="Ej: 8.5" 
                value="<?= h($old['cantidad_estimada'] ?? '') ?>" 
                required 
                style="font-size:1.1rem;font-weight:700;padding:10px 14px;"
              />
              <span style="font-size:0.95rem;font-weight:700;color:var(--muted);background:#F3F4F6;padding:10px 16px;border-radius:10px;border:1px solid var(--border);">
                Kilogramos (kg)
              </span>
            </div>
            <div style="font-size:0.75rem;color:#087A3D;margin-top:6px;display:flex;align-items:center;gap:4px;">
              <span>ℹ️</span><span>La cantidad es una estimación. No asigna puntos automáticos hasta el pesaje certificado.</span>
            </div>
          </div>

          <hr style="border:none;border-top:1px solid #EFEFEF;margin:24px 0;">

          <!-- SECCIÓN 3: UBICACIÓN DE LA RECOLECCIÓN -->
          <div style="margin-bottom:24px;">
            <label class="form-label" style="font-size:0.95rem;font-weight:700;margin-bottom:10px;display:block;">
              📍 3. Ubicación del punto de recolección
            </label>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div class="form-group">
                <label class="form-label" for="id_localidad">Localidad oficial (Bogotá) <span style="color:var(--red);">*</span></label>
                <select id="id_localidad" name="id_localidad" class="form-select" required>
                  <option value="">-- Selecciona Localidad --</option>
                  <?php foreach ($localidades as $loc): ?>
                    <?php 
                      $selLoc = ((int)($old['id_localidad'] ?? $idLocalidadPerfil) === (int)$loc['id_localidad']) ? 'selected' : '';
                    ?>
                    <option value="<?= h($loc['id_localidad']) ?>" <?= $selLoc ?>>
                      <?= h($loc['numero']) ?>. <?= h($loc['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="barrio">Barrio</label>
                <input 
                  type="text" 
                  id="barrio" 
                  name="barrio" 
                  class="form-input" 
                  placeholder="Ej: Rincón de Suba" 
                  value="<?= h($old['barrio'] ?? $barrioPerfil) ?>"
                />
              </div>
            </div>

            <div class="form-group" style="margin-top:12px;">
              <label class="form-label" for="direccion">Dirección exacta o punto de referencia <span style="color:var(--red);">*</span></label>
              <input 
                type="text" 
                id="direccion" 
                name="direccion" 
                class="form-input" 
                placeholder="Ej: Calle 145 # 92-30 (Portería o Conjunto)" 
                value="<?= h($old['direccion'] ?? $direccionPerfil) ?>" 
                required
              />
              <span style="font-size:0.72rem;color:var(--muted);margin-top:4px;display:block;">
                🔒 Esta dirección se gestiona con confidencialidad y solo se entrega al reciclador que atienda tu recolección.
              </span>
            </div>
          </div>

          <hr style="border:none;border-top:1px solid #EFEFEF;margin:24px 0;">

          <!-- SECCIÓN 4: DISPONIBILIDAD Y HORARIOS -->
          <div style="margin-bottom:24px;">
            <label class="form-label" style="font-size:0.95rem;font-weight:700;margin-bottom:10px;display:block;">
              ⏰ 4. Disponibilidad para la entrega
            </label>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
              <div class="form-group">
                <label class="form-label" for="fecha_disponibilidad">Fecha de disponibilidad <span style="color:var(--red);">*</span></label>
                <input 
                  type="date" 
                  id="fecha_disponibilidad" 
                  name="fecha_disponibilidad" 
                  class="form-input" 
                  min="<?= date('Y-m-d') ?>" 
                  value="<?= h($old['fecha_disponibilidad'] ?? date('Y-m-d')) ?>" 
                  required
                />
              </div>

              <div class="form-group">
                <label class="form-label" for="franja_horaria">Franja horaria preferida</label>
                <select id="franja_horaria" name="franja_horaria" class="form-select">
                  <option value="08:00-14:00" <?= ($old['franja_horaria'] ?? '') === '08:00-14:00' ? 'selected' : '' ?>>Mañana extendida (8:00 a.m. - 2:00 p.m.)</option>
                  <option value="08:00-12:00" <?= ($old['franja_horaria'] ?? '') === '08:00-12:00' ? 'selected' : '' ?>>Mañana (8:00 a.m. - 12:00 m.)</option>
                  <option value="12:00-17:00" <?= ($old['franja_horaria'] ?? '') === '12:00-17:00' ? 'selected' : '' ?>>Tarde (12:00 m. - 5:00 p.m.)</option>
                  <option value="07:00-11:00" <?= ($old['franja_horaria'] ?? '') === '07:00-11:00' ? 'selected' : '' ?>>Temprano (7:00 a.m. - 11:00 a.m.)</option>
                  <option value="14:00-18:00" <?= ($old['franja_horaria'] ?? '') === '14:00-18:00' ? 'selected' : '' ?>>Tarde-Noche (2:00 p.m. - 6:00 p.m.)</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="prioridad">Prioridad / Volumen</label>
                <select id="prioridad" name="prioridad" class="form-select">
                  <option value="Normal" <?= ($old['prioridad'] ?? '') === 'Normal' ? 'selected' : '' ?>>Normal (Residuo habitual)</option>
                  <option value="Alta" <?= ($old['prioridad'] ?? '') === 'Alta' ? 'selected' : '' ?>>Alta (Volumen considerable)</option>
                  <option value="Urgente" <?= ($old['prioridad'] ?? '') === 'Urgente' ? 'selected' : '' ?>>Urgente (Mudanza o despeje)</option>
                </select>
              </div>
            </div>

            <div class="form-group" style="margin-top:14px;">
              <label class="form-label" for="observaciones">Indicaciones adicionales para el reciclador</label>
              <textarea 
                id="observaciones" 
                name="observaciones" 
                class="form-input" 
                rows="3" 
                placeholder="Ej: Timbrar al apto 302, material en cajas limpias selladas, acceso por rampa lateral..."
                style="resize:vertical;"
              ><?= h($old['observaciones'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- BOTONES DE ACCIÓN -->
          <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:30px;padding-top:20px;border-top:1px solid #EFEFEF;">
            <a href="<?= url('ciudadano') ?>" class="btn btn-outline" style="padding:11px 22px;">
              Cancelar
            </a>
            <button type="submit" class="btn btn-p" style="padding:11px 26px;font-size:0.95rem;font-weight:700;">
              🚀 Registrar Solicitud de Recolección
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>

  <?php require APP_PATH . 'views/layouts/footer.php'; ?>

<!-- Script interactivo de selección de tarjetas y validación -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Selección visual de tarjetas de material
  const materialLabels = document.querySelectorAll('.material-card-label');
  materialLabels.forEach(function(lbl) {
    lbl.addEventListener('click', function() {
      materialLabels.forEach(function(l) {
        l.style.borderColor = '#E0EBE4';
        l.style.background = '#fff';
      });
      lbl.style.borderColor = '#087A3D';
      lbl.style.background = '#EEF8EE';
    });
  });

  // Validación previa en cliente antes del envío
  const form = document.getElementById('form-crear-solicitud');
  const errorBox = document.getElementById('js-solicitud-error');

  if (form) {
    form.addEventListener('submit', function(e) {
      const cant = parseFloat(document.getElementById('cantidad_estimada')?.value || '0');
      const dir = document.getElementById('direccion')?.value.trim() || '';
      const fecha = document.getElementById('fecha_disponibilidad')?.value || '';
      const hoy = new Date().toISOString().split('T')[0];

      let msg = '';

      if (isNaN(cant) || cant <= 0) {
        msg = 'La cantidad estimada debe ser un número mayor a cero (ej: 5.5 kg).';
      } else if (!dir || dir.length < 5) {
        msg = 'Por favor ingresa una dirección de recolección completa.';
      } else if (!fecha) {
        msg = 'Debes indicar la fecha de disponibilidad.';
      } else if (fecha < hoy) {
        msg = 'La fecha de disponibilidad no puede estar en el pasado.';
      }

      if (msg) {
        e.preventDefault();
        if (errorBox) {
          errorBox.innerHTML = '⚠️ ' + msg;
          errorBox.style.display = 'block';
          errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
          alert(msg);
        }
      }
    });
  }
});
</script>
