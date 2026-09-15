/**
 * ECOPOINT — JS INTERACTIVO Y UTILIDADES FRONTEND
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Alternar Menú Lateral en pantallas móviles
    const toggleBtn = document.querySelector('.menu-toggle-btn');
    const sidebar = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Cerrar si se hace clic afuera en móvil
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }

    // 2. Calculadora en tiempo real de Ecopuntos estimados
    const pesoInput = document.getElementById('cantidad_estimada') || document.getElementById('peso_validado') || document.getElementById('cantidad_recolectada');
    const materialSelect = document.getElementById('id_material');
    const puntosPreview = document.getElementById('puntos_estimados_preview');

    function updatePointsEstimate() {
        if (!pesoInput || !puntosPreview) return;
        const peso = parseFloat(pesoInput.value) || 0;
        let factor = 10; // Default: Cartón 10 pts/kg

        if (materialSelect) {
            const selectedOpt = materialSelect.options[materialSelect.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.pts) {
                factor = parseFloat(selectedOpt.dataset.pts);
            }
        }

        const pts = Math.round(peso * factor);
        puntosPreview.textContent = '+' + pts.toLocaleString('es-CO') + ' Ecopuntos';
    }

    if (pesoInput) {
        pesoInput.addEventListener('input', updatePointsEstimate);
    }
    if (materialSelect) {
        materialSelect.addEventListener('change', updatePointsEstimate);
    }

    // 3. Modales genéricos
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-modal-target');
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.add('open');
            }
        });
    });

    const modalCloses = document.querySelectorAll('.modal-close, [data-modal-close]');
    modalCloses.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('open');
            }
        });
    });

    // Cerrar modal al hacer clic en el backdrop
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('open');
            }
        });
    });
});
