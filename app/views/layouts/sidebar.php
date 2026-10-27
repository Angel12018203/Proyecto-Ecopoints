<?php
/**
 * ============================================================================
 * VISTA: SIDEBAR (Barra Lateral de Navegación)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA VISTA?
 * Renderiza la barra lateral con ancho estándar de 252px, logotipo oficial de
 * EcoPoints, e iconos vectoriales SVG limpios.
 * 
 * Cumple estrictamente con el Punto 10 de los requerimientos:
 * - El actor principal es el reciclador.
 * - Inicio, Solicitudes cercanas, Mi ruta, Mis recolecciones, Centros de acopio,
 *   Mis Ecopoints, Beneficios, Mi impacto, Mis logros, Configuración y Ayuda.
 * - El perfil no es una opción suelta innecesaria en el menú, sino que se abre
 *   desde el avatar de la cabecera superior.
 */
$currentRole = currentRole();
$activeNav = $activeNav ?? 'inicio';
?>
<aside id="sidebar">
  <!-- Logotipo Principal Oficial -->
  <a href="<?= url($currentRole) ?>" class="s-logo" title="EcoPoints" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
    <img src="<?= asset('img/logo-transparente.png') ?>" alt="EcoPoint" style="height:32px;width:auto;object-fit:contain;border-radius:6px;" onerror="this.onerror=null;this.src='<?= asset('img/logo-isotipo.jpg') ?>';" />
    <span class="s-logo-text"><span class="eco">Eco</span><span class="pt">point</span></span>
  </a>

  <!-- Navegación Principal -->
  <nav class="s-nav">
    <?php if ($currentRole === 'reciclador'): ?>
      <a href="<?= url('reciclador') ?>" class="ni <?= $activeNav === 'inicio' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><path d="M3 9.5L10 3L17 9.5V17H13V13H7V17H3V9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
        Inicio
      </a>
      <a href="<?= url('reciclador/solicitudes') ?>" class="ni <?= $activeNav === 'solicitudes' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><path d="M10 2C7.239 2 5 4.239 5 7C5 11 10 18 10 18S15 11 15 7C15 4.239 12.761 2 10 2Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="7" r="2" stroke="currentColor" stroke-width="1.3"/></svg>
        Solicitudes cercanas
        <span class="s-badge"><?= (int)($metricas['solicitudes_activas'] ?? 0) ?></span>
      </a>
      <a href="<?= url('reciclador/recolecciones') ?>" class="ni <?= $activeNav === 'mis_recolecciones' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><path d="M4 4H16V16H4V4Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M8 9L10 11L14 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Mis recolecciones
      </a>
      <a href="<?= url('reciclador/centros') ?>" class="ni <?= $activeNav === 'centros' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><rect x="3" y="9" width="14" height="9" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M1 9L10 3L19 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="8" y="13" width="4" height="5" rx=".5" stroke="currentColor" stroke-width="1.2"/></svg>
        Centros de acopio
      </a>
      <a href="<?= url('reciclador/beneficios') ?>" class="ni <?= $activeNav === 'beneficios' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><rect x="3" y="8" width="14" height="10" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M1 8H19V11H1V8Z" stroke="currentColor" stroke-width="1.5"/><path d="M10 8V18" stroke="currentColor" stroke-width="1.5"/><path d="M10 8C10 8 8 6 8 4.5C8 3.5 8.9 3 10 3C11.1 3 12 3.5 12 4.5C12 6 10 8 10 8Z" stroke="currentColor" stroke-width="1.5"/></svg>
        Beneficios
      </a>
      <a href="<?= url('reciclador/impacto') ?>" class="ni <?= $activeNav === 'impacto' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><path d="M10 2L12 8H18L13 12L15 18L10 14L5 18L7 12L2 8H8L10 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
        Mi impacto
      </a>

    <?php elseif ($currentRole === 'ciudadano'): ?>
      <a href="<?= url('ciudadano') ?>" class="ni <?= $activeNav === 'inicio' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><path d="M3 9.5L10 3L17 9.5V17H13V13H7V17H3V9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
        Inicio
      </a>
      <a href="<?= url('ciudadano/solicitud/crear') ?>" class="ni <?= $activeNav === 'crear_solicitud' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Nueva Solicitud
      </a>
      <a href="<?= url('ciudadano/solicitudes') ?>" class="ni <?= $activeNav === 'mis_solicitudes' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><rect x="3" y="3" width="14" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M7 8H13M7 12H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        Mis Solicitudes
      </a>
    <?php elseif ($currentRole === 'administrador' || $currentRole === 'admin'): ?>
      <a href="<?= url('admin') ?>" class="ni <?= $activeNav === 'admin_dashboard' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><rect x="3" y="3" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="11" y="3" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="3" y="11" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="11" y="11" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/></svg>
        Dashboard Global
      </a>
      <a href="#selector-reciclador" class="ni">
        <svg viewBox="0 0 20 20" fill="none"><path d="M10 9C11.6569 9 13 7.65685 13 6C13 4.34315 11.6569 3 10 3C8.34315 3 7 4.34315 7 6C7 7.65685 8.34315 9 10 9Z" stroke="currentColor" stroke-width="1.5"/><path d="M4 17C4 13.6863 6.68629 11 10 11C13.3137 11 16 13.6863 16 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        Buscador Recicladores
      </a>
      <a href="#ficha-reciclador" class="ni">
        <svg viewBox="0 0 20 20" fill="none"><rect x="4" y="3" width="12" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M7 7H13M7 10H13M7 13H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        Ficha Individual
      </a>
      <a href="#modelos-ml" class="ni">
        <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M10 6V10L13 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="10" cy="10" r="2" fill="currentColor"/></svg>
        Modelos de IA & ML
      </a>
      <a href="#historial-entregas" class="ni">
        <svg viewBox="0 0 20 20" fill="none"><rect x="3" y="9" width="14" height="9" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M1 9L10 3L19 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Historial de Entregas
      </a>
      <a href="<?= url('admin/simulacion') ?>" class="ni <?= $activeNav === 'admin_simulacion' ? 'active' : '' ?>">
        <svg viewBox="0 0 20 20" fill="none"><path d="M3 3V17H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M7 13L11 9L14 12L17 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="7" r="1.5" fill="currentColor"/></svg>
        Modelado y Simulación
      </a>
      <a href="<?= url('admin') ?>#reglas-asociacion-sec" class="ni">
        <svg viewBox="0 0 20 20" fill="none"><path d="M3 17V10M8 17V4M13 17V8M18 17V13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Reglas Apriori
      </a>
    <?php else: ?>
      <a href="<?= url('centro-acopio') ?>" class="ni active">
        <svg viewBox="0 0 20 20" fill="none"><path d="M3 9.5L10 3L17 9.5V17H13V13H7V17H3V9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
        Inicio
      </a>
      <a href="#entregas" class="ni" onclick="alert('Próximamente'); return false;">
        <svg viewBox="0 0 20 20" fill="none"><rect x="3" y="9" width="14" height="9" rx="1" stroke="currentColor" stroke-width="1.5"/></svg>
        Entregas por Validar
      </a>
    <?php endif; ?>
  </nav>

  <!-- Navegación Inferior (Configuración y Ayuda) -->
  <hr class="s-div" style="margin:0;">
  <nav style="padding:6px 0;">
    <a href="#config" class="ni" onclick="alert('Módulo de Configuración'); return false;">
      <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/><path d="M10 2V4M10 16V18M2 10H4M16 10H18M4.22 4.22L5.64 5.64M14.36 14.36L15.78 15.78M15.78 4.22L14.36 5.64M5.64 14.36L4.22 15.78" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
      Configuración
    </a>
    <a href="#ayuda" class="ni" onclick="alert('Módulo de Ayuda y Preguntas Frecuentes'); return false;">
      <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M10 14H10.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 8C8 6.9 8.9 6 10 6C11.1 6 12 6.9 12 8C12 9 10 10 10 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
      Ayuda
    </a>
  </nav>
</aside>
