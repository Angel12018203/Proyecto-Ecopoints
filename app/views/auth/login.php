<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($titulo ?? 'Iniciar sesión — EcoPoints') ?></title>
  
  <!-- Tipografía oficial Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Estilos del Sistema de Diseño -->
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>?v=<?= time() ?>" />
  
  <!-- Favicon e Isotipo Oficial EcoPoints -->
  <link rel="icon" type="image/jpeg" href="<?= asset('img/logo-isotipo.jpg') ?>" />
</head>
<body style="background:#FAFCFA;margin:0;padding:0;">

<!-- Contenedor Dividido de Alta Fidelidad Figma -->
<div style="min-height:100vh;display:flex;align-items:stretch;">

  <!-- Columna Izquierda: Decoración e Identidad de Marca -->
  <div style="flex:1;background:linear-gradient(160deg,#065C2D,#087A3D,#2E9B50);display:flex;flex-direction:column;justify-content:center;align-items:center;padding:48px;position:relative;overflow:hidden;color:#fff;">
    <!-- Marca de agua vectorial -->
    <div style="position:absolute;inset:0;opacity:0.07;font-size:16rem;display:flex;align-items:center;justify-content:center;pointer-events:none;">
      ♻️
    </div>

    <div style="position:relative;max-width:440px;">
      <!-- Logotipo Oficial: Versión Contrapuesta sobre fondo verde (Grande y Centrado) -->
      <div style="display:flex;justify-content:center;margin-bottom:34px;">
        <img src="<?= asset('img/logo-contrapuesto.jpg') ?>" alt="EcoPoints" style="width:260px;max-width:100%;height:auto;border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.24);border:1px solid rgba(255,255,255,0.18);display:block;" onerror="this.onerror=null;this.src='<?= asset('img/logo-transparente.png') ?>';" />
      </div>

      <!-- Frase Central del Negocio -->
      <div style="font-size:1.4rem;font-weight:700;line-height:1.45;margin-bottom:36px;">
        "Tu trabajo transforma residuos en oportunidades."
      </div>

      <!-- Beneficios Clave para el Reciclador -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="display:flex;align-items:center;gap:12px;font-size:0.92rem;opacity:0.95;">
          <span>✅</span><span>Encuentra solicitudes de recolección cercanas</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;font-size:0.92rem;opacity:0.95;">
          <span>✅</span><span>Organiza tus recorridos y optimiza rutas</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;font-size:0.92rem;opacity:0.95;">
          <span>✅</span><span>Registra entregas y acumula Ecopuntos validados</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;font-size:0.92rem;opacity:0.95;">
          <span>✅</span><span>Canjea beneficios reales por tu esfuerzo diario</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Columna Derecha: Formulario de Inicio de Sesión -->
  <div style="width:480px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px;background:#FAFCFA;flex-shrink:0;">
    <div style="width:100%;max-width:360px;">
      
      <!-- Encabezado del Formulario -->
      <div style="text-align:center;margin-bottom:28px;">
        <img src="<?= asset('img/logo-horizontal.jpg') ?>" alt="EcoPoint" style="height:48px;width:auto;object-fit:contain;margin-bottom:6px;" onerror="this.style.display='none';" />
        <div style="font-size:0.85rem;color:#4A6355;font-weight:500;">
          Herramienta profesional de recolección
        </div>
      </div>

      <div style="font-size:1.15rem;font-weight:700;margin-bottom:20px;color:#1A2E22;">
        Iniciar sesión
      </div>

      <!-- Mensajes Flash de Error o Éxito -->
      <?php if (!empty($error)): ?>
        <div class="alert-error"><?= $error ?></div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert-success"><?= h($success) ?></div>
      <?php endif; ?>

      <!-- Formulario Seguro con CSRF -->
      <form action="<?= url('login') ?>" method="POST">
        <?= csrf_input() ?>

        <div class="form-group">
          <label class="form-label" for="correo">Correo electrónico</label>
          <input 
            type="email" 
            id="correo" 
            name="correo" 
            class="form-input" 
            placeholder="ejemplo@ecopoint.app" 
            value="<?= h($_POST['correo'] ?? 'reciclador@ecopoint.app') ?>" 
            required 
            autocomplete="email"
          />
        </div>

        <div class="form-group" style="margin-bottom:8px;">
          <label class="form-label" for="password">Contraseña</label>
          <div class="password-wrapper" style="position:relative;display:flex;align-items:center;width:100%;">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-input" 
              placeholder="••••••••" 
              value="Carlos123*" 
              required 
              autocomplete="current-password"
              style="width:100%;padding-right:44px;"
            />
            <button 
              type="button" 
              class="toggle-password" 
              onclick="togglePasswordVisibility(this)"
              title="Mostrar contraseña" 
              aria-label="Mostrar contraseña"
              style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;cursor:pointer;padding:6px;color:#6B7280;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;outline:none;"
            >
              <!-- Ojo abierto (mostrar) -->
              <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;pointer-events:none;">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <!-- Ojo cerrado (ocultar) -->
              <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;pointer-events:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
        </div>

        <div style="text-align:right;margin-bottom:22px;">
          <a href="#" style="font-size:0.75rem;color:#087A3D;text-decoration:none;font-weight:600;" onclick="alert('Funcionalidad de recuperación de contraseña preparada para la siguiente fase.'); return false;">
            ¿Olvidaste tu contraseña?
          </a>
        </div>

        <button type="submit" class="btn btn-p btn-full" style="padding:13px;font-size:0.92rem;">
          Iniciar sesión
        </button>
      </form>

      <!-- Enlace a Registro -->
      <div style="text-align:center;margin-top:24px;font-size:0.82rem;color:#4A6355;">
        ¿Eres nuevo en EcoPoints?<br>
        <a href="<?= url('registro') ?>" style="display:inline-block;color:#087A3D;font-weight:700;text-decoration:none;font-size:0.88rem;margin-top:4px;">
          Crear cuenta →
        </a>
      </div>

      <!-- Credenciales de Prueba Rápida -->
      <div style="margin-top:28px;padding:12px;background:#EEF8EE;border-radius:10px;border:1px dashed #087A3D;font-size:0.7rem;color:#1A2E22;">
        <strong>🔑 Cuentas demo para probar:</strong><br>
        • <strong>Reciclador:</strong> reciclador@ecopoint.app | Carlos123*<br>
        • <strong>Ciudadano:</strong> ciudadano@ecopoint.app | Maria123*<br>
        • <strong>Centro Acopio:</strong> centro@ecopoint.app | Centro123*<br>
        • <strong>Administrador:</strong> admin@ecopoint.app | Admin123*
      </div>

    </div>
  </div>

</div>

<!-- Script interactivo de autenticación y visualización de contraseñas -->
<script src="<?= asset('js/auth.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
