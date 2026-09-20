/**
 * ============================================================================
 * JAVASCRIPT: COMPORTAMIENTOS DEL PANEL Y NAVEGACIÓN (app.js)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE SCRIPT?
 * Controla los componentes dinámicos de la interfaz de usuario:
 * - Colapsar y expandir la barra lateral (Sidebar) con el botón hamburguesa.
 * - Abrir y cerrar el menú desplegable de notificaciones.
 * - Deslizar hacia adentro el panel lateral de perfil con las estadísticas del usuario.
 * - Cierre automático de menús al hacer clic por fuera del elemento.
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. CONTROL DEL BOTÓN HAMBURGUESA Y SIDEBAR
    const hamburgerBtn = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const hdrLogo = document.getElementById('hdr-logo');

    if (hamburgerBtn && sidebar) {
        hamburgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('collapsed');
            if (hdrLogo) {
                const isCollapsed = sidebar.classList.contains('collapsed');
                hdrLogo.style.display = isCollapsed ? 'flex' : 'none';
            }
        });
    }

    // 2. CONTROL DEL MENÚ DESPLEGABLE DE NOTIFICACIONES
    const notifBtn = document.getElementById('notif-btn');
    const notifDrop = document.getElementById('ndrop');

    if (notifBtn && notifDrop) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDrop.classList.toggle('open');
            // Si el panel de perfil está abierto, lo cerramos
            if (profilePanel) profilePanel.classList.remove('open');
        });
    }

    // 3. CONTROL DEL PANEL DESLIZABLE DE PERFIL
    const profileBtn = document.getElementById('prof-btn');
    const profilePanel = document.getElementById('ppanel');
    const closeProfileBtn = document.getElementById('close-pp');

    if (profileBtn && profilePanel) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profilePanel.classList.toggle('open');
            if (notifDrop) notifDrop.classList.remove('open');
        });

        if (closeProfileBtn) {
            closeProfileBtn.addEventListener('click', () => {
                profilePanel.classList.remove('open');
            });
        }

        // Evitar que hacer clic dentro del panel lo cierre
        profilePanel.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    // 4. CIERRE AL HACER CLIC FUERA DE LOS MENÚS
    document.addEventListener('click', () => {
        if (notifDrop && notifDrop.classList.contains('open')) {
            notifDrop.classList.remove('open');
        }
        if (profilePanel && profilePanel.classList.contains('open')) {
            profilePanel.classList.remove('open');
        }
    });
});
