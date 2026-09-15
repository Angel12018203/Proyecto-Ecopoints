<?php
// =====================================================================
// ECOPOINT — MIS LOGROS Y NIVELES (RECICLADOR)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

$stmt = $db->prepare("SELECT ecopuntos, material_recuperado, nivel FROM perfil_reciclador WHERE id_usuario = :id");
$stmt->execute([':id' => $userId]);
$perfil = $stmt->fetch();

$puntos = (int)($perfil['ecopuntos'] ?? 0);
$nivelActual = $perfil['nivel'] ?? 'Reciclador Destacado';
$kg = (float)($perfil['material_recuperado'] ?? 0);

$pageTitle = 'Ecopoint — Mis Logros y Niveles';
$headerTitle = 'Logros y Reconocimientos';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Mis Logros e Insignias 🏆</h1>
        <p class="psub">Reconocimiento a tu esfuerzo diario y avance en el escalafón de reciclaje profesional.</p>
    </div>
</div>

<!-- Nivel Actual y Progreso -->
<div class="card cp" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 16px;">
        <div>
            <span class="bdg bdg-disp">Escalafón Oficial</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--green); margin-top: 4px;">
                🎖️ <?= h($nivelActual) ?>
            </div>
            <div style="font-size: 0.84rem; color: var(--muted);">
                Saldo actual: <strong><?= number_format($puntos, 0) ?> Ecopuntos</strong>
            </div>
        </div>
        <div>
            <a href="/reciclador/beneficios.php" class="btn btn-gold btn-sm">Ver Beneficios Exclusivos</a>
        </div>
    </div>

    <!-- Barra de Progreso hacia Master Élite (5000 pts) -->
    <div style="margin-top: 10px;">
        <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; color: var(--muted); margin-bottom: 6px;">
            <span>Avanzado (1.000 pts)</span>
            <span>Destacado (2.500 pts)</span>
            <span>Master Élite (5.000 pts)</span>
        </div>
        <div class="pw" style="height: 12px;">
            <?php 
            $porcentaje = min(100, max(10, round(($puntos / 5000) * 100)));
            ?>
            <div class="pb" style="width: <?= $porcentaje ?>%;"></div>
        </div>
    </div>
</div>

<!-- Insignias de Reconocimiento -->
<div class="g3">
    <!-- Logro 1 -->
    <div class="card cp card-static" style="text-align: center; border-color: var(--green);">
        <div style="font-size: 3rem; margin-bottom: 8px;">🌟</div>
        <h3 style="font-size: 1.05rem; font-weight: 700;">Primeros Pasos</h3>
        <p style="font-size: 0.8rem; color: var(--muted); margin: 6px 0 12px;">Registrarse y completar la primera recolección validada.</p>
        <span class="bdg bdg-vali">✓ Desbloqueado</span>
    </div>

    <!-- Logro 2 -->
    <div class="card cp card-static" style="text-align: center; border-color: var(--green);">
        <div style="font-size: 3rem; margin-bottom: 8px;">📦</div>
        <h3 style="font-size: 1.05rem; font-weight: 700;">Centenar de Cartón</h3>
        <p style="font-size: 0.8rem; color: var(--muted); margin: 6px 0 12px;">Superar más de 100 kg de cartón recuperado en balanza.</p>
        <span class="bdg bdg-vali">✓ Desbloqueado</span>
    </div>

    <!-- Logro 3 -->
    <div class="card cp card-static" style="text-align: center; <?= $puntos >= 2500 ? 'border-color: var(--gold);' : 'opacity: 0.6;' ?>">
        <div style="font-size: 3rem; margin-bottom: 8px;">👑</div>
        <h3 style="font-size: 1.05rem; font-weight: 700;">Reciclador Destacado</h3>
        <p style="font-size: 0.8rem; color: var(--muted); margin: 6px 0 12px;">Alcanzar más de 2.500 Ecopuntos acumulados.</p>
        <?php if ($puntos >= 2500): ?>
            <span class="bdg bdg-proc">✓ Desbloqueado</span>
        <?php else: ?>
            <span class="bdg bdg-canc">En progreso</span>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
