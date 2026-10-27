<?php
/**
 * ============================================================================
 * SERVICIO: INFERENCIA SERVICE (Puente de Minería de Datos y Modelos ML)
 * ============================================================================
 * 
 * Conecta el aplicativo web PHP con los 4 modelos de Machine Learning
 * entrenados en Python (.joblib):
 * 1. Regresión: Estimación de puntos del siguiente periodo (RandomForestRegressor).
 * 2. Clasificación: Nivel de actividad Baja/Media/Alta (LogisticRegression).
 * 3. Segmentación: Perfil de clúster K-Means (Ocasionales, Constantes, Líderes).
 * 4. Detección de Anomalías: Desviaciones operativas (IsolationForest).
 * 5. Reglas de Asociación: Análisis transaccional Apriori / Cestas de reciclaje.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

class InferenciaService {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene el análisis predictivo guardado en caché o ejecuta uno nuevo
     * si no existe o si se solicita forzar actualización.
     */
    public function obtenerOEjecutarAnalisis(int $idReciclador, bool $forzar = false): array {
        if (!$forzar) {
            $guardado = $this->getAnaliticaGuardada($idReciclador);
            if ($guardado !== null) {
                return $guardado;
            }
        }

        return $this->ejecutarInferenciaReciclador($idReciclador);
    }

    /**
     * Consulta el análisis más reciente guardado en analitica_predicciones.
     */
    public function getAnaliticaGuardada(int $idReciclador): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM analitica_predicciones 
            WHERE id_reciclador = ? 
            ORDER BY fecha_analisis DESC 
            LIMIT 1
        ");
        $stmt->execute([$idReciclador]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'origen'              => 'cache',
            'id_analitica'        => (int)$row['id_analitica'],
            'fecha_analisis'      => $row['fecha_analisis'],
            'periodo_analizado'   => $row['periodo_analizado'],
            'regresion'           => [
                'puntos_estimados' => (float)$row['prediccion_puntos'],
                'algoritmo'        => 'RandomForestRegressor (n_estimators=100, max_depth=6)',
                'horizonte'        => 'siguiente_observacion'
            ],
            'clasificacion'       => [
                'nivel_actividad'  => $row['nivel_actividad'],
                'probabilidades'   => [
                    'Baja'  => (float)$row['prob_baja'],
                    'Media' => (float)$row['prob_media'],
                    'Alta'  => (float)$row['prob_alta']
                ],
                'algoritmo'        => 'LogisticRegression Multinomial (Anti-Leakage)'
            ],
            'segmentacion'        => [
                'cluster_id'       => (int)$row['cluster_id'],
                'segmento'         => $row['segmento_cluster'],
                'algoritmo'        => 'KMeans (k=3)'
            ],
            'anomalias'           => [
                'es_anomalo'        => (bool)$row['es_anomalo'],
                'score_aislamiento' => (float)$row['score_aislamiento'],
                'estado_operativo'  => $row['estado_operativo'],
                'algoritmo'         => 'IsolationForest (contamination=0.03)'
            ],
            'features_entrada'    => json_decode($row['features_entrada'] ?? '{}', true),
            'recomendaciones'     => json_decode($row['recomendaciones'] ?? '[]', true)
        ];
    }

    /**
     * Ejecuta la inferencia real de los modelos sobre un reciclador específico.
     */
    public function ejecutarInferenciaReciclador(int $idReciclador): array {
        // 1. Extraer datos mensuales históricos desde la vista vw_diccionario_ecopoints_mensual
        $stmt = $this->db->prepare("
            SELECT * FROM vw_diccionario_ecopoints_mensual 
            WHERE reciclador_id = ? 
            ORDER BY mes DESC
        ");
        $stmt->execute([$idReciclador]);
        $filasMensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Si no hay filas en la vista mensual, revisar entregas activas en tiempo real
        if (empty($filasMensuales)) {
            $stmtEntregas = $this->db->prepare("
                SELECT 
                    COUNT(e.id_entrega) AS entregas,
                    COALESCE(SUM(e.cantidad_validada), 0) AS kg_recolectados,
                    COUNT(DISTINCT s.id_material) AS diversidad_materiales,
                    COALESCE(SUM(e.puntos_otorgados), 0) AS puntos_obtenidos,
                    (
                        SELECT m2.nombre 
                        FROM entregas e2
                        JOIN recolecciones r2 ON e2.id_recoleccion = r2.id_recoleccion
                        JOIN solicitudes s2 ON r2.id_solicitud = s2.id_solicitud
                        JOIN materiales m2 ON s2.id_material = m2.id_material
                        WHERE r2.id_reciclador = ? AND e2.estado = 'Validada'
                        GROUP BY m2.id_material, m2.nombre
                        ORDER BY SUM(e2.cantidad_validada) DESC
                        LIMIT 1
                    ) AS material_principal
                FROM entregas e
                JOIN recolecciones r ON e.id_recoleccion = r.id_recoleccion
                JOIN solicitudes s ON r.id_solicitud = s.id_solicitud
                WHERE r.id_reciclador = ? AND e.estado = 'Validada'
            ");
            $stmtEntregas->execute([$idReciclador, $idReciclador]);
            $resEntregas = $stmtEntregas->fetch(PDO::FETCH_ASSOC);

            if (!$resEntregas || (int)$resEntregas['entregas'] === 0) {
                return [
                    'estado'  => 'sin_actividad',
                    'mensaje' => 'El reciclador no cuenta con entregas validadas registradas para realizar el análisis estadístico y predictivo.'
                ];
            }

            // Crear un registro sintetizado a partir de las entregas reales del nuevo reciclador
            $ultimoMes = [
                'reciclador_id'         => $idReciclador,
                'mes'                   => date('Y-m-01'),
                'material_principal'    => $resEntregas['material_principal'] ?? 'Plástico',
                'kg_recolectados'       => (float)$resEntregas['kg_recolectados'],
                'entregas'              => (int)$resEntregas['entregas'],
                'diversidad_materiales' => max(1, (int)$resEntregas['diversidad_materiales']),
                'beneficios_redimidos'  => 0,
                'puntos_obtenidos'      => (float)$resEntregas['puntos_obtenidos']
            ];
            $filasMensuales = [$ultimoMes];
        }

        // 3. Calcular métricas del último periodo y promedios históricos
        $ultimo = $filasMensuales[0];
        $totalPeriodos = count($filasMensuales);

        $sumKg = 0; $sumEntregas = 0; $sumPuntos = 0; $sumDiv = 0; $sumBen = 0;
        foreach ($filasMensuales as $f) {
            $sumKg += (float)$f['kg_recolectados'];
            $sumEntregas += (float)$f['entregas'];
            $sumPuntos += (float)$f['puntos_obtenidos'];
            $sumDiv += (float)$f['diversidad_materiales'];
            $sumBen += (float)$f['beneficios_redimidos'];
        }

        $mesNum = (int)date('n', strtotime($ultimo['mes']));

        $payload = [
            'kg_recolectados'       => (float)$ultimo['kg_recolectados'],
            'entregas'              => (int)$ultimo['entregas'],
            'diversidad_materiales' => (float)$ultimo['diversidad_materiales'],
            'beneficios_redimidos'  => (int)$ultimo['beneficios_redimidos'],
            'puntos_obtenidos'      => (float)$ultimo['puntos_obtenidos'],
            'mes_num'               => $mesNum,
            'material_principal'    => $ultimo['material_principal'] ?? 'Cartón',
            // Promedios históricos para K-Means:
            'promedio_kg'           => round($sumKg / $totalPeriodos, 2),
            'promedio_entregas'     => round($sumEntregas / $totalPeriodos, 2),
            'promedio_puntos'       => round($sumPuntos / $totalPeriodos, 2),
            'promedio_diversidad'   => round($sumDiv / $totalPeriodos, 2),
            'promedio_beneficios'   => round($sumBen / $totalPeriodos, 2)
        ];

        // 4. Invocar el motor de inferencia en Python
        $resultadoML = $this->invocarMotorPython($payload);
        if (!$resultadoML['success']) {
            throw new RuntimeException("Error en inferencia de modelos: " . ($resultadoML['error'] ?? 'desconocido'));
        }

        // 5. Construir recomendaciones explicables basadas en los resultados
        $recomendaciones = $this->generarRecomendaciones($payload, $resultadoML, $totalPeriodos);

        // 6. Guardar / Cachear en analitica_predicciones
        $stmtInsert = $this->db->prepare("
            INSERT INTO analitica_predicciones (
                id_reciclador, fecha_analisis, periodo_analizado,
                prediccion_puntos, nivel_actividad, prob_baja, prob_media, prob_alta,
                segmento_cluster, cluster_id, es_anomalo, score_aislamiento, estado_operativo,
                features_entrada, recomendaciones
            ) VALUES (
                ?, NOW(), ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?
            )
        ");

        $stmtInsert->execute([
            $idReciclador,
            $ultimo['mes'],
            $resultadoML['regresion']['puntos_estimados'] ?? 0,
            $resultadoML['clasificacion']['nivel_actividad'] ?? 'Media',
            $resultadoML['clasificacion']['probabilidades']['Baja'] ?? 0,
            $resultadoML['clasificacion']['probabilidades']['Media'] ?? 0,
            $resultadoML['clasificacion']['probabilidades']['Alta'] ?? 0,
            $resultadoML['segmentacion']['segmento'] ?? 'No clasificado',
            $resultadoML['segmentacion']['cluster_id'] ?? 0,
            !empty($resultadoML['anomalias']['es_anomalo']) ? 1 : 0,
            $resultadoML['anomalias']['score_aislamiento'] ?? 0,
            $resultadoML['anomalias']['estado_operativo'] ?? 'Normal',
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            json_encode($recomendaciones, JSON_UNESCAPED_UNICODE)
        ]);

        return array_merge($resultadoML, [
            'origen'           => 'computado_reciente',
            'fecha_analisis'   => date('Y-m-d H:i:s'),
            'periodo_analizado'=> $ultimo['mes'],
            'periodos_historia'=> $totalPeriodos,
            'features_entrada' => $payload,
            'recomendaciones'  => $recomendaciones
        ]);
    }

    /**
     * Invoca el script app/python/inferencia_engine.py pasando los datos mediante archivo temporal
     */
    private function invocarMotorPython(array $payload): array {
        $pythonBin = PYTHON_BIN;
        $enginePath = ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . 'inferencia_engine.py';

        if (!file_exists($enginePath)) {
            throw new RuntimeException("Script de motor de inferencia no encontrado en: " . $enginePath);
        }

        // Crear archivo temporal para paso seguro de parámetros en Windows
        $tempInputFile = tempnam(sys_get_temp_dir(), 'eco_in_') . '.json';
        file_put_contents($tempInputFile, json_encode($payload, JSON_UNESCAPED_UNICODE));

        // En Windows cmd.exe, requiere envolver el comando completo entre comillas externas
        $cmd = 'cmd /c ""' . $pythonBin . '" "' . $enginePath . '" --file "' . $tempInputFile . '""';

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        // Asegurar variables requeridas en el entorno heredado
        putenv('ECOPOINTS_MODELOS_DIR=' . ML_MODELS_PATH);
        putenv('PYTHONIOENCODING=utf-8');

        // Al pasar null en $env, Windows hereda SystemRoot, PATH y Winsock correctamente
        $process = proc_open($cmd, $descriptors, $pipes, dirname($enginePath), null);

        if (!is_resource($process)) {
            @unlink($tempInputFile);
            throw new RuntimeException("No fue posible iniciar el proceso de Python para inferencia.");
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
                'error'   => "Error ejecutando inferencia en Python (código $returnCode): " . $err
            ];
        }

        $decoded = json_decode($stdout, true);
        if (!$decoded || !isset($decoded['success'])) {
            return [
                'success' => false,
                'error'   => "Respuesta JSON no válida del motor Python: " . substr($stdout, 0, 300)
            ];
        }

        return $decoded;
    }

    /**
     * Genera orientaciones explicables y contextuales basadas en los resultados.
     */
    private function generarRecomendaciones(array $datos, array $resultado, int $periodosHistoria): array {
        $recs = [];

        $puntosEst = $resultado['regresion']['puntos_estimados'] ?? 0;
        $nivel = $resultado['clasificacion']['nivel_actividad'] ?? 'Media';
        $segmento = $resultado['segmentacion']['segmento'] ?? 'Constantes (Medio)';
        $esAnomalo = !empty($resultado['anomalias']['es_anomalo']);

        // 1. Recomendación de Proyección
        $recs[] = "Proyección estimada de producción: Con el volumen actual de {$datos['kg_recolectados']} kg recolectados y {$datos['entregas']} entregas, el modelo de Bosque Aleatorio proyecta una emisión potencial de {$puntosEst} EcoPoints para el siguiente ciclo operativo.";

        // 2. Recomendación de Nivel y Actividad
        if ($nivel === 'Alta') {
            $recs[] = "Perfil de Alto Desempeño: El reciclador muestra una probabilidad dominante ({$resultado['clasificacion']['probabilidades']['Alta']}%) de mantener actividad Alta. Se sugiere priorizar asignación de solicitudes de gran volumen o clientes corporativos.";
        } elseif ($nivel === 'Media') {
            $recs[] = "Actividad Sostenida: Mantiene un ritmo constante con material predominante ({$datos['material_principal']}). Estimular la diversificación de materiales podría incrementar sus puntos por entrega.";
        } else {
            $recs[] = "Oportunidad de Reactivación: La clasificación ubica al reciclador en rango de actividad Baja. Se recomienda verificar rutas cercanas y ofrecer incentivos de canje para dinamizar su participación.";
        }

        // 3. Recomendación de Segmento
        $recs[] = "Segmento Operativo «{$segmento}»: Basado en un historial consolidado de {$periodosHistoria} periodo(s) con promedio mensual de {$datos['promedio_kg']} kg y {$datos['promedio_entregas']} visitas a centros de acopio.";

        // 4. Revisión Operativa de Anomalías
        if ($esAnomalo) {
            $score = $resultado['anomalias']['score_aislamiento'] ?? 0;
            $recs[] = "⚠️ Auditoría Operativa Sugerida: El algoritmo Isolation Forest marcó este registro para verificación contextual (Score de aislamiento: {$score}). Nota técnica: Esto no implica fraude ni altera puntos; simplemente señala una combinación atípica de peso y número de entregas.";
        }

        return $recs;
    }

    /**
     * Retorna las reglas de asociación de materiales minadas con Apriori.
     */
    public function getReglasAsociacion(string $filtro = '', int $limite = 20): array {
        $csvPath = ML_REGLAS_PATH;
        if (!file_exists($csvPath)) {
            return [];
        }

        $reglas = [];
        $file = fopen($csvPath, 'r');
        if (!$file) return [];

        $headers = fgetcsv($file);
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 6) continue;
            
            $item = [
                'antecedente'   => trim($row[0]),
                'consecuente'   => trim($row[1]),
                'soporte'       => (float)$row[2],
                'confianza'     => (float)$row[3],
                'confianza_pct' => round((float)$row[3] * 100, 1),
                'lift'          => round((float)$row[4], 3),
                'conteo_cestas' => (int)$row[5]
            ];

            if ($filtro !== '') {
                $term = mb_strtolower($filtro, 'UTF-8');
                $ant = mb_strtolower($item['antecedente'], 'UTF-8');
                $con = mb_strtolower($item['consecuente'], 'UTF-8');
                if (strpos($ant, $term) === false && strpos($con, $term) === false) {
                    continue;
                }
            }

            $reglas[] = $item;
            if (count($reglas) >= $limite) {
                break;
            }
        }
        fclose($file);

        return $reglas;
    }

    /**
     * Retorna los metadatos y métricas de validación oficial de los modelos.
     */
    public function getMetadatosModelos(): array {
        $metaPath = ML_MODELS_PATH . DIRECTORY_SEPARATOR . 'metadata_modelos.json';
        if (file_exists($metaPath)) {
            $content = file_get_contents($metaPath);
            return json_decode($content, true) ?: [];
        }
        return [];
    }

    /**
     * Retorna la galería estructurada de las 18 evidencias gráficas con metadatos.
     */
    public function getEvidenciasGraficas(): array {
        return [
            [
                'id'          => '01',
                'archivo'     => '01_distribucion_variable_objetivo.png',
                'categoria'   => 'Exploratorio (EDA)',
                'titulo'      => 'Distribución de la Variable Objetivo (Puntos Mes Siguiente)',
                'descripcion' => 'Histograma con KDE y boxplot de la variable continua. Media de 357.83 pts con asimetría positiva (+0.565).'
            ],
            [
                'id'          => '02',
                'archivo'     => '02_matriz_correlacion.png',
                'categoria'   => 'Exploratorio (EDA)',
                'titulo'      => 'Matriz de Correlación de Pearson',
                'descripcion' => 'Relaciones bivariadas entre kilogramos, entregas, ecopuntos y target. Alta correlación entre kg y puntos.'
            ],
            [
                'id'          => '03',
                'archivo'     => '03_relacion_kg_vs_puntos_mes_siguiente.png',
                'categoria'   => 'Exploratorio (EDA)',
                'titulo'      => 'Diagrama de Dispersión: Kg Recolectados vs Puntos Futuros',
                'descripcion' => 'Dispersión con recta de regresión ajustada e intervalos de confianza del 95%.'
            ],
            [
                'id'          => '04',
                'archivo'     => '04_comportamiento_por_material.png',
                'categoria'   => 'Exploratorio (EDA)',
                'titulo'      => 'Comportamiento y Aporte por Categoría de Material',
                'descripcion' => 'Desglose comparativo de peso y puntos aportados por Cartón, Plástico, Papel, Vidrio y Metales.'
            ],
            [
                'id'          => '05',
                'archivo'     => '05_tendencia_temporal_mensual.png',
                'categoria'   => 'Exploratorio (EDA)',
                'titulo'      => 'Tendencia Temporal de Recolección Mes a Mes',
                'descripcion' => 'Evolución de la recolección en kilogramos y puntos emitidos durante todo el periodo evaluado.'
            ],
            [
                'id'          => '06',
                'archivo'     => '06_real_vs_predicho_regresion.png',
                'categoria'   => 'Regresión Predictiva',
                'titulo'      => 'Valores Reales vs Predichos (Random Forest Regressor)',
                'descripcion' => 'Ajuste del modelo ganador en conjunto de prueba. MAE: 75.89 pts | RMSE: 98.99 | R²: 0.3904.'
            ],
            [
                'id'          => '07',
                'archivo'     => '07_comparativa_modelos_ganador.png',
                'categoria'   => 'Regresión Predictiva',
                'titulo'      => 'Comparativa de Modelos de Regresión (Ganador: Random Forest)',
                'descripcion' => 'Evaluación de Línea Base vs Regresión Lineal vs Árbol de Decisión vs Bosque Aleatorio.'
            ],
            [
                'id'          => '08',
                'archivo'     => '08_metodo_codo_y_silueta.png',
                'categoria'   => 'Segmentación (K-Means)',
                'titulo'      => 'Optimización de Hiperparámetros K (Método del Codo y Silueta)',
                'descripcion' => 'Curva de inercia WCSS y coeficiente de silueta identificando k=3 como la partición óptima.'
            ],
            [
                'id'          => '09',
                'archivo'     => '09_segmentacion_clusters_kmeans.png',
                'categoria'   => 'Segmentación (K-Means)',
                'titulo'      => 'Visualización Espacial de Clústeres (K-Means k=3)',
                'descripcion' => 'Dispersión 2D de recicladores segmentados en Ocasionales (Base), Constantes (Medio) y Líderes (Alto).'
            ],
            [
                'id'          => '10',
                'archivo'     => '10_perfil_estadistico_clusters.png',
                'categoria'   => 'Segmentación (K-Means)',
                'titulo'      => 'Perfil Estadístico y Centroides de los Clústeres',
                'descripcion' => 'Promedios comparados de kg, entregas, puntos y canjes para cada uno de los 3 perfiles.'
            ],
            [
                'id'          => '11',
                'archivo'     => '11_comparativa_modelos_clasificacion.png',
                'categoria'   => 'Clasificación Multiclase',
                'titulo'      => 'Comparativa de Algoritmos de Clasificación',
                'descripcion' => 'Regresión Logística (84.66% Acc) superando a Random Forest, Árboles y KNN con enfoque anti-fuga.'
            ],
            [
                'id'          => '12',
                'archivo'     => '12_matriz_confusion_clasificacion.png',
                'categoria'   => 'Clasificación Multiclase',
                'titulo'      => 'Matriz de Confusión Normalizada',
                'descripcion' => 'Desempeño predictivo por clase (Baja, Media, Alta). F1-Score ponderado: 84.65%.'
            ],
            [
                'id'          => '13',
                'archivo'     => '13_importancia_caracteristicas_clasificacion.png',
                'categoria'   => 'Clasificación Multiclase',
                'titulo'      => 'Importancia de Variables en la Clasificación',
                'descripcion' => 'Ponderación de coeficientes para entregas, diversidad, beneficios redimidos y puntos.'
            ],
            [
                'id'          => '14',
                'archivo'     => '14_deteccion_anomalias_iqr_boxplots.png',
                'categoria'   => 'Detección de Anomalías',
                'titulo'      => 'Detección Univariada por Rango Intercuartílico (IQR)',
                'descripcion' => 'Boxplots univariados de kg y entregas mostrando valores fuera del umbral 1.5 × IQR.'
            ],
            [
                'id'          => '15',
                'archivo'     => '15_deteccion_anomalias_isolation_forest.png',
                'categoria'   => 'Detección de Anomalías',
                'titulo'      => 'Detección Multivariada con Isolation Forest',
                'descripcion' => 'Espacio de decisión con contaminación del 3%. Identifica registros atípicos multidimensionales.'
            ],
            [
                'id'          => '16',
                'archivo'     => '16_comparativa_iqr_vs_isolation_forest.png',
                'categoria'   => 'Detección de Anomalías',
                'titulo'      => 'Comparativa Metodológica: IQR Univariado vs Isolation Forest',
                'descripcion' => 'Análisis de solapamiento y robustez contextual para auditoría operativa transparente.'
            ],
            [
                'id'          => '17',
                'archivo'     => '17_reglas_asociacion_soporte_confianza.png',
                'categoria'   => 'Reglas de Asociación',
                'titulo'      => 'Espacio de Reglas: Soporte vs Confianza (Lift)',
                'descripcion' => 'Dispersión de reglas Apriori codificadas por color según su fuerza de elevación (Lift).'
            ],
            [
                'id'          => '18',
                'archivo'     => '18_matriz_afinidad_materiales.png',
                'categoria'   => 'Reglas de Asociación',
                'titulo'      => 'Matriz de Afinidad y Coocurrencia de Materiales',
                'descripcion' => 'Frecuencia relativa de coexistencia en entregas para canastas como Cartón + Plástico -> Papel.'
            ]
        ];
    }
}
