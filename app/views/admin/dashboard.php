<?php
/**
 * ============================================================================
 * VISTA: DASHBOARD DEL ADMINISTRADOR (Analítica Global y Minería de Datos)
 * ============================================================================
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg pg-wide">

      <!-- Header del Administrador -->
      <div style="background: linear-gradient(135deg, #087A3D 0%, #155E38 100%); border-radius: var(--r-xl); padding: 26px 30px; margin-bottom: 24px; color: #fff; box-shadow: 0 8px 24px rgba(8, 122, 61, 0.18);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
          <div>
            <span style="display:inline-block; font-size:0.75rem; font-weight:700; background:rgba(255,255,255,0.2); padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
              🛡️ Rol: Administrador General
            </span>
            <h1 style="font-size: 1.6rem; font-weight: 800; margin: 0; color: #fff;">
              Panel de Control y Analítica Global
            </h1>
            <p style="margin: 6px 0 0; opacity: 0.9; font-size: 0.92rem;">
              Monitoreo operativo en tiempo real, métricas del ecosistema y preparación de datos para minería.
            </p>
          </div>
          <div style="text-align:right;">
            <div style="font-size:0.8rem; opacity:0.85;">Cobertura Oficial</div>
            <div style="font-size:1.1rem; font-weight:700;">20 Localidades de Bogotá</div>
          </div>
        </div>
      </div>

      <!-- Tarjetas KPI Principales -->
      <div class="g4" style="margin-bottom: 24px;">
        
        <!-- KPI 1: Recicladores -->
        <div class="card" style="padding: 20px; border-left: 4px solid #087A3D;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.82rem; font-weight:600; color:var(--g-500); text-transform:uppercase;">Recicladores Activos</span>
            <span style="background:var(--p-50); color:var(--p-600); padding:6px; border-radius:8px;">👥</span>
          </div>
          <div style="font-size: 1.85rem; font-weight: 800; color: var(--g-800); margin: 10px 0 4px;">
            <?= number_format($kpis['total_recicladores']) ?>
          </div>
          <span style="font-size:0.78rem; color:#2E9B50; font-weight:600;">Padrón caracterizado 100%</span>
        </div>

        <!-- KPI 2: Entregas Validadas -->
        <div class="card" style="padding: 20px; border-left: 4px solid #2E9B50;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.82rem; font-weight:600; color:var(--g-500); text-transform:uppercase;">Entregas Validadas</span>
            <span style="background:var(--p-50); color:var(--p-600); padding:6px; border-radius:8px;">⚖️</span>
          </div>
          <div style="font-size: 1.85rem; font-weight: 800; color: var(--g-800); margin: 10px 0 4px;">
            <?= number_format($kpis['total_entregas']) ?>
          </div>
          <span style="font-size:0.78rem; color:var(--g-500);">En centros de acopio</span>
        </div>

        <!-- KPI 3: Kg Recuperados -->
        <div class="card" style="padding: 20px; border-left: 4px solid #00BFA5;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.82rem; font-weight:600; color:var(--g-500); text-transform:uppercase;">Material Recuperado</span>
            <span style="background:#E0F7F4; color:#00796B; padding:6px; border-radius:8px;">📦</span>
          </div>
          <div style="font-size: 1.85rem; font-weight: 800; color: var(--g-800); margin: 10px 0 4px;">
            <?= number_format($kpis['total_kg'], 1) ?> <span style="font-size:1rem; font-weight:600;">kg</span>
          </div>
          <span style="font-size:0.78rem; color:#00796B; font-weight:600;">
            ≈ <?= number_format($kpis['total_kg'] / 1000, 2) ?> Toneladas
          </span>
        </div>

        <!-- KPI 4: Canjes de Beneficios -->
        <div class="card" style="padding: 20px; border-left: 4px solid #FF9800;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.82rem; font-weight:600; color:var(--g-500); text-transform:uppercase;">Canjes de Beneficios</span>
            <span style="background:#FFF3E0; color:#E65100; padding:6px; border-radius:8px;">🎁</span>
          </div>
          <div style="font-size: 1.85rem; font-weight: 800; color: var(--g-800); margin: 10px 0 4px;">
            <?= number_format($kpis['total_canjes']) ?>
          </div>
          <span style="font-size:0.78rem; color:#E65100; font-weight:600;">
            <?= number_format($kpis['total_puntos']) ?> pts emitidos
          </span>
        </div>

      </div>

      <!-- Sección 2 Columnas: Distribución de Materiales y Top Recicladores -->
      <div class="g2" style="margin-bottom: 24px;">

        <!-- Tarjeta: Desglose por Material -->
        <div class="card" style="padding: 24px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="font-size:1.1rem; font-weight:700; margin:0; color:var(--g-800);">
              Distribución de Materiales Recuperados
            </h2>
            <span class="badge" style="background:var(--p-50); color:var(--p-600); font-weight:600;">5 Categorías</span>
          </div>

          <div style="display:flex; flex-direction:column; gap:14px;">
            <?php foreach ($materiales as $mat): ?>
              <div>
                <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:4px;">
                  <span style="font-weight:600; color:var(--g-700);">
                    <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= h($mat['color_hex'] ?? '#087A3D') ?>; margin-right:6px;"></span>
                    <?= h($mat['material']) ?>
                  </span>
                  <span style="color:var(--g-500); font-size:0.82rem;">
                    <strong><?= number_format($mat['total_kg'], 1) ?> kg</strong> (<?= h($mat['porcentaje']) ?>%)
                  </span>
                </div>
                <div style="width:100%; height:8px; background:var(--g-100); border-radius:4px; overflow:hidden;">
                  <div style="width:<?= h($mat['porcentaje']) ?>%; height:100%; background:<?= h($mat['color_hex'] ?? '#087A3D') ?>; border-radius:4px;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tarjeta: Top Recicladores -->
        <div class="card" style="padding: 24px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="font-size:1.1rem; font-weight:700; margin:0; color:var(--g-800);">
              Top Recicladores del Ecosistema
            </h2>
            <span class="badge badge-success">Destacados</span>
          </div>

          <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.86rem;">
              <thead>
                <tr style="border-bottom:1px solid var(--g-100); color:var(--g-500); text-align:left;">
                  <th style="padding:8px 6px;">Reciclador</th>
                  <th style="padding:8px 6px;">Localidad</th>
                  <th style="padding:8px 6px; text-align:right;">Ecopoints</th>
                  <th style="padding:8px 6px; text-align:right;">Material (kg)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($topRecicladores as $rec): ?>
                  <tr style="border-bottom:1px solid var(--g-50);">
                    <td style="padding:10px 6px; font-weight:600; color:var(--g-800);">
                      #<?= h($rec['id_usuario']) ?> <?= h($rec['nombre_completo']) ?>
                    </td>
                    <td style="padding:10px 6px; color:var(--g-600);"><?= h($rec['localidad']) ?></td>
                    <td style="padding:10px 6px; text-align:right; font-weight:700; color:#087A3D;">
                      <?= number_format($rec['ecopuntos']) ?> pts
                    </td>
                    <td style="padding:10px 6px; text-align:right; color:var(--g-700);">
                      <?= number_format($rec['material_recuperado'], 1) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Sección: Vista Analítica del Diccionario de Datos (Minería) -->
      <div class="card" style="padding: 24px; margin-bottom: 24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
          <div>
            <h2 style="font-size:1.15rem; font-weight:700; margin:0; color:var(--g-800);">
              Vista Analítica Homologada para Minería de Datos
            </h2>
            <p style="margin:4px 0 0; font-size:0.84rem; color:var(--g-500);">
              Proyección de la vista SQL nativa <code>vw_diccionario_ecopoints_mensual</code> (Muestra de los primeros registros).
            </p>
          </div>
          <div style="display:flex; gap:8px;">
            <span class="badge" style="background:#E8F5E9; color:#1B5E20; font-weight:600;">9 Columnas Oficiales</span>
            <span class="badge" style="background:#E3F2FD; color:#0D47A1; font-weight:600;">Granularidad Mensual</span>
          </div>
        </div>

        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:0.82rem; text-align:center;">
            <thead>
              <tr style="background:var(--g-50); color:var(--g-700); font-weight:700; border-bottom:2px solid var(--g-100);">
                <th style="padding:10px 8px;">reciclador_id</th>
                <th style="padding:10px 8px;">mes</th>
                <th style="padding:10px 8px;">material_principal</th>
                <th style="padding:10px 8px;">kg_recolectados</th>
                <th style="padding:10px 8px;">entregas</th>
                <th style="padding:10px 8px;">diversidad</th>
                <th style="padding:10px 8px;">beneficios_redimidos</th>
                <th style="padding:10px 8px;">puntos_obtenidos</th>
                <th style="padding:10px 8px; background:#E8F5E9; color:#087A3D;">puntos_mes_siguiente (Target)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($diccionarioMuestra as $row): ?>
                <tr style="border-bottom:1px solid var(--g-100);">
                  <td style="padding:8px; font-weight:600;">Reciclador #<?= h($row['reciclador_id']) ?></td>
                  <td style="padding:8px;"><?= h($row['periodo']) ?></td>
                  <td style="padding:8px;">
                    <span class="badge" style="background:#F0FDF4; color:#15803D; font-weight:600; font-size:0.75rem;">
                      <?= h($row['material_principal']) ?>
                    </span>
                  </td>
                  <td style="padding:8px; font-weight:600;"><?= number_format($row['kg_recolectados'], 1) ?></td>
                  <td style="padding:8px;"><?= h($row['entregas']) ?></td>
                  <td style="padding:8px;"><?= h($row['diversidad_materiales']) ?></td>
                  <td style="padding:8px;"><?= h($row['beneficios_redimidos']) ?></td>
                  <td style="padding:8px; font-weight:700; color:#087A3D;"><?= number_format($row['puntos_obtenidos'], 1) ?></td>
                  <td style="padding:8px; font-weight:700; background:#FAFCFA; color:<?= $row['puntos_mes_siguiente'] === '—' ? '#999' : '#087A3D' ?>;">
                    <?= is_numeric($row['puntos_mes_siguiente']) ? number_format((float)$row['puntos_mes_siguiente'], 1) : h($row['puntos_mes_siguiente']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>

