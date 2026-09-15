<?php
// =====================================================================
// ECOPOINT — SOLICITAR RECOLECCIÓN DE MATERIALES (CIUDADANO)
// (SECCIÓN 22 DEL MANUAL)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('ciudadano');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Consultar perfil del ciudadano para autocompletar dirección
$stmtPerfil = $db->prepare("SELECT * FROM perfil_ciudadano WHERE id_usuario = :id LIMIT 1");
$stmtPerfil->execute([':id' => $userId]);
$perfil = $stmtPerfil->fetch();

// Catálogo de materiales reciclables
$materiales = $db->query("SELECT * FROM materiales WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll();

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errores[] = 'Error de seguridad CSRF.';
    } else {
        $idMaterial = (int)($_POST['id_material'] ?? 0);
        $cantidadEstimada = (float)($_POST['cantidad_estimada'] ?? 0);
        $direccion = trim($_POST['direccion'] ?? '');
        $localidad = trim($_POST['localidad'] ?? 'Suba');
        $barrio = trim($_POST['barrio'] ?? '');
        $fechaDisp = $_POST['fecha_disponibilidad'] ?? date('Y-m-d');
        $horaInicio = $_POST['hora_inicio'] ?? '14:00';
        $horaFin = $_POST['hora_fin'] ?? '17:00';
        $observaciones = trim($_POST['observaciones'] ?? '');
        $prioridad = $_POST['prioridad'] ?? 'Normal';

        if ($idMaterial <= 0) $errores[] = 'Selecciona un tipo de material aprovechable.';
        if ($cantidadEstimada <= 0) $errores[] = 'Ingresa una cantidad estimada mayor a 0 kg.';
        if (empty($direccion)) $errores[] = 'La dirección de recolección es obligatoria.';
        if (empty($fechaDisp)) $errores[] = 'Selecciona la fecha en que tendrás el material disponible.';

        if (empty($errores)) {
            try {
                $stmtInsert = $db->prepare("
                    INSERT INTO solicitudes (
                        id_ciudadano, id_material, cantidad_estimada, direccion, localidad, barrio,
                        latitud, longitud, fecha_solicitud, fecha_disponibilidad, hora_inicio, hora_fin,
                        observaciones, prioridad, estado
                    ) VALUES (
                        :id_ciudadano, :id_material, :cantidad, :direccion, :localidad, :barrio,
                        4.7450000, -74.0850000, NOW(), :fecha_disp, :hora_ini, :hora_fin,
                        :obs, :prio, 'Disponible'
                    )
                ");

                $stmtInsert->execute([
                    ':id_ciudadano' => $userId,
                    ':id_material'  => $idMaterial,
                    ':cantidad'     => $cantidadEstimada,
                    ':direccion'    => $direccion,
                    ':localidad'    => $localidad,
                    ':barrio'       => $barrio,
                    ':fecha_disp'   => $fechaDisp,
                    ':hora_ini'     => $horaInicio,
                    ':hora_fin'     => $horaFin,
                    ':obs'          => $observaciones,
                    ':prio'         => $prioridad
                ]);

                $newId = $db->lastInsertId();

                setFlash('success', "¡Solicitud #{$newId} creada exitosamente! Tu material ha sido publicado en la red de recicladores de tu sector.");
                header('Location: /ciudadano/solicitudes.php');
                exit;
            } catch (Exception $e) {
                $errores[] = 'Error al publicar la solicitud: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Ecopoint — Solicitar Recolección';
$headerTitle = 'Nueva Solicitud de Recolección';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Solicitar Recolección Puerta a Puerta 📦</h1>
        <p class="psub">Publica tus materiales aprovechables clasificados para que un reciclador de tu zona los recoja.</p>
    </div>
</div>

<div class="card cp" style="max-width: 720px; margin: 0 auto;">
    <!-- MENSAJE DE CONCIENTIZACIÓN (SECCIÓN 22) -->
    <div class="alert alert-success" style="margin-bottom: 22px;">
        <div style="font-size: 1.5rem;">♻️</div>
        <div>
            <strong>Material separado y listo para entregar:</strong>
            <div style="font-size: 0.82rem; margin-top: 2px;">
                Asegúrate de que el material esté limpio, seco, desocupado y clasificado en bolsas o cajas atadas.
            </div>
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

    <form action="/ciudadano/solicitar.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

        <div class="g2">
            <div class="form-group">
                <label class="form-label" for="id_material">Material Principal <span style="color:red;">*</span></label>
                <select name="id_material" id="id_material" class="form-select" required>
                    <option value="">-- Selecciona el tipo de material --</option>
                    <?php foreach ($materiales as $mat): ?>
                        <option value="<?= $mat['id_material'] ?>" data-pts="<?= (int)$mat['puntos_por_kg'] ?>">
                            <?= h($mat['nombre']) ?> (<?= h($mat['descripcion']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="cantidad_estimada">Cantidad Aproximada (kg) <span style="color:red;">*</span></label>
                <div style="position: relative;">
                    <input type="number" step="0.5" min="0.5" id="cantidad_estimada" name="cantidad_estimada" class="form-control" placeholder="Ej. 30.0" value="30.0" required style="font-size: 1.15rem; font-weight: 700; padding-right: 48px;">
                    <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--muted);">kg</span>
                </div>
            </div>
        </div>

        <div class="g2">
            <div class="form-group">
                <label class="form-label" for="direccion">Dirección exacta de recogida <span style="color:red;">*</span></label>
                <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Ej. Calle 145 # 92-30 Torre 2 Apto 402" value="<?= h($perfil['direccion'] ?? 'Calle 145 # 92-30 Torre 2') ?>" required>
            </div>

            <div class="g2">
                <div class="form-group">
                    <label class="form-label">Localidad <span style="color:red;">*</span></label>
                    <select name="localidad" class="form-select" required>
                        <option value="Suba" <?= ($perfil['localidad'] ?? '') === 'Suba' ? 'selected' : '' ?>>Suba</option>
                        <option value="Engativá" <?= ($perfil['localidad'] ?? '') === 'Engativá' ? 'selected' : '' ?>>Engativá</option>
                        <option value="Usaquén" <?= ($perfil['localidad'] ?? '') === 'Usaquén' ? 'selected' : '' ?>>Usaquén</option>
                        <option value="Chapinero" <?= ($perfil['localidad'] ?? '') === 'Chapinero' ? 'selected' : '' ?>>Chapinero</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Barrio</label>
                    <input type="text" name="barrio" class="form-control" placeholder="Ej. Suba Centro" value="<?= h($perfil['barrio'] ?? 'Suba Centro') ?>">
                </div>
            </div>
        </div>

        <div class="g3">
            <div class="form-group">
                <label class="form-label">Fecha de Disponibilidad <span style="color:red;">*</span></label>
                <input type="date" name="fecha_disponibilidad" class="form-control" value="<?= date('Y-m-d') ?>" required min="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Hora Inicio Franja</label>
                <input type="time" name="hora_inicio" class="form-control" value="14:00" required>
            </div>
            <div class="form-group">
                <label class="form-label">Hora Fin Franja</label>
                <input type="time" name="hora_fin" class="form-control" value="17:00" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="observaciones">Instrucciones u Observaciones para el Reciclador</label>
            <textarea id="observaciones" name="observaciones" class="form-control" placeholder="Ej. Dejaré las cajas desarmadas en la portería del edificio. Avisar al cel al llegar.">Cartón de cajas limpias y desarmadas listo en la portería.</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Prioridad de Recolección</label>
            <div style="display: flex; gap: 14px;">
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; cursor: pointer;">
                    <input type="radio" name="prioridad" value="Normal" checked> Normal
                </label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; cursor: pointer;">
                    <input type="radio" name="prioridad" value="Alta"> Alta
                </label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; cursor: pointer;">
                    <input type="radio" name="prioridad" value="Urgente"> Urgente (Espacio reducido)
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-p btn-lg btn-full" style="margin-top: 14px;">
            ✓ Publicar Solicitud de Recolección
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
