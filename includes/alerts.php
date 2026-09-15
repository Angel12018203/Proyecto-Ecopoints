<?php
// =====================================================================
// ECOPOINT — RENDERIZADOR DE ALERTAS Y MENSAJES FLASH
// =====================================================================

$flash = getFlash();
if ($flash):
    $alertClass = 'alert-info';
    $icon = 'ℹ️';
    switch ($flash['type']) {
        case 'success':
            $alertClass = 'alert-success';
            $icon = '✅';
            break;
        case 'danger':
        case 'error':
            $alertClass = 'alert-danger';
            $icon = '⚠️';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            $icon = '⚡';
            break;
    }
?>
<div class="alert <?= $alertClass ?>">
    <span style="font-size: 1.1rem;"><?= $icon ?></span>
    <div><?= h($flash['message']) ?></div>
</div>
<?php endif; ?>
