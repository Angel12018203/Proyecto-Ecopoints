<?php
/**
 * ============================================================================
 * VISTA: PANEL DESLIZABLE DE PERFIL (Profile Panel)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA VISTA?
 * Cumple con el Punto 41 de los requisitos:
 * "El perfil debe abrirse desde el Avatar del header. No colocar el perfil como
 * una opción independiente innecesaria dentro de la navegación principal."
 * 
 * Muestra:
 * - Nombre, teléfono, correo electrónico, localidad y zona habitual.
 * - Medio de transporte y disponibilidad horaria.
 * - Resumen de impacto (kg recuperados, recolecciones, Ecopoints).
 * - Botón de cierre de sesión seguro (`url('logout')`).
 */
$user = currentUser();
$p = $perfil ?? [];
$nombreCompleto = $user['nombre_completo'] ?? 'Usuario';
$correo = $user['correo'] ?? '';
$telefono = $user['telefono'] ?? 'Sin registrar';
$zona = $p['zona_trabajo'] ?? $p['localidad_nombre'] ?? 'Bogotá D.C.';
$transporte = $p['medio_transporte'] ?? 'Bicicleta';
$disponibilidad = $p['disponibilidad'] ?? 'Lunes a Sábado 7:00 a.m. - 4:00 p.m.';
$nivel = $p['nivel_calculado']['nombre'] ?? $p['nivel'] ?? 'Reciclador Activo';
$kg = number_format((float)($p['material_recuperado'] ?? 0), 1, ',', '.');
$pts = number_format((int)($p['ecopuntos'] ?? 0), 0, ',', '.');
?>
<div class="ppanel" id="ppanel">
  <!-- Cabecera del Perfil con Gradiente Eco -->
  <div class="pp-hdr">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <span style="font-weight:700;font-size:0.95rem;">Perfil del usuario</span>
      <button id="close-pp" style="background:rgba(255,255,255,0.22);border:none;cursor:pointer;width:28px;height:28px;border-radius:50%;color:#fff;font-size:0.9rem;display:flex;align-items:center;justify-content:center;" title="Cerrar">✕</button>
    </div>

    <div style="display:flex;flex-direction:column;align-items:center;text-align:center;">
      <img src="https://images.unsplash.com/photo-1546961342-ea5f60a193a5?w=160&h=160&fit=crop&auto=format" style="width:76px;height:76px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.35);margin-bottom:8px;" alt="<?= h($nombreCompleto) ?>">
      <div style="font-weight:700;font-size:1.05rem;"><?= h($nombreCompleto) ?></div>
      <div style="font-size:0.72rem;opacity:0.85;text-transform:capitalize;"><?= h($user['rol']) ?> registrado</div>
      
      <div style="margin-top:8px;">
        <span style="background:rgba(245,184,46,0.28);color:#F5B82E;font-size:0.65rem;font-weight:700;padding:4px 12px;border-radius:999px;">
          🏆 <?= h($nivel) ?>
        </span>
      </div>
      
      <div style="display:flex;align-items:center;gap:5px;margin-top:7px;font-size:0.72rem;opacity:0.9;">
        <span>📍</span><span><?= h($zona) ?></span>
      </div>
    </div>

    <!-- Métricas del Perfil -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px;">
      <div style="background:rgba(255,255,255,0.14);border-radius:10px;padding:10px;text-align:center;">
        <div style="font-size:1.25rem;font-weight:800;"><?= $kg ?></div>
        <div style="font-size:0.62rem;opacity:0.85;">kg recuperados</div>
      </div>
      <div style="background:rgba(255,255,255,0.14);border-radius:10px;padding:10px;text-align:center;">
        <div style="font-size:1.25rem;font-weight:800;"><?= $pts ?></div>
        <div style="font-size:0.62rem;opacity:0.85;">Ecopoints</div>
      </div>
    </div>
  </div>

  <!-- Cuerpo del Perfil -->
  <div class="pp-body">
    <div style="font-size:0.72rem;font-weight:700;color:var(--muted);margin-bottom:8px;padding:0 4px;letter-spacing:0.06em;">MI INFORMACIÓN</div>

    <div style="display:flex;flex-direction:column;">
      <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 6px;border-bottom:1px solid #F4F6F5;">
        <span style="font-size:0.95rem;">👤</span>
        <div style="min-width:0;">
          <div style="font-size:0.65rem;color:var(--muted);">Nombre completo</div>
          <div style="font-size:0.78rem;font-weight:600;color:var(--text);"><?= h($nombreCompleto) ?></div>
        </div>
      </div>

      <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 6px;border-bottom:1px solid #F4F6F5;">
        <span style="font-size:0.95rem;">📱</span>
        <div style="min-width:0;">
          <div style="font-size:0.65rem;color:var(--muted);">Teléfono</div>
          <div style="font-size:0.78rem;font-weight:600;color:var(--text);"><?= h($telefono) ?></div>
        </div>
      </div>

      <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 6px;border-bottom:1px solid #F4F6F5;">
        <span style="font-size:0.95rem;">✉️</span>
        <div style="min-width:0;">
          <div style="font-size:0.65rem;color:var(--muted);">Correo electrónico</div>
          <div style="font-size:0.78rem;font-weight:600;color:var(--text);word-break:break-all;"><?= h($correo) ?></div>
        </div>
      </div>

      <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 6px;border-bottom:1px solid #F4F6F5;">
        <span style="font-size:0.95rem;">📍</span>
        <div style="min-width:0;">
          <div style="font-size:0.65rem;color:var(--muted);">Zona habitual</div>
          <div style="font-size:0.78rem;font-weight:600;color:var(--text);"><?= h($zona) ?></div>
        </div>
      </div>

      <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 6px;border-bottom:1px solid #F4F6F5;">
        <span style="font-size:0.95rem;">🚲</span>
        <div style="min-width:0;">
          <div style="font-size:0.65rem;color:var(--muted);">Medio de transporte</div>
          <div style="font-size:0.78rem;font-weight:600;color:var(--text);"><?= h($transporte) ?></div>
        </div>
      </div>

      <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 6px;border-bottom:1px solid #F4F6F5;">
        <span style="font-size:0.95rem;">🕐</span>
        <div style="min-width:0;">
          <div style="font-size:0.65rem;color:var(--muted);">Disponibilidad</div>
          <div style="font-size:0.78rem;font-weight:600;color:var(--text);"><?= h($disponibilidad) ?></div>
        </div>
      </div>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin:14px 0;">

    <!-- Botón de Cerrar Sesión Seguro -->
    <a href="<?= url('logout') ?>" class="pp-opt dng" title="Cerrar sesión">
      <span style="font-size:1rem;">🚪</span>
      <span style="font-weight:600;">Cerrar sesión</span>
    </a>
  </div>
</div>
