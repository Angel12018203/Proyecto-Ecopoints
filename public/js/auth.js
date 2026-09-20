/**
 * ============================================================================
 * JAVASCRIPT: VALIDACIÓN Y COMPORTAMIENTO DE AUTENTICACIÓN (auth.js)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE SCRIPT?
 * 1. Gestiona la alternancia dinámica del formulario de registro:
 *    Al hacer clic en "¿Cómo deseas registrarte?" (Ciudadano, Reciclador o Centro de Acopio),
 *    muestra u oculta los campos pertinentes sin recargar la página.
 * 2. Realiza validaciones inmediatas en el navegador (UX ágil):
 *    - Comprueba que las contraseñas coincidan antes de enviar.
 *    - Comprueba longitud mínima de 6 caracteres.
 *    - Comprueba que se haya aceptado la política de tratamiento de datos.
 * 
 * NOTA DE SEGURIDAD:
 * Las validaciones de JavaScript son exclusivas para comodidad visual del usuario.
 * Todas las validaciones se repiten de forma obligatoria e inquebrantable
 * en el servidor PHP (AuthController.php).
 */

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

                // Actualizar valor en el campo oculto
                roleInput.value = selectedRole;

                // Actualizar estilos activos de las tarjetas
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

                // Mostrar u ocultar bloques de campos condicionales
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

    // 3. VALIDACIÓN PRE-ENVÍO DEL FORMULARIO DE REGISTRO
    const regForm = document.getElementById('registro-form');
    if (regForm) {
        regForm.addEventListener('submit', (e) => {
            const pass = document.getElementById('password')?.value || '';
            const passConf = document.getElementById('password_confirm')?.value || '';
            const terms = document.getElementById('terminos')?.checked;
            const errorDiv = document.getElementById('js-error-alert');

            let errorMsg = '';

            if (pass.length < 6) {
                errorMsg = 'La contraseña debe tener al menos 6 caracteres.';
            } else if (pass !== passConf) {
                errorMsg = 'Las contraseñas no coinciden. Verifícalas.';
            } else if (!terms) {
                errorMsg = 'Debes aceptar la política de tratamiento de datos personales para continuar.';
            }

            if (errorMsg) {
                e.preventDefault(); // Detener el envío del formulario
                if (errorDiv) {
                    errorDiv.innerHTML = errorMsg;
                    errorDiv.style.display = 'block';
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    alert(errorMsg);
                }
            }
        });
    }
});
