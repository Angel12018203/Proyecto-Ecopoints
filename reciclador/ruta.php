<?php
// =====================================================================
// ECOPOINT — RUTA DE RECOLECCIÓN DEL DÍA
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Consultar recolecciones activas para la ruta
$stmt = $db->prepare("
    SELECT r.*, s.id_solicitud, s.cantidad_estimada, s.direccion, s.localidad, s.barrio,
           s.hora_inicio, s.hora_fin, s.observaciones,
           m.nombre AS material_nombre, m.puntos_por_kg,
           u.nombre AS ciudadano_nombre, u.apellido AS ciudadano_apellido, u.telefono AS ciudadano_telefono
    FROM recolecciones r
    INNER JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    INNER JOIN materiales m ON s.id_material = m.id_material
    INNER JOIN usuarios u ON s.id_ciudadano = u.id_usuario
    WHERE r.id_reciclador = :id AND r.estado IN ('Aceptada', 'En camino')
    ORDER BY s.hora_inicio ASC
");
$stmt->execute([':id' => $userId]);
$paradas = $stmt->fetchAll();

$pageTitle = 'Ecopoint — Mi Ruta de Recolección';
$headerTitle = 'Mi Ruta Diaria';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Mi Recorrido de Hoy 🗺️</h1>
        <p class="psub">Organiza tus paradas ordenadas cronológicamente para optimizar tu tiempo y esfuerzo físico.</p>
    </div>
    <div>
        <a href="/reciclador/solicitudes.php" class="btn btn-p btn-sm">+ Agregar más paradas</a>
    </div>
</div>

<!-- Resumen Visual del Itinerario -->
<div class="card cp" style="background: linear-gradient(135deg, #087A3D, #2E9B50); color: #FFF; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; opacity: 0.85;">Estado de la Ruta</div>
            <div style="font-size: 1.8rem; font-weight: 800;">
                <?= count($paradas) ?> Parada(s) Programada(s)
            </div>
            <div style="font-size: 0.84rem; opacity: 0.9; margin-top: 4px;">
                Zona principal: <strong>Suba</strong> • Transporte: Carreta manual
            </div>
        </div>
        <div>
            <a href="/reciclador/centros.php" class="btn btn-gold btn-sm">
                🏢 Ver Centros de Acopio para Entrega
            </a>
        </div>
    </div>
</div>

<?php if (empty($paradas)): ?>
    <div class="card cp" style="text-align: center; padding: 50px 20px;">
        <div style="font-size: 3.5rem; margin-bottom: 12px;">🚶‍♂️</div>
        <h3 style="font-size: 1.25rem; font-weight: 700;">No tienes paradas activas en tu ruta</h3>
        <p style="color: var(--muted); font-size: 0.86rem; max-width: 460px; margin: 8px auto 20px;">
            Acepta solicitudes publicadas por ciudadanos en tu sector para llenar tu plan de recolección de hoy.
        </p>
        <a href="/reciclador/solicitudes.php" class="btn btn-p">Ver Solicitudes Cercanas</a>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($paradas as $idx => $parada): ?>
            <div class="card cp card-static" style="border-left: 6px solid var(--green); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; gap: 16px; align-items: center;">
                    <!-- Número de Parada -->
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--green-light); color: var(--green); font-size: 1.2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        #<?= $idx + 1 ?>
                    </div>

                    <div>
                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                            <span class="bdg bdg-disp">Solicitud #<?= $parada['id_solicitud'] ?></span>
                            <span class="bdg bdg-proc"><?= h($parada['material_nombre']) ?> (~<?= number_format($parada['cantidad_estimada'], 1) ?> kg)</span>
                            <span style="font-size: 0.75rem; color: var(--muted);">⏰ <?= substr($parada['hora_inicio'], 0, 5) ?> - <?= substr($parada['hora_fin'], 0, 5) ?></span>
                        </div>

                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--text);">
                            📍 <?= h($parada['direccion']) ?> (<?= h($parada['barrio'] ?? $parada['localidad']) ?>)
                        </div>

                        <div style="font-size: 0.82rem; color: var(--muted); margin-top: 2px;">
                            Ciudadano: <strong><?= h($parada['ciudadano_nombre']) ?> <?= h($parada['ciudadano_apellido']) ?></strong> • 📞 <?= h($parada['ciudadano_telefono']) ?>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <!-- Botón para Registrar el Peso Recolectado en Sitio (Paso 6 del Flujo) -->
                    <a href="/reciclador/registrar_recoleccion.php?id=<?= $parada['id_recoleccion'] ?>" class="btn btn-p btn-sm">
                        ⚖️ Registrar Peso Recolectado
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
