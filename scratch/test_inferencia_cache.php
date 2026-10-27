<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/services/InferenciaService.php';

$service = new InferenciaService();
$t0 = microtime(true);
$cached = $service->obtenerOEjecutarAnalisis(6, false); // No force, use cache
$t1 = microtime(true);

echo "Carga desde caché en: " . round(($t1 - $t0) * 1000, 2) . " ms!\n";
echo "Origen: " . $cached['origen'] . "\n";
echo "Puntos estimados: " . $cached['regresion']['puntos_estimados'] . "\n";
echo "Nivel: " . $cached['clasificacion']['nivel_actividad'] . "\n";
echo "Segmento: " . $cached['segmentacion']['segmento'] . "\n";
echo "Anomalía: " . ($cached['anomalias']['es_anomalo'] ? 'SÍ' : 'NO') . " (" . $cached['anomalias']['estado_operativo'] . ")\n";
