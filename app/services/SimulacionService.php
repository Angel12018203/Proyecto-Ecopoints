<?php
/**
 * ============================================================================
 * SERVICIO: SIMULACIÓN SERVICE (Puente de Modelado y Simulación Operativa)
 * ============================================================================
 * 
 * Conecta la plataforma web PHP con el motor de Simulación de Eventos Discretos
 * y Monte Carlo desarrollado en Python (SimPy, NumPy, Pandas):
 * 1. Simulación del flujo de recolección, asignación y validación en centros.
 * 2. Evaluación de múltiples réplicas Monte Carlo con semillas controladas.
 * 3. Comparativa de escenarios operativos (Referencia, Alta Demanda, Capacidad).
 * 4. Contraste con el modelo de minería de datos (RandomForestRegressor).
 * 5. Exportación de resultados a CSV sin alterar la base de datos de producción.
 */

require_once __DIR__ . '/../config/config.php';

class SimulacionService {

    /**
     * Retorna los 3 escenarios preconfigurados del sistema.
     */
    public function getEscenariosPredefinidos(): array {
        return [
            'A' => [
                'clave'            => 'A',
                'nombre'           => 'Escenario A: Referencia Histórica',
                'descripcion'      => 'Operación estándar basada en la tasa histórica del padrón: 40 solicitudes/día, 30 recicladores y 2 puestos de pesaje.',
                'solicitudes_dia'  => 40,
                'num_recicladores' => 30,
                'puestos_pesaje'   => 2,
                'duracion_dias'    => 7,
                'num_replicas'     => 10,
                'semilla'          => 42
            ],
            'B' => [
                'clave'            => 'B',
                'nombre'           => 'Escenario B: Incremento de la Demanda (+75%)',
                'descripcion'      => 'Simula campañas masivas de reciclaje ciudadano: aumento a 70 solicitudes/día manteniendo la infraestructura fija para evaluar congestión.',
                'solicitudes_dia'  => 70,
                'num_recicladores' => 30,
                'puestos_pesaje'   => 2,
                'duracion_dias'    => 7,
                'num_replicas'     => 10,
                'semilla'          => 42
            ],
            'C' => [
                'clave'            => 'C',
                'nombre'           => 'Escenario C: Aumento de Capacidad Operativa',
                'descripcion'      => 'Expansión logística para atender alta demanda: 45 recicladores en ruta y 4 puestos de pesaje en centros de acopio.',
                'solicitudes_dia'  => 70,
                'num_recicladores' => 45,
                'puestos_pesaje'   => 4,
                'duracion_dias'    => 7,
                'num_replicas'     => 10,
                'semilla'          => 42
            ]
        ];
    }

    /**
     * Ejecuta la simulación pasando la configuración al motor Python
     */
    public function ejecutarSimulacion(array $params): array {
        $pythonBin = PYTHON_BIN;
        $enginePath = ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . 'simulacion_engine.py';

        if (!file_exists($enginePath)) {
            throw new RuntimeException("Script del motor de simulación no encontrado en: " . $enginePath);
        }

        // Sanitización y validación de parámetros
        $escenarios = $this->getEscenariosPredefinidos();
        $escenarioClave = strtoupper($params['escenario'] ?? 'A');
        
        $base = $escenarios[$escenarioClave] ?? $escenarios['A'];

        $configuracion = [
            'escenario'         => $escenarioClave,
            'nombre_escenario'  => $base['nombre'],
            'solicitudes_dia'   => max(5.0, min(300.0, floatval($params['solicitudes_dia'] ?? $base['solicitudes_dia']))),
            'num_recicladores'  => max(1, min(200, intval($params['num_recicladores'] ?? $base['num_recicladores']))),
            'puestos_pesaje'    => max(1, min(20, intval($params['puestos_pesaje'] ?? $base['puestos_pesaje']))),
            'duracion_dias'     => max(1.0, min(30.0, floatval($params['duracion_dias'] ?? $base['duracion_dias']))),
            'num_replicas'      => max(1, min(50, intval($params['num_replicas'] ?? $base['num_replicas']))),
            'semilla'           => intval($params['semilla'] ?? 42),
            'modo'              => 'simulacion',
            'incluir_comparativa'=> true
        ];

        // Crear archivo temporal para paso seguro de parámetros en Windows
        $tempInputFile = tempnam(sys_get_temp_dir(), 'eco_sim_') . '.json';
        file_put_contents($tempInputFile, json_encode($configuracion, JSON_UNESCAPED_UNICODE));

        $cmd = 'cmd /c ""' . $pythonBin . '" "' . $enginePath . '" --file "' . $tempInputFile . '""';

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        putenv('ECOPOINTS_MODELOS_DIR=' . ML_MODELS_PATH);
        putenv('PYTHONIOENCODING=utf-8');

        $process = proc_open($cmd, $descriptors, $pipes, dirname($enginePath), null);

        if (!is_resource($process)) {
            @unlink($tempInputFile);
            throw new RuntimeException("No fue posible iniciar el subproceso Python para el motor de simulación.");
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);
        @unlink($tempInputFile);

        if ($returnCode !== 0) {
            $err = trim($stderr) ?: trim($stdout);
            return [
                'success' => false,
                'error'   => "Error ejecutando simulación SimPy en Python (código $returnCode): " . $err
            ];
        }

        $decoded = json_decode($stdout, true);
        if (!$decoded || !isset($decoded['success'])) {
            return [
                'success' => false,
                'error'   => "Respuesta JSON no válida del motor de simulación: " . substr($stdout, 0, 300)
            ];
        }

        return $decoded;
    }

    /**
     * Genera el contenido CSV con los resultados detallados de las réplicas
     */
    public function exportarResultadosCSV(array $resultadoSimulacion): string {
        $replicas = $resultadoSimulacion['replicas_detalle'] ?? [];
        if (empty($replicas)) {
            return "No hay réplicas simuladas para exportar.\n";
        }

        $output = fopen('php://temp', 'r+');

        // Metadatos en la cabecera del CSV
        fputcsv($output, ['# REPORTE DE MODELADO Y SIMULACIÓN MONTE CARLO - ECOPOINTS']);
        fputcsv($output, ['# Escenario:', $resultadoSimulacion['nombre_escenario'] ?? 'N/A']);
        fputcsv($output, ['# Duración (Días):', $resultadoSimulacion['configuracion']['duracion_dias'] ?? 'N/A']);
        fputcsv($output, ['# Recicladores en Ruta:', $resultadoSimulacion['configuracion']['num_recicladores'] ?? 'N/A']);
        fputcsv($output, ['# Puestos de Pesaje en Acopio:', $resultadoSimulacion['configuracion']['puestos_pesaje'] ?? 'N/A']);
        fputcsv($output, ['# Solicitudes/Día:', $resultadoSimulacion['configuracion']['solicitudes_dia'] ?? 'N/A']);
        fputcsv($output, ['# Semilla Base:', $resultadoSimulacion['configuracion']['semilla_base'] ?? 'N/A']);
        fputcsv($output, []); // Línea en blanco

        // Cabeceras de columnas de datos
        fputcsv($output, [
            'Replica',
            'Semilla',
            'Solicitudes_Generadas',
            'Solicitudes_Completadas',
            'Solicitudes_Pendientes',
            'Porcentaje_Atendidas',
            'Kg_Totales_Recuperados',
            'EcoPoints_Regla_Directa',
            'EcoPoints_ML_Proyectados',
            'Espera_Reciclador_Min',
            'Espera_Pesaje_Min',
            'Tiempo_Ciclo_Total_Min'
        ]);

        foreach ($replicas as $r) {
            fputcsv($output, [
                $r['replica_num'],
                $r['semilla'],
                $r['solicitudes_generadas'],
                $r['solicitudes_completadas'],
                $r['solicitudes_pendientes'],
                $r['pct_atendidas'] . '%',
                $r['kg_totales'],
                $r['ecopuntos_regla_directa'],
                $r['ecopuntos_ml_proyectados'] ?? 'N/A',
                $r['espera_reciclador_min'],
                $r['espera_pesaje_min'],
                $r['tiempo_ciclo_total_min']
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
