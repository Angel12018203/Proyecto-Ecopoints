<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/AnaliticaEcopointsService.php';
require_once __DIR__ . '/../config/database.php';

class AnaliticaController extends Controller {

    private $service;
    private $db;

    public function __construct() {
        AuthMiddleware::adminOnly();
        $this->service = new AnaliticaEcopointsService();
        $this->db = Database::getConnection();
    }

    public function proxy(string $operation): void {
        $postOperations = [
            'predecir-puntos',
            'clasificar-nivel',
            'segmentar-reciclador',
            'evaluar-anomalia',
            'analizar-reciclador'
        ];
        $getOperations = ['reglas-asociacion', 'graficas-modelos'];

        if (in_array($operation, $postOperations, true)) {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                header('Allow: POST');
                $this->json(['error' => 'Método no permitido.'], 405);
            }

            if (!verify_csrf()) {
                $this->json(['error' => 'Token CSRF inválido o ausente.'], 403);
            }

            $body = file_get_contents('php://input', false, null, 0, 16385);
            if ($body === false || strlen($body) > 16384) {
                $this->json(['error' => 'El cuerpo de la solicitud es inválido o demasiado grande.'], 400);
            }

            $payload = json_decode($body);
            if (json_last_error() !== JSON_ERROR_NONE || !($payload instanceof stdClass)) {
                $this->json(['error' => 'Se requiere un objeto JSON válido.'], 400);
            }

            if ($operation === 'analizar-reciclador') {
                if (
                    count(get_object_vars($payload)) !== 1 ||
                    !property_exists($payload, 'reciclador_id') ||
                    !is_int($payload->reciclador_id) ||
                    $payload->reciclador_id < 1
                ) {
                    $this->json(['error' => 'Se requiere un reciclador_id entero válido.'], 400);
                }

                try {
                    $this->json($this->analyzeRecycler($payload->reciclador_id));
                } catch (AnaliticaEcopointsException $error) {
                    $this->json(
                        ['error' => $error->getMessage()],
                        $error->getResponseStatus()
                    );
                } catch (PDOException $error) {
                    error_log('EcoPoints recycler analysis query failed: ' . $error->getMessage());
                    $this->json(['error' => 'No fue posible consultar los datos mensuales del reciclador.'], 500);
                }
            }
        } elseif (in_array($operation, $getOperations, true)) {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
                header('Allow: GET');
                $this->json(['error' => 'Método no permitido.'], 405);
            }
            $body = '';
        } else {
            $this->json(['error' => 'Operación de analítica no encontrada.'], 404);
        }

        $query = [];
        if ($operation === 'reglas-asociacion') {
            foreach (['antecedente', 'consecuente', 'limite'] as $key) {
                if (!array_key_exists($key, $_GET)) {
                    continue;
                }
                if (!is_string($_GET[$key])) {
                    $this->json(['error' => 'Los filtros deben ser valores simples.'], 400);
                }
                $query[$key] = $_GET[$key];
            }
        }

        try {
            if ($operation === 'graficas-modelos') {
                try {
                    $this->json($this->modelCharts());
                } catch (PDOException $error) {
                    error_log('EcoPoints model chart query failed: ' . $error->getMessage());
                    $this->json(['error' => 'No fue posible consultar la muestra mensual para las gráficas.'], 500);
                }
            }
            $this->json($this->service->request($operation, $body, $query));
        } catch (AnaliticaEcopointsException $error) {
            $this->json(
                ['error' => $error->getMessage()],
                $error->getResponseStatus()
            );
        }
    }

    private function analyzeRecycler(int $recyclerId): array {
        $latestStatement = $this->db->prepare("
            SELECT
                DATE_FORMAT(mes, '%Y-%m') AS periodo_referencia,
                material_principal,
                kg_recolectados,
                entregas,
                diversidad_materiales,
                beneficios_redimidos,
                puntos_obtenidos
            FROM vw_diccionario_ecopoints_mensual
            WHERE reciclador_id = :reciclador_id
            ORDER BY mes DESC
            LIMIT 1
        ");
        $latestStatement->execute(['reciclador_id' => $recyclerId]);
        $latest = $latestStatement->fetch(PDO::FETCH_ASSOC);

        if (!$latest) {
            throw new AnaliticaEcopointsException(
                'No hay registros mensuales para ese reciclador.',
                404
            );
        }

        $averagesStatement = $this->db->prepare("
            SELECT
                AVG(kg_recolectados) AS promedio_kg,
                AVG(entregas) AS promedio_entregas,
                AVG(puntos_obtenidos) AS promedio_puntos,
                AVG(diversidad_materiales) AS promedio_diversidad,
                AVG(beneficios_redimidos) AS promedio_beneficios,
                COUNT(*) AS periodos_historia
            FROM vw_diccionario_ecopoints_mensual
            WHERE reciclador_id = :reciclador_id
        ");
        $averagesStatement->execute(['reciclador_id' => $recyclerId]);
        $averages = $averagesStatement->fetch(PDO::FETCH_ASSOC);

        if (!$averages || $averages['promedio_kg'] === null) {
            throw new AnaliticaEcopointsException(
                'No hay suficientes datos históricos para analizar ese reciclador.',
                404
            );
        }

        $classification = [
            'entregas' => (int)$latest['entregas'],
            'diversidad_materiales' => (int)$latest['diversidad_materiales'],
            'beneficios_redimidos' => (int)$latest['beneficios_redimidos'],
            'puntos_obtenidos' => (float)$latest['puntos_obtenidos'],
            'material_principal' => $latest['material_principal']
        ];
        $regression = $classification + [
            'kg_recolectados' => (float)$latest['kg_recolectados'],
            'mes_num' => (int)substr($latest['periodo_referencia'], 5, 2),
            'periodo_referencia' => $latest['periodo_referencia']
        ];
        $segmentation = [];
        foreach ($averages as $key => $value) {
            $segmentation[$key] = (float)$value;
        }

        $classificationJson = json_encode($classification, JSON_UNESCAPED_UNICODE);
        $regressionJson = json_encode($regression, JSON_UNESCAPED_UNICODE);
        $segmentationJson = json_encode($segmentation, JSON_UNESCAPED_UNICODE);
        $anomalyJson = json_encode([
            'kg_recolectados' => (float)$latest['kg_recolectados'],
            'entregas' => (int)$latest['entregas'],
            'diversidad_materiales' => (int)$latest['diversidad_materiales'],
            'beneficios_redimidos' => (int)$latest['beneficios_redimidos'],
            'puntos_obtenidos' => (float)$latest['puntos_obtenidos']
        ], JSON_UNESCAPED_UNICODE);
        if (
            $classificationJson === false ||
            $regressionJson === false ||
            $segmentationJson === false ||
            $anomalyJson === false
        ) {
            throw new AnaliticaEcopointsException(
                'No fue posible preparar los datos para los modelos.',
                500
            );
        }

        $classificationResult = $this->service->request('clasificar-nivel', $classificationJson);
        $regressionResult = $this->service->request('predecir-puntos', $regressionJson);
        $segmentationResult = $this->service->request('segmentar-reciclador', $segmentationJson);
        $anomalyResult = $this->service->request('evaluar-anomalia', $anomalyJson);

        return [
            'periodo_observado' => $latest['periodo_referencia'],
            'periodos_historia' => (int)$averages['periodos_historia'],
            'clasificacion' => $classificationResult,
            'prediccion' => $regressionResult,
            'segmentacion' => $segmentationResult,
            'anomalia' => $anomalyResult,
            'recomendaciones' => $this->buildRecommendations(
                $latest,
                $averages,
                $segmentationResult,
                $anomalyResult
            )
        ];
    }

    private function modelCharts(): array {
        $statement = $this->db->query("
            WITH periodos AS (
                SELECT
                    mensual.*,
                    LEAD(mensual.mes) OVER (
                        PARTITION BY mensual.reciclador_id
                        ORDER BY mensual.mes
                    ) AS periodo_siguiente_observado
                FROM vw_diccionario_ecopoints_mensual AS mensual
            ),
            historial AS (
                SELECT
                    periodos.*,
                    AVG(periodos.kg_recolectados) OVER (PARTITION BY periodos.reciclador_id) AS promedio_kg,
                    AVG(periodos.entregas) OVER (PARTITION BY periodos.reciclador_id) AS promedio_entregas,
                    AVG(periodos.puntos_obtenidos) OVER (PARTITION BY periodos.reciclador_id) AS promedio_puntos,
                    AVG(periodos.diversidad_materiales) OVER (PARTITION BY periodos.reciclador_id) AS promedio_diversidad,
                    AVG(periodos.beneficios_redimidos) OVER (PARTITION BY periodos.reciclador_id) AS promedio_beneficios,
                    COUNT(*) OVER (PARTITION BY periodos.reciclador_id) AS periodos_historia
                FROM periodos
            ),
            elegibles AS (
                SELECT
                    historial.*,
                    ROW_NUMBER() OVER (
                        PARTITION BY historial.reciclador_id
                        ORDER BY historial.mes DESC
                    ) AS periodo_rank
                FROM historial
                WHERE historial.puntos_mes_siguiente IS NOT NULL
                    AND historial.periodo_siguiente_observado = DATE_ADD(historial.mes, INTERVAL 1 MONTH)
            )
            SELECT
                elegibles.*,
                CASE WHEN usuarios.correo LIKE 'test.ecopoints.synthetic%@ecopoints.invalid' THEN 1 ELSE 0 END AS es_sintetico
            FROM elegibles
            INNER JOIN usuarios ON usuarios.id_usuario = elegibles.reciclador_id
            WHERE elegibles.periodo_rank = 1
            ORDER BY es_sintetico DESC, elegibles.reciclador_id DESC
            LIMIT 10
        ");
        $samples = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($samples === []) {
            throw new AnaliticaEcopointsException(
                'No hay pares de periodos consecutivos con target observado para graficar.',
                404
            );
        }

        $observations = [];
        foreach ($samples as $sample) {
            $period = substr($sample['mes'], 0, 7);
            $labelPrefix = $sample['es_sintetico'] ? 'Prueba' : 'Reciclador';
            $observation = [
                'etiqueta' => $labelPrefix . ' #' . (int)$sample['reciclador_id'],
                'periodo' => $period,
                'periodo_objetivo_observado' => substr($sample['periodo_siguiente_observado'], 0, 7),
                'periodos_historia' => (int)$sample['periodos_historia'],
                'puntos_observados' => (float)$sample['puntos_mes_siguiente']
            ];
            $regression = [
                'kg_recolectados' => (float)$sample['kg_recolectados'],
                'entregas' => (int)$sample['entregas'],
                'diversidad_materiales' => (int)$sample['diversidad_materiales'],
                'beneficios_redimidos' => (int)$sample['beneficios_redimidos'],
                'puntos_obtenidos' => (float)$sample['puntos_obtenidos'],
                'mes_num' => (int)substr($period, 5, 2),
                'material_principal' => $sample['material_principal'],
                'periodo_referencia' => $period
            ];
            $classification = [
                'entregas' => (int)$sample['entregas'],
                'diversidad_materiales' => (int)$sample['diversidad_materiales'],
                'beneficios_redimidos' => (int)$sample['beneficios_redimidos'],
                'puntos_obtenidos' => (float)$sample['puntos_obtenidos'],
                'material_principal' => $sample['material_principal']
            ];
            $segmentation = [
                'promedio_kg' => (float)$sample['promedio_kg'],
                'promedio_entregas' => (float)$sample['promedio_entregas'],
                'promedio_puntos' => (float)$sample['promedio_puntos'],
                'promedio_diversidad' => (float)$sample['promedio_diversidad'],
                'promedio_beneficios' => (float)$sample['promedio_beneficios']
            ];
            $anomaly = [
                'kg_recolectados' => (float)$sample['kg_recolectados'],
                'entregas' => (int)$sample['entregas'],
                'diversidad_materiales' => (int)$sample['diversidad_materiales'],
                'beneficios_redimidos' => (int)$sample['beneficios_redimidos'],
                'puntos_obtenidos' => (float)$sample['puntos_obtenidos']
            ];

            $jsonPayloads = [];
            foreach ([$regression, $classification, $segmentation, $anomaly] as $payload) {
                $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
                if ($encodedPayload === false) {
                    throw new AnaliticaEcopointsException(
                        'No fue posible preparar la muestra para los modelos.',
                        500
                    );
                }
                $jsonPayloads[] = $encodedPayload;
            }

            $regressionResult = $this->service->request(
                'predecir-puntos',
                $jsonPayloads[0]
            );
            $classificationResult = $this->service->request(
                'clasificar-nivel',
                $jsonPayloads[1]
            );
            $segmentationResult = $this->service->request(
                'segmentar-reciclador',
                $jsonPayloads[2]
            );
            $anomalyResult = $this->service->request(
                'evaluar-anomalia',
                $jsonPayloads[3]
            );

            $observation['puntos_estimados'] = $regressionResult['ecopuntos_estimados'];
            $observation['nivel_actividad'] = $classificationResult['nivel_actividad'];
            $observation['segmento'] = $segmentationResult['segmento'];
            $observation['es_atipico'] = $anomalyResult['es_atipico'];
            $observations[] = $observation;
        }

        return [
            'muestra' => $observations,
            'reglas' => $this->service->request('reglas-asociacion', '', ['limite' => '100'])
        ];
    }

    private function buildRecommendations(
        array $latest,
        array $averages,
        array $segment,
        array $anomaly
    ): array {
        $recommendations = [];
        $latestDeliveries = (float)$latest['entregas'];
        $averageDeliveries = (float)$averages['promedio_entregas'];
        if ($latestDeliveries < $averageDeliveries) {
            $recommendations[] = 'En ' . $latest['periodo_referencia'] . ' registraste ' .
                number_format($latestDeliveries, 1, ',', '.') . ' entregas frente a tu promedio histórico de ' .
                number_format($averageDeliveries, 1, ',', '.') .
                '. Si hubo barreras de ruta o disponibilidad, revisarlas puede ayudar a planificar el siguiente periodo.';
        }

        $latestKg = (float)$latest['kg_recolectados'];
        $averageKg = (float)$averages['promedio_kg'];
        if ($latestKg < $averageKg) {
            $recommendations[] = 'El peso validado del último periodo (' .
                number_format($latestKg, 1, ',', '.') . ' kg) estuvo por debajo de tu promedio (' .
                number_format($averageKg, 1, ',', '.') .
                ' kg). Considera si los horarios, rutas o disponibilidad de solicitudes influyeron; es una comparación descriptiva.';
        }

        $latestDiversity = (float)$latest['diversidad_materiales'];
        $averageDiversity = (float)$averages['promedio_diversidad'];
        if ($averageDiversity > 1 && $latestDiversity < $averageDiversity) {
            $recommendations[] = 'En el último periodo registraste ' .
                number_format($latestDiversity, 1, ',', '.') . ' tipos de material, frente a ' .
                number_format($averageDiversity, 1, ',', '.') .
                ' en promedio. Si está disponible en tu zona, explorar solicitudes de otros materiales podría ampliar la diversidad.';
        }

        if (!empty($anomaly['es_atipico'])) {
            $recommendations[] = 'El detector marcó el registro para revisión contextual. Comprueba que el pesaje y la captura estén completos; la marca no implica fraude ni modifica puntos o beneficios.';
        }

        if ($recommendations === []) {
            $recommendations[] = 'Los indicadores comparados no quedaron por debajo de tus promedios históricos en este periodo. Mantén el registro de entregas validado y revisa los cambios periodo a periodo.';
        }
        $recommendations[] = 'El perfil histórico «' . $segment['segmento'] .
            '» resume promedios y sirve como referencia descriptiva, no como meta ni criterio de acceso a beneficios.';

        return $recommendations;
    }
}
