<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($titulo ?? 'Crear cuenta — EcoPoints') ?></title>
  
  <!-- Tipografía oficial Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Estilos del Sistema de Diseño -->
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>?v=<?= time() ?>" />
  
  <!-- Favicon e Isotipo Oficial EcoPoints -->
  <link rel="icon" type="image/jpeg" href="<?= asset('img/logo-isotipo.jpg') ?>" />
  <style>
    .role-cards-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-bottom: 22px;
    }
    .role-card {
      border: 2px solid var(--border);
      border-radius: 12px;
      padding: 12px 8px;
      text-align: center;
      cursor: pointer;
      background: #fff;
      transition: all 0.18s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }
    .role-card:hover {
      border-color: var(--green);
    }
    .role-card.active {
      border-color: var(--green);
      background: var(--green-light);
      box-shadow: 0 2px 8px rgba(8, 122, 61, 0.15);
    }
    .role-icon {
      font-size: 1.6rem;
    }
    .role-title {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--text);
    }
    .role-sub {
      font-size: 0.64rem;
      color: var(--muted);
      line-height: 1.25;
    }
  </style>
</head>
<body style="background:#FAFCFA;margin:0;padding:0;">

<div style="min-height:100vh;display:flex;align-items:stretch;">

  <!-- Columna Izquierda: Identidad y Beneficios -->
  <div style="flex:0 0 380px;background:linear-gradient(160deg,#065C2D,#087A3D,#2E9B50);display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px;position:relative;overflow:hidden;color:#fff;">
    <div style="position:absolute;inset:0;opacity:0.07;font-size:14rem;display:flex;align-items:center;justify-content:center;pointer-events:none;">
      🌿
    </div>
    <div style="position:relative;text-align:center;">
      <div style="display:flex;justify-content:center;margin-bottom:24px;">
        <img src="<?= asset('img/logo-contrapuesto.jpg') ?>" alt="EcoPoints" style="width:240px;max-width:100%;height:auto;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,0.22);border:1px solid rgba(255,255,255,0.18);display:block;" onerror="this.onerror=null;this.src='<?= asset('img/logo-transparente.png') ?>';" />
      </div>
      <div style="font-size:0.95rem;line-height:1.6;opacity:0.92;margin-bottom:28px;">
        Únete a la red digital que conecta tu esfuerzo diario con beneficios tangibles.
      </div>
      <div style="background:rgba(255,255,255,0.14);border-radius:12px;padding:16px;font-size:0.8rem;text-align:left;line-height:1.5;">
        <strong>Regla de oro de EcoPoints:</strong><br>
        Recoge → Entrega en Centro → Pesaje Certificado → Validación → Recibe Ecopoints.
      </div>
    </div>
  </div>

  <!-- Columna Derecha: Formulario Dinámico Multi-Rol -->
  <div style="flex:1;overflow-y:auto;display:flex;align-items:center;justify-content:center;padding:40px 24px;background:#FAFCFA;">
    <div style="width:100%;max-width:540px;">

      <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <div>
          <div style="font-size:1.4rem;font-weight:800;color:#1A2E22;">Crea tu cuenta en EcoPoints</div>
          <div style="font-size:0.82rem;color:#4A6355;margin-top:2px;">
            Selecciona tu rol en la plataforma para desplegar los campos adecuados.
          </div>
        </div>
        <img src="<?= asset('img/logo-isotipo.jpg') ?>" alt="EcoPoint" style="height:40px;width:40px;border-radius:8px;object-fit:cover;box-shadow:0 2px 6px rgba(0,0,0,0.1);" />
      </div>

      <!-- Alerta de Errores del Servidor -->
      <?php if (!empty($error)): ?>
        <div class="alert-error"><?= $error ?></div>
      <?php endif; ?>

      <!-- Contenedor para Errores de Validación en JavaScript -->
      <div id="js-error-alert" class="alert-error" style="display:none;"></div>

      <form id="registro-form" action="<?= url('registro') ?>" method="POST">
        <?= csrf_input() ?>

        <!-- Campo Oculto para Almacenar el Rol Seleccionado -->
        <?php $selectedRole = $old['rol'] ?? 'reciclador'; ?>
        <input type="hidden" name="rol" id="selected-role" value="<?= h($selectedRole) ?>">

        <!-- Selector Visual de Rol -->
        <label class="form-label" style="margin-bottom:8px;">¿Cómo deseas registrarte?</label>
        <div class="role-cards-container">
          
          <!-- Opción 1: Reciclador (Actor Principal) -->
          <div class="role-card <?= $selectedRole === 'reciclador' ? 'active' : '' ?>" data-role="reciclador">
            <span class="role-icon">🚲</span>
            <span class="role-title">Reciclador</span>
            <span class="role-sub">Recolecto y entrego materiales</span>
          </div>

          <!-- Opción 2: Ciudadano -->
          <div class="role-card <?= $selectedRole === 'ciudadano' ? 'active' : '' ?>" data-role="ciudadano">
            <span class="role-icon">🏡</span>
            <span class="role-title">Ciudadano</span>
            <span class="role-sub">Solicito recolección en casa</span>
          </div>

          <!-- Opción 3: Centro de Acopio -->
          <div class="role-card <?= $selectedRole === 'centro_acopio' ? 'active' : '' ?>" data-role="centro_acopio">
            <span class="role-icon">🏢</span>
            <span class="role-title">Centro Acopio</span>
            <span class="role-sub">Recepción, pesaje y validación</span>
          </div>
        </div>

        <!-- ── Campos Comunes ── -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label" for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-input" placeholder="Ej: Carlos" value="<?= h($old['nombre'] ?? '') ?>" required data-sanitize="letters-only" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]{2,80}" title="Solo se permiten letras y espacios (sin números)">
          </div>
          <div class="form-group">
            <label class="form-label" for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" class="form-input" placeholder="Ej: Rodríguez" value="<?= h($old['apellido'] ?? '') ?>" required data-sanitize="letters-only" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]{2,80}" title="Solo se permiten letras y espacios (sin números)">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label" for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" class="form-input" placeholder="carlos@correo.com" value="<?= h($old['correo'] ?? '') ?>" required autocomplete="email">
          </div>
          <div class="form-group">
            <label class="form-label" for="telefono">Teléfono / Celular</label>
            <input type="tel" id="telefono" name="telefono" class="form-input" placeholder="311 555 4321" value="<?= h($old['telefono'] ?? '') ?>" required data-sanitize="numbers-only" inputmode="tel" pattern="[0-9\+\-\s\(\)]{7,20}" title="Solo se permiten números (sin letras, mínimo 7 dígitos)">
          </div>
        </div>

        <!-- Catálogo Oficial de Localidades de Bogotá (Punto 17) -->
        <div class="form-group">
          <label class="form-label" for="id_localidad">Localidad oficial (Bogotá D.C.)</label>
          <select id="id_localidad" name="id_localidad" class="form-select" required>
            <option value="">-- Selecciona una localidad --</option>
            <?php foreach ($localidades as $loc): ?>
              <?php $selected = ((int)($old['id_localidad'] ?? 11) === (int)$loc['id_localidad']) ? 'selected' : ''; ?>
              <option value="<?= h($loc['id_localidad']) ?>" <?= $selected ?>>
                <?= h($loc['numero']) ?>. <?= h($loc['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- ── Campos Específicos para RECICLADOR ── -->
        <div id="fields-reciclador" style="display: <?= $selectedRole === 'reciclador' ? 'block' : 'none' ?>;">
          <div class="form-group">
            <label class="form-label" for="zona_trabajo">Zona habitual de trabajo</label>
            <input type="text" id="zona_trabajo" name="zona_trabajo" class="form-input" placeholder="Ej: Suba Centro / Rincón" value="<?= h($old['zona_trabajo'] ?? 'Suba') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Medio de transporte</label>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
              <?php 
                $medios = ['A pie', 'Bicicleta', 'Carro de recolección', 'Motocicleta', 'Otro'];
                $medioActual = $old['medio_transporte'] ?? 'Bicicleta';
                foreach ($medios as $m):
                  $isAct = ($medioActual === $m);
              ?>
                <label style="display:flex;align-items:center;gap:6px;padding:7px 14px;border:2px solid <?= $isAct ? '#087A3D' : '#E0EBE4' ?>;border-radius:999px;cursor:pointer;font-size:0.75rem;font-weight:600;color:<?= $isAct ? '#087A3D' : '#4A6355' ?>;background:<?= $isAct ? '#EEF8EE' : '#fff' ?>;transition:all 0.15s;">
                  <input type="radio" name="medio_transporte" value="<?= h($m) ?>" style="display:none;" <?= $isAct ? 'checked' : '' ?>>
                  <?= h($m) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- ── Campos Específicos para CIUDADANO ── -->
        <div id="fields-ciudadano" style="display: <?= $selectedRole === 'ciudadano' ? 'block' : 'none' ?>;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
              <label class="form-label" for="direccion">Dirección domiciliaria</label>
              <input type="text" id="direccion" name="direccion" class="form-input" placeholder="Ej: Calle 145 # 92-30" value="<?= h($old['direccion'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="barrio">Barrio</label>
              <input type="text" id="barrio" name="barrio" class="form-input" placeholder="Ej: Suba Centro" value="<?= h($old['barrio'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="preferencias">Observaciones para entrega</label>
            <input type="text" id="preferencias" name="preferencias" class="form-input" placeholder="Ej: Material limpio y seco en bolsas verdes" value="<?= h($old['preferencias'] ?? '') ?>">
          </div>
        </div>

        <!-- ── Campos Específicos para CENTRO DE ACOPIO ── -->
        <div id="fields-centro" style="display: <?= $selectedRole === 'centro_acopio' ? 'block' : 'none' ?>;">
          <div class="form-group">
            <label class="form-label" for="nombre_centro">Nombre del centro de acopio</label>
            <input type="text" id="nombre_centro" name="nombre_centro" class="form-input" placeholder="Ej: EcoCentro Suba Verde" value="<?= h($old['nombre_centro'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="direccion_centro">Dirección del establecimiento</label>
            <input type="text" id="direccion_centro" name="direccion_centro" class="form-input" placeholder="Ej: Calle 139 # 101-20" value="<?= h($old['direccion_centro'] ?? '') ?>">
          </div>
        </div>

        <!-- Contraseñas -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px;">
          <div class="form-group">
            <label class="form-label" for="password">Contraseña</label>
            <div class="password-wrapper" style="position:relative;display:flex;align-items:center;width:100%;">
              <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required style="width:100%;padding-right:44px;">
              <button 
                type="button" 
                class="toggle-password" 
                onclick="togglePasswordVisibility(this)"
                title="Mostrar contraseña" 
                aria-label="Mostrar contraseña"
                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;cursor:pointer;padding:6px;color:#6B7280;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;outline:none;"
              >
                <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;pointer-events:none;">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;pointer-events:none;">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="password_confirm">Confirmar contraseña</label>
            <div class="password-wrapper" style="position:relative;display:flex;align-items:center;width:100%;">
              <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="••••••••" required style="width:100%;padding-right:44px;">
              <button 
                type="button" 
                class="toggle-password" 
                onclick="togglePasswordVisibility(this)"
                title="Mostrar contraseña" 
                aria-label="Mostrar contraseña"
                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;cursor:pointer;padding:6px;color:#6B7280;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;outline:none;"
              >
                <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;pointer-events:none;">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;pointer-events:none;">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Aceptación de Términos -->
        <label style="display:flex;align-items:flex-start;gap:10px;margin:16px 0 22px;cursor:pointer;">
          <input type="checkbox" id="terminos" name="terminos" checked style="margin-top:3px;accent-color:#087A3D;" required>
          <span style="font-size:0.75rem;color:#4A6355;line-height:1.45;">
            Acepto el tratamiento de mis datos personales según la <a href="javascript:void(0)" id="btn-abrir-politica" style="color:#087A3D;font-weight:700;text-decoration:underline;cursor:pointer;">política de privacidad</a> de EcoPoints.
          </span>
        </label>

        <button type="submit" class="btn btn-p btn-full" style="padding:13px;font-size:0.92rem;">
          Crear cuenta
        </button>
      </form>

      <div style="text-align:center;margin-top:22px;font-size:0.82rem;color:#4A6355;">
        ¿Ya tienes cuenta registrada? 
        <a href="<?= url('login') ?>" style="color:#087A3D;font-weight:700;text-decoration:none;">Iniciar sesión</a>
      </div>

    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: POLÍTICA DE PRIVACIDAD Y TRATAMIENTO DE DATOS (LEY 1581 DE 2012)   -->
<!-- ========================================================================= -->
<div id="modal-politica-privacidad" style="display:none;position:fixed;inset:0;background:rgba(10,35,20,0.68);backdrop-filter:blur(4px);z-index:99999;align-items:center;justify-content:center;padding:20px;box-sizing:border-box;">
  <div style="background:#FFFFFF;border-radius:20px;max-width:740px;width:100%;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 24px 60px rgba(0,0,0,0.28);border:1px solid #E0EBE4;overflow:hidden;animation:slideUpModal 0.25s ease;">
    
    <!-- Encabezado con Identidad Oficial EcoPoints -->
    <div style="background:linear-gradient(135deg,#065C2D 0%,#087A3D 55%,#15803D 100%);padding:22px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start;position:relative;flex-shrink:0;">
      <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
          <span style="background:rgba(255,255,255,0.22);padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;">
            🇨🇴 Marco Legal Colombiano
          </span>
          <span style="background:#22C55E;color:#064E22;padding:3px 9px;border-radius:20px;font-size:0.7rem;font-weight:700;">
            Ley 1581 de 2012
          </span>
        </div>
        <h3 style="font-size:1.25rem;font-weight:800;margin:0;letter-spacing:-0.3px;">
          Política de Tratamiento y Protección de Datos Personales
        </h3>
        <p style="margin:4px 0 0;font-size:0.82rem;opacity:0.92;">
          EcoPoints — Plataforma para la Gestión y Trazabilidad del Reciclaje en Bogotá D.C.
        </p>
      </div>
      <button type="button" id="btn-cerrar-politica" style="background:rgba(255,255,255,0.2);border:none;color:#fff;width:34px;height:34px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:background 0.2s;flex-shrink:0;" title="Cerrar política">
        ✕
      </button>
    </div>

    <!-- Contenido Scrollable con los Lineamientos de la Ley 1581 de 2012 -->
    <div style="padding:26px 28px;overflow-y:auto;font-size:0.86rem;color:#1A2E22;line-height:1.68;font-family:'Poppins',sans-serif;">
      
      <!-- Aviso de Autorización Previa e Informada -->
      <div style="background:#EEF8EE;border-left:4px solid #087A3D;padding:14px 18px;border-radius:0 12px 12px 0;margin-bottom:20px;font-size:0.84rem;color:#065C2D;">
        <strong>📜 Autorización Previa, Expresa e Informada:</strong><br>
        En cumplimiento de la <strong>Ley Estatutaria 1581 de 2012</strong> (Régimen General de Protección de Datos Personales) y el <strong>Decreto 1377 de 2013</strong> de la República de Colombia, al registrarte en <strong>EcoPoints</strong> autorizas libre, previa, expresa e informadamente el tratamiento de tus datos personales conforme a los lineamientos aquí establecidos.
      </div>

      <!-- 1. Responsable del Tratamiento -->
      <h4 style="font-size:0.96rem;font-weight:800;color:#087A3D;margin:18px 0 6px;display:flex;align-items:center;gap:6px;">
        <span>🏢</span> 1. Responsable del Tratamiento
      </h4>
      <p style="margin:0 0 14px;color:#4A6355;">
        El responsable del tratamiento de los datos personales es <strong>EcoPoints Colombia</strong>, entidad que gestiona la red digital de reciclaje inclusivo y economía circular en el Distrito Capital de Bogotá. Para cualquier petición, queja, reclamo o consulta relacionada con tus datos personales, puedes contactarnos a través del correo oficial: <a href="mailto:privacidad@ecopoints.com.co" style="color:#087A3D;font-weight:700;">privacidad@ecopoints.com.co</a>.
      </p>

      <!-- 2. Finalidades Legítimas del Tratamiento -->
      <h4 style="font-size:0.96rem;font-weight:800;color:#087A3D;margin:18px 0 6px;display:flex;align-items:center;gap:6px;">
        <span>🎯</span> 2. Finalidades del Tratamiento de la Información
      </h4>
      <p style="margin:0 0 10px;color:#4A6355;">
        Los datos recolectados se destinan exclusivamente a propósitos operativos, ambientales y de trazabilidad en la plataforma:
      </p>
      <ul style="margin:0 0 14px;padding-left:22px;color:#4A6355;">
        <li><strong>Creación y gestión de cuentas:</strong> Registro, autenticación y habilitación de perfiles según el rol seleccionado (Reciclador, Ciudadano o Centro de Acopio).</li>
        <li><strong>Coordinación logística y de rutas:</strong> Conectar a los recicladores de oficio con las solicitudes domiciliarias de recolección en las 20 localidades de Bogotá D.C.</li>
        <li><strong>Certificación y pesaje de materiales:</strong> Registrar el ingreso fidedigno de kilogramos en los centros de acopio y validar la entrega física.</li>
        <li><strong>Liquidación y canje de EcoPoints:</strong> Calcular, emitir y acreditar los puntos de recompensa ganados por el reciclaje efectivo, permitiendo su canje transparente por beneficios.</li>
        <li><strong>Monitoreo del impacto ambiental:</strong> Calcular métricas agregadas de mitigación de CO₂, ahorro de agua y energía, sin divulgar datos confidenciales individuales.</li>
        <li><strong>Seguridad y prevención de fraudes:</strong> Prevenir suplantaciones, verificar registros duplicados y cumplir con las órdenes de las autoridades competentes.</li>
      </ul>

      <!-- 3. Tratamiento de Datos según el Rol del Titular -->
      <h4 style="font-size:0.96rem;font-weight:800;color:#087A3D;margin:18px 0 6px;display:flex;align-items:center;gap:6px;">
        <span>👥</span> 3. Información Tratada por Cada Rol
      </h4>
      <ul style="margin:0 0 14px;padding-left:22px;color:#4A6355;">
        <li><strong>Reciclador de Oficio:</strong> Nombre, teléfono de contacto, localidad, zona habitual de recolección, medio de transporte y disponibilidad horaria.</li>
        <li><strong>Ciudadano:</strong> Nombre, correo electrónico, teléfono, localidad, dirección domiciliaria y observaciones para el retiro del material reciclable.</li>
        <li><strong>Centro de Acopio:</strong> Razón social o nombre del establecimiento, dirección física, horario de atención y tipos de materiales recibidos.</li>
      </ul>

      <!-- 4. Derechos de los Titulares (Habeas Data) -->
      <h4 style="font-size:0.96rem;font-weight:800;color:#087A3D;margin:18px 0 6px;display:flex;align-items:center;gap:6px;">
        <span>🛡️</span> 4. Derechos del Titular (Artículo 8, Ley 1581 de 2012)
      </h4>
      <p style="margin:0 0 10px;color:#4A6355;">
        En concordancia con el principio de Habeas Data constitucional, como titular tienes derecho a:
      </p>
      <ul style="margin:0 0 14px;padding-left:22px;color:#4A6355;">
        <li><strong>Conocer, actualizar y rectificar</strong> tus datos personales frente a información inexacta, incompleta o fraccionada.</li>
        <li><strong>Solicitar prueba</strong> de la autorización otorgada a EcoPoints para el tratamiento de tus datos.</li>
        <li><strong>Ser informado</strong> previa solicitud respecto del uso que se le ha dado a tus datos personales.</li>
        <li><strong>Presentar quejas</strong> ante la Superintendencia de Industria y Comercio (SIC) por infracciones a la normatividad vigente.</li>
        <li><strong>Revocar la autorización o solicitar la supresión</strong> del dato cuando no se respeten los principios, derechos y garantías constitucionales.</li>
        <li><strong>Acceder gratuitamente</strong> a tus datos personales que hayan sido objeto de tratamiento.</li>
      </ul>

      <!-- 5. Procedimiento para el Ejercicio de Derechos -->
      <h4 style="font-size:0.96rem;font-weight:800;color:#087A3D;margin:18px 0 6px;display:flex;align-items:center;gap:6px;">
        <span>✉️</span> 5. Procedimiento de Consultas y Reclamos (Art. 14 y 15, Ley 1581)
      </h4>
      <p style="margin:0 0 14px;color:#4A6355;">
        Podrás radicar tus consultas o solicitudes de supresión enviando un mensaje a <strong style="color:#087A3D;">privacidad@ecopoints.com.co</strong>. Las consultas serán atendidas en un plazo máximo de diez (10) días hábiles, y los reclamos en quince (15) días hábiles, prorrogables conforme a los términos de la ley.
      </p>

      <!-- 6. Seguridad y Confidencialidad -->
      <h4 style="font-size:0.96rem;font-weight:800;color:#087A3D;margin:18px 0 6px;display:flex;align-items:center;gap:6px;">
        <span>🔐</span> 6. Medidas de Seguridad de la Información
      </h4>
      <p style="margin:0;color:#4A6355;">
        EcoPoints implementa medidas técnicas de seguridad de vanguardia: las contraseñas se almacenan mediante algoritmos criptográficos unidireccionales (BCrypt con salting), las solicitudes web se validan mediante tokens anti-CSRF, y los experimentos de modelado y simulación operan de manera aislada sin exponer registros personales sensibles.
      </p>

    </div>

    <!-- Pie del Modal con Botón de Confirmación -->
    <div style="background:#F8FAFC;border-top:1px solid #E0EBE4;padding:16px 28px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-shrink:0;">
      <span style="font-size:0.75rem;color:#4A6355;">
        Ley 1581 de 2012 • Superintendencia de Industria y Comercio (Colombia)
      </span>
      <button type="button" id="btn-entendido-politica" class="btn btn-p" style="padding:10px 22px;background:#087A3D;border-radius:10px;font-size:0.88rem;font-weight:700;display:flex;align-items:center;gap:6px;cursor:pointer;">
        ✓ He leído y acepto la política
      </button>
    </div>

  </div>
</div>

<style>
@keyframes slideUpModal {
  from { transform: translateY(24px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>

<!-- Script interactivo de validación y visualización de contraseñas -->
<script src="<?= asset('js/auth.js') ?>?v=<?= time() ?>"></script>
</body>
</html>

