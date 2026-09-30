<?php
/**
 * ============================================================================
 * VISTA: TOPBAR (Cabecera Superior del Dashboard)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA VISTA?
 * Muestra la barra superior permanente:
 * - Botón hamburguesa para colapsar/expandir el sidebar.
 * - Píldora de Ecopoints con estrella dorada y puntos reales formateados.
 * - Campanita de notificaciones con badge y dropdown emergente.
 * - Botón de perfil con avatar y nombre del usuario logueado. Al hacer clic,
 *   despliega el panel lateral de perfil desde la derecha.
 */
$user = currentUser();
$puntos = $metricas['ecopuntos'] ?? $perfil['ecopuntos'] ?? 0;
$nombre = $user['nombre'] ?? 'Usuario';
?>
<header id="hdr">
  <!-- Botón Menú Hamburguesa -->
  <button class="icon-btn" id="hamburger" title="Alternar menú">
    <svg width="17" height="17" viewBox="0 0 20 20" fill="none">
      <path d="M3 5H17M3 10H17M3 15H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
    </svg>
  </button>

  <!-- Logotipo compacto oficial visible cuando el sidebar está colapsado -->
  <div id="hdr-logo" style="display:none;align-items:center;gap:8px;">
    <img src="<?= asset('img/logo-compacto.jpg') ?>" alt="EcoPoint" style="height:26px;width:auto;border-radius:4px;" />
    <span style="font-size:1rem;font-weight:800;"><span style="color:#087A3D;">Eco</span><span style="color:#1A2E22;">point</span></span>
  </div>

  <div class="hsp"></div>

  <!-- Píldora de Ecopoints -->
  <div class="pts-pill" title="Saldo actual de Ecopoints">
    ⭐ <span id="pts-disp"><?= number_format($puntos, 0, ',', '.') ?> pts</span>
  </div>

  <!-- Botón y Desplegable de Notificaciones -->
  <div style="position:relative;">
    <button class="icon-btn" id="notif-btn" title="Notificaciones">
      <svg width="17" height="17" viewBox="0 0 20 20" fill="none">
        <path d="M10 2C7.239 2 5 4.239 5 7V12L3 14V15H17V14L15 12V7C15 4.239 12.761 2 10 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
        <path d="M8 15C8 16.105 8.895 17 10 17C11.105 17 12 16.105 12 15" stroke="currentColor" stroke-width="1.5"/>
      </svg>
      <span class="badge-dot">3</span>
    </button>
    
    <div class="ndrop" id="ndrop">
      <div class="ndrop-h">Notificaciones</div>
      <div class="ndrop-item">
        <span style="font-size:1.1rem;">📍</span>
        <div>
          <div class="ndrop-msg">Nueva solicitud a 800 m: Cartón 30 kg</div>
          <div class="ndrop-time">Hace 5 min</div>
        </div>
      </div>
      <div class="ndrop-item">
        <span style="font-size:1.1rem;">✅</span>
        <div>
          <div class="ndrop-msg">Entrega en EcoCentro Suba validada — +285 pts</div>
          <div class="ndrop-time">Hace 1 hora</div>
        </div>
      </div>
      <div class="ndrop-item">
        <span style="font-size:1.1rem;">⭐</span>
        <div>
          <div class="ndrop-msg">Estás cerca del siguiente nivel de EcoReciclador</div>
          <div class="ndrop-time">Ayer</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Botón de Perfil con Avatar -->
  <button class="prof-btn" id="prof-btn" title="Abrir perfil de usuario">
    <img src="<?= asset('img/logo-isotipo.jpg') ?>" alt="<?= h($nombre) ?>" style="object-fit:cover;">
    <span>¡Hola, <?= h($nombre) ?>!</span>
    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
      <path d="M4 6L8 10L12 6" stroke="#4A6355" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>
</header>
