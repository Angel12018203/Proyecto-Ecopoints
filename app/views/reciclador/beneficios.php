<?php
/**
 * VISTA: CATÁLOGO DE BENEFICIOS Y RECOMPENSAS (ROL RECICLADOR)
 * Basado fielmente en el diseño de Figma (Imagen 4)
 */
require APP_PATH . 'views/layouts/header.php';
require APP_PATH . 'views/layouts/sidebar.php';

$puntosUsuario = (int)($metricas['ecopuntos'] ?? 0);
?>

<div id="main">
  <?php require APP_PATH . 'views/layouts/topbar.php'; ?>

  <div id="pc">
    <div class="pg pg-wide">

      <!-- Notificaciones Flash -->
      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success" style="margin-bottom:20px;display:flex;align-items:center;gap:10px;">
          <span style="font-size:1.2rem;">🎉</span>
          <div><?= h($msg) ?></div>
        </div>
      <?php endif; ?>
      <?php if ($err = flash('error')): ?>
        <div class="alert alert-error" style="margin-bottom:20px;display:flex;align-items:center;gap:10px;">
          <span style="font-size:1.2rem;">⚠️</span>
          <div><?= h($err) ?></div>
        </div>
      <?php endif; ?>

      <!-- Header con Badge de Ecopuntos Disponibles (Fiel a Imagen 4) -->
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:22px;">
        <div>
          <h1 style="font-size:1.6rem;font-weight:800;color:var(--g-800);margin:0 0 6px;">
            Beneficios
          </h1>
          <p style="margin:0;font-size:0.9rem;color:var(--g-500);">
            Canjea tus Ecopuntos por herramientas, alimentación, conectividad y más.
          </p>
        </div>

        <!-- Badge Verde Superior Derecho con Saldo Real -->
        <div style="background:#087A3D;color:#fff;padding:12px 24px;border-radius:14px;box-shadow:0 6px 16px rgba(8,122,61,0.22);text-align:right;">
          <div style="font-size:1.45rem;font-weight:800;display:flex;align-items:center;gap:6px;justify-content:flex-end;">
            <span style="color:#FFD700;">★</span> <?= number_format($puntosUsuario) ?>
          </div>
          <span style="font-size:0.75rem;opacity:0.9;font-weight:600;">Ecopuntos disponibles</span>
        </div>
      </div>

      <!-- Pastillas de Filtro por Categoría -->
      <?php 
        $categorias = [
          'Todos' => 'Todos',
          'Trabajo' => 'Elementos de trabajo',
          'Alimentos' => 'Alimentación',
          'Conectividad' => 'Conectividad',
          'Limpieza' => 'Limpieza',
          'Descuentos' => 'Descuentos'
        ];
      ?>
      <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:12px;margin-bottom:24px;">
        <?php foreach ($categorias as $key => $label): ?>
          <?php $isActive = (strtolower($categoriaActiva) === strtolower($key)); ?>
          <a href="<?= url('reciclador/beneficios', ['categoria' => $key]) ?>" 
             style="text-decoration:none;padding:8px 18px;border-radius:20px;font-size:0.86rem;font-weight:600;white-space:nowrap;transition:all 0.2s;<?= $isActive ? 'background:#087A3D;color:#fff;box-shadow:0 3px 10px rgba(8,122,61,0.25);' : 'background:#fff;color:var(--g-600);border:1px solid var(--g-200);' ?>">
            <?= h($label) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Grid de Tarjetas de Beneficios (Fiel a Imagen 4) -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:22px;">
        <?php foreach ($beneficios as $b): ?>
          <?php
            $costo = (int)$b['costo_ecopuntos'];
            $puedeCanjear = ($puntosUsuario >= $costo);
            $faltan = $costo - $puntosUsuario;

            // Paleta de degradado de banner según categoría
            $grad = 'linear-gradient(135deg, #2D3748 0%, #1A202C 100%)';
            $icon = '🎁';
            if ($b['categoria'] === 'Trabajo') {
              $grad = 'linear-gradient(135deg, #718096 0%, #4A5568 100%)';
              $icon = '🛠️';
            } elseif ($b['categoria'] === 'Alimentos') {
              $grad = 'linear-gradient(135deg, #C53030 0%, #9B2C2C 100%)';
              $icon = '🥗';
            } elseif ($b['categoria'] === 'Conectividad') {
              $grad = 'linear-gradient(135deg, #3182CE 0%, #2B6CB0 100%)';
              $icon = '📱';
            } elseif ($b['categoria'] === 'Limpieza') {
              $grad = 'linear-gradient(135deg, #319795 0%, #285E61 100%)';
              $icon = '🧴';
            } elseif ($b['categoria'] === 'Descuentos') {
              $grad = 'linear-gradient(135deg, #805AD5 0%, #6B46C1 100%)';
              $icon = '📚';
            }
          ?>
          <div class="card" style="background:#fff;border-radius:18px;border:1px solid #E5EBE7;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.2s;">
            
            <div>
              <!-- Imagen / Banner decorativo con icono y gradiente -->
              <div style="background:<?= $grad ?>;height:120px;padding:16px;display:flex;align-items:flex-end;justify-content:space-between;color:#fff;position:relative;">
                <div style="font-size:2.8rem;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                  <?= $icon ?>
                </div>
                <span style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);padding:4px 10px;border-radius:12px;font-size:0.75rem;font-weight:600;">
                  Stock: <?= (int)$b['stock'] ?>
                </span>
              </div>

              <!-- Cuerpo de la tarjeta -->
              <div style="padding:18px 20px 14px;">
                <h3 style="margin:0 0 4px;font-size:1.15rem;font-weight:800;color:var(--g-800);">
                  <?= h($b['nombre']) ?>
                </h3>
                <p style="margin:0 0 10px;font-size:0.83rem;color:var(--g-500);line-height:1.4;">
                  <?= h($b['descripcion']) ?>
                </p>
                <span style="font-size:0.75rem;font-weight:700;color:var(--g-400);text-transform:uppercase;letter-spacing:0.5px;">
                  <?= h($categorias[$b['categoria']] ?? $b['categoria']) ?>
                </span>
              </div>
            </div>

            <!-- Footer: Costo en Puntos y Botón de Canje -->
            <div style="padding:14px 20px 18px;border-top:1px solid #F0F4F1;display:flex;justify-content:space-between;align-items:center;">
              <div style="font-size:1.1rem;font-weight:800;color:var(--g-800);display:flex;align-items:center;gap:4px;">
                <span style="color:#FFB800;">★</span> <?= number_format($costo) ?> <span style="font-size:0.8rem;color:var(--g-500);font-weight:600;">pts</span>
              </div>

              <div>
                <?php if ($puedeCanjear): ?>
                  <form action="<?= url('reciclador/beneficio/canjear') ?>" method="POST" style="margin:0;">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id_beneficio" value="<?= (int)$b['id_beneficio'] ?>">
                    <button type="submit" 
                            onclick="return confirm('¿Confirmas el canje de <?= number_format($costo) ?> Ecopoints por este beneficio?')"
                            class="btn btn-p" 
                            style="padding:8px 16px;border-radius:10px;font-size:0.82rem;font-weight:700;background:#087A3D;display:inline-flex;align-items:center;gap:4px;">
                      ✓ Canjear
                    </button>
                  </form>
                <?php else: ?>
                  <span style="font-size:0.8rem;color:#D9381E;font-weight:700;background:#FDE8E8;padding:6px 12px;border-radius:8px;">
                    Faltan <?= number_format($faltan) ?> pts
                  </span>
                <?php endif; ?>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>

<?php require APP_PATH . 'views/layouts/footer.php'; ?>
