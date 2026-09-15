<?php
// =====================================================================
// ECOPOINT — PERFIL DEL CIUDADANO
// (SECCIÓN 29 DEL MANUAL)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('ciudadano');

$userId = getCurrentUserId();
$db = Database::getConnection();

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errores[] = 'Error de seguridad CSRF.';
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $localidad = trim($_POST['localidad'] ?? 'Suba');
        $barrio = trim($_POST['barrio'] ?? '');
        $preferencias = trim($_POST['preferencias'] ?? '');

        if (empty($nombre) || empty($apellido) || empty($telefono) || empty($direccion) || empty($barrio)) {
            $errores[] = 'Por favor completa todos los campos requeridos.';
        } else {
            try {
                $db->beginTransaction();

                // 1. Actualizar tabla usuarios
                $db->prepare("UPDATE usuarios SET nombre = :nom, apellido = :ape, telefono = :tel WHERE id_usuario = :id")
                   ->execute([':nom' => $nombre, ':ape' => $apellido, ':tel' => $telefono, ':id' => $userId]);

                // 2. Actualizar perfil_ciudadano
                $db->prepare("
                    UPDATE perfil_ciudadano 
                    SET direccion = :dir, localidad = :loc, barrio = :barrio, preferencias = :pref
                    WHERE id_usuario = :id
                ")->execute([
                    ':dir'    => $direccion,
                    ':loc'    => $localidad,
                    ':barrio' => $barrio,
                    ':pref'   => $preferencias,
                    ':id'     => $userId
                ]);

                $db->commit();

                $_SESSION['nombre'] = $nombre . ' ' . $apellido;
                setFlash('success', '¡Perfil de ciudadano actualizado correctamente!');
                header('Location: /ciudadano/perfil.php');
                exit;
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $errores[] = 'Error al actualizar: ' . $e->getMessage();
            }
        }
    }
}

// Consultar datos actuales
$stmt = $db->prepare("
    SELECT u.nombre, u.apellido, u.correo, u.telefono,
           pc.direccion, pc.localidad, pc.barrio, pc.preferencias
    FROM usuarios u
    INNER JOIN perfil_ciudadano pc ON u.id_usuario = pc.id_usuario
    WHERE u.id_usuario = :id
    LIMIT 1
");
$stmt->execute([':id' => $userId]);
$usuario = $stmt->fetch();

$pageTitle = 'Ecopoint — Mi Perfil de Ciudadano';
$headerTitle = 'Mi Perfil Ciudadano';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Mi Perfil Ciudadano 👤</h1>
        <p class="psub">Administra tu dirección de recogida por defecto y preferencias de entrega.</p>
    </div>
</div>

<div class="card cp" style="max-width: 680px; margin: 0 auto;">
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $err): ?>
                    <li><?= h($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/ciudadano/perfil.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

        <div class="g2">
            <div class="form-group">
                <label class="form-label">Nombre <span style="color:red;">*</span></label>
                <input type="text" name="nombre" class="form-control" value="<?= h($usuario['nombre']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Apellido <span style="color:red;">*</span></label>
                <input type="text" name="apellido" class="form-control" value="<?= h($usuario['apellido']) ?>" required>
            </div>
        </div>

        <div class="g2">
            <div class="form-group">
                <label class="form-label">Correo Electrónico (No modificable)</label>
                <input type="email" class="form-control" value="<?= h($usuario['correo']) ?>" disabled style="background:#F4F6F5;">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono / WhatsApp <span style="color:red;">*</span></label>
                <input type="tel" name="telefono" class="form-control" value="<?= h($usuario['telefono']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dirección habitual de recolección <span style="color:red;">*</span></label>
            <input type="text" name="direccion" class="form-control" value="<?= h($usuario['direccion']) ?>" required>
        </div>

        <div class="g2">
            <div class="form-group">
                <label class="form-label">Localidad <span style="color:red;">*</span></label>
                <select name="localidad" class="form-select" required>
                    <option value="Suba" <?= $usuario['localidad'] === 'Suba' ? 'selected' : '' ?>>Suba</option>
                    <option value="Engativá" <?= $usuario['localidad'] === 'Engativá' ? 'selected' : '' ?>>Engativá</option>
                    <option value="Usaquén" <?= $usuario['localidad'] === 'Usaquén' ? 'selected' : '' ?>>Usaquén</option>
                    <option value="Chapinero" <?= $usuario['localidad'] === 'Chapinero' ? 'selected' : '' ?>>Chapinero</option>
                    <option value="Kennedy" <?= $usuario['localidad'] === 'Kennedy' ? 'selected' : '' ?>>Kennedy</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Barrio <span style="color:red;">*</span></label>
                <input type="text" name="barrio" class="form-control" value="<?= h($usuario['barrio']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Preferencias o Indicaciones Especiales</label>
            <textarea name="preferencias" class="form-control" placeholder="Ej. Dejar en portería o tocar citófono"><?= h($usuario['preferencias'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-p btn-lg btn-full" style="margin-top: 10px;">
            Guardar Cambios
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
