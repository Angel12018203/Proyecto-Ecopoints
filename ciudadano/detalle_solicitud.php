<?php
// =====================================================================
// ECOPOINT — DETALLE Y TRAZABILIDAD EN VIVO (CIUDADANO)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('ciudadano');

$userId = getCurrentUserId();
$db = Database::getConnection();

$idSol = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("
    SELECT s.*, m.nombre AS material_nombre, m.descripcion AS material_desc,
           r.id_recoleccion, r.fecha_aceptacion, r.fecha_recoleccion, r.cantidad_recolectada,
           ur.nombre AS reciclador_nombre, ur.apellido AS reciclador_apellido, ur.telefono AS reciclador_telefono,
           e.id_entrega, e.fecha_entrega, e.cantidad_validada, e.estado AS estado_entrega,
           c.nombre AS centro_nombre
    FROM solicitudes s
    INNER JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN recolecciones r ON s.id_solicitud = r.id_solicitud
    LEFT JOIN usuarios ur ON r.id_reciclador = ur.id_usuario
    LEFT JOIN entregas e ON r.id_recoleccion = e.id_recoleccion
    LEFT JOIN centros_acopio c ON e.id_centro = c.id_centro
    WHERE s.id_solicitud = :id AND s.id_ciudadano = :user_id
    LIMIT 1
");
$stmt->execute([':id' => $idSol, ':user_id' => $userId]);
$sol = $stmt->fetch();

if (!$sol) {
    setFlash('danger', 'Solicitud no encontrada.');
    header('Location: /ciudadano/solicitudes.php');
    exit;
}

// Determinar el paso actual en el stepper de trazabilidad
// 1: Disponible | 2: Aceptada | 3: Recolectada | 4: Entregada | 5: Validada
$pasoActual = 1;
if ($sol['estado'] === 'Aceptada') $pasoActual = 2;
if ($sol['estado'] === 'Recolectada') $pasoActual = 3;
if ($sol['estado'] === 'Entregada') $pasoActual = 4;
if ($sol['estado'] === 'Validada') $pasoActual = 5;

$pageTitle = 'Ecopoint — Trazabilidad Solicitud #' . $sol['id_solicitud'];
$headerTitle = 'Trazabilidad de Solicitud';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom: 16px;">
    <a href="/ciudadano/solicitudes.php" class="btn btn-o btn-sm">← Volver a mis solicitudes</a>
</div>

<div class="card cp" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
            <span class="bdg bdg-proc">Solicitud #<?= $sol['id_solicitud'] ?></span>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text); margin-top: 6px;">
                <?= number_format($sol['cantidad_estimada'], 1) ?> kg de <?= h($sol['material_nombre']) ?>
            </h1>
            <div style="font-size: 0.84rem; color: var(--muted); margin-top: 2px;">
                Publicada el <?= substr($sol['fecha_solicitud'], 0, 16) ?>
            </div>
        </div>

        <div>
            <span class="bdg <?= $sol['estado'] === 'Validada' ? 'bdg-vali' : 'bdg-proc' ?>" style="font-size: 0.9rem; padding: 6px 16px;">
                Estado: <?= h($sol['estado']) ?>
            </span>
        </div>
    </div>

    <!-- Stepper Visual del Ciclo de Vida Completo -->
    <div style="margin: 30px 0 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
            <!-- Línea conectora -->
            <div style="position: absolute; top: 18px; left: 5%; right: 5%; height: 4px; background: #E0EBE4; z-index: 1;"></div>
            <div style="position: absolute; top: 18px; left: 5%; width: <?= ($pasoActual - 1) * 23 ?>%; height: 4px; background: var(--green); z-index: 1; transition: width 0.4s ease;"></div>

            <!-- Paso 1 -->
            <div style="text-align: center; z-index: 2; flex: 1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $pasoActual >= 1 ? 'var(--green)' : '#E0EBE4' ?>; color: #FFF; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px;">
                    1
                </div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text);">Solicitud Creada</div>
                <div style="font-size: 0.65rem; color: var(--muted);">En espera</div>
            </div>

            <!-- Paso 2 -->
            <div style="text-align: center; z-index: 2; flex: 1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $pasoActual >= 2 ? 'var(--green)' : '#E0EBE4' ?>; color: #FFF; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px;">
                    2
                </div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text);">Reciclador Asignado</div>
                <div style="font-size: 0.65rem; color: var(--muted);">Aceptada en ruta</div>
            </div>

            <!-- Paso 3 -->
            <div style="text-align: center; z-index: 2; flex: 1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $pasoActual >= 3 ? 'var(--green)' : '#E0EBE4' ?>; color: #FFF; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px;">
                    3
                </div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text);">Recolectada</div>
                <div style="font-size: 0.65rem; color: var(--muted);">Pesaje en sitio</div>
            </div>

            <!-- Paso 4 -->
            <div style="text-align: center; z-index: 2; flex: 1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $pasoActual >= 4 ? 'var(--green)' : '#E0EBE4' ?>; color: #FFF; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px;">
                    4
                </div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text);">Entregada en Centro</div>
                <div style="font-size: 0.65rem; color: var(--muted);">En cola de balanza</div>
            </div>

            <!-- Paso 5 -->
            <div style="text-align: center; z-index: 2; flex: 1;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $pasoActual >= 5 ? 'var(--green)' : '#E0EBE4' ?>; color: #FFF; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px;">
                    5
                </div>
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text);">Pesaje Validado</div>
                <div style="font-size: 0.65rem; color: var(--muted);">Ecopuntos generados</div>
            </div>
        </div>
    </div>
</div>

<div class="g2">
    <!-- Detalles del Lugar y Horario -->
    <div class="card cp">
        <h3 class="card-title" style="margin-bottom: 14px;">📍 Datos de la Recolección</h3>
        
        <div style="margin-bottom: 12px;">
            <div class="form-label">Dirección:</div>
            <div style="font-size: 0.95rem; font-weight: 600; color: var(--text);">
                <?= h($sol['direccion']) ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--muted);">
                <?= h($sol['localidad']) ?> — <?= h($sol['barrio'] ?? '') ?>
            </div>
        </div>

        <div class="g2" style="margin-bottom: 12px;">
            <div>
                <div class="form-label">Fecha Programada:</div>
                <div style="font-weight: 600;"><?= h($sol['fecha_disponibilidad']) ?></div>
            </div>
            <div>
                <div class="form-label">Horario:</div>
                <div style="font-weight: 600;"><?= substr($sol['hora_inicio'], 0, 5) ?> a <?= substr($sol['hora_fin'], 0, 5) ?></div>
            </div>
        </div>

        <?php if (!empty($sol['observaciones'])): ?>
            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border);">
                <div class="form-label">Instrucciones:</div>
                <div style="font-size: 0.84rem; color: var(--text);">"<?= h($sol['observaciones']) ?>"</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Información del Reciclador y Centro de Acopio -->
    <div class="card cp">
        <h3 class="card-title" style="margin-bottom: 14px;">👥 Participantes en la Cadena</h3>

        <?php if (!empty($sol['reciclador_nombre'])): ?>
            <div style="background: #F8FAF9; padding: 14px; border-radius: var(--r); margin-bottom: 14px; border-left: 4px solid var(--green);">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--green); text-transform: uppercase;">Reciclador a Cargo</div>
                <div style="font-size: 1rem; font-weight: 700; color: var(--text); margin-top: 2px;">
                    <?= h($sol['reciclador_nombre']) ?> <?= h($sol['reciclador_apellido']) ?>
                </div>
                <div style="font-size: 0.82rem; color: var(--muted); margin-top: 4px;">
                    📞 Contacto: <strong><?= h($sol['reciclador_telefono']) ?></strong>
                </div>
                <?php if ($sol['cantidad_recolectada']): ?>
                    <div style="font-size: 0.82rem; color: var(--green); margin-top: 6px; font-weight: 600;">
                        ✓ Peso recolectado en sitio: <?= number_format($sol['cantidad_recolectada'], 1) ?> kg
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Buscando un reciclador disponible en la zona de <?= h($sol['localidad']) ?>. Tan pronto acepte tu solicitud, verás aquí su información.
            </div>
        <?php endif; ?>

        <?php if (!empty($sol['centro_nombre'])): ?>
            <div style="background: #F0F7FF; padding: 14px; border-radius: var(--r); border-left: 4px solid #1E6091;">
                <div style="font-size: 0.78rem; font-weight: 700; color: #1E6091; text-transform: uppercase;">Centro de Acopio Receptor</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: var(--text); margin-top: 2px;">
                    <?= h($sol['centro_nombre']) ?>
                </div>
                <?php if ($sol['cantidad_validada']): ?>
                    <div style="font-size: 0.82rem; color: #0D652D; font-weight: 700; margin-top: 4px;">
                        ⚖️ Peso Oficial Validado: <?= number_format($sol['cantidad_validada'], 1) ?> kg
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
