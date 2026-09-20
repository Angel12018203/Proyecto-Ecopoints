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
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>" />
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
      <div style="font-size:1.8rem;font-weight:800;margin-bottom:12px;">EcoPoints</div>
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

      <div style="margin-bottom:20px;">
        <div style="font-size:1.4rem;font-weight:800;color:#1A2E22;">Crea tu cuenta en EcoPoints</div>
        <div style="font-size:0.82rem;color:#4A6355;margin-top:2px;">
          Selecciona tu rol en la plataforma para desplegar los campos adecuados.
        </div>
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
            <input type="text" id="nombre" name="nombre" class="form-input" placeholder="Ej: Carlos" value="<?= h($old['nombre'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" class="form-input" placeholder="Ej: Rodríguez" value="<?= h($old['apellido'] ?? '') ?>" required>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label" for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" class="form-input" placeholder="carlos@correo.com" value="<?= h($old['correo'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="telefono">Teléfono / Celular</label>
            <input type="tel" id="telefono" name="telefono" class="form-input" placeholder="311 555 4321" value="<?= h($old['telefono'] ?? '') ?>" required>
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
            <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="password_confirm">Confirmar contraseña</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="••••••••" required>
          </div>
        </div>

        <!-- Aceptación de Términos -->
        <label style="display:flex;align-items:flex-start;gap:10px;margin:16px 0 22px;cursor:pointer;">
          <input type="checkbox" id="terminos" name="terminos" checked style="margin-top:3px;accent-color:#087A3D;" required>
          <span style="font-size:0.75rem;color:#4A6355;line-height:1.45;">
            Acepto el tratamiento de mis datos personales según la <a href="#" style="color:#087A3D;font-weight:600;">política de privacidad</a> de EcoPoints.
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

<!-- Script interactivo de validación y alternancia de campos -->
<script src="<?= asset('js/auth.js') ?>"></script>
</body>
</html>
