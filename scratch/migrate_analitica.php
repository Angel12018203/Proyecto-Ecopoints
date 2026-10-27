<?php
require_once __DIR__ . '/../app/config/database.php';

$db = Database::getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS analitica_predicciones (
    id_analitica INT AUTO_INCREMENT PRIMARY KEY,
    id_reciclador INT NOT NULL,
    fecha_analisis DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    periodo_analizado VARCHAR(20) NULL,
    prediccion_puntos DECIMAL(10,2) NULL,
    nivel_actividad VARCHAR(50) NULL,
    prob_baja DECIMAL(5,2) NULL,
    prob_media DECIMAL(5,2) NULL,
    prob_alta DECIMAL(5,2) NULL,
    segmento_cluster VARCHAR(100) NULL,
    cluster_id INT NULL,
    es_anomalo TINYINT(1) DEFAULT 0,
    score_aislamiento DECIMAL(8,4) NULL,
    estado_operativo VARCHAR(100) NULL,
    features_entrada JSON NULL,
    recomendaciones JSON NULL,
    KEY idx_reciclador (id_reciclador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$db->exec($sql);
echo "Table analitica_predicciones successfully ensured in database!\n";
