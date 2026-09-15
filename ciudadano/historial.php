<?php
// =====================================================================
// ECOPOINT — HISTORIAL DE RECICLAJE (CIUDADANO)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('ciudadano');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Consultar todas las solicitudes finalizadas o históricas
$stmt = $db->prepare("
    SELECT s.*, m.nombre AS material_nombre,
           r.cantidad_recolectada, r.fecha_recoleccion,
           ur.nombre AS reciclador_nombre, ur.apellido AS reciclador_apellido,
           e.cantidad_validada, e.fecha_entrega, c.nombre AS centro_nombre
    FROM solicitudes s
    INNER JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN recolecciones r ON s.id_solicitud = r.id_solicitud
    LEFT JOIN usuarios ur ON r.id_reciclador = ur.id_usuario
    LEFT JOIN entregas e ON r.id_recoleccion = e.id_recoleccion
    LEFT JOIN centros_acopio c ON e.id_centro = c.id_centro
    WHERE s.id_ciudadano = :id AND s.estado IN ('Recolectada', 'Entregada', 'Validada')
    ORDER BY s.id_solicitud DESC
");
$stmt->execute([':id' => $userId]);
$historial = $stmt->fetchAll();

// Sumar total kg
$totalKg = 0;
foreach ($historial as $h) {
    $totalKg += (float)($h['cantidad_validada'] ?? $h['cantidad_recolectada'] ?? $h['cantidad_estimada']);
}

$pageTitle = 'Ecopoint — Historial de Reciclaje';
$headerTitle = 'Historial de Entregas';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Historial de Reciclaje 📜</h1>
        <p class="psub">Registro de todas tus donaciones de material aprovechable entregadas a recicladores.</p>
    </div>
</div>

<div class="card cp" style="background: var(--green-light); border: 1.5px solid #CBEACF; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: var(--green); text-transform: uppercase;">Aporte Total a la Economía Circular</div>
            <div style="font-size: 2.4rem; font-weight: 800; color: var(--green-dark); line-height: 1.1; margin-top: 4px;">
                <?= number_format($totalKg, 1) ?> kg recuperados
            </div>
            <div style="font-size: 0.82rem; color: var(--muted); margin-top: 4px;">
                Gracias a tu separación en la fuente has evitado que estos residuos lleguen al relleno Doña Juana.
            </div>
        </div>
        <div>
            <a href="/ciudadano/solicitar.php" class="btn btn-p btn-sm">+ Solicitar Nueva Recolección</a>
        </div>
    </div>
</div>

<div class="card" style="border: 1px solid var(--border);">
    <div class="cp" style="padding-bottom: 0;">
        <h2 class="card-title">Entregas Realizadas con Éxito</h2>
    </div>

    <?php if (empty($historial)): ?>
        <div style="text-align: center; padding: 40px; color: var(--muted);">
            Aún no tienes recolecciones completadas en tu historial.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Fecha Recogida</th>
                        <th>Material</th>
                        <th>Cantidad Final</th>
                        <th>Reciclador Asistente</th>
                        <th>Centro de Acopio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><strong>#<?= $h['id_solicitud'] ?></strong></td>
                            <td><?= substr($h['fecha_recoleccion'] ?? $h['fecha_disponibilidad'], 0, 10) ?></td>
                            <td><span class="bdg bdg-proc"><?= h($h['material_nombre']) ?></span></td>
                            <td>
                                <strong style="color: var(--green); font-size: 0.95rem;">
                                    <?= number_format($h['cantidad_validada'] ?? $h['cantidad_recolectada'] ?? $h['cantidad_estimada'], 1) ?> kg
                                </strong>
                            </td>
                            <td>
                                <div><?= h($h['reciclador_nombre'] ?? 'Reciclador asignado') ?> <?= h($h['reciclador_apellido'] ?? '') ?></div>
                            </td>
                            <td><?= h($h['centro_nombre'] ?? 'Punto Autorizado') ?></td>
                            <td><span class="bdg bdg-vali">✓ <?= h($h['estado']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
