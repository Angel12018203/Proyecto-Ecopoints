<?php
// =====================================================================
// ECOPOINT — SIDEBAR MODULAR DINÁMICO POR ROL
// =====================================================================

$currentPath = $_SERVER['PHP_SELF'];
$rol = getCurrentRole();
$userId = getCurrentUserId();
$userName = getCurrentUserName();
$userEmail = getCurrentUserEmail();

// Consultar datos adicionales de perfil para mostrar en el sidebar
$db = Database::getConnection();
$ecopuntosSaldo = 0;
$nivelReciclador = '';

if ($rol === 'reciclador' && $userId) {
    $stmt = $db->prepare("SELECT ecopuntos, nivel FROM perfil_reciclador WHERE id_usuario = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $perfil = $stmt->fetch();
    if ($perfil) {
        $ecopuntosSaldo = (int)$perfil['ecopuntos'];
        $nivelReciclador = $perfil['nivel'];
    }
}
?>

<aside id="sidebar">
    <div class="s-logo">
        <div class="s-logo-icon">🌿</div>
        <div>
            <div class="s-logo-text"><span class="eco">Eco</span><span class="pt">point</span></div>
            <span class="s-role-badge <?= $rol ?>"><?= $rol === 'reciclador' ? '♻️ Reciclador' : '👤 Ciudadano' ?></span>
        </div>
    </div>

    <nav class="s-nav">
        <?php if ($rol === 'reciclador'): ?>
            <!-- MENÚ DEL RECICLADOR -->
            <div class="s-section-title">Operación Diaria</div>
            
            <a href="/reciclador/dashboard.php" class="ni <?= strpos($currentPath, 'dashboard.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Inicio
            </a>

            <a href="/reciclador/solicitudes.php" class="ni <?= strpos($currentPath, 'solicitudes.php') !== false || strpos($currentPath, 'solicitud_detalle.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Solicitudes Cercanas
            </a>

            <a href="/reciclador/ruta.php" class="ni <?= strpos($currentPath, 'ruta.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                Mi Ruta
            </a>

            <a href="/reciclador/recolecciones.php" class="ni <?= strpos($currentPath, 'recolecciones.php') !== false || strpos($currentPath, 'registrar_recoleccion.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                Mis Recolecciones
            </a>

            <a href="/reciclador/entregas.php" class="ni <?= strpos($currentPath, 'entregas.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                Entregas & Pesaje
            </a>

            <a href="/reciclador/centros.php" class="ni <?= strpos($currentPath, 'centros.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                Centros de Acopio
            </a>

            <div class="s-section-title">Recompensas e Impacto</div>

            <a href="/reciclador/ecopuntos.php" class="ni <?= strpos($currentPath, 'ecopuntos.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                Mis Ecopuntos
            </a>

            <a href="/reciclador/beneficios.php" class="ni <?= strpos($currentPath, 'beneficios.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                Beneficios & Canjes
            </a>

            <a href="/reciclador/impacto.php" class="ni <?= strpos($currentPath, 'impacto.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                Mi Impacto
            </a>

            <a href="/reciclador/logros.php" class="ni <?= strpos($currentPath, 'logros.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Mis Logros
            </a>

            <div class="s-section-title">Cuenta</div>

            <a href="/reciclador/perfil.php" class="ni <?= strpos($currentPath, 'perfil.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Mi Perfil
            </a>

        <?php else: ?>
            <!-- MENÚ DEL CIUDADANO -->
            <div class="s-section-title">Gestión de Residuos</div>

            <a href="/ciudadano/dashboard.php" class="ni <?= strpos($currentPath, 'dashboard.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Inicio
            </a>

            <a href="/ciudadano/solicitar.php" class="ni <?= strpos($currentPath, 'solicitar.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                Solicitar Recolección
            </a>

            <a href="/ciudadano/solicitudes.php" class="ni <?= strpos($currentPath, 'solicitudes.php') !== false || strpos($currentPath, 'detalle_solicitud.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Mis Solicitudes
            </a>

            <a href="/ciudadano/historial.php" class="ni <?= strpos($currentPath, 'historial.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                Historial
            </a>

            <div class="s-section-title">Cuenta</div>

            <a href="/ciudadano/perfil.php" class="ni <?= strpos($currentPath, 'perfil.php') !== false ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Mi Perfil
            </a>
        <?php endif; ?>

        <a href="/logout.php" class="ni" style="color: #E53E3E; margin-top: 10px;">
            <svg viewBox="0 0 24 24" style="stroke: #E53E3E;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            Cerrar Sesión
        </a>
    </nav>

    <div class="s-user-footer">
        <div class="s-user-info">
            <div class="s-user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
            <div style="min-width: 0;">
                <div class="s-user-name" title="<?= h($userName) ?>"><?= h($userName) ?></div>
                <div class="s-user-email" title="<?= h($userEmail) ?>"><?= h($userEmail) ?></div>
            </div>
        </div>
        <a href="/logout.php" title="Cerrar sesión" style="color: var(--muted); padding: 4px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </a>
    </div>
</aside>
