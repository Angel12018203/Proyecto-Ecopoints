<?php

class AnaliticaEcopointsService {

    private const API_BASE_URL = 'http://127.0.0.1:8001';

    private const OPERATIONS = [
        'predecir-puntos' => ['path' => '/predecir_puntos', 'method' => 'POST'],
        'clasificar-nivel' => ['path' => '/clasificar_nivel', 'method' => 'POST'],
        'segmentar-reciclador' => ['path' => '/segmentar_reciclador', 'method' => 'POST'],
        'evaluar-anomalia' => ['path' => '/evaluar_anomalia', 'method' => 'POST'],
        'reglas-asociacion' => ['path' => '/reglas_asociacion', 'method' => 'GET']
    ];

    public function request(string $operation, string $body = '', array $query = []): array {
        if (!isset(self::OPERATIONS[$operation])) {
            throw new InvalidArgumentException('Operación de analítica no permitida.');
        }

        $token = getenv('ECOPOINTS_API_TOKEN');
        if ($token === false || trim($token) === '') {
            error_log('EcoPoints analytics proxy: ECOPOINTS_API_TOKEN is not configured.');
            throw new AnaliticaEcopointsException(
                'El servicio de analítica no está configurado en el servidor.',
                503
            );
        }

        if (!function_exists('curl_init')) {
            error_log('EcoPoints analytics proxy: PHP cURL extension is unavailable.');
            throw new AnaliticaEcopointsException(
                'El servicio de analítica no está disponible.',
                503
            );
        }

        $configuration = self::OPERATIONS[$operation];
        $url = self::API_BASE_URL . $configuration['path'];
        if ($configuration['method'] === 'GET' && $query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $handle = curl_init($url);
        if ($handle === false) {
            error_log('EcoPoints analytics proxy: cURL could not initialize.');
            throw new AnaliticaEcopointsException(
                'El servicio de analítica no está disponible.',
                503
            );
        }

        $headers = [
            'Accept: application/json',
            'X-Ecopoints-Token: ' . $token
        ];
        if ($configuration['method'] === 'POST') {
            $headers[] = 'Content-Type: application/json; charset=utf-8';
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROXY => '',
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($configuration['method'] === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        if (!curl_setopt_array($handle, $options)) {
            curl_close($handle);
            error_log('EcoPoints analytics proxy: cURL options could not be applied.');
            throw new AnaliticaEcopointsException(
                'El servicio de analítica no está disponible.',
                503
            );
        }

        $responseBody = curl_exec($handle);
        $curlErrorNumber = curl_errno($handle);
        $curlError = curl_error($handle);
        $statusCode = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if ($responseBody === false) {
            error_log(
                'EcoPoints analytics proxy: cURL request failed (' .
                $curlErrorNumber . '): ' . $curlError
            );
            throw new AnaliticaEcopointsException(
                'No fue posible conectar con el servicio de analítica.',
                503
            );
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            error_log('EcoPoints analytics proxy: upstream returned HTTP ' . $statusCode . '.');
            if ($statusCode === 400 || $statusCode === 422) {
                throw new AnaliticaEcopointsException(
                    'Los datos enviados no cumplen los requisitos de analítica.',
                    $statusCode
                );
            }
            throw new AnaliticaEcopointsException(
                'El servicio de analítica no pudo completar la solicitud.',
                502
            );
        }

        $decodedResponse = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedResponse)) {
            error_log('EcoPoints analytics proxy: upstream returned invalid JSON.');
            throw new AnaliticaEcopointsException(
                'El servicio de analítica devolvió una respuesta inválida.',
                502
            );
        }

        return $decodedResponse;
    }
}

class AnaliticaEcopointsException extends RuntimeException {

    private $responseStatus;

    public function __construct(string $message, int $responseStatus) {
        parent::__construct($message);
        $this->responseStatus = $responseStatus;
    }

    public function getResponseStatus(): int {
        return $this->responseStatus;
    }
}
