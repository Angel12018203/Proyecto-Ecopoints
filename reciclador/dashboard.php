<?php
// =====================================================================
// ECOPOINT — DASHBOARD DEL RECICLADOR
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

// Exigir estrictamente rol de reciclador
requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// 1. Obtener perfil del reciclador
$stmtPerfil = $db->prepare("
    SELECT pr.*, u.nombre, u.apellido, u.correo, u.telefono
    FROM perfil_reciclador pr
    INNER JOIN usuarios u ON pr.id_usuario = u.id_usuario
    WHERE pr.id_usuario = :id
    LIMIT 1
");
$stmtPerfil->execute([':id' => $userId]);
$perfil = $stmtPerfil->fetch();

$ecopuntos = (int)($perfil['ecopuntos'] ?? 0);
$materialRecuperado = (float)($perfil['material_recuperado'] ?? 0);
$nivel = $perfil['nivel'] ?? 'Reciclador Activo';
$zona = $perfil['zona_trabajo'] ?? 'Suba';

// 2. Conteo de solicitudes cercanas disponibles
$stmtCercanas = $db->prepare("
    SELECT COUNT(*) 
    FROM solicitudes 
    WHERE estado = 'Disponible' AND localidad = :zona
");
$stmtCercanas->execute([':zona' => $zona]);
$totalCercanas = (int)$stmtCercanas->fetchColumn();

// 3. Conteo de recolecciones activas (Aceptadas o en camino)
$stmtActivas = $db->prepare("
    SELECT COUNT(*) 
    FROM recolecciones 
    WHERE id_reciclador = :id AND estado IN ('Aceptada', 'En camino')
");
$stmtActivas->execute([':id' => $userId]);
$totalActivas = (int)$stmtActivas->fetchColumn();

// 4. Conteo de centros de acopio en la zona
$totalCentros = (int)$db->query("SELECT COUNT(*) FROM centros_acopio WHERE estado = 'activo'")->fetchColumn();

// 5. Últimas solicitudes disponibles en su zona para vista rápida
$stmtUltimas = $db->prepare("
    SELECT s.*, m.nombre AS material_nombre, m.icono AS material_icono, m.puntos_por_kg,
           u.nombre AS ciudadano_nombre
    FROM solicitudes s
    INNER JOIN materiales m ON s.id_material = m.id_material
    INNER JOIN usuarios u ON s.id_ciudadano = u.id_usuario
    WHERE s.estado = 'Disponible'
    ORDER BY s.id_solicitud DESC
    LIMIT 4
");
$stmtUltimas->execute();
$ultimasSolicitudes = $stmtUltimas->fetchAll();

// 6. Recolecciones pendientes de entrega a centro de acopio
$stmtPendientesEntrega = $db->prepare("
    SELECT r.*, s.direccion, s.localidad, m.nombre AS material_nombre
    FROM recolecciones r
    INNER JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    INNER JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN entregas e ON r.id_recoleccion = e.id_recoleccion
    WHERE r.id_reciclador = :id AND r.estado = 'Recolectada' AND e.id_entrega IS NULL
    ORDER BY r.id_recoleccion DESC
");
$stmtPendientesEntrega->execute([':id' => $userId]);
$pendientesEntrega = $stmtPendientesEntrega->fetchAll();

$pageTitle = 'Ecopoint — Panel de Reciclador';
$headerTitle = 'Panel del Reciclador';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">¡Hola, <?= h($perfil['nombre'] ?? 'Reciclador') ?>! 👋</h1>
        <p class="psub">Zona de trabajo actual: <strong><?= h($zona) ?></strong> • Nivel: <strong><?= h($nivel) ?></strong></p>
    </div>
    <div>
        <!-- ACCIÓN PRINCIPAL DEL RECICLADOR SEGÚN SECCIÓN 21 -->
        <a href="/reciclador/solicitudes.php" class="btn btn-p btn-lg" style="box-shadow: 0 6px 18px rgba(8,122,61,.3);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            Ver solicitudes cercanas
        </a>
    </div>
</div>

<!-- Métricas Principales (Sección 21) -->
<div class="g4" style="margin-bottom: 24px;">
    <!-- Tarjeta Héroe Ecopuntos -->
    <div class="stat-hero">
        <div class="hero-points-sub">Saldo Disponible</div>
        <div class="hero-points-num"><?= number_format($ecopuntos, 0, ',', '.') ?></div>
        <div style="font-size: 0.82rem; margin-top: 6px; opacity: 0.95;">🪙 Ecopuntos acumulados</div>
        <div style="margin-top: 14px;">
            <a href="/reciclador/beneficios.php" class="btn btn-gold btn-sm">Canjear beneficios</a>
        </div>
    </div>

    <!-- Material Recuperado -->
    <div class="card cp">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Material Recuperado</div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text); margin-top: 4px;">
                    <?= number_format($materialRecuperado, 1, ',', '.') ?> <span style="font-size: 1rem; font-weight: 500;">kg</span>
                </div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--green-light); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                ⚖️
            </div>
        </div>
        <div style="font-size: 0.78rem; color: var(--green); font-weight: 600; margin-top: 12px;">
            ✓ Pesado y validado en centros
        </div>
    </div>

    <!-- Solicitudes Cercanas -->
    <div class="card cp">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Oportunidades en <?= h($zona) ?></div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text); margin-top: 4px;">
                    <?= $totalCercanas ?>
                </div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #EAF4FA; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                📍
            </div>
        </div>
        <div style="font-size: 0.78rem; color: var(--muted); margin-top: 12px;">
            Listas para aceptar hoy
        </div>
    </div>

    <!-- Recolecciones Activas -->
    <div class="card cp">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 0.76rem; font-weight: 700; color: var(--muted); text-transform: uppercase;">Recolecciones en Curso</div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--text); margin-top: 4px;">
                    <?= $totalActivas ?>
                </div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF8E7; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                🚛
            </div>
        </div>
        <div style="font-size: 0.78rem; color: #9C6A00; font-weight: 600; margin-top: 12px;">
            <a href="/reciclador/ruta.php">Ver mi ruta de hoy →</a>
        </div>
    </div>
</div>

<?php if (!empty($pendientesEntrega)): ?>
    <!-- Alerta de material recolectado pendiente de llevar al centro de acopio -->
    <div class="card cp" style="background: #FFF9E6; border-color: #FFE699; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-weight: 700; color: #8F5200; font-size: 0.95rem;">
                    📦 Tienes <?= count($pendientesEntrega) ?> recolección(es) lista(s) para entregar en centro de acopio
                </div>
                <div style="font-size: 0.82rem; color: #805B10; margin-top: 2px;">
                    Recuerda: Los Ecopuntos se generarán únicamente cuando el centro pese y valide el material.
                </div>
            </div>
            <a href="/reciclador/entregas.php" class="btn btn-p btn-sm" style="background: #9C6A00;">
                Registrar Entrega en Centro →
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Oportunidades de Recolección Recientes -->
<div class="card cp" style="margin-bottom: 24px;">
    <div class="card-header-clean">
        <div>
            <h2 class="card-title">Solicitudes Disponibles para Recoger</h2>
            <div style="font-size: 0.78rem; color: var(--muted);">Ciudadanos esperando recolección en tu sector</div>
        </div>
        <a href="/reciclador/solicitudes.php" class="btn btn-o btn-sm">Ver todas (<?= $totalCercanas ?>)</a>
    </div>

    <?php if (empty($ultimasSolicitudes)): ?>
        <div style="text-align: center; padding: 30px; color: var(--muted);">
            No hay solicitudes disponibles en este momento. ¡Buen trabajo!
        </div>
    <?php else: ?>
        <div class="g2">
            <?php foreach ($ultimasSolicitudes as $sol): ?>
                <div class="card cp card-static" style="border: 1.5px solid var(--border); background: #FCFDFC;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <div>
                            <span class="bdg bdg-disp">Solicitud #<?= $sol['id_solicitud'] ?></span>
                            <span class="bdg bdg-proc" style="margin-left: 4px;"><?= h($sol['material_nombre']) ?></span>
                        </div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--green);">
                            ~800 m distancia
                        </div>
                    </div>

                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text); margin-bottom: 4px;">
                        <?= number_format($sol['cantidad_estimada'], 1, ',', '.') ?> kg de <?= h($sol['material_nombre']) ?>
                    </div>

                    <div style="font-size: 0.82rem; color: var(--muted); margin-bottom: 6px;">
                        📍 <strong><?= h($sol['direccion']) ?></strong> (<?= h($sol['localidad']) ?> - <?= h($sol['barrio'] ?? '') ?>)
                    </div>

                    <div style="font-size: 0.78rem; color: var(--muted); margin-bottom: 14px;">
                        🕒 Horario: <?= substr($sol['hora_inicio'], 0, 5) ?> - <?= substr($sol['hora_fin'], 0, 5) ?>
                    </div>

                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="/reciclador/solicitud_detalle.php?id=<?= $sol['id_solicitud'] ?>" class="btn btn-o btn-sm">
                            Ver Detalle
                        </a>
                        <form action="/reciclador/solicitudes.php" method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                            <input type="hidden" name="action" value="aceptar">
                            <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                            <button type="submit" class="btn btn-p btn-sm">
                                Aceptar Solicitud
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Enlaces Rápidos de Operación -->
<div class="g3">
    <div class="card cp">
        <div style="font-size: 2rem; margin-bottom: 8px;">🗺️</div>
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Mi Ruta</h3>
        <p style="font-size: 0.82rem; color: var(--muted); margin-bottom: 12px;">Organiza tus paradas ordenadas de manera eficiente para ahorrar tiempo y esfuerzo.</p>
        <a href="/reciclador/ruta.php" class="btn btn-sec btn-sm">Ir a mi ruta →</a>
    </div>

    <div class="card cp">
        <div style="font-size: 2rem; margin-bottom: 8px;">🏢</div>
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Centros de Acopio</h3>
        <p style="font-size: 0.82rem; color: var(--muted); margin-bottom: 12px;">Consulta horarios, teléfonos y ubicaciones de los puntos autorizados con pesaje digital.</p>
        <a href="/reciclador/centros.php" class="btn btn-sec btn-sm">Consultar centros →</a>
    </div>

    <div class="card cp">
        <div style="font-size: 2rem; margin-bottom: 8px;">🌱</div>
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Mi Impacto Ambiental</h3>
        <p style="font-size: 0.82rem; color: var(--muted); margin-bottom: 12px;">Descubre cuántos árboles y litros de agua has salvado con tu trabajo diario de reciclaje.</p>
        <a href="/reciclador/impacto.php" class="btn btn-sec btn-sm">Ver impacto →</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
