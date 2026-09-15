<?php
// =====================================================================
// ECOPOINT — MIS ECOPUNTOS E HISTORIAL DE MOVIMIENTOS
// (PASO 10 DEL FLUJO MAESTRO)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Consultar perfil de reciclador
$stmtPerfil = $db->prepare("SELECT * FROM perfil_reciclador WHERE id_usuario = :id LIMIT 1");
$stmtPerfil->execute([':id' => $userId]);
$perfil = $stmtPerfil->fetch();

$saldoActual = (int)($perfil['ecopuntos'] ?? 0);
$materialRecuperado = (float)($perfil['material_recuperado'] ?? 0);
$nivel = $perfil['nivel'] ?? 'Reciclador Destacado';

// Historial de Ecopuntos generados por entregas
$stmtPuntos = $db->prepare("
    SELECT ep.*, e.cantidad_validada, e.fecha_entrega, c.nombre AS centro_nombre, m.nombre AS material_nombre
    FROM ecopuntos ep
    INNER JOIN entregas e ON ep.id_entrega = e.id_entrega
    INNER JOIN centros_acopio c ON e.id_centro = c.id_centro
    INNER JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
    INNER JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    INNER JOIN materiales m ON s.id_material = m.id_material
    WHERE ep.id_reciclador = :id
    ORDER BY ep.id_ecopunto DESC
");
$stmtPuntos->execute([':id' => $userId]);
$movimientos = $stmtPuntos->fetchAll();

// Historial de Canjes de Beneficios
$stmtCanjes = $db->prepare("
    SELECT c.*, b.nombre AS beneficio_nombre, b.costo_ecopuntos
    FROM canjes c
    INNER JOIN beneficios b ON c.id_beneficio = b.id_beneficio
    WHERE c.id_usuario = :id
    ORDER BY c.id_canje DESC
");
$stmtCanjes->execute([':id' => $userId]);
$canjes = $stmtCanjes->fetchAll();

$pageTitle = 'Ecopoint — Mis Ecopuntos';
$headerTitle = 'Mis Ecopuntos y Recompensas';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Mis Ecopuntos 🪙</h1>
        <p class="psub">Historial de puntos generados tras validaciones oficiales de pesaje en centros de acopio.</p>
    </div>
    <div>
        <a href="/reciclador/beneficios.php" class="btn btn-gold btn-sm">
            🎁 Canjear por Beneficios
        </a>
    </div>
</div>

<!-- Tarjetas de Resumen de Saldo y Nivel -->
<div class="g3" style="margin-bottom: 24px;">
    <div class="stat-hero">
        <div class="hero-points-sub">Saldo Disponible</div>
        <div class="hero-points-num"><?= number_format($saldoActual, 0, ',', '.') ?></div>
        <div style="font-size: 0.82rem; margin-top: 6px; opacity: 0.9;">Ecopuntos para canjear en catálogo</div>
    </div>

    <div class="card cp">
        <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Nivel de Reciclador</div>
        <div style="font-size: 1.4rem; font-weight: 800; color: var(--green); margin-top: 8px;">
            🎖️ <?= h($nivel) ?>
        </div>
        <p style="font-size: 0.8rem; color: var(--muted); margin-top: 6px; line-height: 1.4;">
            Con <?= number_format($saldoActual, 0) ?> puntos acumulados accedes a beneficios preferenciales de bienestar y herramientas.
        </p>
    </div>

    <div class="card cp">
        <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Material Certificado</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--text); margin-top: 6px;">
            <?= number_format($materialRecuperado, 1, ',', '.') ?> <span style="font-size: 0.95rem; font-weight: 500;">kg</span>
        </div>
        <div style="font-size: 0.78rem; color: var(--green); font-weight: 600; margin-top: 8px;">
            ✓ Pesaje 100% verificado en balanza
        </div>
    </div>
</div>

<!-- Tabla de Movimientos de Ecopuntos Generados -->
<div class="card" style="margin-bottom: 24px; border: 1px solid var(--border);">
    <div class="cp" style="padding-bottom: 0;">
        <h2 class="card-title">Movimientos Recientes de Ecopuntos (Entregas Validadas)</h2>
        <div style="font-size: 0.78rem; color: var(--muted);">Acreditaciones directas de pesaje en centros de acopio autorizados</div>
    </div>

    <?php if (empty($movimientos)): ?>
        <div style="text-align: center; padding: 40px; color: var(--muted);">
            Aún no tienes movimientos registrados. Realiza una entrega en un centro de acopio para recibir tus primeros Ecopuntos.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto / Motivo</th>
                        <th>Centro de Acopio</th>
                        <th>Peso Validado</th>
                        <th style="text-align: right;">Ecopuntos Acreditados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td>
                                <div><?= substr($mov['fecha_generacion'], 0, 10) ?></div>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= substr($mov['fecha_generacion'], 11, 5) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?= h($mov['motivo']) ?></div>
                                <div style="font-size: 0.72rem; color: var(--muted);">Transacción #<?= $mov['id_ecopunto'] ?> • Entrega #<?= $mov['id_entrega'] ?></div>
                            </td>
                            <td><?= h($mov['centro_nombre']) ?></td>
                            <td>
                                <strong style="color: var(--text);"><?= number_format($mov['cantidad_validada'], 1) ?> kg</strong>
                                <span style="font-size: 0.72rem; color: var(--muted);">(<?= h($mov['material_nombre']) ?>)</span>
                            </td>
                            <td style="text-align: right;">
                                <span class="pts-pill" style="display: inline-flex; font-size: 0.9rem; padding: 4px 12px;">
                                    +<?= number_format($mov['cantidad_puntos'], 0, ',', '.') ?> pts
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Canjes Realizados -->
<?php if (!empty($canjes)): ?>
    <div class="card" style="border: 1px solid var(--border);">
        <div class="cp" style="padding-bottom: 0;">
            <h2 class="card-title">Canjes de Beneficios Redimidos</h2>
        </div>
        <div class="table-responsive">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>Fecha Canje</th>
                        <th>Beneficio</th>
                        <th>Puntos Descontados</th>
                        <th>Código Único</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($canjes as $canje): ?>
                        <tr>
                            <td><?= substr($canje['fecha_canje'], 0, 16) ?></td>
                            <td><strong><?= h($canje['beneficio_nombre']) ?></strong></td>
                            <td style="color: #C5221F; font-weight: 700;">-<?= number_format($canje['ecopuntos_utilizados'], 0) ?> pts</td>
                            <td><code><?= h($canje['codigo_canje']) ?></code></td>
                            <td><span class="bdg bdg-vali"><?= h($canje['estado']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
