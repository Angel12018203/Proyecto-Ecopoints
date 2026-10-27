<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL ADMINISTRADOR (Reconstrucción Integral y Minería ML)
 * ============================================================================
 * 
 * Interfaz administrativa de EcoPoints:
 * - Resumen global del ecosistema y KPIs consolidados de la base de datos.
 * - Selector universal y autocompletado en vivo de recicladores.
 * - Ficha individual interactiva con perfil, métricas e historial de entregas.
 * - Integración verificable de los 4 modelos de minería de datos (Joblib / Python).
 * - Tratamiento diferenciado de recicladores nuevos sin actividad previa.
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';

$csrfToken = csrf_token();
$metaML = $metadatosML['modelos'] ?? [];
$infoReg = $metaML['regresion_puntos'] ?? [];
$infoClf = $metaML['clasificacion_actividad'] ?? [];
$infoKm  = $metaML['segmentacion_recicladores'] ?? [];
$infoIso = $metaML['deteccion_anomalias'] ?? [];
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg pg-wide">

      <!-- ===================================================================== -->
      <!-- CABECERA PRINCIPAL: IDENTIDAD ADMINISTRATIVA & ESTADO DE MODELOS     -->
      <!-- ===================================================================== -->
      <section id="resumen-global" style="background: linear-gradient(135deg, #065C2D 0%, #087A3D 50%, #0A8F47 100%); border-radius: var(--r-xl); padding: 26px 32px; margin-bottom: 24px; color: #fff; box-shadow: 0 10px 28px rgba(8, 122, 61, 0.22); position: relative; overflow: hidden;">
        <div style="position:absolute; right:-20px; bottom:-30px; font-size:12rem; opacity:0.06; pointer-events:none;">🌱</div>
        
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:18px; position:relative; z-index:2;">
          <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
              <span style="font-size:0.75rem; font-weight:700; background:rgba(255,255,255,0.22); padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">
                🛡️ Rol: Administrador General
              </span>
              <span style="font-size:0.75rem; font-weight:700; background:#22C55E; color:#064E22; padding:4px 10px; border-radius:20px;">
                ● Sistema Operativo 100%
              </span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0; color: #fff; letter-spacing:-0.5px;">
              Panel de Control y Minería Predictiva EcoPoints
            </h1>
            <p style="margin: 6px 0 0; opacity: 0.92; font-size: 0.94rem; max-width: 680px;">
              Supervisión en tiempo real del ecosistema de reciclaje en Bogotá, análisis individualizado por reciclador y aplicación de modelos estadísticos y de Machine Learning sobre datos reales.
            </p>
          </div>

          <!-- Estado de los 4 Modelos ML -->
          <div style="background: rgba(0, 0, 0, 0.22); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.18); border-radius: var(--r-lg); padding: 14px 18px; min-width: 280px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; opacity:0.9;">
                🤖 Modelos Serializados (.joblib)
              </span>
              <span style="font-size:0.72rem; background:rgba(255,255,255,0.25); padding:2px 8px; border-radius:12px; font-weight:600;">v1.0</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:5px; font-size:0.8rem;">
              <div style="display:flex; justify-content:space-between;">
                <span>Regresión (RandomForest):</span>
                <strong style="color:#86EFAC;">R² <?= number_format($infoReg['metricas_validacion']['R2'] ?? 0.39, 2) ?> · MAE <?= number_format($infoReg['metricas_validacion']['MAE'] ?? 75.89, 1) ?></strong>
              </div>
              <div style="display:flex; justify-content:space-between;">
                <span>Clasificación (LogReg):</span>
                <strong style="color:#86EFAC;">Acc <?= number_format($infoClf['metricas_validacion']['Accuracy'] ?? 84.66, 1) ?>%</strong>
              </div>
              <div style="display:flex; justify-content:space-between;">
                <span>Segmentación (K-Means):</span>
                <strong style="color:#86EFAC;">k=3 (Silh <?= number_format($infoKm['metricas_validacion']['Silhouette_Score'] ?? 0.32, 2) ?>)</strong>
              </div>
              <div style="display:flex; justify-content:space-between;">
                <span>Anomalías (IsolationForest):</span>
                <strong style="color:#86EFAC;">Contam 3%</strong>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===================================================================== -->
      <!-- SECCIÓN 1: RESUMEN GENERAL DE ECOPOINTS (KPIS GLOBALES Y MATERIALES)  -->
      <!-- ===================================================================== -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px;">
        <div>
          <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text); margin: 0;">
            1. Resumen General del Ecosistema
          </h2>
          <p style="font-size: 0.85rem; color: var(--muted); margin: 2px 0 0;">
            Indicadores calculados con datos reales consolidados de la base de datos de producción.
          </p>
        </div>
      </div>

      <!-- Tarjetas KPI Globales -->
      <div class="g4" style="margin-bottom: 24px;">
        
        <!-- KPI 1: Recicladores Registrados y Caracterizados -->
        <div class="card" style="padding: 22px; border-left: 4px solid #087A3D; box-shadow: var(--shadow); transition: transform 0.2s ease;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.8rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.4px;">Recicladores Activos</span>
            <span style="background:var(--green-light); color:var(--green); width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">👥</span>
          </div>
          <div style="font-size: 2rem; font-weight: 800; color: var(--text); margin: 10px 0 4px;">
            <?= number_format($kpis['total_recicladores']) ?>
          </div>
          <span style="font-size:0.78rem; color:#087A3D; font-weight:600; display:flex; align-items:center; gap:4px;">
            <span>✓</span> Caracterizados en 20 localidades
          </span>
        </div>

        <!-- KPI 2: Total Entregas Validadas -->
        <div class="card" style="padding: 22px; border-left: 4px solid #2E9B50; box-shadow: var(--shadow); transition: transform 0.2s ease;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.8rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.4px;">Entregas Validadas</span>
            <span style="background:var(--green-light); color:#2E9B50; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">⚖️</span>
          </div>
          <div style="font-size: 2rem; font-weight: 800; color: var(--text); margin: 10px 0 4px;">
            <?= number_format($kpis['total_entregas']) ?>
          </div>
          <span style="font-size:0.78rem; color:var(--muted); font-weight:500;">
            En <?= number_format($kpis['total_centros']) ?> centros de acopio autorizados
          </span>
        </div>

        <!-- KPI 3: Kilogramos Recolectados -->
        <div class="card" style="padding: 22px; border-left: 4px solid #00BFA5; box-shadow: var(--shadow); transition: transform 0.2s ease;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.8rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.4px;">Material Recuperado</span>
            <span style="background:#E0F7F4; color:#00796B; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">📦</span>
          </div>
          <div style="font-size: 2rem; font-weight: 800; color: var(--text); margin: 10px 0 4px;">
            <?= number_format($kpis['total_kg'], 1) ?> <span style="font-size:1rem; font-weight:600; color:var(--muted);">kg</span>
          </div>
          <span style="font-size:0.78rem; color:#00796B; font-weight:700;">
            ≈ <?= number_format($kpis['total_kg'] / 1000, 2) ?> Toneladas evitadas en relleno
          </span>
        </div>

        <!-- KPI 4: Ecopuntos y Canjes -->
        <div class="card" style="padding: 22px; border-left: 4px solid #FF9800; box-shadow: var(--shadow); transition: transform 0.2s ease;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.8rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.4px;">Puntos & Canjes</span>
            <span style="background:#FFF3E0; color:#E65100; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">🎁</span>
          </div>
          <div style="font-size: 2rem; font-weight: 800; color: var(--text); margin: 10px 0 4px;">
            <?= number_format($kpis['total_puntos']) ?> <span style="font-size:0.95rem; font-weight:600; color:var(--muted);">pts</span>
          </div>
          <span style="font-size:0.78rem; color:#E65100; font-weight:700;">
            <?= number_format($kpis['total_canjes']) ?> beneficios canjeados por recicladores
          </span>
        </div>

      </div>

      <!-- Desglose Global de Materiales Recuperados -->
      <div class="card" style="padding: 24px; margin-bottom: 28px; box-shadow: var(--shadow);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
          <div>
            <h3 style="font-size:1.1rem; font-weight:700; margin:0; color:var(--text);">
              Distribución de Materiales Recuperados (Plataforma General)
            </h3>
            <p style="font-size:0.84rem; color:var(--muted); margin:3px 0 0;">
              Proporción del peso total validado en báscula según categoría de material reciclable.
            </p>
          </div>
          <span class="badge" style="background:var(--green-light); color:var(--green); font-weight:700; font-size:0.8rem;">
            5 Categorías Oficiales
          </span>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
          <?php foreach ($materiales as $mat): ?>
            <div style="background:#FAFCFA; border:1px solid var(--border); border-radius:var(--r); padding:14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span style="font-weight:700; font-size:0.88rem; color:var(--text);">
                  <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= h($mat['color_hex']) ?>; margin-right:6px;"></span>
                  <?= h($mat['material']) ?>
                </span>
                <span style="font-size:0.8rem; font-weight:700; color:var(--green);"><?= h($mat['porcentaje']) ?>%</span>
              </div>
              <div style="width:100%; height:8px; background:#E5E7EB; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                <div style="width:<?= h($mat['porcentaje']) ?>%; height:100%; background:<?= h($mat['color_hex']) ?>; border-radius:4px;"></div>
              </div>
              <div style="display:flex; justify-content:space-between; font-size:0.78rem; color:var(--muted);">
                <span><?= number_format($mat['total_kg'], 1) ?> kg</span>
                <span><?= number_format((int)$mat['total_entregas']) ?> entregas</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ===================================================================== -->
      <!-- SECCIÓN 2: SELECTOR Y BUSCADOR UNIVERSAL DE RECICLADORES             -->
      <!-- ===================================================================== -->
      <section id="selector-reciclador" style="background:#FFFFFF; border:1px solid var(--border); border-radius: var(--r-xl); padding: 24px 28px; margin-bottom: 24px; box-shadow: 0 4px 18px rgba(0,0,0,0.05);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
          <div>
            <div style="display:inline-flex; align-items:center; gap:6px; font-size:0.75rem; font-weight:700; color:var(--green); background:var(--green-light); padding:3px 10px; border-radius:12px; text-transform:uppercase; margin-bottom:4px;">
              🔍 Búsqueda Dinámica
            </div>
            <h2 style="font-size:1.35rem; font-weight:800; color:var(--text); margin:0;">
              2. Selector y Ficha Individual del Reciclador
            </h2>
            <p style="font-size:0.86rem; color:var(--muted); margin:3px 0 0;">
              Busca y selecciona cualquier reciclador registrado mediante su ID, nombre, correo o teléfono para consultar su historial completo y activar los modelos analíticos.
            </p>
          </div>
          <div>
            <span style="font-size:0.8rem; color:var(--muted); font-weight:600;">
              Recicladores en padrón: <strong style="color:var(--green);"><?= count($recicladoresIniciales) ?></strong> cargados
            </span>
          </div>
        </div>

        <!-- Input de Búsqueda Tipo Spotlight con Dropdown Autocompletable -->
        <div style="position:relative; margin-bottom:16px;">
          <div style="display:flex; align-items:center; background:#F8FAFC; border:2px solid #E2E8F0; border-radius:12px; padding:6px 14px; transition:border-color 0.2s ease;">
            <span style="font-size:1.2rem; margin-right:10px; color:#64748B;">🔎</span>
            <input 
              type="text" 
              id="buscador-reciclador" 
              placeholder="Buscar por ID (ej. 6), nombre (ej. Carlos), correo o teléfono..." 
              style="width:100%; border:none; background:transparent; outline:none; font-size:0.95rem; font-family:'Poppins', sans-serif; color:var(--text);"
              autocomplete="off"
            />
          </div>

          <!-- Dropdown Flotante de Resultados en Tiempo Real -->
          <div 
            id="dropdown-resultados" 
            style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:#fff; border:1px solid #CBD5E1; border-radius:12px; box-shadow:0 12px 32px rgba(0,0,0,0.12); z-index:100; max-height:280px; overflow-y:auto;"
          ></div>
        </div>

        <!-- Pills de Selección Rápida (Primeros Recicladores) -->
        <div style="display:flex; align-items:center; gap:8px; overflow-x:auto; padding-bottom:6px;">
          <span style="font-size:0.78rem; font-weight:700; color:var(--muted); text-transform:uppercase; white-space:nowrap;">
            Acceso Rápido:
          </span>
          <?php foreach ($recicladoresIniciales as $index => $recRapido): ?>
            <button 
              type="button" 
              class="chip-reciclador-rapido <?= $recRapido['id_usuario'] === $primerRecicladorId ? 'active' : '' ?>"
              data-id="<?= (int)$recRapido['id_usuario'] ?>"
              style="border:1px solid var(--border); background:#FAFCFA; padding:5px 12px; border-radius:20px; font-size:0.8rem; font-weight:600; cursor:pointer; white-space:nowrap; transition:all 0.15s ease; font-family:'Poppins', sans-serif;"
            >
              #<?= $recRapido['id_usuario'] ?> <?= h($recRapido['nombre_completo']) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Indicador de Carga Asíncrono para la Ficha -->
      <div id="loading-ficha" style="display:none; align-items:center; justify-content:center; padding:30px; gap:12px; color:var(--green); font-weight:700;">
        <span class="spinner-sm" style="display:inline-block; width:22px; height:22px; border:3px solid #E0EBE4; border-top-color:#087A3D; border-radius:50%; animation:spin 0.8s linear infinite;"></span>
        Cargando datos del reciclador y ejecutando modelos de minería...
      </div>

      <!-- ===================================================================== -->
      <!-- SECCIÓN 3: FICHA INDIVIDUAL DEL RECICLADOR SELECCIONADO              -->
      <!-- ===================================================================== -->
      <div id="contenedor-ficha-reciclador" style="transition: opacity 0.25s ease;">
        
        <!-- Tarjeta de Perfil & Mini-KPIs del Reciclador -->
        <section id="ficha-reciclador" class="card" style="padding: 26px; margin-bottom: 24px; box-shadow: var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:18px;">
            <div style="display:flex; align-items:center; gap:16px;">
              <div 
                id="rec-avatar-initials" 
                style="width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg, #087A3D 0%, #2E9B50 100%); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:800; box-shadow:0 4px 12px rgba(8,122,61,0.25);"
              >
                R
              </div>
              <div>
                <div style="display:flex; align-items:center; gap:8px;">
                  <h3 id="rec-nombre" style="font-size:1.4rem; font-weight:800; color:var(--text); margin:0;">
                    Cargando reciclador...
                  </h3>
                  <span id="rec-id-badge" class="badge" style="background:#E2E8F0; color:#334155; font-weight:700;">#—</span>
                  <span id="rec-estado-badge" class="badge badge-success">Cuenta Activa</span>
                  <span id="rec-nivel-badge" class="badge" style="background:#FEF3C7; color:#92400E; font-weight:700;">Bronce</span>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:14px; margin-top:6px; font-size:0.83rem; color:var(--muted);">
                  <span>📧 <span id="rec-correo">—</span></span>
                  <span>📱 <span id="rec-telefono">—</span></span>
                  <span>📍 <strong id="rec-localidad">—</strong> (Zona: <span id="rec-zona">—</span>)</span>
                  <span>🚲 Transporte: <span id="rec-transporte">—</span></span>
                  <span>📅 Miembro desde: <span id="rec-registro">—</span></span>
                </div>
              </div>
            </div>

            <!-- Botón para forzar actualización de análisis ML -->
            <div>
              <button 
                type="button" 
                id="btn-actualizar-analisis" 
                class="btn btn-p" 
                style="display:flex; align-items:center; gap:6px; font-size:0.86rem; padding:9px 18px;"
              >
                ⚡ Re-ejecutar Modelos ML
              </button>
            </div>
          </div>

          <!-- 4 Mini-KPIs del Reciclador -->
          <div class="g4" style="margin-bottom:20px;">
            <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:var(--r); padding:16px;">
              <span style="font-size:0.75rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Kilogramos Totales</span>
              <div id="kpi-rec-kg" style="font-size:1.6rem; font-weight:800; color:var(--text); margin-top:4px;">0 kg</div>
              <span style="font-size:0.74rem; color:var(--green); font-weight:600;">Validados en báscula</span>
            </div>
            <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:var(--r); padding:16px;">
              <span style="font-size:0.75rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Entregas Realizadas</span>
              <div id="kpi-rec-entregas" style="font-size:1.6rem; font-weight:800; color:var(--text); margin-top:4px;">0</div>
              <span style="font-size:0.74rem; color:var(--muted);">Viajes a centros de acopio</span>
            </div>
            <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:var(--r); padding:16px;">
              <span style="font-size:0.75rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Ecopuntos Activos</span>
              <div id="kpi-rec-puntos" style="font-size:1.6rem; font-weight:800; color:#087A3D; margin-top:4px;">0 pts</div>
              <span style="font-size:0.74rem; color:#087A3D; font-weight:600;">Disponibles para canjear</span>
            </div>
            <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:var(--r); padding:16px;">
              <span style="font-size:0.75rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Beneficios Redimidos</span>
              <div id="kpi-rec-canjes" style="font-size:1.6rem; font-weight:800; color:#E65100; margin-top:4px;">0</div>
              <span style="font-size:0.74rem; color:#E65100; font-weight:600;">Cupones generados</span>
            </div>
          </div>

          <!-- Desglose de Materiales Propios del Reciclador -->
          <div id="bloque-materiales-reciclador" style="background:#FAFCFA; border:1px solid var(--border); border-radius:var(--r); padding:16px;">
            <h4 style="font-size:0.92rem; font-weight:700; margin:0 0 12px; color:var(--text);">
              Materiales Recuperados por este Reciclador
            </h4>
            <div id="lista-materiales-reciclador"></div>
          </div>

          <!-- Banner de Estado: Usuario Nuevo Sin Actividad -->
          <div 
            id="banner-sin-actividad" 
            style="display:none; background:#FFFBEB; border:1px solid #FCD34D; border-radius:var(--r); padding:18px 22px; margin-top:16px;"
          >
            <div style="display:flex; align-items:center; gap:12px;">
              <span style="font-size:2rem;">ℹ️</span>
              <div>
                <h4 style="margin:0; color:#92400E; font-size:1rem; font-weight:700;">
                  Reciclador Registrado sin Actividad Operativa
                </h4>
                <p style="margin:4px 0 0; font-size:0.86rem; color:#B45309;">
                  Este usuario se encuentra debidamente creado en la plataforma, pero aún no tiene entregas validadas en centros de acopio. A medida que registre sus primeros pesajes y canjes, el sistema actualizará su historial y habilitará automáticamente las inferencias predictivas y de segmentación de los modelos de minería de datos.
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- ===================================================================== -->
        <!-- SECCIÓN 4: ANÁLISIS DE MINERÍA DE DATOS & MACHINE LEARNING            -->
        <!-- ===================================================================== -->
        <section id="modelos-ml" style="margin-bottom:28px;">
          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
            <div>
              <div style="display:inline-flex; align-items:center; gap:6px; font-size:0.75rem; font-weight:700; color:#087A3D; background:#DCFCE7; padding:3px 10px; border-radius:12px; text-transform:uppercase; margin-bottom:4px;">
                ⚙️ Motor de Inferencia Python (.joblib)
              </div>
              <h2 style="font-size:1.35rem; font-weight:800; color:var(--text); margin:0;">
                3. Análisis de Minería de Datos del Reciclador
              </h2>
              <p style="font-size:0.86rem; color:var(--muted); margin:3px 0 0;">
                Resultados derivados de los 4 modelos entrenados aplicados sobre las variables de este reciclador.
              </p>
            </div>
            <div style="text-align:right; font-size:0.78rem; color:var(--muted);">
              <div><strong id="ml-meta-origen">Desde caché analítico auditado</strong></div>
              <div>Fecha análisis: <span id="ml-meta-fecha">—</span> · <span id="ml-meta-periodo">—</span></div>
            </div>
          </div>

          <!-- Cuadrícula de los 4 Modelos -->
          <div id="bloque-analitica-ml" class="g2" style="gap:20px; margin-bottom:20px;">
            
            <!-- MODELO 1: Regresión de Ecopoints (RandomForestRegressor) -->
            <div class="card" style="padding:22px; border-top:4px solid #087A3D; box-shadow:var(--shadow);">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                <div>
                  <span style="font-size:0.74rem; font-weight:700; color:var(--green); text-transform:uppercase;">Modelo 1 · Regresión Supervisada</span>
                  <h3 style="font-size:1.15rem; font-weight:800; margin:3px 0 0; color:var(--text);">Predicción de Ecopoints</h3>
                </div>
                <span class="badge" style="background:#E8F5E9; color:#087A3D; font-weight:700;">RandomForest</span>
              </div>
              <p style="font-size:0.83rem; color:var(--muted); margin-bottom:16px;">
                Proyecta la cantidad esperada de Ecopuntos para el siguiente ciclo operativo a partir de kg, entregas y material predominante.
              </p>
              
              <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:var(--r); padding:16px; text-align:center; margin-bottom:14px;">
                <span style="font-size:0.78rem; font-weight:700; color:#166534; text-transform:uppercase;">Estimación para Siguiente Periodo</span>
                <div id="ml-regresion-puntos" style="font-size:2.2rem; font-weight:800; color:#087A3D; margin:4px 0;">— pts</div>
                <small style="font-size:0.75rem; color:#15803D;">Horizonte: Siguiente observación disponible</small>
              </div>

              <div style="font-size:0.78rem; color:var(--muted); border-top:1px solid var(--border); padding-top:10px; display:flex; justify-content:space-between;">
                <span>Algoritmo: <strong id="ml-regresion-algo">RandomForestRegressor</strong></span>
                <span>R²: <strong>0.39</strong> · MAE: <strong>75.9</strong></span>
              </div>
            </div>

            <!-- MODELO 2: Clasificación de Nivel (LogisticRegression Multinomial) -->
            <div class="card" style="padding:22px; border-top:4px solid #3B82F6; box-shadow:var(--shadow);">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                <div>
                  <span style="font-size:0.74rem; font-weight:700; color:#2563EB; text-transform:uppercase;">Modelo 2 · Clasificación Multiclase</span>
                  <h3 style="font-size:1.15rem; font-weight:800; margin:3px 0 0; color:var(--text);">Nivel de Actividad Operativa</h3>
                </div>
                <span id="ml-clasificacion-badge" class="badge" style="background:#EFF6FF; color:#1D4ED8; font-weight:700;">Nivel: —</span>
              </div>
              <p style="font-size:0.83rem; color:var(--muted); margin-bottom:14px;">
                Clasifica la intensidad operativa esperada del reciclador (Baja, Media, Alta) con probabilidades matemáticas reales sin data leakage.
              </p>

              <!-- Barras de Probabilidad por Clase -->
              <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:14px;">
                <div>
                  <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:3px;">
                    <span style="font-weight:600;">Actividad Alta</span>
                    <strong id="txt-prob-alta" style="color:#087A3D;">0%</strong>
                  </div>
                  <div style="width:100%; height:7px; background:#E2E8F0; border-radius:4px; overflow:hidden;">
                    <div id="bar-prob-alta" style="width:0%; height:100%; background:#087A3D; border-radius:4px; transition:width 0.4s ease;"></div>
                  </div>
                </div>
                <div>
                  <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:3px;">
                    <span style="font-weight:600;">Actividad Media</span>
                    <strong id="txt-prob-media" style="color:#F59E0B;">0%</strong>
                  </div>
                  <div style="width:100%; height:7px; background:#E2E8F0; border-radius:4px; overflow:hidden;">
                    <div id="bar-prob-media" style="width:0%; height:100%; background:#F59E0B; border-radius:4px; transition:width 0.4s ease;"></div>
                  </div>
                </div>
                <div>
                  <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:3px;">
                    <span style="font-weight:600;">Actividad Baja</span>
                    <strong id="txt-prob-baja" style="color:#64748B;">0%</strong>
                  </div>
                  <div style="width:100%; height:7px; background:#E2E8F0; border-radius:4px; overflow:hidden;">
                    <div id="bar-prob-baja" style="width:0%; height:100%; background:#64748B; border-radius:4px; transition:width 0.4s ease;"></div>
                  </div>
                </div>
              </div>

              <div style="font-size:0.78rem; color:var(--muted); border-top:1px solid var(--border); padding-top:10px; display:flex; justify-content:space-between;">
                <span>Algoritmo: <strong>LogisticRegression</strong></span>
                <span>Accuracy: <strong>84.7%</strong></span>
              </div>
            </div>

            <!-- MODELO 3: Segmentación K-Means -->
            <div class="card" style="padding:22px; border-top:4px solid #8B5CF6; box-shadow:var(--shadow);">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                <div>
                  <span style="font-size:0.74rem; font-weight:700; color:#7C3AED; text-transform:uppercase;">Modelo 3 · Aprendizaje No Supervisado</span>
                  <h3 style="font-size:1.15rem; font-weight:800; margin:3px 0 0; color:var(--text);">Segmentación de Reciclador</h3>
                </div>
                <span id="ml-kmeans-cluster-id" class="badge" style="background:#F5F3FF; color:#6D28D9; font-weight:700;">Clúster #—</span>
              </div>
              <p style="font-size:0.83rem; color:var(--muted); margin-bottom:14px;">
                Agrupa el comportamiento histórico en base a promedios consolidados de peso, visitas y diversidad de materiales.
              </p>

              <div style="background:#F5F3FF; border:1px solid #DDD6FE; border-radius:var(--r); padding:16px; margin-bottom:14px;">
                <div style="font-size:0.78rem; font-weight:700; color:#6D28D9; text-transform:uppercase;">Segmento Asignado</div>
                <div id="ml-kmeans-segmento" style="font-size:1.45rem; font-weight:800; color:#5B21B6; margin:3px 0 6px;">—</div>
                <p id="ml-kmeans-desc" style="font-size:0.82rem; color:#4C1D95; margin:0;">
                  Evaluando clúster del reciclador...
                </p>
              </div>

              <div style="font-size:0.78rem; color:var(--muted); border-top:1px solid var(--border); padding-top:10px; display:flex; justify-content:space-between;">
                <span>Algoritmo: <strong>K-Means (k=3)</strong></span>
                <span>Silhouette Score: <strong>0.32</strong></span>
              </div>
            </div>

            <!-- MODELO 4: Detección de Anomalías (Isolation Forest) -->
            <div class="card" style="padding:22px; border-top:4px solid #EF4444; box-shadow:var(--shadow);">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                <div>
                  <span style="font-size:0.74rem; font-weight:700; color:#DC2626; text-transform:uppercase;">Modelo 4 · Detección de Desviaciones</span>
                  <h3 style="font-size:1.15rem; font-weight:800; margin:3px 0 0; color:var(--text);">Control Operativo de Anomalías</h3>
                </div>
                <span id="ml-iso-badge" class="badge badge-success">Normal</span>
              </div>
              <p style="font-size:0.83rem; color:var(--muted); margin-bottom:14px;">
                Analiza combinaciones multidimensionales inusuales de kg y entregas para auditoría operativa responsable.
              </p>

              <div style="background:#FEF2F2; border:1px solid #FECACA; border-radius:var(--r); padding:16px; margin-bottom:14px;">
                <div style="font-size:0.78rem; font-weight:700; color:#991B1B; text-transform:uppercase;">Diagnóstico Estadístico</div>
                <div id="ml-iso-estado" style="font-size:1.2rem; font-weight:800; color:#B91C1C; margin:3px 0;">Operación Normal</div>
                <div id="ml-iso-score" style="font-size:0.78rem; color:#7F1D1D; font-weight:600;">Score: —</div>
              </div>

              <p style="font-size:0.75rem; color:#6B7280; margin:0 0 10px; font-style:italic;">
                Nota ética: Una alerta indica un patrón estadísticamente poco frecuente en báscula. No presupone fraude ni cancela beneficios sin revisión humana.
              </p>

              <div style="font-size:0.78rem; color:var(--muted); border-top:1px solid var(--border); padding-top:10px; display:flex; justify-content:space-between;">
                <span>Algoritmo: <strong>IsolationForest</strong></span>
                <span>Contaminación esperada: <strong>3%</strong></span>
              </div>
            </div>

          </div>

          <!-- Recomendaciones Contextuales Generadas por los Modelos -->
          <div class="card" style="padding:20px 24px; box-shadow:var(--shadow); background:#FFFFFF;">
            <h4 style="font-size:0.95rem; font-weight:800; color:var(--text); margin:0 0 10px; display:flex; align-items:center; gap:8px;">
              <span>💡</span> Orientaciones Operativas Basadas en los Modelos
            </h4>
            <ul id="ml-recomendaciones-lista" style="padding-left:20px; font-size:0.86rem; color:var(--muted); line-height:1.7;">
              <li>Cargando orientaciones analíticas...</li>
            </ul>
          </div>
        </section>

        <!-- ===================================================================== -->
        <!-- SECCIÓN 5: HISTORIAL CRONOLÓGICO DE ENTREGAS Y CANJES                -->
        <!-- ===================================================================== -->
        <section id="historial-entregas" class="card" style="padding:24px; margin-bottom:28px; box-shadow:var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <div>
              <h2 style="font-size:1.25rem; font-weight:800; color:var(--text); margin:0;">
                4. Historial Transaccional Detallado
              </h2>
              <p style="font-size:0.85rem; color:var(--muted); margin:2px 0 0;">
                Registros individuales de entregas en centros de acopio y redención de beneficios.
              </p>
            </div>
            
            <!-- Selector de Pestañas: Entregas vs Canjes -->
            <div style="display:flex; gap:6px;">
              <button type="button" id="tab-btn-entregas" class="btn btn-p" style="font-size:0.82rem; padding:6px 14px;">
                📦 Entregas Validadas
              </button>
              <button type="button" id="tab-btn-canjes" class="btn btn-o" style="font-size:0.82rem; padding:6px 14px;">
                🎁 Beneficios Canjeados
              </button>
            </div>
          </div>

          <!-- Panel de Entregas con Filtros -->
          <div id="panel-tabla-entregas">
            <!-- Barra de Filtros -->
            <div style="background:#F8FAFC; border:1px solid var(--border); border-radius:var(--r); padding:12px 16px; margin-bottom:16px; display:flex; flex-wrap:wrap; gap:14px; align-items:center;">
              <div style="display:flex; align-items:center; gap:6px; font-size:0.82rem;">
                <label for="filter-fecha-desde" style="font-weight:600; color:var(--muted);">Desde:</label>
                <input type="date" id="filter-fecha-desde" class="form-input" style="padding:4px 8px; font-size:0.82rem; width:135px;" />
              </div>
              <div style="display:flex; align-items:center; gap:6px; font-size:0.82rem;">
                <label for="filter-fecha-hasta" style="font-weight:600; color:var(--muted);">Hasta:</label>
                <input type="date" id="filter-fecha-hasta" class="form-input" style="padding:4px 8px; font-size:0.82rem; width:135px;" />
              </div>
              <div style="display:flex; align-items:center; gap:6px; font-size:0.82rem;">
                <label for="filter-material" style="font-weight:600; color:var(--muted);">Material:</label>
                <select id="filter-material" class="form-select" style="padding:4px 8px; font-size:0.82rem; width:130px;">
                  <option value="">Todos</option>
                  <option value="Cartón">Cartón</option>
                  <option value="Papel">Papel</option>
                  <option value="Plástico">Plástico</option>
                  <option value="Vidrio">Vidrio</option>
                  <option value="Metales">Metales</option>
                </select>
              </div>
              <div style="margin-left:auto; font-size:0.8rem; color:var(--muted); font-weight:600;" id="contador-entregas-filtradas">
                Cargando entregas...
              </div>
            </div>

            <!-- Tabla de Entregas -->
            <div style="overflow-x:auto;">
              <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                <thead>
                  <tr style="background:#F1F5F9; color:var(--muted); text-align:left; border-bottom:2px solid var(--border);">
                    <th style="padding:10px 12px;">Fecha y Hora</th>
                    <th style="padding:10px 12px;">Material</th>
                    <th style="padding:10px 12px; text-align:right;">Kg Validados</th>
                    <th style="padding:10px 12px; text-align:right;">Puntos Asignados</th>
                    <th style="padding:10px 12px;">Centro de Acopio</th>
                    <th style="padding:10px 12px; text-align:center;">Estado</th>
                  </tr>
                </thead>
                <tbody id="tabla-entregas-body">
                  <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:var(--muted);">
                      Cargando historial de entregas...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Panel de Canjes -->
          <div id="panel-tabla-canjes" style="display:none;">
            <div style="overflow-x:auto;">
              <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                <thead>
                  <tr style="background:#F1F5F9; color:var(--muted); text-align:left; border-bottom:2px solid var(--border);">
                    <th style="padding:10px 12px;">Fecha Canje</th>
                    <th style="padding:10px 12px;">Beneficio</th>
                    <th style="padding:10px 12px;">Categoría</th>
                    <th style="padding:10px 12px; text-align:right;">Puntos Utilizados</th>
                    <th style="padding:10px 12px; text-align:center;">Estado</th>
                  </tr>
                </thead>
                <tbody id="tabla-canjes-body">
                  <tr>
                    <td colspan="5" style="text-align:center; padding:20px; color:var(--muted);">
                      Cargando historial de canjes...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- ===================================================================== -->
        <!-- SECCIÓN 6: REGLAS DE ASOCIACIÓN DE MATERIALES (APRIORI)               -->
        <!-- ===================================================================== -->
        <section id="reglas-asociacion-sec" class="card" style="padding:24px; box-shadow:var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
            <div>
              <span class="badge" style="background:#FEF3C7; color:#92400E; font-weight:700; margin-bottom:4px;">
                🧺 Análisis de Canasta y Coexistencia (Apriori)
              </span>
              <h3 style="font-size:1.15rem; font-weight:800; color:var(--text); margin:2px 0 0;">
                Reglas de Asociación de Materiales Descubiertas
              </h3>
              <p style="font-size:0.84rem; color:var(--muted); margin:3px 0 0;">
                Patrones de recolección conjunta de materiales identificados con soporte mínimo del 20% y confianza superior al 50%.
              </p>
            </div>
          </div>

          <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.84rem;">
              <thead>
                <tr style="background:#F8FAFC; color:var(--muted); text-align:left; border-bottom:2px solid var(--border);">
                  <th style="padding:8px 12px;">Antecedente (Materiales presentes)</th>
                  <th style="padding:8px 12px;">Consecuente (Material asociado)</th>
                  <th style="padding:8px 12px; text-align:right;">Soporte</th>
                  <th style="padding:8px 12px; text-align:right;">Confianza</th>
                  <th style="padding:8px 12px; text-align:right;">Lift</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($reglasAsociacion)): ?>
                  <tr><td colspan="5" style="padding:14px; text-align:center; color:var(--muted);">Sin reglas de asociación cargadas.</td></tr>
                <?php else: ?>
                  <?php foreach ($reglasAsociacion as $regla): ?>
                    <tr style="border-bottom:1px solid var(--border);">
                      <td style="padding:9px 12px; font-weight:600; color:var(--text);">
                        <?= h($regla['antecedente']) ?>
                      </td>
                      <td style="padding:9px 12px; font-weight:700; color:#087A3D;">
                        ➔ <?= h($regla['consecuente']) ?>
                      </td>
                      <td style="padding:9px 12px; text-align:right; color:var(--muted);"><?= number_format($regla['soporte'] * 100, 1) ?>%</td>
                      <td style="padding:9px 12px; text-align:right; font-weight:700; color:#087A3D;"><?= number_format($regla['confianza'] * 100, 1) ?>%</td>
                      <td style="padding:9px 12px; text-align:right; font-weight:700; color:#2563EB;"><?= number_format($regla['lift'], 3) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

      </div> <!-- Fin contenedor-ficha-reciclador -->

    </div>
  </div>

<!-- ========================================================================= -->
<!-- CONFIGURACIÓN GLOBAL Y SCRIPT REACTIVO DEL DASHBOARD                     -->
<!-- ========================================================================= -->
<script>
  window.ecopointsAdminConfig = {
    baseUrl: '<?= BASE_URL ?>',
    csrfToken: '<?= $csrfToken ?>',
    primerRecicladorId: <?= (int)$primerRecicladorId ?>
  };
</script>
<script src="<?= asset('js/admin-dashboard.js') ?>"></script>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
