<?php
/**
 * VISTA: CENTROS DE ACOPIO (ROL RECICLADOR)
 * Basado fielmente en el diseño de Figma (Imagen 3)
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg pg-wide">

      <!-- Header -->
      <div style="margin-bottom: 22px;">
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--g-800); margin: 0 0 6px;">
          Centros de acopio
        </h1>
        <p style="margin: 0; font-size: 0.9rem; color: var(--g-500);">
          Encuentra dónde entregar tus materiales y validar tu recuperación.
        </p>
      </div>

      <!-- Layout 2 Columnas: Mapa interactivo + Lista de Centros -->
      <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Columna Izquierda: Mapa Visual Interactivo (Fiel a Imagen 3) -->
        <div class="card" style="background:#E2EFE6;border-radius:20px;padding:24px;position:relative;min-height:560px;display:flex;flex-direction:column;justify-content:space-between;border:1px solid #D1E5D8;overflow:hidden;">
          
          <!-- Cuadrícula simulada de mapa urbano -->
          <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:grid;grid-template-columns:repeat(4, 1fr);grid-template-rows:repeat(4, 1fr);gap:16px;padding:20px;box-sizing:border-box;opacity:0.4;pointer-events:none;">
            <?php for ($i = 0; $i < 16; $i++): ?>
              <div style="background:#CCDDCF;border-radius:12px;"></div>
            <?php endfor; ?>
          </div>

          <!-- Líneas de Ruta Interconectadas -->
          <svg style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:2;">
            <line x1="30%" y1="62%" x2="48%" y2="28%" stroke="#087A3D" stroke-width="2.5" stroke-dasharray="6 6" opacity="0.6"/>
            <line x1="30%" y1="62%" x2="68%" y2="44%" stroke="#087A3D" stroke-width="2.5" stroke-dasharray="6 6" opacity="0.6"/>
          </svg>

          <!-- Pin 1: Posición del Reciclador "TÚ" -->
          <div style="position:absolute;top:56%;left:26%;z-index:3;text-align:center;">
            <div style="width:40px;height:40px;background:#087A3D;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;box-shadow:0 4px 14px rgba(8,122,61,0.4);border:3px solid #fff;margin:0 auto 4px;">
              📍
            </div>
            <span style="background:#087A3D;color:#fff;font-size:0.75rem;font-weight:800;padding:2px 8px;border-radius:10px;">
              TÚ
            </span>
          </div>

          <!-- Pin 2: EcoCentro Suba -->
          <div style="position:absolute;top:20%;left:42%;z-index:3;text-align:center;">
            <div style="width:48px;height:48px;background:#fff;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 6px 18px rgba(0,0,0,0.1);border:2px solid #087A3D;margin:0 auto 4px;">
              🏢
            </div>
            <span style="background:#fff;color:var(--g-800);font-size:0.75rem;font-weight:700;padding:3px 8px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);display:inline-block;">
              EcoCentro Suba
            </span>
          </div>

          <!-- Pin 3: Centro Verde -->
          <div style="position:absolute;top:38%;left:64%;z-index:3;text-align:center;">
            <div style="width:44px;height:44px;background:#fff;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;box-shadow:0 6px 18px rgba(0,0,0,0.1);border:2px solid #2E7D32;margin:0 auto 4px;">
              🏬
            </div>
            <span style="background:#fff;color:var(--g-800);font-size:0.75rem;font-weight:700;padding:3px 8px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);display:inline-block;">
              C. Verde
            </span>
          </div>

          <!-- Pin 4: Acopio Circular -->
          <div style="position:absolute;top:70%;left:68%;z-index:3;text-align:center;">
            <div style="width:44px;height:44px;background:#fff;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;box-shadow:0 6px 18px rgba(0,0,0,0.1);border:2px solid #D9381E;margin:0 auto 4px;">
              🏭
            </div>
            <span style="background:#fff;color:var(--g-800);font-size:0.75rem;font-weight:700;padding:3px 8px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);display:inline-block;">
              Circular
            </span>
          </div>

          <!-- Leyenda del Mapa Inferior -->
          <div style="z-index:4;margin-top:auto;display:flex;align-items:center;gap:18px;background:rgba(255,255,255,0.85);backdrop-filter:blur(4px);padding:10px 16px;border-radius:12px;width:fit-content;font-size:0.8rem;font-weight:600;">
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="width:10px;height:10px;border-radius:50%;background:#087A3D;"></span>
              Tu posición
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="width:10px;height:10px;border-radius:50%;background:#2E7D32;"></span>
              Abierto
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <span style="width:10px;height:10px;border-radius:50%;background:#D9381E;"></span>
              Cerrado
            </div>
          </div>

        </div>

        <!-- Columna Derecha: Tarjetas de Centros de Acopio Reales -->
        <div style="display:flex;flex-direction:column;gap:16px;">
          <?php foreach ($centros as $idx => $c): ?>
            <?php
              $distancia = [2.5, 3.8, 5.1, 6.2][$idx % 4] . ' km';
              $isAbierto = ($idx !== 2); // Ejemplo acorde al diseño
              $mats = explode(',', $c['materiales_recibidos'] ?? 'Plástico, Cartón, Vidrio');
            ?>
            <div class="card" style="background:#fff;border-radius:16px;border:1px solid #E5EBE7;padding:18px 20px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
              
              <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:12px;">
                  <div style="width:40px;height:40px;background:#F0F7F2;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                    🏢
                  </div>
                  <div>
                    <h3 style="margin:0;font-size:1.05rem;font-weight:800;color:var(--g-800);">
                      <?= h($c['nombre']) ?>
                    </h3>
                    <div style="font-size:0.78rem;color:var(--g-500);margin-top:2px;">
                      📍 <?= $distancia ?> · <?= h($c['direccion']) ?>
                    </div>
                  </div>
                </div>

                <span style="display:inline-flex;align-items:center;gap:5px;font-size:0.75rem;font-weight:700;color:<?= $isAbierto ? '#2E7D32' : '#C62828' ?>;">
                  <span style="width:7px;height:7px;border-radius:50%;background:<?= $isAbierto ? '#2E7D32' : '#C62828' ?>;"></span>
                  <?= $isAbierto ? 'Abierto' : 'Cerrado' ?>
                </span>
              </div>

              <!-- Horario -->
              <div style="font-size:0.8rem;color:var(--g-600);margin-bottom:12px;">
                🕒 <?= substr($c['hora_apertura'], 0, 5) ?> - <?= substr($c['hora_cierre'], 0, 5) ?> · Resp: <?= h($c['responsable'] ?? 'Oficial') ?>
              </div>

              <!-- Pastillas de Materiales Recibidos -->
              <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
                <?php foreach ($mats as $m): ?>
                  <span style="background:#F2F7F4;color:#1B5E20;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:12px;">
                    <?= h(trim($m)) ?>
                  </span>
                <?php endforeach; ?>
              </div>

              <!-- Botón Ver Centro -->
              <button type="button" 
                      onclick="mostrarDetalleCentro(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>, '<?= $distancia ?>')"
                      style="width:100%;background:#087A3D;color:#fff;border:none;padding:10px;border-radius:10px;font-size:0.84rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:background 0.2s;">
                Ver centro &rarr;
              </button>

            </div>
          <?php endforeach; ?>
        </div>

      </div>

    </div>
  </div>

<!-- Modal Detalle Centro -->
<div id="modal-centro" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:18px;max-width:480px;width:100%;padding:28px;box-shadow:0 12px 36px rgba(0,0,0,0.2);position:relative;">
    <button onclick="cerrarModalCentro()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--g-400);">&times;</button>
    <div id="modal-centro-content"></div>
  </div>
</div>

<script>
function mostrarDetalleCentro(c, dist) {
  var div = document.getElementById('modal-centro-content');
  div.innerHTML = `
    <div style="font-size:0.8rem;font-weight:700;color:var(--g-400);text-transform:uppercase;margin-bottom:6px;">Centro de Acopio Autorizado</div>
    <h2 style="margin:0 0 12px;font-size:1.35rem;font-weight:800;color:var(--g-800);">${c.nombre}</h2>
    
    <div style="background:#F5F9F6;padding:16px;border-radius:12px;margin-bottom:18px;font-size:0.88rem;line-height:1.6;">
      <div><strong>Dirección:</strong> ${c.direccion} (${dist})</div>
      <div><strong>Horario de atención:</strong> ${c.hora_apertura} a ${c.hora_cierre}</div>
      <div><strong>Responsable de pesaje:</strong> ${c.responsable || 'Ana Morales'}</div>
      <div><strong>Teléfono de contacto:</strong> ${c.telefono || '315 889 4410'}</div>
      <div style="margin-top:8px;"><strong>Materiales recibidos:</strong> ${c.materiales_recibidos}</div>
    </div>

    <button onclick="cerrarModalCentro()" class="btn btn-p" style="width:100%;padding:11px;background:#087A3D;border-radius:10px;font-weight:700;justify-content:center;">
      Entendido, volver al mapa
    </button>
  `;
  document.getElementById('modal-centro').style.display = 'flex';
}

function cerrarModalCentro() {
  document.getElementById('modal-centro').style.display = 'none';
}
</script>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
