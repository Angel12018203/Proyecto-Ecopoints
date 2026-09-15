<?php
// =====================================================================
// ECOPOINT — DETALLE DE SOLICITUD PARA RECICLADOR
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

$idSolicitud = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("
    SELECT s.*, m.nombre AS material_nombre, m.descripcion AS material_desc, m.puntos_por_kg,
           u.nombre AS ciudadano_nombre, u.apellido AS ciudadano_apellido, u.telefono AS ciudadano_telefono,
           u.correo AS ciudadano_correo
    FROM solicitudes s
    INNER JOIN materiales m ON s.id_material = m.id_material
    INNER JOIN usuarios u ON s.id_ciudadano = u.id_usuario
    WHERE s.id_solicitud = :id
    LIMIT 1
");
$stmt->execute([':id' => $idSolicitud]);
$solicitud = $stmt->fetch();

if (!$solicitud) {
    setFlash('danger', 'La solicitud no existe.');
    header('Location: /reciclador/solicitudes.php');
    exit;
}

$pageTitle = 'Ecopoint — Detalle de Solicitud #' . $solicitud['id_solicitud'];
$headerTitle = 'Detalle de Solicitud #' . $solicitud['id_solicitud'];
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 16px;">
    <a href="/reciclador/solicitudes.php" class="btn btn-o btn-sm">← Volver a solicitudes cercanas</a>
</div>

<div class="g2">
    <!-- Información Principal del Material y Lugar -->
    <div class="card cp">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <span class="bdg bdg-disp">Solicitud #<?= $solicitud['id_solicitud'] ?></span>
            <span class="bdg bdg-proc"><?= h($solicitud['estado']) ?></span>
        </div>

        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 6px;">
            <?= number_format($solicitud['cantidad_estimada'], 1, ',', '.') ?> kg de <?= h($solicitud['material_nombre']) ?>
        </h2>
        <p style="color: var(--muted); font-size: 0.85rem; margin-bottom: 20px;">
            <?= h($solicitud['material_desc']) ?>
        </p>

        <div style="background: var(--gold-light); border: 1px solid #FFE7A8; border-radius: var(--r); padding: 14px 18px; margin-bottom: 20px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #8F5200; text-transform: uppercase;">
                🪙 Ecopuntos Estimados al Validar
            </div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #734300; margin-top: 2px;">
                ~<?= (int)round($solicitud['cantidad_estimada'] * $solicitud['puntos_por_kg']) ?> Ecopuntos
            </div>
            <div style="font-size: 0.74rem; color: #8F5200; margin-top: 2px;">
                Tasa oficial: <?= (int)$solicitud['puntos_por_kg'] ?> puntos por kg pesado en centro de acopio.
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <div class="form-label">Dirección de Recolección:</div>
            <div style="font-size: 0.95rem; font-weight: 600; color: var(--text);">
                <?= h($solicitud['direccion']) ?>
            </div>
            <div style="font-size: 0.82rem; color: var(--muted);">
                Localidad de <?= h($solicitud['localidad']) ?> — Barrio <?= h($solicitud['barrio'] ?? 'Centro') ?>
            </div>
        </div>

        <div class="g2" style="margin-bottom: 16px;">
            <div>
                <div class="form-label">Fecha de Disponibilidad:</div>
                <div style="font-size: 0.88rem; font-weight: 600; color: var(--text);">
                    📅 <?= h($solicitud['fecha_disponibilidad']) ?>
                </div>
            </div>
            <div>
                <div class="form-label">Franja Horaria:</div>
                <div style="font-size: 0.88rem; font-weight: 600; color: var(--text);">
                    ⏰ <?= substr($solicitud['hora_inicio'], 0, 5) ?> - <?= substr($solicitud['hora_fin'], 0, 5) ?>
                </div>
            </div>
        </div>

        <?php if (!empty($solicitud['observaciones'])): ?>
            <div style="margin-bottom: 20px;">
                <div class="form-label">Instrucciones del Ciudadano:</div>
                <div style="background: #F8FAF9; border: 1px solid var(--border); padding: 12px 14px; border-radius: var(--r); font-size: 0.85rem; color: var(--text);">
                    "<?= h($solicitud['observaciones']) ?>"
                </div>
            </div>
        <?php endif; ?>

        <?php if ($solicitud['estado'] === 'Disponible'): ?>
            <form action="/reciclador/solicitudes.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                <input type="hidden" name="action" value="aceptar">
                <input type="hidden" name="id_solicitud" value="<?= $solicitud['id_solicitud'] ?>">
                <button type="submit" class="btn btn-p btn-lg btn-full" onclick="return confirm('¿Confirmas que deseas aceptar esta solicitud?');">
                    ✓ Aceptar esta Solicitud y Agregar a mi Ruta
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-info" style="margin-bottom:0;">
                Esta solicitud se encuentra actualmente en estado <strong><?= h($solicitud['estado']) ?></strong>.
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel de Ubicación y Contacto -->
    <div>
        <!-- Simulación de Mapa de Bogotá / Sector -->
        <div class="card cp" style="margin-bottom: 18px;">
            <div class="card-header-clean">
                <h3 class="card-title">🗺️ Mapa de Ubicación</h3>
                <span class="bdg bdg-disp">Sector <?= h($solicitud['localidad']) ?></span>
            </div>

            <!-- Visualizador simulado de ruta y radar -->
            <div style="height: 220px; background: #EAF4FA; border-radius: var(--r); border: 1px solid #B8E1F9; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                <div style="position: absolute; inset: 0; opacity: 0.15; background-image: radial-gradient(#1E6091 1px, transparent 1px); background-size: 16px 16px;"></div>
                <div style="font-size: 2.8rem; z-index: 1;">📍</div>
                <div style="font-weight: 700; color: #1E6091; font-size: 0.9rem; z-index: 1; margin-top: 4px;">
                    <?= h($solicitud['direccion']) ?>
                </div>
                <div style="font-size: 0.78rem; color: #3A7BA8; z-index: 1;">
                    Aproximadamente a 800 metros de tu ubicación actual
                </div>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="card cp">
            <h3 class="card-title" style="margin-bottom: 12px;">👤 Contacto del Ciudadano</h3>
            <div style="font-size: 0.92rem; font-weight: 600; color: var(--text);">
                <?= h($solicitud['ciudadano_nombre']) ?> <?= h($solicitud['ciudadano_apellido']) ?>
            </div>
            <div style="font-size: 0.84rem; color: var(--muted); margin-top: 4px;">
                📞 Teléfono: <strong><?= h($solicitud['ciudadano_telefono']) ?></strong>
            </div>
            <div style="font-size: 0.84rem; color: var(--muted); margin-top: 2px;">
                ✉️ Correo: <?= h($solicitud['ciudadano_correo']) ?>
            </div>

            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); font-size: 0.78rem; color: var(--muted); line-height: 1.5;">
                ℹ️ <strong>Importante:</strong> Comunícate con el ciudadano únicamente dentro de la franja horaria acordada para coordinar la entrega en portería o puerta de acceso.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
