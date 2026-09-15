<?php
// =====================================================================
// ECOPOINT — REGISTRAR RECOLECCIÓN EN SITIO (PASO 6 DEL FLUJO)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

$idRecoleccion = (int)($_GET['id'] ?? 0);

// Consultar la recolección
$stmt = $db->prepare("
    SELECT r.*, s.id_solicitud, s.cantidad_estimada, s.direccion, s.localidad, s.barrio,
           m.nombre AS material_nombre, m.puntos_por_kg,
           u.nombre AS ciudadano_nombre, u.apellido AS ciudadano_apellido
    FROM recolecciones r
    INNER JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    INNER JOIN materiales m ON s.id_material = m.id_material
    INNER JOIN usuarios u ON s.id_ciudadano = u.id_usuario
    WHERE r.id_recoleccion = :id AND r.id_reciclador = :id_rec
    LIMIT 1
");
$stmt->execute([':id' => $idRecoleccion, ':id_rec' => $userId]);
$recoleccion = $stmt->fetch();

if (!$recoleccion) {
    setFlash('danger', 'Recolección no encontrada o no te pertenece.');
    header('Location: /reciclador/recolecciones.php');
    exit;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errores[] = 'Error de validación CSRF.';
    } else {
        $cantidadRecolectada = (float)($_POST['cantidad_recolectada'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');

        if ($cantidadRecolectada <= 0) {
            $errores[] = 'Ingresa una cantidad recolectada válida en kilogramos (mayor a 0).';
        } else {
            try {
                $db->beginTransaction();

                // 1. Actualizar tabla recolecciones
                $updateRec = $db->prepare("
                    UPDATE recolecciones 
                    SET fecha_recoleccion = NOW(),
                        cantidad_recolectada = :peso,
                        observaciones = :obs,
                        estado = 'Recolectada'
                    WHERE id_recoleccion = :id
                ");
                $updateRec->execute([
                    ':peso' => $cantidadRecolectada,
                    ':obs'  => $observaciones,
                    ':id'   => $idRecoleccion
                ]);

                // 2. Actualizar estado de la solicitud a Recolectada
                $updateSol = $db->prepare("
                    UPDATE solicitudes SET estado = 'Recolectada' WHERE id_solicitud = :id_sol
                ");
                $updateSol->execute([':id_sol' => $recoleccion['id_solicitud']]);

                $db->commit();

                setFlash('success', "¡Recolección registrada exitosamente! Recogiste {$cantidadRecolectada} kg de {$recoleccion['material_nombre']}. Ahora dirígete a un Centro de Acopio para realizar el pesaje y validación oficial.");
                header('Location: /reciclador/entregas.php');
                exit;
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $errores[] = 'Error al registrar la recolección: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Ecopoint — Registrar Recolección #' . $recoleccion['id_recoleccion'];
$headerTitle = 'Registrar Recolección';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 16px;">
    <a href="/reciclador/recolecciones.php" class="btn btn-o btn-sm">← Volver a mis recolecciones</a>
</div>

<div class="card cp" style="max-width: 640px; margin: 0 auto;">
    <div class="card-header-clean">
        <div>
            <h2 class="card-title">Registrar Recolección en Sitio</h2>
            <div style="font-size: 0.8rem; color: var(--muted);">Solicitud #<?= $recoleccion['id_solicitud'] ?></div>
        </div>
        <span class="bdg bdg-proc"><?= h($recoleccion['material_nombre']) ?></span>
    </div>

    <!-- REGLA DE NEGOCIO DESTACADA (Sección 14 y 17) -->
    <div class="alert alert-warning" style="margin-bottom: 20px;">
        <div>
            <strong>⚠️ Regla del Sistema:</strong> Registrar esta recolección <strong>NO</strong> genera Ecopuntos de forma automática. Los puntos se acreditarán exclusivamente tras la entrega y certificación de pesaje en un Centro de Acopio.
        </div>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $err): ?>
                    <li><?= h($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="background: #F8FAF9; border: 1px solid var(--border); border-radius: var(--r); padding: 14px 18px; margin-bottom: 20px;">
        <div style="font-size: 0.82rem; color: var(--muted);">Punto de Recogida:</div>
        <div style="font-weight: 700; color: var(--text); font-size: 0.95rem;">
            <?= h($recoleccion['direccion']) ?> (<?= h($recoleccion['localidad']) ?>)
        </div>
        <div style="font-size: 0.82rem; color: var(--muted); margin-top: 4px;">
            Ciudadano: <?= h($recoleccion['ciudadano_nombre']) ?> <?= h($recoleccion['ciudadano_apellido']) ?> • Estimado previo: <strong><?= number_format($recoleccion['cantidad_estimada'], 1) ?> kg</strong>
        </div>
    </div>

    <form action="/reciclador/registrar_recoleccion.php?id=<?= $recoleccion['id_recoleccion'] ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

        <div class="form-group">
            <label class="form-label" for="cantidad_recolectada">
                Kilogramos Recolectados Reales <span style="color:red;">*</span>
            </label>
            <div style="position: relative;">
                <input type="number" step="0.1" min="0.1" id="cantidad_recolectada" name="cantidad_recolectada" class="form-control" placeholder="Ej. 28.0" value="<?= h($_POST['cantidad_recolectada'] ?? '28.0') ?>" required style="font-size: 1.2rem; font-weight: 700; padding-right: 50px;">
                <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--muted);">kg</span>
            </div>
            <div style="font-size: 0.76rem; color: var(--muted); margin-top: 4px;">
                Ingresa el peso verificado en tu romana o báscula de mano al momento de cargar.
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="observaciones">Observaciones o Novedades en Sitio</label>
            <textarea id="observaciones" name="observaciones" class="form-control" placeholder="Ej. Material en excelente estado, cartón seco amarrado con cabuya."><?= h($_POST['observaciones'] ?? 'Material limpio y embalado en bolsas.') ?></textarea>
        </div>

        <button type="submit" class="btn btn-p btn-lg btn-full" style="margin-top: 10px;">
            ✓ Confirmar Recolección y Marcar Pendiente de Entrega
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
