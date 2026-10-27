<?php
/**
 * VISTA: MI IMPACTO AMBIENTAL (ROL RECICLADOR)
 * Basado fielmente en el diseño de Figma (Imagen 5)
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';

$puntosUsuario = (int)($metricas['ecopuntos'] ?? 0);
$kgTotal = (float)($impacto['material_recuperado'] ?? 0);
$co2 = (float)($impacto['co2_evitado'] ?? 0);
$totalRec = (int)($impacto['total_recolecciones'] ?? 0);
$materiales = $impacto['materiales'] ?? [];
$evolucion = $impacto['evolucion_mensual'] ?? [];
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg pg-wide">

      <!-- Header -->
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
        <div>
          <h1 style="font-size:1.6rem;font-weight:800;color:var(--g-800);margin:0 0 6px;">
            Mi impacto ambiental
          </h1>
          <p style="margin:0;font-size:0.9rem;color:var(--g-500);">
            Tus estadísticas y el impacto de tu trabajo de reciclaje en el planeta.
          </p>
        </div>

        <div style="background:#087A3D;color:#fff;padding:10px 20px;border-radius:12px;display:flex;align-items:center;gap:6px;font-weight:800;font-size:1.15rem;box-shadow:0 4px 12px rgba(8,122,61,0.2);">
          <span style="color:#FFD700;">★</span> <?= number_format($puntosUsuario) ?> pts
        </div>
      </div>

      <!-- 4 Tarjetas KPI Superiores (Fiel a Imagen 5) -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:18px;margin-bottom:24px;">
        
        <!-- KPI 1: Recolecciones -->
        <div class="card" style="background:#fff;border-radius:16px;padding:22px;border:1px solid #E5EBE7;box-shadow:0 2px 8px rgba(0,0,0,0.02);">
          <div style="font-size:1.8rem;margin-bottom:8px;">♻️</div>
          <div style="font-size:1.9rem;font-weight:800;color:var(--g-800);line-height:1;">
            <?= $totalRec ?>
          </div>
          <div style="font-size:0.82rem;color:var(--g-500);font-weight:600;margin-top:6px;">
            Recolecciones
          </div>
        </div>

        <!-- KPI 2: Material Recuperado -->
        <div class="card" style="background:#fff;border-radius:16px;padding:22px;border:1px solid #E5EBE7;box-shadow:0 2px 8px rgba(0,0,0,0.02);">
          <div style="font-size:1.8rem;margin-bottom:8px;">📦</div>
          <div style="font-size:1.9rem;font-weight:800;color:var(--g-800);line-height:1;">
            <?= number_format($kgTotal, 1) ?> kg
          </div>
          <div style="font-size:0.82rem;color:var(--g-500);font-weight:600;margin-top:6px;">
            Material recuperado
          </div>
        </div>

        <!-- KPI 3: Ecopuntos acumulados -->
        <div class="card" style="background:#fff;border-radius:16px;padding:22px;border:1px solid #E5EBE7;box-shadow:0 2px 8px rgba(0,0,0,0.02);">
          <div style="font-size:1.8rem;margin-bottom:8px;color:#FFB800;">⭐</div>
          <div style="font-size:1.9rem;font-weight:800;color:var(--g-800);line-height:1;">
            <?= number_format($puntosUsuario) ?>
          </div>
          <div style="font-size:0.82rem;color:var(--g-500);font-weight:600;margin-top:6px;">
            Ecopuntos acumulados
          </div>
        </div>

        <!-- KPI 4: CO2 Evitado -->
        <div class="card" style="background:#fff;border-radius:16px;padding:22px;border:1px solid #E5EBE7;box-shadow:0 2px 8px rgba(0,0,0,0.02);">
          <div style="font-size:1.8rem;margin-bottom:8px;">🌱</div>
          <div style="font-size:1.9rem;font-weight:800;color:#2E7D32;line-height:1;">
            <?= number_format($co2, 1) ?> kg
          </div>
          <div style="font-size:0.82rem;color:var(--g-500);font-weight:600;margin-top:6px;">
            CO₂ evitado estimado
          </div>
        </div>

      </div>

      <!-- Fila Intermedia: Gráfico Mensual + Barras por Material (Fiel a Imagen 5) -->
      <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:24px;margin-bottom:24px;">
        
        <!-- Panel Izquierdo: Material recuperado por mes (kg) -->
        <div class="card" style="background:#fff;border-radius:18px;padding:24px;border:1px solid #E5EBE7;">
          <h3 style="font-size:1.05rem;font-weight:800;color:var(--g-800);margin:0 0 20px;">
            Material recuperado por mes (kg)
          </h3>

          <div style="display:flex;align-items:flex-end;justify-content:space-between;height:180px;padding-top:20px;border-bottom:2px solid #E5EBE7;">
            <?php 
              $maxKg = max(array_column($evolucion, 'kg') ?: [100]);
              foreach ($evolucion as $m): 
                $alturaPct = max(15, min(100, round(($m['kg'] / $maxKg) * 100)));
            ?>
              <div style="display:flex;flex-direction:column;align-items:center;flex:1;">
                <span style="font-size:0.75rem;font-weight:700;color:var(--g-600);margin-bottom:6px;">
                  <?= (int)$m['kg'] ?>
                </span>
                <div style="width:28px;height:<?= $alturaPct ?>%;background:#087A3D;border-radius:6px 6px 0 0;transition:height 0.3s;"></div>
                <span style="font-size:0.75rem;color:var(--g-400);font-weight:600;margin-top:8px;">
                  <?= h($m['mes']) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Panel Derecho: Por Material -->
        <div class="card" style="background:#fff;border-radius:18px;padding:24px;border:1px solid #E5EBE7;">
          <h3 style="font-size:1.05rem;font-weight:800;color:var(--g-800);margin:0 0 20px;">
            Por material
          </h3>

          <?php 
            $colores = [
              'Cartón'   => '#C86828',
              'Plástico' => '#2E7D32',
              'Vidrio'   => '#7B1FA2',
              'Papel'    => '#00838F',
              'Metales'  => '#E65100'
            ];
          ?>
          <div style="display:flex;flex-direction:column;gap:18px;">
            <?php foreach ($materiales as $mat): ?>
              <?php 
                $nombre = $mat['material'];
                $kgMat = (float)$mat['total_kg'];
                $color = $colores[$nombre] ?? '#087A3D';
                $pct = ($kgTotal > 0) ? min(100, round(($kgMat / $kgTotal) * 100)) : 20;
              ?>
              <div>
                <div style="display:flex;justify-content:space-between;font-size:0.85rem;font-weight:700;margin-bottom:6px;">
                  <span style="color:var(--g-700);"><?= h($nombre) ?></span>
                  <span style="color:var(--g-800);"><?= number_format($kgMat, 0) ?> kg</span>
                </div>
                <div style="width:100%;height:8px;background:#EEF3F0;border-radius:10px;overflow:hidden;">
                  <div style="width:<?= $pct ?>%;height:100%;background:<?= $color ?>;border-radius:10px;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <!-- Banner Verde Inferior: Tu Impacto en el Planeta (Fiel a Imagen 5) -->
      <div style="background:linear-gradient(135deg, #087A3D 0%, #0F6636 100%);border-radius:18px;padding:28px 32px;color:#fff;box-shadow:0 8px 24px rgba(8,122,61,0.22);">
        
        <div style="display:flex;align-items:center;gap:10px;font-size:1.15rem;font-weight:800;margin-bottom:20px;">
          <span>🌍</span> Tu impacto en el planeta
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:24px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.2);">
          <div>
            <div style="font-size:2rem;font-weight:800;line-height:1;margin-bottom:4px;">
              <?= number_format($kgTotal, 1) ?> kg
            </div>
            <div style="font-size:0.84rem;opacity:0.85;">Material recuperado</div>
          </div>

          <div>
            <div style="font-size:2rem;font-weight:800;line-height:1;margin-bottom:4px;">
              <?= number_format($co2, 1) ?> kg
            </div>
            <div style="font-size:0.84rem;opacity:0.85;">CO₂ evitado</div>
          </div>

          <div>
            <div style="font-size:2rem;font-weight:800;line-height:1;margin-bottom:4px;">
              <?= $totalRec ?>
            </div>
            <div style="font-size:0.84rem;opacity:0.85;">Entregas validadas</div>
          </div>
        </div>

        <div style="font-size:0.9rem;opacity:0.95;display:flex;align-items:center;gap:8px;">
          Tu trabajo como reciclador es fundamental en la economía circular. ¡Gracias por tu esfuerzo diario! 🌱
        </div>

      </div>

    </div>
  </div>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
