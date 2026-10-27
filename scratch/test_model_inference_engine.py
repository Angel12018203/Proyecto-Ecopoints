import os
import sys
import json
import warnings

warnings.filterwarnings('ignore')

import joblib
import pandas as pd
import numpy as np

MODELS_DIR = r'C:\Users\rueda\Desktop\Proyecto_Integrador_X\Ecopoints_Estructura\11_modelos_guardados'

def run_inference(input_data: dict):
    # Load metadata
    meta_path = os.path.join(MODELS_DIR, 'metadata_modelos.json')
    with open(meta_path, 'r', encoding='utf-8') as f:
        meta = json.load(f)

    # Extract input variables with sensible defaults
    kg = float(input_data.get('kg_recolectados', 120.0))
    entregas = int(input_data.get('entregas', 6))
    diversidad = float(input_data.get('diversidad_materiales', 2.0))
    beneficios = int(input_data.get('beneficios_redimidos', 1))
    puntos = float(input_data.get('puntos_obtenidos', 200.0))
    mat_principal = str(input_data.get('material_principal', 'Plástico'))
    mes_num = int(input_data.get('mes_num', 10))

    prom_kg = float(input_data.get('promedio_kg', kg))
    prom_entregas = float(input_data.get('promedio_entregas', float(entregas)))
    prom_puntos = float(input_data.get('promedio_puntos', puntos))
    prom_diversidad = float(input_data.get('promedio_diversidad', diversidad))
    prom_beneficios = float(input_data.get('promedio_beneficios', float(beneficios)))

    # Load 4 models
    modelo_reg = joblib.load(os.path.join(MODELS_DIR, 'modelo_regresion_ecopoints.joblib'))
    modelo_clf = joblib.load(os.path.join(MODELS_DIR, 'modelo_clasificacion_ecopoints.joblib'))
    paquete_km = joblib.load(os.path.join(MODELS_DIR, 'modelo_segmentacion_kmeans.joblib'))
    paquete_iso = joblib.load(os.path.join(MODELS_DIR, 'modelo_anomalias_isolation_forest.joblib'))

    # 1. Regresión (RandomForestRegressor)
    df_reg = pd.DataFrame([{
        "kg_recolectados": kg,
        "entregas": entregas,
        "diversidad_materiales": diversidad,
        "beneficios_redimidos": beneficios,
        "puntos_obtenidos": puntos,
        "mes_num": mes_num,
        "material_principal": mat_principal
    }])
    pred_puntos = float(modelo_reg.predict(df_reg)[0])

    # 2. Clasificación (LogisticRegression Multinomial Anti-Leakage)
    df_clf = pd.DataFrame([{
        "entregas": entregas,
        "diversidad_materiales": diversidad,
        "beneficios_redimidos": beneficios,
        "puntos_obtenidos": puntos,
        "material_principal": mat_principal
    }])
    pred_nivel = str(modelo_clf.predict(df_clf)[0])
    probs = modelo_clf.predict_proba(df_clf)[0]
    classes = list(modelo_clf.classes_)
    prob_dict = {classes[i]: round(float(probs[i]) * 100, 1) for i in range(len(classes))}

    # 3. Segmentación K-Means
    scaler_km = paquete_km['scaler']
    km_core = paquete_km['kmeans']
    features_km = paquete_km['features']
    mapping_km = paquete_km['cluster_mapping']

    df_km = pd.DataFrame([{
        "promedio_kg": prom_kg,
        "promedio_entregas": prom_entregas,
        "promedio_puntos": prom_puntos,
        "promedio_diversidad": prom_diversidad,
        "promedio_beneficios": prom_beneficios
    }])
    X_km_scaled = scaler_km.transform(df_km[features_km])
    cluster_id = int(km_core.predict(X_km_scaled)[0])
    cluster_nombre = mapping_km.get(cluster_id, f"Cluster {cluster_id}")
    cluster_desc = meta['modelos']['segmentacion_recicladores']['perfiles'].get(str(cluster_id), cluster_nombre)

    # 4. Detección de Anomalías Isolation Forest
    iso_core = paquete_iso['modelo']
    features_iso = paquete_iso['features']
    df_iso = pd.DataFrame([{
        "kg_recolectados": kg,
        "entregas": entregas,
        "diversidad_materiales": diversidad,
        "beneficios_redimidos": beneficios,
        "puntos_obtenidos": puntos
    }])
    iso_score = float(iso_core.decision_function(df_iso[features_iso])[0])
    es_anomalo = bool(iso_core.predict(df_iso[features_iso])[0] == -1)

    return {
        "status": "success",
        "timestamp": meta.get('fecha_empaquetado', ''),
        "version": meta.get('version', '1.0.0'),
        "regresion": {
            "algoritmo": meta['modelos']['regresion_puntos']['algoritmo'],
            "puntos_predichos": round(pred_puntos, 1),
            "mae": meta['modelos']['regresion_puntos']['metricas_validacion']['MAE'],
            "rmse": meta['modelos']['regresion_puntos']['metricas_validacion']['RMSE'],
            "r2": meta['modelos']['regresion_puntos']['metricas_validacion']['R2'],
            "interpretacion": f"Se estiman {round(pred_puntos, 1)} Ecopoints para el próximo ciclo con un R² de {meta['modelos']['regresion_puntos']['metricas_validacion']['R2']}."
        },
        "clasificacion": {
            "algoritmo": meta['modelos']['clasificacion_actividad']['algoritmo'],
            "nivel_predicho": pred_nivel,
            "probabilidades": prob_dict,
            "accuracy": meta['modelos']['clasificacion_actividad']['metricas_validacion']['Accuracy'],
            "f1_score": meta['modelos']['clasificacion_actividad']['metricas_validacion']['F1_Weighted'],
            "interpretacion": f"Nivel de actividad clasificado como '{pred_nivel}' con {prob_dict.get(pred_nivel, 0)}% de probabilidad."
        },
        "segmentacion": {
            "algoritmo": meta['modelos']['segmentacion_recicladores']['algoritmo'],
            "cluster_id": cluster_id,
            "segmento_nombre": cluster_nombre,
            "descripcion_perfil": cluster_desc,
            "silhouette": meta['modelos']['segmentacion_recicladores']['metricas_validacion']['Silhouette_Score'],
            "interpretacion": f"Asignado al grupo '{cluster_nombre}' ({cluster_desc})."
        },
        "anomalias": {
            "algoritmo": meta['modelos']['deteccion_anomalias']['algoritmo'],
            "es_anomalo": es_anomalo,
            "score_aislamiento": round(iso_score, 4),
            "diagnostico": "Atípico para Revisión Operativa" if es_anomalo else "Operación Normal",
            "interpretacion": "Alerta preventiva de comportamiento inusual para auditoría de campo." if es_anomalo else "El patrón de pesajes y visitas se encuentra dentro de los parámetros esperados."
        },
        "reglas_asociacion": meta.get('reglas_asociacion_principales', [])
    }

if __name__ == '__main__':
    # Can receive JSON string from sys.argv or stdin
    raw_input = "{}"
    if len(sys.argv) > 1:
        raw_input = sys.argv[1]
    elif not sys.stdin.isatty():
        raw_input = sys.stdin.read()

    try:
        data = json.loads(raw_input)
    except Exception:
        data = {}

    res = run_inference(data)
    print(json.dumps(res, indent=2, ensure_ascii=False))
