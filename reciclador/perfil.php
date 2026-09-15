<?php
// =====================================================================
// ECOPOINT — PERFIL DEL RECICLADOR
// (SECCIÓN 29 DEL MANUAL)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

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
        $zona = trim($_POST['zona_trabajo'] ?? 'Suba');
        $transporte = trim($_POST['medio_transporte'] ?? '');
        $disponibilidad = trim($_POST['disponibilidad'] ?? '');

        if (empty($nombre) || empty($apellido) || empty($telefono)) {
            $errores[] = 'Nombre, apellido y teléfono son obligatorios.';
        } else {
            try {
                $db->beginTransaction();

                // 1. Actualizar tabla usuarios
                $db->prepare("UPDATE usuarios SET nombre = :nom, apellido = :ape, telefono = :tel WHERE id_usuario = :id")
                   ->execute([':nom' => $nombre, ':ape' => $apellido, ':tel' => $telefono, ':id' => $userId]);

                // 2. Actualizar perfil_reciclador
                $db->prepare("
                    UPDATE perfil_reciclador 
                    SET zona_trabajo = :zona, medio_transporte = :trans, disponibilidad = :disp
                    WHERE id_usuario = :id
                ")->execute([
                    ':zona'  => $zona,
                    ':trans' => $transporte,
                    ':disp'  => $disponibilidad,
                    ':id'    => $userId
                ]);

                $db->commit();

                $_SESSION['nombre'] = $nombre . ' ' . $apellido;
                setFlash('success', '¡Perfil de reciclador actualizado correctamente!');
                header('Location: /reciclador/perfil.php');
                exit;
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $errores[] = 'Error al actualizar perfil: ' . $e->getMessage();
            }
        }
    }
}

// Consultar datos actuales
$stmt = $db->prepare("
    SELECT u.nombre, u.apellido, u.correo, u.telefono,
           pr.zona_trabajo, pr.medio_transporte, pr.disponibilidad, pr.ecopuntos, pr.nivel
    FROM usuarios u
    INNER JOIN perfil_reciclador pr ON u.id_usuario = pr.id_usuario
    WHERE u.id_usuario = :id
    LIMIT 1
");
$stmt->execute([':id' => $userId]);
$usuario = $stmt->fetch();

$pageTitle = 'Ecopoint — Mi Perfil de Reciclador';
$headerTitle = 'Mi Perfil Operativo';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Perfil del Reciclador ⚙️</h1>
        <p class="psub">Administra tus datos personales, zona de cobertura y vehículo de recolección.</p>
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

    <form action="/reciclador/perfil.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

        <div class="g2">
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= h($usuario['nombre']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Apellido</label>
                <input type="text" name="apellido" class="form-control" value="<?= h($usuario['apellido']) ?>" required>
            </div>
        </div>

        <div class="g2">
            <div class="form-group">
                <label class="form-label">Correo Electrónico (No modificable)</label>
                <input type="email" class="form-control" value="<?= h($usuario['correo']) ?>" disabled style="background:#F4F6F5;">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono / WhatsApp</label>
                <input type="tel" name="telefono" class="form-control" value="<?= h($usuario['telefono']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Zona habitual de trabajo</label>
            <select name="zona_trabajo" class="form-select" required>
                <option value="Suba" <?= $usuario['zona_trabajo'] === 'Suba' ? 'selected' : '' ?>>Suba</option>
                <option value="Engativá" <?= $usuario['zona_trabajo'] === 'Engativá' ? 'selected' : '' ?>>Engativá</option>
                <option value="Usaquén" <?= $usuario['zona_trabajo'] === 'Usaquén' ? 'selected' : '' ?>>Usaquén</option>
                <option value="Chapinero" <?= $usuario['zona_trabajo'] === 'Chapinero' ? 'selected' : '' ?>>Chapinero</option>
                <option value="Kennedy" <?= $usuario['zona_trabajo'] === 'Kennedy' ? 'selected' : '' ?>>Kennedy</option>
            </select>
        </div>

        <div class="g2">
            <div class="form-group">
                <label class="form-label">Medio de transporte</label>
                <select name="medio_transporte" class="form-select" required>
                    <option value="Carreta de reciclaje manual" <?= $usuario['medio_transporte'] === 'Carreta de reciclaje manual' ? 'selected' : '' ?>>Carreta de reciclaje manual</option>
                    <option value="Bicitriciclo de carga" <?= $usuario['medio_transporte'] === 'Bicitriciclo de carga' ? 'selected' : '' ?>>Bicitriciclo de carga</option>
                    <option value="Motocarro / Vehículo ligero" <?= $usuario['medio_transporte'] === 'Motocarro / Vehículo ligero' ? 'selected' : '' ?>>Motocarro / Vehículo ligero</option>
                    <option value="A pie con costal" <?= $usuario['medio_transporte'] === 'A pie con costal' ? 'selected' : '' ?>>A pie con costal</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Disponibilidad</label>
                <input type="text" name="disponibilidad" class="form-control" value="<?= h($usuario['disponibilidad']) ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-p btn-lg btn-full" style="margin-top: 10px;">
            Guardar Cambios de Perfil
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
