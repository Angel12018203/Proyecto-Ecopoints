/**
 * ============================================================================
 * JAVASCRIPT: VALIDACIÓN Y COMPORTAMIENTO DE AUTENTICACIÓN (auth.js)
 * ============================================================================
 * 
 * Funcionalidades:
 * 1. Selector dinámico de roles en registro (Reciclador, Ciudadano, Centro de Acopio).
 * 2. Visualizador y alternador de contraseñas seguro (Mostrar / Ocultar Contraseña).
 * 3. Sanitización y filtrado estricto en tiempo real:
 *    - Campos de solo letras (nombre, apellido): impide y filtra números y símbolos extraños.
 *    - Campos de teléfono: impide y filtra letras, permitiendo solo dígitos y formato telefónico.
 * 4. Validación exhaustiva pre-envío del formulario de registro.
 */

// Función global única para alternar visibilidad de contraseña
window.togglePasswordVisibility = function(btn) {
    if (!btn) return;
    const wrapper = btn.closest('.password-wrapper');
    if (!wrapper) return;
    const input = wrapper.querySelector('input');
    if (!input) return;

    const isPassword = (input.type === 'password');
    input.type = isPassword ? 'text' : 'password';

    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');

    if (eyeOpen && eyeClosed) {
        if (isPassword) {
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
            btn.setAttribute('aria-label', 'Ocultar contraseña');
            btn.title = 'Ocultar contraseña';
        } else {
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
            btn.setAttribute('aria-label', 'Mostrar contraseña');
            btn.title = 'Mostrar contraseña';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {

    // 1. SELECTOR DINÁMICO DE ROL EN REGISTRO
    const roleCards = document.querySelectorAll('.role-card');
    const roleInput = document.getElementById('selected-role');
    const fieldsReciclador = document.getElementById('fields-reciclador');
    const fieldsCiudadano = document.getElementById('fields-ciudadano');
    const fieldsCentro = document.getElementById('fields-centro');

    if (roleCards.length && roleInput) {
        roleCards.forEach(card => {
            card.addEventListener('click', () => {
                const selectedRole = card.dataset.role;
                roleInput.value = selectedRole;

                roleCards.forEach(c => {
                    c.classList.remove('active');
                    c.style.borderColor = '#E0EBE4';
                    c.style.background = '#fff';
                    c.style.boxShadow = 'none';
                });

                card.classList.add('active');
                card.style.borderColor = '#087A3D';
                card.style.background = '#EEF8EE';
                card.style.boxShadow = '0 2px 8px rgba(8, 122, 61, 0.15)';

                if (fieldsReciclador) fieldsReciclador.style.display = (selectedRole === 'reciclador') ? 'block' : 'none';
                if (fieldsCiudadano) fieldsCiudadano.style.display = (selectedRole === 'ciudadano') ? 'block' : 'none';
                if (fieldsCentro) fieldsCentro.style.display = (selectedRole === 'centro_acopio') ? 'block' : 'none';
            });
        });
    }

    // 2. SELECTOR VISUAL DE MEDIO DE TRANSPORTE (Reciclador)
    const transportRadios = document.querySelectorAll('input[name="medio_transporte"]');
    if (transportRadios.length) {
        transportRadios.forEach(radio => {
            radio.parentElement.addEventListener('click', function() {
                transportRadios.forEach(r => {
                    r.parentElement.style.borderColor = '#E0EBE4';
                    r.parentElement.style.background = '#fff';
                    r.parentElement.style.color = '#4A6355';
                });
                this.style.borderColor = '#087A3D';
                this.style.background = '#EEF8EE';
                this.style.color = '#087A3D';
            });
        });
    }

    // 3. SANITIZACIÓN EN TIEMPO REAL: LETRAS VS NÚMEROS
    // A. Campos que solo deben aceptar letras y espacios (Nombre, Apellido)
    const letterOnlyInputs = document.querySelectorAll('#nombre, #apellido, [data-sanitize="letters-only"]');
    letterOnlyInputs.forEach(input => {
        // Bloquear teclas numéricas directamente
        input.addEventListener('keypress', (e) => {
            if (/\d/.test(e.key)) {
                e.preventDefault();
            }
        });
        // Filtrar al pegar o escribir
        input.addEventListener('input', () => {
            // Remueve cualquier dígito o carácter no alfabético/espacio
            input.value = input.value.replace(/[0-9]/g, '');
        });
    });

    // B. Campos que solo deben aceptar números y formato telefónico (Teléfono)
    const phoneInputs = document.querySelectorAll('#telefono, [data-sanitize="numbers-only"]');
    phoneInputs.forEach(input => {
        // Bloquear teclas alfabéticas
        input.addEventListener('keypress', (e) => {
            if (/[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]/.test(e.key)) {
                e.preventDefault();
            }
        });
        // Filtrar caracteres no numéricos excepto +, -, paréntesis y espacios
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9+\-()\s]/g, '');
        });
    });

    // 4. CONFIGURACIÓN DEL BOTÓN TOGGLE PASSWORD (Delegación de eventos segura)
    // No usamos onclick doble; vinculamos listener seguro evitando ejecuciones dobles
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(btn => {
        // Eliminar atributo onclick si viene en el HTML para evitar ejecución doble
        btn.removeAttribute('onclick');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            window.togglePasswordVisibility(btn);
        });
    });

    // 5. VALIDACIÓN PRE-ENVÍO DEL FORMULARIO DE REGISTRO
    const regForm = document.getElementById('registro-form');
    if (regForm) {
        regForm.addEventListener('submit', (e) => {
            const nombre = document.getElementById('nombre')?.value.trim() || '';
            const apellido = document.getElementById('apellido')?.value.trim() || '';
            const correo = document.getElementById('correo')?.value.trim() || '';
            const telefono = document.getElementById('telefono')?.value.trim() || '';
            const pass = document.getElementById('password')?.value || '';
            const passConf = document.getElementById('password_confirm')?.value || '';
            const terms = document.getElementById('terminos')?.checked;
            const errorDiv = document.getElementById('js-error-alert');

            let errorMsg = '';

            // Validar que nombre y apellido no tengan números y solo letras
            if (!nombre || /\d/.test(nombre) || !/^[\p{L}\s'-]+$/u.test(nombre)) {
                errorMsg = 'El nombre no puede contener números ni caracteres especiales.';
            } else if (!apellido || /\d/.test(apellido) || !/^[\p{L}\s'-]+$/u.test(apellido)) {
                errorMsg = 'El apellido no puede contener números ni caracteres especiales.';
            } else if (!correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                errorMsg = 'Por favor ingresa un correo electrónico válido.';
            } else if (!telefono || /[a-zA-Z]/.test(telefono) || telefono.replace(/\D/g, '').length < 7) {
                errorMsg = 'El teléfono no puede contener letras y debe tener al menos 7 dígitos.';
            } else if (pass.length < 6) {
                errorMsg = 'La contraseña debe tener al menos 6 caracteres.';
            } else if (pass !== passConf) {
                errorMsg = 'Las contraseñas no coinciden. Por favor verifícalas.';
            } else if (!terms) {
                errorMsg = 'Debes aceptar la política de tratamiento de datos personales para continuar.';
            }

            if (errorMsg) {
                e.preventDefault();
                if (errorDiv) {
                    errorDiv.innerHTML = '⚠️ ' + errorMsg;
                    errorDiv.style.display = 'block';
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    alert(errorMsg);
                }
            } else if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        });
    }

    // 5. CONTROL DEL MODAL DE POLÍTICA DE TRATAMIENTO DE DATOS (LEY 1581 DE 2012)
    const btnAbrirPolitica = document.getElementById('btn-abrir-politica');
    const modalPolitica = document.getElementById('modal-politica-privacidad');
    const btnCerrarPolitica = document.getElementById('btn-cerrar-politica');
    const btnAceptarPolitica = document.getElementById('btn-entendido-politica');
    const checkboxTerminos = document.getElementById('terminos');

    if (btnAbrirPolitica && modalPolitica) {
        const abrirModalPolitica = (e) => {
            if (e) e.preventDefault();
            modalPolitica.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        const cerrarModalPolitica = () => {
            modalPolitica.style.display = 'none';
            document.body.style.overflow = '';
        };

        btnAbrirPolitica.addEventListener('click', abrirModalPolitica);

        if (btnCerrarPolitica) {
            btnCerrarPolitica.addEventListener('click', cerrarModalPolitica);
        }

        if (btnAceptarPolitica) {
            btnAceptarPolitica.addEventListener('click', () => {
                if (checkboxTerminos) {
                    checkboxTerminos.checked = true;
                }
                cerrarModalPolitica();
            });
        }

        modalPolitica.addEventListener('click', (e) => {
            if (e.target === modalPolitica) {
                cerrarModalPolitica();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalPolitica.style.display === 'flex') {
                cerrarModalPolitica();
            }
        });
    }
});

