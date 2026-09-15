<?php
// =====================================================================
// ECOPOINT — CATÁLOGO DE BENEFICIOS Y CANJE DE ECOPUNTOS
// (SECCIÓN 19 Y 20 DEL MANUAL)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Procesar solicitud de Canje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'canjear') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('danger', 'Error de validación CSRF.');
    } else {
        $idBeneficio = (int)($_POST['id_beneficio'] ?? 0);

        try {
            $db->beginTransaction();

            // 1. Consultar beneficio y stock disponible
            $stmtBen = $db->prepare("SELECT * FROM beneficios WHERE id_beneficio = :id AND estado = 'activo' FOR UPDATE");
            $stmtBen->execute([':id' => $idBeneficio]);
            $beneficio = $stmtBen->fetch();

            if (!$beneficio) {
                throw new Exception('El beneficio seleccionado no existe o no está activo.');
            }
            if ($beneficio['stock'] <= 0) {
                throw new Exception('Lo sentimos, este beneficio se encuentra temporalmente agotado.');
            }

            // 2. Consultar saldo actual de Ecopuntos del reciclador
            $stmtPerfil = $db->prepare("SELECT ecopuntos FROM perfil_reciclador WHERE id_usuario = :id FOR UPDATE");
            $stmtPerfil->execute([':id' => $userId]);
            $saldoActual = (int)$stmtPerfil->fetchColumn();

            $costo = (int)$beneficio['costo_ecopuntos'];

            // 3. Comparar saldo con costo y rechazar si es insuficiente
            if ($saldoActual < $costo) {
                $faltantes = $costo - $saldoActual;
                throw new Exception("Saldo insuficiente. Tienes {$saldoActual} Ecopuntos y el beneficio cuesta {$costo} Ecopuntos (te faltan {$faltantes} pts).");
            }

            // 4. Descontar puntos del perfil
            $db->prepare("UPDATE perfil_reciclador SET ecopuntos = ecopuntos - :costo WHERE id_usuario = :id")
               ->execute([':costo' => $costo, ':id' => $userId]);

            // 5. Descontar stock del beneficio
            $db->prepare("UPDATE beneficios SET stock = stock - 1 WHERE id_beneficio = :id")
               ->execute([':id' => $idBeneficio]);

            // 6. Generar código único de canje
            $codigoCanje = 'ECO-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));

            // 7. Registrar el canje en la tabla canjes
            $stmtCanje = $db->prepare("
                INSERT INTO canjes (id_usuario, id_beneficio, ecopuntos_utilizados, codigo_canje, fecha_canje, estado)
                VALUES (:id_user, :id_ben, :pts, :cod, NOW(), 'generado')
            ");
            $stmtCanje->execute([
                ':id_user' => $userId,
                ':id_ben'  => $idBeneficio,
                ':pts'     => $costo,
                ':cod'     => $codigoCanje
            ]);

            $db->commit();

            setFlash('success', "🎉 ¡Canje exitoso! Has obtenido '{$beneficio['nombre']}'. Tu código de reclamo es: {$codigoCanje}. Presenta este código para reclamar tu beneficio.");
            header('Location: /reciclador/ecopuntos.php');
            exit;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            setFlash('danger', $e->getMessage());
        }
    }
}

// Consultar saldo actual
$stmtSaldo = $db->prepare("SELECT ecopuntos FROM perfil_reciclador WHERE id_usuario = :id");
$stmtSaldo->execute([':id' => $userId]);
$saldo = (int)$stmtSaldo->fetchColumn();

// Catálogo de beneficios disponibles
$beneficios = $db->query("SELECT * FROM beneficios WHERE estado = 'activo' ORDER BY costo_ecopuntos ASC")->fetchAll();

$pageTitle = 'Ecopoint — Beneficios y Canjes';
$headerTitle = 'Catálogo de Beneficios';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Recompensas y Beneficios 🎁</h1>
        <p class="psub">Canjea tus Ecopuntos ganados por equipamiento de seguridad, bonos de alimentación y herramientas.</p>
    </div>
    <div>
        <div class="pts-pill" style="font-size: 0.95rem; padding: 8px 18px;">
            <span>Saldo:</span>
            <strong class="pts-num"><?= number_format($saldo, 0, ',', '.') ?></strong>
            <span>Ecopuntos</span>
        </div>
    </div>
</div>

<div class="g3">
    <?php foreach ($beneficios as $ben): 
        $puedeCanjear = ($saldo >= $ben['costo_ecopuntos']) && ($ben['stock'] > 0);
    ?>
        <div class="card cp card-static" style="border: 1.5px solid var(--border); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span class="bdg bdg-proc"><?= h($ben['categoria']) ?></span>
                    <span style="font-size: 0.75rem; color: var(--muted);">Stock: <strong><?= $ben['stock'] ?></strong> unid.</span>
                </div>

                <div style="font-size: 2.2rem; margin-bottom: 8px;">
                    <?php
                    $iconoEmoji = '🎁';
                    if (strpos($ben['nombre'], 'Protección') !== false) $iconoEmoji = '🦺';
                    elseif (strpos($ben['nombre'], 'Alimentación') !== false) $iconoEmoji = '🛒';
                    elseif (strpos($ben['nombre'], 'Carro') !== false) $iconoEmoji = '⚙️';
                    elseif (strpos($ben['nombre'], 'Conectividad') !== false) $iconoEmoji = '📶';
                    elseif (strpos($ben['nombre'], 'Salud') !== false) $iconoEmoji = '👓';
                    echo $iconoEmoji;
                    ?>
                </div>

                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 8px;">
                    <?= h($ben['nombre']) ?>
                </h3>

                <p style="font-size: 0.82rem; color: var(--muted); line-height: 1.5; margin-bottom: 18px;">
                    <?= h($ben['descripcion']) ?>
                </p>
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 0.8rem; color: var(--muted);">Costo:</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: #8F5200;">
                        🪙 <?= number_format($ben['costo_ecopuntos'], 0, ',', '.') ?> pts
                    </span>
                </div>

                <?php if ($puedeCanjear): ?>
                    <form action="/reciclador/beneficios.php" method="POST" onsubmit="return confirm('¿Confirmas el canje de <?= number_format($ben['costo_ecopuntos'], 0) ?> Ecopuntos por <?= addslashes($ben['nombre']) ?>?');">
                        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                        <input type="hidden" name="action" value="canjear">
                        <input type="hidden" name="id_beneficio" value="<?= $ben['id_beneficio'] ?>">
                        <button type="submit" class="btn btn-gold btn-full">
                            ✓ Canjear Beneficio
                        </button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-o btn-full" disabled style="opacity: 0.6; cursor: not-allowed;">
                        <?php if ($ben['stock'] <= 0): ?>
                            Agotado temporalmente
                        <?php else: ?>
                            Te faltan <?= number_format($ben['costo_ecopuntos'] - $saldo, 0) ?> pts
                        <?php endif; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
