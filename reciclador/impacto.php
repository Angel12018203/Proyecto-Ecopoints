<?php
// =====================================================================
// ECOPOINT — IMPACTO AMBIENTAL DEL RECICLADOR
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// Consultar material acumulado del reciclador
$stmt = $db->prepare("SELECT material_recuperado, ecopuntos FROM perfil_reciclador WHERE id_usuario = :id");
$stmt->execute([':id' => $userId]);
$perfil = $stmt->fetch();

$kgTotal = (float)($perfil['material_recuperado'] ?? 0);

// Factores de equivalencia ambiental por kilogramo reciclado promedio
$arbolesSalvados = round(($kgTotal * 0.017), 1); // 1 tonelada papel/carton salva ~17 arboles adultos
$co2EvitadoKg   = round(($kgTotal * 1.5), 1);    // 1 kg reciclado evita ~1.5 kg CO2e
$litrosAgua      = round(($kgTotal * 26), 0);     // 1 kg papel/plastico ahorra ~26L agua
$kwhEnergia      = round(($kgTotal * 4.2), 1);    // Ahorro energetico en kWh

$pageTitle = 'Ecopoint — Mi Impacto Ambiental';
$headerTitle = 'Huella Ecológica Positiva';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Tu Impacto Ambiental 🌍</h1>
        <p class="psub">Cada kilogramo que recolectas y entregas en centros certificados genera una transformación ecológica real.</p>
    </div>
</div>

<div class="card cp" style="background: linear-gradient(135deg, #087A3D 0%, #2E9B50 100%); color: #FFF; margin-bottom: 24px;">
    <div style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.9;">Total Recuperado por Ti</div>
    <div style="font-size: 3rem; font-weight: 800; line-height: 1.1; margin: 6px 0;">
        <?= number_format($kgTotal, 1, ',', '.') ?> kg
    </div>
    <div style="font-size: 0.9rem; opacity: 0.95;">
        Material aprovechable rescatado de los botaderos y devuelto a la economía circular.
    </div>
</div>

<div class="g4">
    <!-- Árboles -->
    <div class="card cp">
        <div style="font-size: 2.2rem; margin-bottom: 8px;">🌳</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--green);">
            <?= $arbolesSalvados ?>
        </div>
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text); margin-top: 2px;">Árboles Preservados</div>
        <p style="font-size: 0.76rem; color: var(--muted); margin-top: 6px;">
            Equivalencia en tala evitada gracias al papel y cartón reincorporados a la industria.
        </p>
    </div>

    <!-- CO2 -->
    <div class="card cp">
        <div style="font-size: 2.2rem; margin-bottom: 8px;">☁️</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #1E6091;">
            <?= number_format($co2EvitadoKg, 1) ?> kg
        </div>
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text); margin-top: 2px;">CO₂ No Emitido</div>
        <p style="font-size: 0.76rem; color: var(--muted); margin-top: 6px;">
            Gases de efecto invernadero prevenidos en transporte y fabricación virgen.
        </p>
    </div>

    <!-- Agua -->
    <div class="card cp">
        <div style="font-size: 2.2rem; margin-bottom: 8px;">💧</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #0284C7;">
            <?= number_format($litrosAgua, 0, ',', '.') ?> L
        </div>
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text); margin-top: 2px;">Litros de Agua Ahorrados</div>
        <p style="font-size: 0.76rem; color: var(--muted); margin-top: 6px;">
            Reducción del consumo hídrico en procesos industriales de pulpa y polímeros.
        </p>
    </div>

    <!-- Energía -->
    <div class="card cp">
        <div style="font-size: 2.2rem; margin-bottom: 8px;">⚡</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #D97706;">
            <?= number_format($kwhEnergia, 1) ?> kWh
        </div>
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text); margin-top: 2px;">Energía Ahorrada</div>
        <p style="font-size: 0.76rem; color: var(--muted); margin-top: 6px;">
            Suficiente para abastecer un hogar promedio durante semanas enteras.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
