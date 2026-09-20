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
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>" />
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
      <!-- Logotipo Blanco -->
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
        <svg width="42" height="42" viewBox="0 0 30 30" fill="none">
          <rect width="30" height="30" rx="9" fill="white"/>
          <path d="M15 5C8 9.5 8 17 8 17C8 20.866 11.134 24 15 24C18.866 24 22 20.866 22 17C22 9.5 15 5 15 5Z" fill="#087A3D" opacity=".92"/>
          <path d="M15 9V22" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
          <path d="M11.5 14L15 10.5L18.5 14" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span style="font-size:1.8rem;font-weight:800;letter-spacing:-0.5px;">Eco<span style="opacity:0.9;">point</span></span>
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
      <div style="text-align:center;margin-bottom:32px;">
        <div style="font-size:1.8rem;font-weight:800;margin-bottom:4px;">
          <span style="color:#087A3D;">Eco</span><span style="color:#1A2E22;">point</span>
        </div>
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
          <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-input" 
            placeholder="••••••••" 
            value="Carlos123*" 
            required 
            autocomplete="current-password"
          />
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
        • <strong>Centro Acopio:</strong> centro@ecopoint.app | Centro123*
      </div>

    </div>
  </div>

</div>

</body>
</html>
