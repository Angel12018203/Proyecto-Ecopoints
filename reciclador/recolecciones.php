<?php
// =====================================================================
// ECOPOINT — MIS RECOLECCIONES (RECICLADOR)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Consultar todas las recolecciones del reciclador
$stmt = $db->prepare("
    SELECT r.*, s.id_solicitud, s.cantidad_estimada, s.direccion, s.localidad, s.barrio,
           s.hora_inicio, s.hora_fin,
           m.nombre AS material_nombre, m.puntos_por_kg,
           u.nombre AS ciudadano_nombre, u.apellido AS ciudadano_apellido, u.telefono AS ciudadano_telefono,
           e.id_entrega, e.estado AS estado_entrega
    FROM recolecciones r
    INNER JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    INNER JOIN materiales m ON s.id_material = m.id_material
    INNER JOIN usuarios u ON s.id_ciudadano = u.id_usuario
    LEFT JOIN entregas e ON r.id_recoleccion = e.id_recoleccion
    WHERE r.id_reciclador = :id
    ORDER BY r.id_recoleccion DESC
");
$stmt->execute([':id' => $userId]);
$recolecciones = $stmt->fetchAll();

$pageTitle = 'Ecopoint — Mis Recolecciones';
$headerTitle = 'Mis Recolecciones';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Mis Recolecciones 📦</h1>
        <p class="psub">Control de solicitudes aceptadas, pesos recogidos y estado de traslado a centros de acopio.</p>
    </div>
    <div>
        <a href="/reciclador/solicitudes.php" class="btn btn-p btn-sm">+ Aceptar nueva recolección</a>
    </div>
</div>

<?php if (empty($recolecciones)): ?>
    <div class="card cp" style="text-align: center; padding: 50px 20px;">
        <div style="font-size: 3.5rem; margin-bottom: 12px;">📋</div>
        <h3 style="font-size: 1.25rem; font-weight: 700;">Aún no tienes recolecciones registradas</h3>
        <p style="color: var(--muted); font-size: 0.86rem; max-width: 460px; margin: 8px auto 20px;">
            Acepta solicitudes publicadas en tu zona para comenzar tu jornada de recolección y acumular materiales.
        </p>
        <a href="/reciclador/solicitudes.php" class="btn btn-p">Ver Solicitudes Cercanas</a>
    </div>
<?php else: ?>
    <div class="card" style="border: 1px solid var(--border);">
        <div class="table-responsive">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>ID / Solicitud</th>
                        <th>Material</th>
                        <th>Punto de Recogida</th>
                        <th>Ciudadano</th>
                        <th>Cant. Recolectada</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recolecciones as $rec): ?>
                        <tr>
                            <td>
                                <strong>#<?= $rec['id_recoleccion'] ?></strong>
                                <div style="font-size: 0.72rem; color: var(--muted);">Sol. #<?= $rec['id_solicitud'] ?></div>
                            </td>
                            <td>
                                <span class="bdg bdg-proc"><?= h($rec['material_nombre']) ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?= h($rec['direccion']) ?></div>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= h($rec['localidad']) ?> - <?= h($rec['barrio'] ?? '') ?></div>
                            </td>
                            <td>
                                <div><?= h($rec['ciudadano_nombre']) ?> <?= h($rec['ciudadano_apellido']) ?></div>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= h($rec['ciudadano_telefono']) ?></div>
                            </td>
                            <td>
                                <?php if ($rec['cantidad_recolectada']): ?>
                                    <strong style="color: var(--green);"><?= number_format($rec['cantidad_recolectada'], 1) ?> kg</strong>
                                <?php else: ?>
                                    <span style="color: var(--muted);">Est. <?= number_format($rec['cantidad_estimada'], 1) ?> kg</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($rec['estado'] === 'Aceptada'): ?>
                                    <span class="bdg bdg-disp">En Ruta / Aceptada</span>
                                <?php elseif ($rec['estado'] === 'Recolectada'): ?>
                                    <?php if ($rec['id_entrega']): ?>
                                        <span class="bdg bdg-vali">Entregada en Centro</span>
                                    <?php else: ?>
                                        <span class="bdg bdg-proc">Pendiente de Entrega</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="bdg bdg-reco"><?= h($rec['estado']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($rec['estado'] === 'Aceptada'): ?>
                                    <a href="/reciclador/registrar_recoleccion.php?id=<?= $rec['id_recoleccion'] ?>" class="btn btn-p btn-sm">
                                        ⚖️ Registrar Peso
                                    </a>
                                <?php elseif ($rec['estado'] === 'Recolectada' && empty($rec['id_entrega'])): ?>
                                    <a href="/reciclador/entregas.php?rec_id=<?= $rec['id_recoleccion'] ?>" class="btn btn-gold btn-sm">
                                        🏢 Registrar Entrega
                                    </a>
                                <?php else: ?>
                                    <a href="/reciclador/entregas.php" class="btn btn-o btn-sm">
                                        Ver Entrega
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
