<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL RECICLADOR (Actor Principal)
 * ============================================================================
 * 
 * Muestra el panel principal de trabajo del reciclador con métricas reales
 * de la base de datos MySQL, solicitudes disponibles en tiempo real y centros de acopio.
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg">

      <!-- Alerta Flash de Éxito si viene de Login o Registro -->
      <?php if ($success = flash('success')): ?>
        <div class="alert-success" style="margin-bottom:20px;">
          <?= h($success) ?>
        </div>
      <?php endif; ?>

      <!-- ── Hero Banner de Bienvenida ── -->
      <div style="background:linear-gradient(135deg,#065C2D,#087A3D,#2E9B50);border-radius:var(--r-xl);padding:24px 28px;margin-bottom:24px;color:#fff;position:relative;overflow:hidden;box-shadow:var(--shadow);">
        <div style="position:absolute;right:0;top:0;bottom:0;width:200px;opacity:0.08;font-size:10rem;display:flex;align-items:center;justify-content:flex-end;padding-right:16px;pointer-events:none;">
          ♻️
        </div>
        <div style="font-size:1.45rem;font-weight:800;margin-bottom:6px;">
          ¡Hola, <?= h($user['nombre']) ?>! 👋
        </div>
        <div style="font-size:0.92rem;opacity:0.92;max-width:520px;line-height:1.55;">
          Estas son las oportunidades de recolección disponibles en la plataforma para tu zona de trabajo en <strong><?= h($perfil['zona_trabajo'] ?? 'Bogotá D.C.') ?></strong>.
        </div>
        <div style="display:flex;gap:10px;margin-top:16px;">
          <a href="#solicitudes-list" class="btn" style="background:rgba(255,255,255,0.22);color:#fff;border:1px solid rgba(255,255,255,0.4);font-size:0.8rem;text-decoration:none;">
            📍 Ver oportunidades disponibles
          </a>
        </div>
      </div>

      <!-- ── Cuadrícula de Métricas Principales (Datos 100% Reales de la BD) ── -->
      <div class="g4" style="margin-bottom:24px;">
        
        <!-- Tarjeta 1: Ecopoints Reales -->
        <div class="sh" style="background:var(--green);color:#fff;">
          <div class="dec">⭐</div>
          <div style="font-size:0.68rem;font-weight:700;opacity:0.75;letter-spacing:0.08em;margin-bottom:8px;">
            ECOPOINTS
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:2rem;">⭐</span>
            <span class="sh-val"><?= number_format($metricas['ecopuntos'], 0, ',', '.') ?></span>
          </div>
          <div class="sh-sub">
            <?= ((int)($metricas['puntos_mes'] ?? 0) > 0) ? '+' . number_format($metricas['puntos_mes'], 0, ',', '.') . ' este mes' : 'Saldo activo acumulado' ?>
          </div>
        </div>

        <!-- Tarjeta 2: Material Recuperado Real -->
        <div class="card cp">
          <div style="font-size:0.68rem;font-weight:700;color:#C97D3A;letter-spacing:0.08em;margin-bottom:8px;">
            MATERIAL RECUPERADO
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:1.6rem;">📦</span>
            <div>
              <div style="font-size:1.75rem;font-weight:800;line-height:1;">
                <?= number_format($metricas['material_kg'], 1, ',', '.') ?>
              </div>
              <div style="font-size:0.65rem;color:var(--muted);margin-top:2px;">kilogramos validados</div>
            </div>
          </div>
          <div style="font-size:0.72rem;color:var(--muted);margin-top:8px;">Total histórico en base de datos</div>
        </div>

        <!-- Tarjeta 3: Solicitudes Reales Disponibles -->
        <div class="card cp">
          <div style="font-size:0.68rem;font-weight:700;color:var(--green-mid);letter-spacing:0.08em;margin-bottom:8px;">
            SOLICITUDES DISPONIBLES
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:1.6rem;">📍</span>
            <div>
              <div style="font-size:1.75rem;font-weight:800;line-height:1;">
                <?= (int)$metricas['solicitudes_activas'] ?>
              </div>
              <div style="font-size:0.65rem;color:var(--muted);margin-top:2px;">pendientes de recolección</div>
            </div>
          </div>
          <div style="font-size:0.72rem;color:var(--muted);margin-top:8px;">En Bogotá D.C.</div>
        </div>

        <!-- Tarjeta 4: Nivel del Reciclador Real -->
        <div class="card cp">
          <div style="font-size:0.68rem;font-weight:700;color:#B28300;letter-spacing:0.08em;margin-bottom:8px;">
            NIVEL OFICIAL
          </div>
          <div style="font-size:1.15rem;font-weight:800;color:var(--text);margin-bottom:6px;">
            <?= h($metricas['nivel']['nombre']) ?>
          </div>
          <div class="pw" style="margin-bottom:6px;">
            <div class="pb" style="width: <?= min(100, $metricas['nivel']['pct']) ?>%;"></div>
          </div>
          <div style="font-size:0.72rem;color:var(--muted);display:flex;justify-content:space-between;">
            <span><?= number_format($metricas['ecopuntos'], 0, ',', '.') ?> pts</span>
            <span>Meta: <?= number_format($metricas['nivel']['meta'], 0, ',', '.') ?></span>
          </div>
        </div>

      </div>

      <!-- ── Sección: Solicitudes Reales en Base de Datos ── -->
      <div id="solicitudes-list" style="margin-bottom:28px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
          <div>
            <div style="font-size:1.1rem;font-weight:700;color:var(--text);">Oportunidades de recolección disponibles</div>
            <div style="font-size:0.78rem;color:var(--muted);">Solicitudes publicadas por ciudadanos en la plataforma.</div>
          </div>
          <div style="display:flex;gap:8px;align-items:center;">
            <a href="<?= url('reciclador/solicitudes') ?>" class="btn btn-outline" style="font-size:0.8rem;padding:6px 14px;border-radius:10px;">
              Ver todas las solicitudes &rarr;
            </a>
            <span class="bdg bg">🟢 En tiempo real (MySQL)</span>
          </div>
        </div>

        <?php if (empty($solicitudes)): ?>
          <div class="card" style="padding:32px 20px;text-align:center;background:#fff;border-radius:14px;border:1px dashed var(--border);">
            <div style="font-size:2.5rem;margin-bottom:8px;">📦</div>
            <div style="font-weight:700;font-size:0.95rem;color:var(--text);margin-bottom:4px;">
              No hay solicitudes pendientes en este momento
            </div>
            <p style="font-size:0.8rem;color:var(--muted);margin:0 auto;max-width:440px;">
              Cuando un ciudadano registre una solicitud de recolección desde su panel, aparecerá aquí inmediatamente con los detalles del material y ubicación.
            </p>
          </div>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($solicitudes as $sol): ?>
              <?php
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
              <div class="card cp" style="display:flex;align-items:center;gap:16px;padding:16px 20px;background:#fff;border-radius:14px;border:1px solid var(--border);flex-wrap:wrap;">
                <div style="width:50px;height:50px;border-radius:12px;background:#F0FDF4;display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;border:1px solid #DCFCE7;">
                  <?= $emojiMat ?>
                </div>
                <div style="flex:1;min-width:240px;">
                  <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                    <span style="font-weight:700;font-size:0.92rem;color:var(--text);">
                      Solicitud #<?= str_pad((string)$sol['id_solicitud'], 5, '0', STR_PAD_LEFT) ?>
                    </span>
                    <span class="bdg bg">🟢 <?= h($sol['estado']) ?></span>
                    <?php if (($sol['prioridad'] ?? 'Normal') === 'Alta' || ($sol['prioridad'] ?? '') === 'Urgente'): ?>
                      <span class="bdg br" style="font-size:0.62rem;">Prioridad <?= h($sol['prioridad']) ?></span>
                    <?php endif; ?>
                  </div>
                  <div style="font-size:0.86rem;font-weight:600;color:var(--green);">
                    <?= h($sol['material_nombre']) ?> — <?= number_format((float)$sol['cantidad_estimada'], 1, ',', '.') ?> kg aprox.
                  </div>
                  <div style="font-size:0.75rem;color:var(--muted);display:flex;gap:16px;margin-top:4px;flex-wrap:wrap;">
                    <span>📍 <?= h($sol['localidad_nombre']) ?><?= !empty($sol['barrio']) ? ' (' . h($sol['barrio']) . ')' : '' ?></span>
                    <span>📅 Disponibilidad: <?= date('d/m/Y', strtotime($sol['fecha_disponibilidad'])) ?></span>
                    <span>⏰ <?= date('h:i A', strtotime($sol['hora_inicio'])) ?> - <?= date('h:i A', strtotime($sol['hora_fin'])) ?></span>
                    <span>👤 <?= h($sol['ciudadano_nombre']) ?></span>
                  </div>
                </div>

                <!-- Botón de Aceptar Recolección -->
                <div style="margin-left:auto;">
                  <form action="<?= url('reciclador/solicitud/aceptar') ?>" method="POST" style="margin:0;">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id_solicitud" value="<?= (int)$sol['id_solicitud'] ?>">
                    <button type="submit" class="btn btn-p" 
                            onclick="return confirm('¿Aceptar esta recolección #<?= (int)$sol['id_solicitud'] ?>?')"
                            style="padding:9px 16px;font-size:0.84rem;font-weight:700;border-radius:10px;background:#087A3D;">
                      Aceptar recolección
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

      <!-- ── Centros de Acopio Reales de la BD ── -->
      <div class="g2">
        
        <div class="card cp">
          <div style="font-size:0.95rem;font-weight:700;margin-bottom:6px;">Centros de acopio autorizados</div>
          <p style="font-size:0.78rem;color:var(--muted);margin-bottom:14px;">
            Puntos oficiales activos para pesaje certificado y acreditación de Ecopoints.
          </p>
          <?php if (!empty($centros)): ?>
            <?php foreach (array_slice($centros, 0, 3) as $c): ?>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#FAFCFA;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;">
                <div>
                  <div style="font-weight:600;font-size:0.82rem;color:var(--text);"><?= h($c['nombre']) ?></div>
                  <div style="font-size:0.72rem;color:var(--muted);">📍 <?= h($c['direccion']) ?> • <?= h($c['localidad_nombre']) ?></div>
                </div>
                <span class="bdg bg">Abierto</span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="card cp">
          <div style="font-size:0.95rem;font-weight:700;margin-bottom:6px;">Tus recompensas disponibles</div>
          <p style="font-size:0.78rem;color:var(--muted);margin-bottom:14px;">
            Convierte tus <strong><?= number_format($metricas['ecopuntos'], 0, ',', '.') ?> Ecopoints</strong> en equipamiento y beneficios para tu labor diaria.
          </p>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#FAFCFA;border:1px solid var(--border);border-radius:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="font-size:1.5rem;">🦺</span>
              <div>
                <div style="font-weight:600;font-size:0.84rem;">Kit de Protección y Seguridad</div>
                <div style="font-size:0.72rem;color:#087A3D;font-weight:700;">800 Ecopoints</div>
              </div>
            </div>
            <span class="bdg bg" style="font-size:0.72rem;">Disponible para canje</span>
          </div>
        </div>

      </div>

    </div>
  </div>

  <?php require APP_PATH . 'views/layouts/footer.php'; ?>
