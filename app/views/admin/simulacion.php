<?php
/**
 * ============================================================================
 * VISTA: MODELADO Y SIMULACIÓN OPERATIVA (SimPy + Monte Carlo)
 * ============================================================================
 * 
 * Permite al Administrador General:
 * 1. Configurar y simular el flujo logístico de eventos discretos con SimPy.
 * 2. Ejecutar experimentos Monte Carlo para medir variabilidad y percentiles.
 * 3. Comparar escenarios operativos (Referencia, Alta Demanda, Capacidad).
 * 4. Contrastar los puntos generados con el modelo de minería (RandomForest).
 * 5. Exportar los resultados de simulación a CSV para soporte en toma de decisiones.
 */

require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';

$csrfToken = csrf_token();
$escA = $escenarios['A'] ?? [];
$escB = $escenarios['B'] ?? [];
$escC = $escenarios['C'] ?? [];
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg pg-wide">

      <!-- ===================================================================== -->
      <!-- CABECERA: IDENTIDAD DEL MÓDULO DE MODELADO Y SIMULACIÓN               -->
      <!-- ===================================================================== -->
      <section style="background: linear-gradient(135deg, #065C2D 0%, #087A3D 55%, #15803D 100%); border-radius: var(--r-xl); padding: 26px 32px; margin-bottom: 24px; color: #fff; box-shadow: 0 10px 28px rgba(8, 122, 61, 0.22); position: relative; overflow: hidden;">
        <div style="position:absolute; right:-15px; bottom:-25px; font-size:11rem; opacity:0.06; pointer-events:none;">🔬</div>

        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; position:relative; z-index:2;">
          <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
              <span style="font-size:0.75rem; font-weight:700; background:rgba(255,255,255,0.22); padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">
                ⚙️ Motor: SimPy & Monte Carlo
              </span>
              <span style="font-size:0.75rem; font-weight:700; background:#DCFCE7; color:#14532D; padding:4px 10px; border-radius:20px;">
                ● Aislamiento Ético Activo
              </span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0; color: #fff; letter-spacing:-0.5px;">
              Modelado y Simulación Operativa de EcoPoints
            </h1>
            <p style="margin: 6px 0 0; opacity: 0.92; font-size: 0.94rem; max-width: 720px;">
              Experimentación estocástica de eventos discretos para anticipar congestión en rutas, colas en centros de acopio y balancear la capacidad de atención sin alterar la base de datos de producción.
            </p>
          </div>

          <div style="background: rgba(0, 0, 0, 0.22); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.18); border-radius: var(--r-lg); padding: 14px 18px; min-width: 260px;">
            <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; opacity:0.9; margin-bottom:6px;">
              Calibración Empírica
            </div>
            <div style="font-size:0.82rem; line-height:1.5;">
              <div>• Base CSV: <strong>641 registros mensuales</strong></div>
              <div>• Carga media: <strong>~20.8 kg / entrega</strong></div>
              <div>• 5 Materiales: <strong>Distribución Real</strong></div>
              <div>• Regresión ML: <strong>RandomForest Conectado</strong></div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===================================================================== -->
      <!-- APARTADO A: PANEL DE INDICADORES PRINCIPALES DE LA SIMULACIÓN         -->
      <!-- ===================================================================== -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
        <div>
          <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text); margin: 0;">
            A. Panel de Indicadores de Rendimiento Simulado
          </h2>
          <p style="font-size: 0.85rem; color: var(--muted); margin: 2px 0 0;">
            Resultados consolidados de las réplicas Monte Carlo. Los valores corresponden a medias estadísticas esperadas.
          </p>
        </div>
        <span class="badge" style="background:#E0F2FE; color:#0369A1; font-weight:700; font-size:0.8rem;">
          Resultados Sintéticos Simulados
        </span>
      </div>

      <!-- Tarjetas KPI Simuladas -->
      <div class="g4" style="margin-bottom: 24px;">
        
        <!-- KPI 1: Solicitudes Atendidas -->
        <div class="card" style="padding: 20px; border-left: 4px solid #087A3D; box-shadow: var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.78rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Solicitudes Completadas</span>
            <span style="background:var(--green-light); color:var(--green); width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">✅</span>
          </div>
          <div id="kpi-sim-completadas" style="font-size: 1.9rem; font-weight: 800; color: var(--text); margin: 8px 0 4px;">—</div>
          <span style="font-size:0.78rem; color:#087A3D; font-weight:700;">
            Tasa de Atención: <strong id="kpi-sim-atendidas-pct">—%</strong>
          </span>
        </div>

        <!-- KPI 2: Kilos Recuperados -->
        <div class="card" style="padding: 20px; border-left: 4px solid #00BFA5; box-shadow: var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.78rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Material Recuperado</span>
            <span style="background:#E0F7F4; color:#00796B; width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">⚖️</span>
          </div>
          <div id="kpi-sim-kg" style="font-size: 1.9rem; font-weight: 800; color: var(--text); margin: 8px 0 4px;">— kg</div>
          <span id="kpi-sim-tons" style="font-size:0.78rem; color:#00796B; font-weight:700;">— Toneladas</span>
        </div>

        <!-- KPI 3: EcoPoints Emitidos (Regla Directa vs ML) -->
        <div class="card" style="padding: 20px; border-left: 4px solid #FF9800; box-shadow: var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.78rem; font-weight:700; color:var(--muted); text-transform:uppercase;">EcoPoints Simulados</span>
            <span style="background:#FFF3E0; color:#E65100; width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">🎁</span>
          </div>
          <div id="kpi-sim-puntos-regla" style="font-size: 1.9rem; font-weight: 800; color: #E65100; margin: 8px 0 4px;">— pts</div>
          <span style="font-size:0.75rem; color:var(--muted);">
            Proyección ML: <strong id="kpi-sim-puntos-ml" style="color:var(--text);">— pts</strong>
          </span>
        </div>

        <!-- KPI 4: Tiempos de Espera y Ciclo -->
        <div class="card" style="padding: 20px; border-left: 4px solid #3B82F6; box-shadow: var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.78rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Tiempos de Espera</span>
            <span style="background:#EFF6FF; color:#1D4ED8; width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.1rem;">⏱️</span>
          </div>
          <div style="display:flex; justify-content:space-between; align-items:baseline; margin: 8px 0 4px;">
            <span style="font-size:0.8rem; color:var(--muted);">Espera Báscula:</span>
            <strong id="kpi-sim-espera-pes" style="font-size:1.35rem; color:#1D4ED8;">— min</strong>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--muted);">
            <span>Espera Reciclador: <strong id="kpi-sim-espera-rec">— min</strong></span>
            <span>Ciclo: <strong id="kpi-sim-ciclo-total">— min</strong></span>
          </div>
        </div>

      </div>

      <!-- ===================================================================== -->
      <!-- APARTADO B: CONFIGURACIÓN DE ESCENARIOS Y PARÁMETROS                  -->
      <!-- ===================================================================== -->
      <section class="card" style="padding: 24px 28px; margin-bottom: 24px; box-shadow: var(--shadow);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
          <div>
            <span class="badge" style="background:var(--green-light); color:var(--green); font-weight:700; margin-bottom:4px;">
              🎛️ Parámetros de Simulación
            </span>
            <h2 style="font-size:1.25rem; font-weight:800; color:var(--text); margin:2px 0 0;">
              B. Configuración de Escenarios y Recursos
            </h2>
            <p style="font-size:0.84rem; color:var(--muted); margin:3px 0 0;">
              Ajusta los recursos operativos, tiempos de atención y volumen de demanda para experimentar con SimPy.
            </p>
          </div>
          <div>
            <label for="sim-escenario" style="font-weight:700; font-size:0.85rem; color:var(--text); margin-right:8px;">
              Plantilla de Escenario:
            </label>
            <select id="sim-escenario" class="form-select" style="padding:6px 12px; font-weight:600; font-size:0.85rem; width:260px;">
              <option value="A">Escenario A: Referencia Histórica</option>
              <option value="B">Escenario B: Alta Demanda (+75%)</option>
              <option value="C">Escenario C: Expansión de Capacidad</option>
              <option value="CUSTOM">Personalizado</option>
            </select>
          </div>
        </div>

        <!-- Formulario en Grid de 4 Columnas -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:18px; margin-bottom:18px;">
          
          <div>
            <label for="sim-solicitudes" style="display:block; font-size:0.82rem; font-weight:700; color:var(--text); margin-bottom:4px;">
              Solicitudes / Día (Demanda):
            </label>
            <input type="number" id="sim-solicitudes" class="form-input" value="40" min="5" max="300" step="5" style="width:100%;" />
            <small style="color:var(--muted); font-size:0.74rem;">Frecuencia Poisson de llegada</small>
          </div>

          <div>
            <label for="sim-recicladores" style="display:block; font-size:0.82rem; font-weight:700; color:var(--text); margin-bottom:4px;">
              Recicladores en Ruta (Capacidad):
            </label>
            <input type="number" id="sim-recicladores" class="form-input" value="30" min="1" max="200" step="1" style="width:100%;" />
            <small style="color:var(--muted); font-size:0.74rem;">Recurso SimPy limitado</small>
          </div>

          <div>
            <label for="sim-puestos" style="display:block; font-size:0.82rem; font-weight:700; color:var(--text); margin-bottom:4px;">
              Puestos de Pesaje en Acopio:
            </label>
            <input type="number" id="sim-puestos" class="form-input" value="2" min="1" max="20" step="1" style="width:100%;" />
            <small style="color:var(--muted); font-size:0.74rem;">Básculas de atención simultánea</small>
          </div>

          <div>
            <label for="sim-duracion" style="display:block; font-size:0.82rem; font-weight:700; color:var(--text); margin-bottom:4px;">
              Duración Simulación (Días):
            </label>
            <input type="number" id="sim-duracion" class="form-input" value="7" min="1" max="30" step="1" style="width:100%;" />
            <small style="color:var(--muted); font-size:0.74rem;">Horario diurno 8h/día</small>
          </div>

          <div>
            <label for="sim-replicas" style="display:block; font-size:0.82rem; font-weight:700; color:var(--text); margin-bottom:4px;">
              Réplicas Monte Carlo:
            </label>
            <input type="number" id="sim-replicas" class="form-input" value="10" min="1" max="50" step="1" style="width:100%;" />
            <small style="color:var(--muted); font-size:0.74rem;">Experimentos estocásticos independientes</small>
          </div>

          <div>
            <label for="sim-semilla" style="display:block; font-size:0.82rem; font-weight:700; color:var(--text); margin-bottom:4px;">
              Semilla Aleatoria (Seed):
            </label>
            <input type="number" id="sim-semilla" class="form-input" value="42" min="1" max="99999" step="1" style="width:100%;" />
            <small style="color:var(--muted); font-size:0.74rem;">Garantiza reproducibilidad científica</small>
          </div>

        </div>

        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:var(--r); padding:10px 14px; font-size:0.78rem; color:#92400E;">
          <strong>Nota de Supuestos Operativos:</strong> Los tiempos de recolección en domicilio (20 a 50 min), traslado en carreta/vehículo (15 a 40 min) y pesaje en báscula (8 a 18 min) corresponden a supuestos de diseño modelados con distribución triangular empírica, ya que la base transaccional no almacena cronometrajes GPS individuales.
        </div>

        <!-- ===================================================================== -->
        <!-- APARTADO C: EJECUCIÓN DEL MOTOR DE SIMULACIÓN                         -->
        <!-- ===================================================================== -->
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:18px; border-top:1px solid var(--border); padding-top:16px;">
          <div style="display:flex; align-items:center; gap:12px;">
            <button type="button" id="btn-ejecutar-simulacion" class="btn btn-p" style="padding:10px 22px; font-size:0.92rem; font-weight:700; display:flex; align-items:center; gap:8px;">
              <span id="sim-spinner" class="spinner-sm" style="display:none; width:16px; height:16px; border:2px solid #fff; border-top-color:transparent; border-radius:50%; animation:spin 0.8s linear infinite;"></span>
              ▶ Ejecutar Simulación Monte Carlo
            </button>
            <button type="button" id="btn-exportar-csv" class="btn btn-o" style="padding:10px 18px; font-size:0.92rem; font-weight:600;">
              📥 Exportar Resultados a CSV
            </button>
          </div>

          <div id="sim-status-banner" style="display:none; font-size:0.84rem; font-weight:600; padding:6px 14px; border-radius:8px;"></div>
        </div>
      </section>

      <!-- ===================================================================== -->
      <!-- APARTADO D: RESULTADOS, COMPARATIVAS Y GRÁFICAS                       -->
      <!-- ===================================================================== -->
      <section style="margin-bottom:28px;">
        <h2 style="font-size:1.25rem; font-weight:800; color:var(--text); margin:0 0 14px;">
          D. Resultados Comparativos y Diagnóstico de Cuellos de Botella
        </h2>

        <!-- Diagnóstico de Cuello de Botella -->
        <div class="card" style="padding:20px 24px; margin-bottom:20px; box-shadow:var(--shadow); border-left:4px solid #3B82F6;">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:8px;">
            <div>
              <span style="font-size:0.75rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Diagnóstico Logístico Automático</span>
              <h3 id="diag-cuello-titulo" style="font-size:1.15rem; font-weight:800; margin:2px 0 0; color:var(--text);">
                Evaluando cuellos de botella...
              </h3>
            </div>
            <span id="diag-cuello-badge" class="badge badge-success">Operación Óptima</span>
          </div>
          <p id="diag-cuello-recom" style="margin:0; font-size:0.86rem; color:var(--muted); line-height:1.5;">
            El balance de recursos permite atender las solicitudes dentro de los umbrales esperados.
          </p>
        </div>

        <!-- Comparativa de Escenarios A, B y C -->
        <div class="card" style="padding:22px; margin-bottom:20px; box-shadow:var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
              <h3 style="font-size:1.05rem; font-weight:800; margin:0; color:var(--text);">
                Comparativa de Escenarios Operativos (A, B y C)
              </h3>
              <p style="font-size:0.8rem; color:var(--muted); margin:3px 0 0;">
                Efecto del incremento de demanda y expansión de flota sobre tiempos de espera y cumplimiento logístico.
              </p>
            </div>
            <span class="badge" style="background:#F1F5F9; color:#475569; font-size:0.75rem;">Medias Esperadas Monte Carlo</span>
          </div>
          <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.82rem;">
              <thead>
                <tr style="border-bottom:2px solid var(--border); color:var(--muted); text-align:left;">
                  <th style="padding:10px 12px;">Indicador Operativo</th>
                  <th style="padding:10px 12px; text-align:right;">Esc. A (Ref: 40 sol, 30 rec, 2 bás)</th>
                  <th style="padding:10px 12px; text-align:right; color:#B45309;">Esc. B (+75% Demanda: 70 sol, 30 rec, 2 bás)</th>
                  <th style="padding:10px 12px; text-align:right; color:#15803D;">Esc. C (Expansión: 70 sol, 45 rec, 4 bás)</th>
                </tr>
              </thead>
              <tbody id="tabla-comparativa-body">
                <tr><td colspan="4" style="text-align:center; padding:18px; color:var(--muted);">Cargando comparativa de escenarios...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Evolución Temporal de Colas en Simulación (SVG Interactivo y Ampliado) -->
        <div class="card" style="padding:24px; margin-bottom:22px; box-shadow:var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
            <div>
              <h3 style="font-size:1.12rem; font-weight:800; margin:0; color:var(--text); display:flex; align-items:center; gap:8px;">
                <span>📈</span> Evolución Temporal de Colas en Simulación
              </h3>
              <p style="font-size:0.82rem; color:var(--muted); margin:3px 0 0;">
                Monitoreo continuo de solicitudes en cola y flujo de pesaje a lo largo de las horas simuladas de operación.
              </p>
            </div>
            <div style="display:flex; align-items:center; gap:14px; background:#F8FAFC; border:1px solid #E2E8F0; padding:6px 14px; border-radius:999px; font-size:0.78rem;">
              <span style="display:flex; align-items:center; gap:6px; color:#EF4444; font-weight:700;">
                <span style="display:inline-block; width:16px; height:3px; background:#EF4444; border-radius:2px;"></span>
                Cola Solicitudes Recicladores
              </span>
              <span style="display:flex; align-items:center; gap:6px; color:#2563EB; font-weight:700;">
                <span style="display:inline-block; width:16px; height:3px; background:#2563EB; border-radius:2px; border-top:1px dashed #fff;"></span>
                Cola Báscula en Acopio
              </span>
            </div>
          </div>

          <!-- Contenedor del Gráfico SVG Ampliado -->
          <div id="grafico-colas-svg" style="min-height:330px; width:100%; display:flex; align-items:center; justify-content:center; background:#FAFCFA; border-radius:12px; border:1px solid #F1F5F9; padding:12px 14px; box-sizing:border-box;">
            <span style="color:var(--muted); font-size:0.85rem;">Generando curva temporal de colas...</span>
          </div>

          <!-- Bloque Dinámico de Explicación y Diagnóstico del Escenario -->
          <div id="explicacion-grafico-colas" style="margin-top:18px; padding:18px 22px; background:#F8FAFC; border-radius:12px; border:1px solid #E2E8F0; transition:all 0.3s ease;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
              <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.1rem;">💡</span>
                <strong style="color:var(--text); font-size:0.94rem;">Explicación y Diagnóstico de la Simulación:</strong>
              </div>
              <span id="explicacion-escenario-tag" class="badge" style="background:#EEF8EE; color:#087A3D; font-weight:700; font-size:0.78rem; padding:4px 12px; border-radius:999px;">
                Escenario Activo
              </span>
            </div>
            
            <div id="explicacion-contenido-texto" style="font-size:0.86rem; color:var(--text); line-height:1.65;">
              Cargando análisis detallado de colas y balance de recursos...
            </div>

            <!-- Leyenda explicativa de apoyo para interpretación inmediata -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px; margin-top:14px; padding-top:12px; border-top:1px dashed #CBD5E1; font-size:0.8rem;">
              <div style="display:flex; gap:8px; align-items:flex-start;">
                <span style="color:#EF4444; font-size:1.05rem; line-height:1;">●</span>
                <div>
                  <strong style="color:var(--text);">Línea Roja continua (Cola de Recicladores):</strong>
                  <span style="color:var(--muted); display:block; margin-top:2px;">Solicitudes ciudadanas esperando ser asignadas o recolectadas en calle. Un ascenso prolongado indica falta de recicladores en ruta.</span>
                </div>
              </div>
              <div style="display:flex; gap:8px; align-items:flex-start;">
                <span style="color:#2563EB; font-size:1.05rem; line-height:1;">●</span>
                <div>
                  <strong style="color:var(--text);">Línea Azul discontinua (Cola de Báscula):</strong>
                  <span style="color:var(--muted); display:block; margin-top:2px;">Recicladores que ya llegaron al centro de acopio pero esperan turno de pesaje. Picos altos revelan saturación en las básculas de recepción.</span>
                </div>
              </div>
            </div>

          </div>

        </div>


        <!-- Tabla Detallada de Variabilidad Monte Carlo (Percentiles) -->
        <div class="card" style="padding:22px; box-shadow:var(--shadow);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
              <h3 style="font-size:1.05rem; font-weight:800; margin:0; color:var(--text);">
                Análisis de Variabilidad Estocástica (Monte Carlo)
              </h3>
              <p style="font-size:0.82rem; color:var(--muted); margin:2px 0 0;">
                Distribución empírica de métricas entre las réplicas ejecutadas: Media, Desviación Estándar, P5, Mediana (P50) y P95.
              </p>
            </div>
          </div>

          <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.84rem;">
              <thead>
                <tr style="background:#F8FAFC; color:var(--muted); text-align:left; border-bottom:2px solid var(--border);">
                  <th style="padding:10px 12px;">Variable Operativa Evaluada</th>
                  <th style="padding:10px 12px; text-align:right;">Media Esperada</th>
                  <th style="padding:10px 12px; text-align:right;">Desv. Estándar</th>
                  <th style="padding:10px 12px; text-align:right;">Percentil 5 (P5)</th>
                  <th style="padding:10px 12px; text-align:right;">Mediana (P50)</th>
                  <th style="padding:10px 12px; text-align:right;">Percentil 95 (P95)</th>
                </tr>
              </thead>
              <tbody id="tabla-montecarlo-body">
                <tr><td colspan="6" style="text-align:center; padding:18px; color:var(--muted);">Cargando estadísticas de Monte Carlo...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

      </section>

      <!-- ===================================================================== -->
      <!-- APARTADO E: PROTECCIÓN DE DATOS, ÉTICA Y RECOMENDACIONES              -->
      <!-- ===================================================================== -->
      <section class="card" style="padding:20px 24px; box-shadow:var(--shadow); background:#FFFFFF; border-left:4px solid #10B981;">
        <h4 style="font-size:0.95rem; font-weight:800; color:var(--text); margin:0 0 8px; display:flex; align-items:center; gap:8px;">
          <span>🛡️</span> Protocolo de Protección de Datos y Uso Ético de la Simulación
        </h4>
        <ul style="padding-left:20px; font-size:0.83rem; color:var(--muted); line-height:1.6; margin:0;">
          <li><strong>Aislamiento Operativo:</strong> Ningún registro de reciclador, entrega real ni saldo de EcoPoints en la base de datos es modificado o sobreescrito durante las corridas de simulación.</li>
          <li><strong>Uso Responsable:</strong> Los tiempos de espera y cuellos de botella son estimaciones orientativas para dimensionamiento logístico. No deben emplearse como criterio para penalizar o dar de baja a recicladores en el sistema real.</li>
          <li><strong>Reproducibilidad Científica:</strong> Cada corrida almacena su semilla pseudoaleatoria fija (`seed`) para permitir que cualquier escenario simulado pueda ser auditado y replicado exactamente.</li>
        </ul>
      </section>

    </div>
  </div>

<!-- ========================================================================= -->
<!-- CONFIGURACIÓN GLOBAL Y SCRIPT REACTIVO DE SIMULACIÓN                     -->
<!-- ========================================================================= -->
<script>
  window.ecopointsSimConfig = {
    baseUrl: '<?= BASE_URL ?>',
    csrfToken: '<?= $csrfToken ?>',
    simulacionInicial: <?= json_encode($simulacionInicial ?? null, JSON_UNESCAPED_UNICODE) ?>
  };
</script>
<script src="<?= asset('js/admin-simulacion.js') ?>"></script>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
