<?php
// =====================================================================
// ECOPOINT — HEADER Y BARRA SUPERIOR
// =====================================================================

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../auth/permissions.php';

// Validar que exista sesión activa
requireLogin();

$pageTitle = $pageTitle ?? 'Ecopoint — Plataforma de Reciclaje';
$rol = getCurrentRole();
$userId = getCurrentUserId();

// Consultar saldo actualizado de Ecopuntos si es reciclador
$saldoEcopuntos = 0;
if ($rol === 'reciclador' && $userId) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT ecopuntos FROM perfil_reciclador WHERE id_usuario = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $saldoEcopuntos = (int)$stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <!-- Google Fonts Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Estilos Ecopoint -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div id="app">
    <!-- Sidebar Modular -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Contenido Principal -->
    <main id="main">
        <header id="hdr">
            <div class="hdr-left">
                <button type="button" class="menu-toggle-btn" aria-label="Abrir menú">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <div class="hdr-title"><?= h($headerTitle ?? 'Panel de Control') ?></div>
            </div>

            <div class="hdr-right">
                <?php if ($rol === 'reciclador'): ?>
                    <a href="/reciclador/ecopuntos.php" class="pts-pill" title="Ver detalle de tus Ecopuntos">
                        <span>🪙</span>
                        <span class="pts-num"><?= number_format($saldoEcopuntos, 0, ',', '.') ?></span>
                        <span style="font-size: 0.72rem; opacity: 0.85;">Ecopuntos</span>
                    </a>
                <?php else: ?>
                    <a href="/ciudadano/solicitar.php" class="btn btn-p btn-sm">
                        <span>+</span> Nueva Solicitud
                    </a>
                <?php endif; ?>

                <a href="<?= $rol === 'reciclador' ? '/reciclador/perfil.php' : '/ciudadano/perfil.php' ?>" class="btn btn-o btn-sm" style="display: flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>Perfil</span>
                </a>
            </div>
        </header>

        <!-- Contenedor con scroll de página -->
        <div id="pc">
            <!-- Alertas Flash del Sistema -->
            <?php require_once __DIR__ . '/alerts.php'; ?>
