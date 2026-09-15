<?php
// =====================================================================
// ECOPOINT — SOLICITUDES CERCANAS DISPONIBLES PARA RECICLADOR
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Procesar aceptación de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'aceptar') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('danger', 'Error de seguridad CSRF.');
    } else {
        $idSolicitud = (int)($_POST['id_solicitud'] ?? 0);
        
        try {
            $db->beginTransaction();

            // Verificar que esté Disponible
            $checkStmt = $db->prepare("SELECT id_solicitud, estado, cantidad_estimada FROM solicitudes WHERE id_solicitud = :id FOR UPDATE");
            $checkStmt->execute([':id' => $idSolicitud]);
            $sol = $checkStmt->fetch();

            if (!$sol) {
                throw new Exception('La solicitud no existe.');
            }
            if ($sol['estado'] !== 'Disponible') {
                throw new Exception('Esta solicitud ya ha sido aceptada por otro reciclador o no está disponible.');
            }

            // Cambiar estado a Aceptada
            $updateStmt = $db->prepare("UPDATE solicitudes SET estado = 'Aceptada' WHERE id_solicitud = :id");
            $updateStmt->execute([':id' => $idSolicitud]);

            // Crear registro en la tabla recolecciones (Sección 14)
            $insertRec = $db->prepare("
                INSERT INTO recolecciones (id_solicitud, id_reciclador, fecha_aceptacion, estado)
                VALUES (:id_solicitud, :id_reciclador, NOW(), 'Aceptada')
            ");
            $insertRec->execute([
                ':id_solicitud'  => $idSolicitud,
                ':id_reciclador' => $userId
            ]);

            $db->commit();
            setFlash('success', "¡Excelente! Has aceptado la Solicitud #{$idSolicitud}. Ahora puedes coordinar la recolección en tu ruta.");
            header('Location: /reciclador/recolecciones.php');
            exit;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            setFlash('danger', $e->getMessage());
        }
    }
}

// Filtros
$filtroMaterial = (int)($_GET['material'] ?? 0);
$filtroLocalidad = trim($_GET['localidad'] ?? '');

$sql = "
    SELECT s.*, m.nombre AS material_nombre, m.puntos_por_kg, m.icono AS material_icono,
           u.nombre AS ciudadano_nombre, u.apellido AS ciudadano_apellido, u.telefono AS ciudadano_telefono
    FROM solicitudes s
    INNER JOIN materiales m ON s.id_material = m.id_material
    INNER JOIN usuarios u ON s.id_ciudadano = u.id_usuario
    WHERE s.estado = 'Disponible'
";
$params = [];

if ($filtroMaterial > 0) {
    $sql .= " AND s.id_material = :material";
    $params[':material'] = $filtroMaterial;
}
if (!empty($filtroLocalidad)) {
    $sql .= " AND s.localidad = :localidad";
    $params[':localidad'] = $filtroLocalidad;
}

$sql .= " ORDER BY s.id_solicitud DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$solicitudes = $stmt->fetchAll();

// Catálogo de materiales para selector de filtro
$materiales = $db->query("SELECT * FROM materiales WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll();

$pageTitle = 'Ecopoint — Solicitudes Cercanas';
$headerTitle = 'Solicitudes Cercanas';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Oportunidades de Recolección</h1>
        <p class="psub">Explora las solicitudes publicadas por ciudadanos en tu zona y agrégalas a tu recorrido.</p>
    </div>
</div>

<!-- Barra de Filtros -->
<div class="card cp" style="margin-bottom: 22px; padding: 16px 20px;">
    <form method="GET" action="/reciclador/solicitudes.php" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-size: 0.78rem;">Filtrar por Material:</label>
            <select name="material" class="form-select" onchange="this.form.submit()">
                <option value="0">Todos los materiales</option>
                <?php foreach ($materiales as $mat): ?>
                    <option value="<?= $mat['id_material'] ?>" <?= $filtroMaterial === (int)$mat['id_material'] ? 'selected' : '' ?>>
                        <?= h($mat['nombre']) ?> (<?= (int)$mat['puntos_por_kg'] ?> pts/kg)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-size: 0.78rem;">Filtrar por Localidad:</label>
            <select name="localidad" class="form-select" onchange="this.form.submit()">
                <option value="">Todas las localidades</option>
                <option value="Suba" <?= $filtroLocalidad === 'Suba' ? 'selected' : '' ?>>Suba</option>
                <option value="Engativá" <?= $filtroLocalidad === 'Engativá' ? 'selected' : '' ?>>Engativá</option>
                <option value="Usaquén" <?= $filtroLocalidad === 'Usaquén' ? 'selected' : '' ?>>Usaquén</option>
            </select>
        </div>

        <div>
            <a href="/reciclador/solicitudes.php" class="btn btn-o btn-sm" style="height: 38px;">Limpiar</a>
        </div>
    </form>
</div>

<!-- Listado de Solicitudes -->
<?php if (empty($solicitudes)): ?>
    <div class="card cp" style="text-align: center; padding: 50px 20px;">
        <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
        <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text);">No se encontraron solicitudes disponibles</h3>
        <p style="color: var(--muted); font-size: 0.88rem; max-width: 480px; margin: 8px auto 20px;">
            Prueba cambiando los filtros seleccionados o vuelve a consultar más tarde cuando los ciudadanos publiquen nuevos materiales.
        </p>
        <a href="/reciclador/solicitudes.php" class="btn btn-sec">Ver todas las solicitudes</a>
    </div>
<?php else: ?>
    <div class="g2">
        <?php foreach ($solicitudes as $sol): ?>
            <div class="card cp card-static" style="border: 1.5px solid var(--border); background: #FFF;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <span class="bdg bdg-disp">Solicitud #<?= $sol['id_solicitud'] ?></span>
                        <span class="bdg bdg-proc"><?= h($sol['material_nombre']) ?></span>
                        <?php if ($sol['prioridad'] === 'Alta' || $sol['prioridad'] === 'Urgente'): ?>
                            <span class="bdg bdg-canc"><?= h($sol['prioridad']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--green); background: var(--green-light); padding: 3px 8px; border-radius: var(--r-full);">
                        📍 ~800 metros
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--text);">
                        <?= number_format($sol['cantidad_estimada'], 1, ',', '.') ?> kg
                    </div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #B27B00;">
                        ~<?= (int)round($sol['cantidad_estimada'] * $sol['puntos_por_kg']) ?> Ecopuntos potenciales
                    </div>
                </div>

                <div style="font-size: 0.84rem; color: var(--label); margin-bottom: 6px; line-height: 1.4;">
                    <strong>Dirección:</strong> <?= h($sol['direccion']) ?>
                </div>

                <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 6px;">
                    <strong>Sector:</strong> <?= h($sol['localidad']) ?> — <?= h($sol['barrio'] ?? 'Centro') ?>
                </div>

                <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 12px;">
                    <strong>Disponibilidad:</strong> <?= h($sol['fecha_disponibilidad']) ?> (<?= substr($sol['hora_inicio'], 0, 5) ?> - <?= substr($sol['hora_fin'], 0, 5) ?>)
                </div>

                <?php if (!empty($sol['observaciones'])): ?>
                    <div style="font-size: 0.78rem; color: #555; background: #F8FAF9; padding: 8px 12px; border-radius: var(--r); margin-bottom: 16px; border-left: 3px solid var(--green);">
                        "<?= h($sol['observaciones']) ?>"
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 14px; margin-top: 6px;">
                    <a href="/reciclador/solicitud_detalle.php?id=<?= $sol['id_solicitud'] ?>" class="btn btn-o btn-sm">
                        Ver Detalles
                    </a>

                    <form action="/reciclador/solicitudes.php" method="POST" style="margin:0;">
                        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                        <input type="hidden" name="action" value="aceptar">
                        <input type="hidden" name="id_solicitud" value="<?= $sol['id_solicitud'] ?>">
                        <button type="submit" class="btn btn-p btn-sm" onclick="return confirm('¿Deseas aceptar esta solicitud para tu ruta de hoy?');">
                            ✓ Aceptar Solicitud
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
