<?php
// =====================================================================
// ECOPOINT — MIS SOLICITUDES (CIUDADANO)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('ciudadano');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Procesar cancelación de solicitud si aún está disponible
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancelar') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('danger', 'Error de seguridad CSRF.');
    } else {
        $idSol = (int)($_POST['id_solicitud'] ?? 0);
        $check = $db->prepare("SELECT estado FROM solicitudes WHERE id_solicitud = :id AND id_ciudadano = :user_id");
        $check->execute([':id' => $idSol, ':user_id' => $userId]);
        $estado = $check->fetchColumn();

        if ($estado === 'Disponible') {
            $db->prepare("UPDATE solicitudes SET estado = 'Cancelada' WHERE id_solicitud = :id")->execute([':id' => $idSol]);
            setFlash('info', "La Solicitud #{$idSol} ha sido cancelada.");
        } else {
            setFlash('danger', 'No puedes cancelar una solicitud que ya ha sido aceptada o recolectada.');
        }
        header('Location: /ciudadano/solicitudes.php');
        exit;
    }
}

// Filtro de estado
$filtroEstado = trim($_GET['estado'] ?? '');
$sql = "
    SELECT s.*, m.nombre AS material_nombre, m.puntos_por_kg,
           r.id_recoleccion, r.cantidad_recolectada,
           ur.nombre AS reciclador_nombre, ur.apellido AS reciclador_apellido, ur.telefono AS reciclador_telefono
    FROM solicitudes s
    INNER JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN recolecciones r ON s.id_solicitud = r.id_solicitud
    LEFT JOIN usuarios ur ON r.id_reciclador = ur.id_usuario
    WHERE s.id_ciudadano = :id
";
$params = [':id' => $userId];

if (!empty($filtroEstado)) {
    $sql .= " AND s.estado = :estado";
    $params[':estado'] = $filtroEstado;
}

$sql .= " ORDER BY s.id_solicitud DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$solicitudes = $stmt->fetchAll();

$pageTitle = 'Ecopoint — Mis Solicitudes de Recolección';
$headerTitle = 'Mis Solicitudes';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Mis Solicitudes Publicadas 📋</h1>
        <p class="psub">Consulta el estado en vivo de recolección de tus materiales reciclables.</p>
    </div>
    <div>
        <a href="/ciudadano/solicitar.php" class="btn btn-p btn-sm">+ Nueva Solicitud</a>
    </div>
</div>

<!-- Filtros de Estado -->
<div class="frow">
    <a href="/ciudadano/solicitudes.php" class="fp <?= empty($filtroEstado) ? 'act' : '' ?>">Todas</a>
    <a href="/ciudadano/solicitudes.php?estado=Disponible" class="fp <?= $filtroEstado === 'Disponible' ? 'act' : '' ?>">En Espera de Reciclador</a>
    <a href="/ciudadano/solicitudes.php?estado=Aceptada" class="fp <?= $filtroEstado === 'Aceptada' ? 'act' : '' ?>">Aceptadas en Ruta</a>
    <a href="/ciudadano/solicitudes.php?estado=Recolectada" class="fp <?= $filtroEstado === 'Recolectada' ? 'act' : '' ?>">Recolectadas</a>
    <a href="/ciudadano/solicitudes.php?estado=Validada" class="fp <?= $filtroEstado === 'Validada' ? 'act' : '' ?>">Validadas en Centro</a>
</div>

<?php if (empty($solicitudes)): ?>
    <div class="card cp" style="text-align: center; padding: 50px 20px;">
        <div style="font-size: 3rem; margin-bottom: 12px;">🌱</div>
        <h3 style="font-size: 1.2rem; font-weight: 700;">No tienes solicitudes en esta categoría</h3>
        <p style="color: var(--muted); font-size: 0.86rem; margin: 8px auto 20px; max-width: 440px;">
            Separa tus materiales aprovechables y programa una recolección para ayudar a los recicladores de oficio.
        </p>
        <a href="/ciudadano/solicitar.php" class="btn btn-p">Crear Solicitud Ahora</a>
    </div>
<?php else: ?>
    <div class="card" style="border: 1px solid var(--border);">
        <div class="table-responsive">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Material</th>
                        <th>Cantidad Est.</th>
                        <th>Fecha y Horario</th>
                        <th>Reciclador Asignado</th>
                        <th>Estado Actual</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $sol): ?>
                        <tr>
                            <td>
                                <strong>#<?= $sol['id_solicitud'] ?></strong>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= substr($sol['fecha_solicitud'], 0, 10) ?></div>
                            </td>
                            <td>
                                <span class="bdg bdg-proc"><?= h($sol['material_nombre']) ?></span>
                            </td>
                            <td><strong><?= number_format($sol['cantidad_estimada'], 1) ?> kg</strong></td>
                            <td>
                                <div><?= h($sol['fecha_disponibilidad']) ?></div>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= substr($sol['hora_inicio'], 0, 5) ?> - <?= substr($sol['hora_fin'], 0, 5) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($sol['reciclador_nombre'])): ?>
                                    <div style="font-weight: 600; color: var(--green);">
                                        ♻️ <?= h($sol['reciclador_nombre']) ?> <?= h($sol['reciclador_apellido']) ?>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--muted);">📞 <?= h($sol['reciclador_telefono']) ?></div>
                                <?php else: ?>
                                    <span style="color: var(--muted); font-style: italic;">Buscando reciclador...</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sol['estado'] === 'Disponible'): ?>
                                    <span class="bdg bdg-disp">Buscando Reciclador</span>
                                <?php elseif ($sol['estado'] === 'Aceptada'): ?>
                                    <span class="bdg bdg-proc">En Ruta de Recogida</span>
                                <?php elseif ($sol['estado'] === 'Recolectada'): ?>
                                    <span class="bdg bdg-reco">Recolectada</span>
                                <?php elseif ($sol['estado'] === 'Validada'): ?>
                                    <span class="bdg bdg-vali">✓ Validada en Balanza</span>
                                <?php elseif ($sol['estado'] === 'Cancelada'): ?>
                                    <span class="bdg bdg-canc">Cancelada</span>
                                <?php else: ?>
                                    <span class="bdg bdg-proc"><?= h($sol['estado']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="/ciudadano/detalle_solicitud.php?id=<?= $sol['id_solicitud'] ?>" class="btn btn-o btn-sm">
                                        Ver Trazabilidad
                                    </a>

                                    <?php if ($sol['estado'] === 'Disponible'): ?>
                                        <form action="/ciudadano/solicitudes.php" method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar esta solicitud?');" style="margin:0;">
                                            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                                            <input type="hidden" name="action" value="cancelar">
                                            <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
