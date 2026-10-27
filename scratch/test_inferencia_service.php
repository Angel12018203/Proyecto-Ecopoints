<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/services/InferenciaService.php';

$service = new InferenciaService();

echo "1. Probando getMetadatosModelos():\n";
$meta = $service->getMetadatosModelos();
echo "Proyecto: " . ($meta['proyecto'] ?? 'N/A') . "\n";
echo "Modelos cargados: " . implode(', ', array_keys($meta['modelos'] ?? [])) . "\n";

echo "\n2. Probando getReglasAsociacion():\n";
$reglas = $service->getReglasAsociacion('', 5);
echo "Reglas cargadas: " . count($reglas) . "\n";
foreach ($reglas as $r) {
    echo " - " . $r['antecedente'] . " => " . $r['consecuente'] . " (Conf: " . $r['confianza_pct'] . "%, Lift: " . $r['lift'] . ")\n";
}

echo "\n3. Probando getEvidenciasGraficas():\n";
$evidencias = $service->getEvidenciasGraficas();
echo "Evidencias disponibles: " . count($evidencias) . "\n";

echo "\n4. Probando ejecución de inferencia sobre Reciclador ID 6 (que tiene registros en la vista):\n";
$res = $service->obtenerOEjecutarAnalisis(6, true);
echo "Resultado de inferencia:\n";
print_r($res);
