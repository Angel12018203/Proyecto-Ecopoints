<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL RECICLADOR (Actor Principal)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA VISTA?
 * Representa la pantalla principal de trabajo del reciclador según el diseño
 * de alta fidelidad aprobado en Figma (Puntos 23, 24, 25 de requisitos):
 * 
 * 1. Saludo personalizado con su nombre real: "¡Hola, <?= h($user['nombre']) ?>! 👋"
 * 2. Tarjetas métricas dinámicas procedentes de la base de datos:
 *    - Ecopoints acumulados.
 *    - Material recuperado en kg.
 *    - Conteo de solicitudes cercanas en su localidad.
 *    - Nivel oficial calculado (EcoReciclador) con barra de progreso.
 * 3. Demostración visual de Solicitudes Cercanas (#1024, 30 kg de cartón, 800 m).
 * 4. Atajos rápidos para Mi ruta, Centros de Acopio y Beneficios.
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
      <div style="background:linear-gradient(135deg,#087A3D,#2E9B50);border-radius:var(--r-xl);padding:24px 28px;margin-bottom:24px;color:#fff;position:relative;overflow:hidden;box-shadow:var(--shadow);">
        <div style="position:absolute;right:0;top:0;bottom:0;width:200px;opacity:0.08;font-size:10rem;display:flex;align-items:center;justify-content:flex-end;padding-right:16px;pointer-events:none;">
          ♻️
        </div>
        <div style="font-size:1.45rem;font-weight:800;margin-bottom:6px;">
          ¡Hola, <?= h($user['nombre']) ?>! 👋
        </div>
        <div style="font-size:0.92rem;opacity:0.92;max-width:520px;line-height:1.55;">
          Estas son las oportunidades de recolección disponibles cerca de ti en <strong><?= h($perfil['zona_trabajo'] ?? 'tu zona') ?></strong>. Conecta tu trabajo con nuevos materiales y convierte tu esfuerzo en beneficios.
        </div>
        <div style="display:flex;gap:10px;margin-top:16px;">
          <a href="#solicitudes-list" class="btn" style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.78rem;">
            📍 Ver solicitudes cercanas
          </a>
          <a href="#ruta" class="btn" style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.35);font-size:0.78rem;" onclick="alert('Módulo de organización de rutas en desarrollo para la siguiente fase.'); return false;">
            🗺️ Organizar mi ruta
          </a>
        </div>
      </div>

      <!-- ── Cuadrícula de Métricas Principales (Punto 23) ── -->
      <div class="g4" style="margin-bottom:24px;">
        
        <!-- Tarjeta 1: Ecopoints -->
        <div class="sh" style="background:var(--green);color:#fff;">
          <div class="dec">⭐</div>
          <div style="font-size:0.68rem;font-weight:700;opacity:0.75;letter-spacing:0.08em;margin-bottom:8px;">
            ECOPOINTS
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:2rem;">⭐</span>
            <span class="sh-val"><?= number_format($metricas['ecopuntos'], 0, ',', '.') ?></span>
          </div>
          <div class="sh-sub">+320 este mes</div>
        </div>

        <!-- Tarjeta 2: Material Recuperado -->
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
              <div style="font-size:0.65rem;color:var(--muted);margin-top:2px;">kilogramos</div>
            </div>
          </div>
          <div style="font-size:0.72rem;color:var(--muted);margin-top:8px;">Total histórico registrado</div>
        </div>

        <!-- Tarjeta 3: Solicitudes Cercanas -->
        <div class="card cp">
          <div style="font-size:0.68rem;font-weight:700;color:var(--green-mid);letter-spacing:0.08em;margin-bottom:8px;">
            SOLICITUDES CERCANAS
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:1.6rem;">📍</span>
            <div>
              <div style="font-size:1.75rem;font-weight:800;line-height:1;">
                <?= h($metricas['solicitudes_activas']) ?>
              </div>
              <div style="font-size:0.65rem;color:var(--muted);margin-top:2px;">disponibles ahora</div>
            </div>
          </div>
          <div style="font-size:0.72rem;color:var(--muted);margin-top:8px;">En <?= h($perfil['zona_trabajo'] ?? 'tu zona') ?></div>
        </div>

        <!-- Tarjeta 4: Nivel del Reciclador -->
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

      <!-- ── Sección: Solicitudes Cercanas Disponibles (Punto 24 y 25) ── -->
      <div id="solicitudes-list" style="margin-bottom:28px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
          <div>
            <div style="font-size:1.1rem;font-weight:700;color:var(--text);">Oportunidades de recolección destacadas</div>
            <div style="font-size:0.78rem;color:var(--muted);">Solicitudes publicadas por ciudadanos verificados cerca de tu ubicación.</div>
          </div>
          <span class="bdg bg">🟢 En tiempo real</span>
        </div>

        <!-- Tarjeta de Solicitud Destacada (#1024) -->
        <div class="card cp" style="display:flex;align-items:center;gap:18px;margin-bottom:12px;">
          <div style="width:52px;height:52px;border-radius:12px;background:#FFF4EB;display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;">
            📦
          </div>
          <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
              <span style="font-weight:700;font-size:0.92rem;color:var(--text);">Solicitud #1024</span>
              <span class="bdg bg">🟢 Disponible</span>
              <span class="bdg br" style="font-size:0.62rem;">Prioridad Alta</span>
            </div>
            <div style="font-size:0.85rem;font-weight:600;color:#C97D3A;">
              Cartón limpio y desarmado — 30 kg aprox.
            </div>
            <div style="font-size:0.75rem;color:var(--muted);display:flex;gap:16px;margin-top:4px;flex-wrap:wrap;">
              <span>📍 Suba Centro (800 metros)</span>
              <span>🕐 Horario: 2:00 p.m. – 5:00 p.m.</span>
              <span>👤 María Gómez (Ciudadana verificada)</span>
            </div>
          </div>
          <div style="display:flex;gap:8px;flex-shrink:0;">
            <button class="btn btn-o btn-sm" onclick="alert('Detalle de Solicitud #1024: Cartón en portería listo para retirar.');">
              Ver solicitud
            </button>
            <button class="btn btn-p btn-sm" onclick="alert('¡Recolección aceptada! Se agrega a tus recolecciones. Recuerda: los Ecopoints se otorgarán únicamente tras el pesaje y validación en el centro de acopio.');">
              Aceptar recolección
            </button>
          </div>
        </div>

        <!-- Tarjeta de Solicitud Secundaria (#1025) -->
        <div class="card cp" style="display:flex;align-items:center;gap:18px;">
          <div style="width:52px;height:52px;border-radius:12px;background:var(--green-light);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;">
            🧴
          </div>
          <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
              <span style="font-weight:700;font-size:0.92rem;color:var(--text);">Solicitud #1025</span>
              <span class="bdg bg">🟢 Disponible</span>
            </div>
            <div style="font-size:0.85rem;font-weight:600;color:var(--green);">
              Plástico PET y botellas compactadas — 15 kg aprox.
            </div>
            <div style="font-size:0.75rem;color:var(--muted);display:flex;gap:16px;margin-top:4px;flex-wrap:wrap;">
              <span>📍 Suba, La Colina (1.2 km)</span>
              <span>🕐 Horario: 3:00 p.m. – 6:00 p.m.</span>
            </div>
          </div>
          <div style="display:flex;gap:8px;flex-shrink:0;">
            <button class="btn btn-o btn-sm" onclick="alert('Detalle de Solicitud #1025');">Ver solicitud</button>
            <button class="btn btn-p btn-sm" onclick="alert('¡Recolección aceptada!');">Aceptar recolección</button>
          </div>
        </div>

      </div>

      <!-- ── Acceso Rápido a Centros y Beneficios ── -->
      <div class="g2">
        
        <div class="card cp">
          <div style="font-size:0.95rem;font-weight:700;margin-bottom:6px;">Centros de acopio cercanos</div>
          <p style="font-size:0.78rem;color:var(--muted);margin-bottom:14px;">
            Entrega tus recolecciones en puntos oficiales certificados para su pesaje y validación.
          </p>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#FAFCFA;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;">
            <div>
              <div style="font-weight:600;font-size:0.82rem;">EcoCentro Suba Verde</div>
              <div style="font-size:0.72rem;color:var(--muted);">Calle 139 # 101-20 • A 2.5 km</div>
            </div>
            <span class="bdg bg">Abierto</span>
          </div>
        </div>

        <div class="card cp">
          <div style="font-size:0.95rem;font-weight:700;margin-bottom:6px;">Tus recompensas disponibles</div>
          <p style="font-size:0.78rem;color:var(--muted);margin-bottom:14px;">
            Convierte tus <strong><?= number_format($metricas['ecopuntos'], 0, ',', '.') ?> Ecopoints</strong> en equipamiento y bonos reales.
          </p>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#FAFCFA;border:1px solid var(--border);border-radius:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="font-size:1.4rem;">🦺</span>
              <div>
                <div style="font-weight:600;font-size:0.82rem;">Kit de Protección y Seguridad</div>
                <div style="font-size:0.72rem;color:#087A3D;font-weight:700;">800 Ecopoints</div>
              </div>
            </div>
            <button class="btn btn-p btn-sm" onclick="alert('Canje disponible en el módulo de Beneficios.');">Canjear</button>
          </div>
        </div>

      </div>

    </div>
  </div>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
