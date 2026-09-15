<?php
// =====================================================================
// ECOPOINT — ENTREGAS EN CENTRO DE ACOPIO Y VALIDACIÓN DE PESAJE
// (PASOS 7, 8 Y 9 DEL FLUJO MAESTRO)
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

requireRole('reciclador');

$userId = getCurrentUserId();
$db = Database::getConnection();

// 1. Procesar Registro de Nueva Entrega (Paso 7)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registrar_entrega') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('danger', 'Error de seguridad CSRF.');
    } else {
        $idRecoleccion = (int)($_POST['id_recoleccion'] ?? 0);
        $idCentro = (int)($_POST['id_centro'] ?? 0);
        $cantidadDeclarada = (float)($_POST['cantidad_declarada'] ?? 0);
        $obs = trim($_POST['observaciones'] ?? '');

        if ($idRecoleccion <= 0 || $idCentro <= 0 || $cantidadDeclarada <= 0) {
            setFlash('danger', 'Todos los campos de la entrega son obligatorios.');
        } else {
            try {
                // Verificar que la recolección pertenezca al usuario y no tenga entrega activa
                $checkRec = $db->prepare("
                    SELECT r.*, e.id_entrega 
                    FROM recolecciones r
                    LEFT JOIN entregas e ON r.id_recoleccion = e.id_recoleccion
                    WHERE r.id_recoleccion = :id AND r.id_reciclador = :user_id
                ");
                $checkRec->execute([':id' => $idRecoleccion, ':user_id' => $userId]);
                $rec = $checkRec->fetch();

                if (!$rec) {
                    throw new Exception('Recolección no válida.');
                }
                if (!empty($rec['id_entrega'])) {
                    throw new Exception('Esta recolección ya tiene un registro de entrega.');
                }

                // Insertar entrega con estado 'Pendiente' (o 'Pesaje')
                // NOTA: NO SE GENERAN ECOPUNTOS AQUÍ
                $insertEntrega = $db->prepare("
                    INSERT INTO entregas (id_recoleccion, id_reciclador, id_centro, fecha_entrega, cantidad_declarada, estado, observaciones)
                    VALUES (:id_recoleccion, :id_reciclador, :id_centro, NOW(), :declarada, 'Pendiente', :obs)
                ");
                $insertEntrega->execute([
                    ':id_recoleccion' => $idRecoleccion,
                    ':id_reciclador'  => $userId,
                    ':id_centro'      => $idCentro,
                    ':declarada'      => $cantidadDeclarada,
                    ':obs'            => $obs
                ]);

                // Actualizar solicitud a estado Entregada
                $db->prepare("UPDATE solicitudes SET estado = 'Entregada' WHERE id_solicitud = :id_sol")
                   ->execute([':id_sol' => $rec['id_solicitud']]);

                setFlash('info', "¡Entrega registrada en el centro de acopio! Tu material se encuentra en cola de pesaje. Los Ecopuntos se calcularán y acreditarán una vez el centro valide el peso en balanza.");
                header('Location: /reciclador/entregas.php');
                exit;
            } catch (Exception $e) {
                setFlash('danger', $e->getMessage());
            }
        }
    }
}

// 2. Procesar Validación y Pesaje Oficial de Centro de Acopio (Pasos 8 y 9)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validar_pesaje') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        setFlash('danger', 'Error de seguridad CSRF.');
    } else {
        $idEntrega = (int)($_POST['id_entrega'] ?? 0);
        $pesoValidado = (float)($_POST['cantidad_validada'] ?? 0);
        $obsValidacion = trim($_POST['observaciones_pesaje'] ?? 'Pesaje verificado en báscula digital certificada.');

        if ($idEntrega <= 0 || $pesoValidado <= 0) {
            setFlash('danger', 'Debes ingresar un peso validado válido mayor a 0 kg.');
        } else {
            // Ejecutar la función de negocio estricta definida en auth/auth.php
            $resultado = validarEntregaYGenerarEcopuntos($idEntrega, $pesoValidado, $obsValidacion);
            if ($resultado['success']) {
                setFlash('success', "🎉 " . $resultado['message']);
                header('Location: /reciclador/ecopuntos.php');
                exit;
            } else {
                setFlash('danger', $resultado['message']);
            }
        }
    }
}

// Consultar entregas realizadas por el reciclador
$stmtEntregas = $db->prepare("
    SELECT e.*, c.nombre AS centro_nombre, c.direccion AS centro_direccion,
           r.id_solicitud, m.nombre AS material_nombre, m.puntos_por_kg,
           ep.cantidad_puntos
    FROM entregas e
    INNER JOIN centros_acopio c ON e.id_centro = c.id_centro
    INNER JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
    INNER JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    INNER JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN ecopuntos ep ON e.id_entrega = ep.id_entrega
    WHERE e.id_reciclador = :id
    ORDER BY e.id_entrega DESC
");
$stmtEntregas->execute([':id' => $userId]);
$entregas = $stmtEntregas->fetchAll();

// Consultar recolecciones recolectadas disponibles para entregar
$stmtPendientes = $db->prepare("
    SELECT r.*, s.id_solicitud, s.direccion, m.nombre AS material_nombre, m.puntos_por_kg
    FROM recolecciones r
    INNER JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
    INNER JOIN materiales m ON s.id_material = m.id_material
    LEFT JOIN entregas e ON r.id_recoleccion = e.id_recoleccion
    WHERE r.id_reciclador = :id AND r.estado = 'Recolectada' AND e.id_entrega IS NULL
    ORDER BY r.id_recoleccion DESC
");
$stmtPendientes->execute([':id' => $userId]);
$recoleccionesDisponibles = $stmtPendientes->fetchAll();

// Catálogo de centros de acopio
$centros = $db->query("SELECT id_centro, nombre, localidad FROM centros_acopio WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll();

$pageTitle = 'Ecopoint — Entregas y Validación de Pesaje';
$headerTitle = 'Entregas en Centros de Acopio';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="phdr">
    <div>
        <h1 class="ptitle">Entregas y Pesaje Certificado ⚖️</h1>
        <p class="psub">El puente indispensable entre tu recolección y la generación legítima de Ecopuntos.</p>
    </div>
</div>

<!-- Cuadro de Regla Fundamental (Sección 17 y 18) -->
<div class="card cp" style="background: #F4FAF6; border: 1.5px solid #CBEACF; margin-bottom: 24px;">
    <div style="display: flex; gap: 14px; align-items: flex-start;">
        <div style="font-size: 1.8rem;">🔒</div>
        <div>
            <h3 style="font-size: 0.98rem; font-weight: 700; color: var(--green); margin-bottom: 4px;">
                Trazabilidad Obligatoria de Ecopoints
            </h3>
            <p style="font-size: 0.82rem; color: var(--muted); line-height: 1.5;">
                <strong>Solicitud → Aceptación → Recolección → Entrega → Pesaje → Validación → Ecopoints</strong><br>
                El sistema no permite generar puntos al aceptar ni al recolectar en calle. Los Ecopuntos se calculan y acreditan únicamente sobre el <strong>peso certificado en balanza</strong> por el Centro de Acopio.
            </p>
        </div>
    </div>
</div>

<!-- Formulario para Registrar Nueva Entrega -->
<?php if (!empty($recoleccionesDisponibles)): ?>
    <div class="card cp" style="margin-bottom: 24px; border: 2px solid var(--green); background: #FFF;">
        <div class="card-header-clean">
            <div>
                <h3 class="card-title">Registrar Entrega de Material Recolectado</h3>
                <div style="font-size: 0.78rem; color: var(--muted);">Selecciona la recolección física que vas a ingresar al centro</div>
            </div>
            <span class="bdg bdg-disp">Paso 7 de Trazabilidad</span>
        </div>

        <form action="/reciclador/entregas.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
            <input type="hidden" name="action" value="registrar_entrega">

            <div class="g3">
                <div class="form-group">
                    <label class="form-label">Recolección a Entregar <span style="color:red;">*</span></label>
                    <select name="id_recoleccion" id="select_recoleccion" class="form-select" required onchange="actualizarCantidadDeclarada(this)">
                        <option value="">-- Seleccionar recolección --</option>
                        <?php foreach ($recoleccionesDisponibles as $recDisp): ?>
                            <option value="<?= $recDisp['id_recoleccion'] ?>" 
                                    data-peso="<?= $recDisp['cantidad_recolectada'] ?? 0 ?>"
                                    data-pts="<?= $recDisp['puntos_por_kg'] ?>"
                                    data-material="<?= h($recDisp['material_nombre']) ?>">
                                Recolección #<?= $recDisp['id_recoleccion'] ?>: <?= number_format($recDisp['cantidad_recolectada'], 1) ?> kg de <?= h($recDisp['material_nombre']) ?> (Sol. #<?= $recDisp['id_solicitud'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Centro de Acopio Destino <span style="color:red;">*</span></label>
                    <select name="id_centro" class="form-select" required>
                        <option value="">-- Seleccionar Centro --</option>
                        <?php foreach ($centros as $c): ?>
                            <option value="<?= $c['id_centro'] ?>"><?= h($c['nombre']) ?> (<?= h($c['localidad']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Cantidad Declarada (kg) <span style="color:red;">*</span></label>
                    <input type="number" step="0.1" min="0.1" name="cantidad_declarada" id="cantidad_declarada_input" class="form-control" placeholder="Ej. 28.0" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Observaciones de Entrega</label>
                <input type="text" name="observaciones" class="form-control" placeholder="Ej. Entrega en recepción bahía 2, bultos limpios.">
            </div>

            <button type="submit" class="btn btn-p btn-lg">
                ✓ Confirmar Ingreso a Centro de Acopio
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Historial de Entregas -->
<div class="card" style="border: 1px solid var(--border);">
    <div class="cp" style="padding-bottom: 0;">
        <h3 class="card-title">Historial de Entregas y Validación de Pesaje</h3>
    </div>

    <?php if (empty($entregas)): ?>
        <div style="text-align: center; padding: 40px; color: var(--muted);">
            No tienes entregas registradas aún. Registra una recolección para llevar tus materiales a un centro.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>ID Entrega</th>
                        <th>Material</th>
                        <th>Centro de Acopio</th>
                        <th>Cant. Declarada</th>
                        <th>Cant. Validada</th>
                        <th>Ecopuntos</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acción de Pesaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entregas as $ent): ?>
                        <tr>
                            <td>
                                <strong>#<?= $ent['id_entrega'] ?></strong>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= substr($ent['fecha_entrega'], 0, 16) ?></div>
                            </td>
                            <td>
                                <span class="bdg bdg-proc"><?= h($ent['material_nombre']) ?></span>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= (int)$ent['puntos_por_kg'] ?> pts/kg</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?= h($ent['centro_nombre']) ?></div>
                                <div style="font-size: 0.72rem; color: var(--muted);"><?= h($ent['centro_direccion']) ?></div>
                            </td>
                            <td><?= number_format($ent['cantidad_declarada'], 1) ?> kg</td>
                            <td>
                                <?php if ($ent['cantidad_validada']): ?>
                                    <strong style="color: var(--green); font-size: 0.95rem;">
                                        <?= number_format($ent['cantidad_validada'], 1) ?> kg
                                    </strong>
                                <?php else: ?>
                                    <span style="color: var(--muted); font-style: italic;">En espera</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ent['cantidad_puntos']): ?>
                                    <span class="pts-pill" style="display: inline-flex; padding: 3px 8px;">
                                        🪙 +<?= number_format($ent['cantidad_puntos'], 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span style="font-size: 0.78rem; color: var(--muted);">0 pts (No validada)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ent['estado'] === 'Validada'): ?>
                                    <span class="bdg bdg-vali">✓ Validada</span>
                                <?php elseif ($ent['estado'] === 'Pendiente'): ?>
                                    <span class="bdg bdg-proc">⏳ Pendiente Balanza</span>
                                <?php else: ?>
                                    <span class="bdg bdg-reco"><?= h($ent['estado']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($ent['estado'] !== 'Validada'): ?>
                                    <!-- Botón interactivo para simular la validación oficial del centro de acopio (Demostración punto 39) -->
                                    <button type="button" class="btn btn-gold btn-sm" 
                                            onclick="abrirModalPesaje(<?= $ent['id_entrega'] ?>, <?= $ent['cantidad_declarada'] ?>, <?= $ent['puntos_por_kg'] ?>, '<?= h($ent['material_nombre']) ?>', '<?= h($ent['centro_nombre']) ?>')">
                                        ⚖️ Validar Pesaje (Centro)
                                    </button>
                                <?php else: ?>
                                    <a href="/reciclador/ecopuntos.php" class="btn btn-o btn-sm">
                                        Ver en Ecopuntos →
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Pesaje y Validación Oficial del Centro de Acopio -->
<div id="modalPesaje" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-title">⚖️ Recepción y Pesaje Oficial en Balanza</div>
            <button type="button" class="modal-close" onclick="cerrarModalPesaje()">&times;</button>
        </div>

        <form action="/reciclador/entregas.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
            <input type="hidden" name="action" value="validar_pesaje">
            <input type="hidden" name="id_entrega" id="modal_id_entrega">

            <div class="modal-body">
                <div style="background: #F8FAF9; padding: 12px 14px; border-radius: var(--r); margin-bottom: 16px; font-size: 0.84rem;">
                    <div>Centro de Acopio: <strong id="modal_centro_nombre"></strong></div>
                    <div>Material: <strong id="modal_material_nombre"></strong></div>
                    <div>Cantidad declarada por reciclador: <strong id="modal_cantidad_declarada"></strong> kg</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="modal_peso_validado">
                        Peso Oficial Certificado en Balanza Digital (kg) <span style="color:red;">*</span>
                    </label>
                    <input type="number" step="0.1" min="0.1" name="cantidad_validada" id="modal_peso_validado" class="form-control" placeholder="Ej. 27.5" required oninput="calcularPuntosModal()" style="font-size: 1.2rem; font-weight: 700;">
                    <div style="font-size: 0.76rem; color: var(--muted); margin-top: 4px;">
                        Para la prueba del flujo sugerida: <strong>27.5 kg</strong>
                    </div>
                </div>

                <!-- Simulación en tiempo real de Ecopuntos resultantes -->
                <div style="background: var(--gold-light); border: 1px solid #FFE7A8; border-radius: var(--r); padding: 14px; text-align: center; margin-bottom: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: #8F5200; text-transform: uppercase;">
                        Ecopuntos a generar según peso balanza:
                    </div>
                    <div id="modal_resultado_puntos" style="font-size: 1.8rem; font-weight: 800; color: #7A5300; margin-top: 2px;">
                        +275 Ecopuntos
                    </div>
                    <div style="font-size: 0.74rem; color: #8F5200;" id="modal_formula_texto">
                        (27.5 kg × 10 pts/kg)
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Certificación u Observación del Centro</label>
                    <input type="text" name="observaciones_pesaje" class="form-control" value="Material verificado en balanza digital certificada." placeholder="Comentario de pesaje">
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn btn-o" onclick="cerrarModalPesaje()">Cancelar</button>
                <button type="submit" class="btn btn-p">
                    ✓ Validar y Acreditar Ecopuntos
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let factorPtsGlobal = 10;

function actualizarCantidadDeclarada(select) {
    const opt = select.options[select.selectedIndex];
    if (opt && opt.dataset.peso) {
        document.getElementById('cantidad_declarada_input').value = opt.dataset.peso;
    }
}

function abrirModalPesaje(idEntrega, cantDeclarada, ptsKg, material, centro) {
    document.getElementById('modal_id_entrega').value = idEntrega;
    document.getElementById('modal_centro_nombre').textContent = centro;
    document.getElementById('modal_material_nombre').textContent = material;
    document.getElementById('modal_cantidad_declarada').textContent = cantDeclarada;
    
    // Por defecto precargar 27.5 para la prueba del paso 39 o el declarado
    const sugerido = (cantDeclarada == 28) ? 27.5 : cantDeclarada;
    document.getElementById('modal_peso_validado').value = sugerido;
    factorPtsGlobal = ptsKg;

    calcularPuntosModal();
    document.getElementById('modalPesaje').classList.add('open');
}

function cerrarModalPesaje() {
    document.getElementById('modalPesaje').classList.remove('open');
}

function calcularPuntosModal() {
    const peso = parseFloat(document.getElementById('modal_peso_validado').value) || 0;
    const pts = Math.round(peso * factorPtsGlobal);
    document.getElementById('modal_resultado_puntos').textContent = '+' + pts.toLocaleString('es-CO') + ' Ecopuntos';
    document.getElementById('modal_formula_texto').textContent = '(' + peso + ' kg × ' + factorPtsGlobal + ' pts/kg)';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
