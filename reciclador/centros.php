<?php
// =====================================================================
// ECOPOINT — DIRECTORIO DE CENTROS DE ACOPIO
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$db = Database::getConnection();
$centros = $db->query("
    SELECT * FROM centros_acopio 
    WHERE estado = 'activo' 
    ORDER BY localidad ASC, nombre ASC
")->fetchAll();

$pageTitle = 'Ecopoint — Centros de Acopio Autorizados';
$headerTitle = 'Centros de Acopio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Centros de Acopio Autorizados 🏢</h1>
        <p class="psub">Puntos de pesaje oficial y recepción con balanzas certificadas para la acreditación de Ecopuntos.</p>
    </div>
    <div>
        <a href="/reciclador/entregas.php" class="btn btn-p btn-sm">Registrar entrega de material</a>
    </div>
</div>

<div class="g3">
    <?php foreach ($centros as $centro): ?>
        <div class="card cp card-static" style="border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <span class="bdg bdg-disp">Balanza Certificada ✓</span>
                    <span class="bdg bdg-proc"><?= h($centro['localidad']) ?></span>
                </div>

                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 6px;">
                    <?= h($centro['nombre']) ?>
                </h3>

                <div style="font-size: 0.84rem; color: var(--label); margin-bottom: 8px;">
                    📍 <strong><?= h($centro['direccion']) ?></strong>
                </div>

                <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 4px;">
                    ⏰ Horario: <strong><?= substr($centro['hora_apertura'], 0, 5) ?> a <?= substr($centro['hora_cierre'], 0, 5) ?></strong>
                </div>

                <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 14px;">
                    📞 Contacto: <strong><?= h($centro['telefono']) ?></strong>
                </div>

                <div style="background: #F8FAF9; padding: 10px 12px; border-radius: var(--r); font-size: 0.76rem; color: var(--muted); line-height: 1.4; margin-bottom: 16px;">
                    ♻️ Recibe: Cartón, Papel, Plástico (PET/PEAD), Metales y Vidrio clasificado.
                </div>
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 12px;">
                <a href="/reciclador/entregas.php?centro_id=<?= $centro['id_centro'] ?>" class="btn btn-sec btn-sm btn-full">
                    Entregar en este Centro →
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
