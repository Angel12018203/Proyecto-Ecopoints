<?php
// =====================================================================
// ECOPOINT — DASHBOARD DEL CIUDADANO
// (SECCIÓN 22 DEL MANUAL)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

// Exigir estrictamente rol de ciudadano
requireRole('ciudadano');

$userId = getCurrentUserId();
$db = Database::getConnection();

// 1. Obtener perfil del ciudadano
$stmtPerfil = $db->prepare("
    SELECT pc.*, u.nombre, u.apellido, u.correo, u.telefono
    FROM perfil_ciudadano pc
    INNER JOIN usuarios u ON pc.id_usuario = u.id_usuario
    WHERE pc.id_usuario = :id
    LIMIT 1
");
$stmtPerfil->execute([':id' => $userId]);
$perfil = $stmtPerfil->fetch();

// 2. Conteo de solicitudes por estado
$stmtStats = $db->prepare("
    SELECT 
        COUNT(CASE WHEN estado IN ('Disponible', 'Aceptada', 'En proceso') THEN 1 END) AS activas,
        COUNT(CASE WHEN estado IN ('Recolectada', 'Entregada', 'Validada') THEN 1 END) AS completadas,
        COUNT(CASE WHEN estado = 'Cancelada' THEN 1 END) AS canceladas,
        IFNULL(SUM(CASE WHEN estado = 'Validada' THEN cantidad_estimada ELSE 0 END), 0) AS kg_reciclados
    FROM solicitudes
    WHERE id_ciudadano = :id
");
$stmtStats->execute([':id' => $userId]);
$stats = $stmtStats->fetch();

// 3. Solicitudes recientes
$stmtRecientes = $db->prepare("
    SELECT s.*, m.nombre AS material_nombre, m.icono AS material_icono,
           r.id_recoleccion, r.estado AS recoleccion_estado,
           ur.nombre AS reciclador_nombre, ur.apellido AS reciclador_apellido, ur.telefono AS reciclador_telefono
    FROM solicitudes s
    INNER JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN recolecciones r ON s.id_solicitud = r.id_solicitud
    LEFT JOIN usuarios ur ON r.id_reciclador = ur.id_usuario
    WHERE s.id_ciudadano = :id
    ORDER BY s.id_solicitud DESC
    LIMIT 5
");
$stmtRecientes->execute([':id' => $userId]);
$solicitudesRecientes = $stmtRecientes->fetchAll();

$pageTitle = 'Ecopoint — Panel del Ciudadano';
$headerTitle = 'Panel del Ciudadano';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">¡Hola, <?= h($perfil['nombre'] ?? 'Ciudadano') ?>! 👋</h1>
        <p class="psub">Gestiona la recolección selectiva de tus materiales reciclables en <strong><?= h($perfil['localidad'] ?? 'Suba') ?></strong>.</p>
    </div>
    <div>
        <!-- ACCIÓN PRINCIPAL DEL CIUDADANO (SECCIÓN 22) -->
        <a href="/ciudadano/solicitar.php" class="btn btn-p btn-lg" style="box-shadow: 0 6px 18px rgba(8,122,61,.3);">
            <span>+</span> Solicitar Recolección
        </a>
    </div>
</div>

<!-- Métricas de Impacto Ciudadano -->
<div class="g3" style="margin-bottom: 24px;">
    <!-- Solicitudes Activas -->
    <div class="card cp">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Solicitudes Activas</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--green); margin-top: 4px;">
                    <?= (int)$stats['activas'] ?>
                </div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--green-light); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                ⏳
            </div>
        </div>
        <div style="font-size: 0.78rem; color: var(--muted); margin-top: 10px;">
            En espera o en camino de recogida
        </div>
    </div>

    <!-- Solicitudes Completadas -->
    <div class="card cp">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Recolecciones Exitosas</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: #1E6091; margin-top: 4px;">
                    <?= (int)$stats['completadas'] ?>
                </div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EAF4FA; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                ✓
            </div>
        </div>
        <div style="font-size: 0.78rem; color: #1E6091; font-weight: 600; margin-top: 10px;">
            Material entregado a recicladores
        </div>
    </div>

    <!-- Total Kilos Aportados -->
    <div class="card cp">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Tu Aporte Ecológico</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: #8F5200; margin-top: 4px;">
                    <?= number_format((float)$stats['kg_reciclados'], 1) ?> <span style="font-size: 1rem; font-weight: 500;">kg</span>
                </div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF8E7; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                🌱
            </div>
        </div>
        <div style="font-size: 0.78rem; color: #8F5200; font-weight: 600; margin-top: 10px;">
            Reincorporado a la industria
        </div>
    </div>
</div>

<!-- Banner de Separación Correcta -->
<div class="card cp" style="background: #F4FAF6; border-color: #CBEACF; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; gap: 14px; align-items: center;">
            <div style="font-size: 2.2rem;">📦</div>
            <div>
                <div style="font-weight: 700; color: var(--green); font-size: 1rem;">Material limpio, seco y separado</div>
                <div style="font-size: 0.82rem; color: var(--muted); margin-top: 2px;">
                    Facilita el trabajo del reciclador empaquetando el cartón plegado y las botellas plásticas sin líquidos.
                </div>
            </div>
        </div>
        <a href="/ciudadano/solicitar.php" class="btn btn-p btn-sm">
            Crear Nueva Solicitud
        </a>
    </div>
</div>

<!-- Listado de Solicitudes Recientes -->
<div class="card" style="border: 1px solid var(--border);">
    <div class="cp" style="padding-bottom: 0;">
        <div class="card-header-clean">
            <div>
                <h3 class="card-title">Mis Solicitudes de Recolección</h3>
                <div style="font-size: 0.78rem; color: var(--muted);">Seguimiento de entregas programadas</div>
            </div>
            <a href="/ciudadano/solicitudes.php" class="btn btn-o btn-sm">Ver todas</a>
        </div>
    </div>

    <?php if (empty($solicitudesRecientes)): ?>
        <div style="text-align: center; padding: 40px; color: var(--muted);">
            <div style="font-size: 2.5rem; margin-bottom: 8px;">🗑️</div>
            Aún no has creado solicitudes de recolección. ¡Haz tu primera solicitud hoy!
            <div style="margin-top: 14px;">
                <a href="/ciudadano/solicitar.php" class="btn btn-p btn-sm">Solicitar Recolección</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Material</th>
                        <th>Cantidad Est.</th>
                        <th>Fecha / Franja</th>
                        <th>Reciclador Asignado</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudesRecientes as $sol): ?>
                        <tr>
                            <td><strong>#<?= $sol['id_solicitud'] ?></strong></td>
                            <td><span class="bdg bdg-proc"><?= h($sol['material_nombre']) ?></span></td>
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
                                    <span style="color: var(--muted); font-style: italic;">Esperando reciclador</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sol['estado'] === 'Disponible'): ?>
                                    <span class="bdg bdg-disp">Buscando Reciclador</span>
                                <?php elseif ($sol['estado'] === 'Aceptada'): ?>
                                    <span class="bdg bdg-proc">Aceptada por Reciclador</span>
                                <?php elseif ($sol['estado'] === 'Recolectada'): ?>
                                    <span class="bdg bdg-reco">Recolectada</span>
                                <?php elseif ($sol['estado'] === 'Validada'): ?>
                                    <span class="bdg bdg-vali">✓ Validada en Centro</span>
                                <?php else: ?>
                                    <span class="bdg bdg-proc"><?= h($sol['estado']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="/ciudadano/detalle_solicitud.php?id=<?= $sol['id_solicitud'] ?>" class="btn btn-o btn-sm">
                                    Ver Estado →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
