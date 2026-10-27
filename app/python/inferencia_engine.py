"""
=============================================================================
MOTOR DE INFERENCIA ANALÍTICA ECOPOINTS
=============================================================================
Carga los 4 modelos de minería de datos (.joblib) entrenados en el proyecto
y ejecuta la inferencia predictiva, de clasificación, segmentación y
detección de anomalías.

Uso:
  python inferencia_engine.py --json "{\"kg_recolectados\": 240, ...}"
  python inferencia_engine.py --file "ruta/a/input.json"
=============================================================================
"""

import os
import sys
import json
import warnings

# Suprimir advertencias de versiones de scikit-learn / joblib
warnings.filterwarnings('ignore')

# Configurar salida UTF-8 estricta para evitar errores de consola en Windows
if sys.stdout.encoding and sys.stdout.encoding.lower() != 'utf-8':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

# Ubicación base de modelos
DIRECTORIO_MODELOS = os.environ.get(
    'ECOPOINTS_MODELOS_DIR',
    r"C:\Users\rueda\Desktop\Proyecto_Integrador_X\Ecopoints_Estructura\11_modelos_guardados"
)

def ejecutar_inferencia(datos_entrada):
    import joblib
    import pandas as pd
    import numpy as np

    ruta_reg = os.path.join(DIRECTORIO_MODELOS, 'modelo_regresion_ecopoints.joblib')
    ruta_clf = os.path.join(DIRECTORIO_MODELOS, 'modelo_clasificacion_ecopoints.joblib')
    ruta_km = os.path.join(DIRECTORIO_MODELOS, 'modelo_segmentacion_kmeans.joblib')
    ruta_iso = os.path.join(DIRECTORIO_MODELOS, 'modelo_anomalias_isolation_forest.joblib')
    ruta_meta = os.path.join(DIRECTORIO_MODELOS, 'metadata_modelos.json')

    # Validar existencia
    for r in [ruta_reg, ruta_clf, ruta_km, ruta_iso]:
        if not os.path.exists(r):
            raise FileNotFoundError(f"Artefacto de modelo no encontrado: {r}")

    # Cargar metadata
    meta = {}
    if os.path.exists(ruta_meta):
        with open(ruta_meta, 'r', encoding='utf-8') as f:
            meta = json.load(f)

    # Cargar modelos
    modelo_reg = joblib.load(ruta_reg)
    modelo_clf = joblib.load(ruta_clf)
    paquete_km = joblib.load(ruta_km)
    paquete_iso = joblib.load(ruta_iso)

    # Preparar DataFrame de entrada para modelos mensuales
    features_reg = [
        "kg_recolectados", "entregas", "diversidad_materiales",
        "beneficios_redimidos", "puntos_obtenidos", "mes_num", "material_principal"
    ]
    features_clf = [
        "entregas", "diversidad_materiales", "beneficios_redimidos",
        "puntos_obtenidos", "material_principal"
    ]

    row_mensual = {
        "kg_recolectados": float(datos_entrada.get("kg_recolectados", 0.0)),
        "entregas": int(datos_entrada.get("entregas", 1)),
        "diversidad_materiales": float(datos_entrada.get("diversidad_materiales", 1.0)),
        "beneficios_redimidos": int(datos_entrada.get("beneficios_redimidos", 0)),
        "puntos_obtenidos": float(datos_entrada.get("puntos_obtenidos", 0.0)),
        "mes_num": int(datos_entrada.get("mes_num", 11)),
        "material_principal": str(datos_entrada.get("material_principal", "Cartón"))
    }
    df_mensual = pd.DataFrame([row_mensual])

    # 1. Regresión (RandomForestRegressor)
    puntos_pred = float(modelo_reg.predict(df_mensual[features_reg])[0])

    # 2. Clasificación (LogisticRegression Multinomial)
    nivel_pred = str(modelo_clf.predict(df_mensual[features_clf])[0])
    probs = modelo_clf.predict_proba(df_mensual[features_clf])[0]
    clases = list(modelo_clf.classes_)
    dict_probs = {clase: round(float(prob) * 100, 2) for clase, prob in zip(clases, probs)}

    # 3. Segmentación (K-Means)
    scaler_km = paquete_km['scaler']
    km_core = paquete_km['kmeans']
    features_km = paquete_km['features']
    mapping_km = paquete_km.get('cluster_mapping', {0: "Ocasionales (Base)", 1: "Constantes (Medio)", 2: "Líderes (Alto)"})

    row_km = {
        "promedio_kg": float(datos_entrada.get("promedio_kg", row_mensual["kg_recolectados"])),
        "promedio_entregas": float(datos_entrada.get("promedio_entregas", row_mensual["entregas"])),
        "promedio_puntos": float(datos_entrada.get("promedio_puntos", row_mensual["puntos_obtenidos"])),
        "promedio_diversidad": float(datos_entrada.get("promedio_diversidad", row_mensual["diversidad_materiales"])),
        "promedio_beneficios": float(datos_entrada.get("promedio_beneficios", row_mensual["beneficios_redimidos"]))
    }
    df_km = pd.DataFrame([row_km])
    X_km_scaled = scaler_km.transform(df_km[features_km])
    cluster_id = int(km_core.predict(X_km_scaled)[0])
    segmento_nombre = mapping_km.get(cluster_id, f"Cluster {cluster_id}")

    # 4. Anomalías (Isolation Forest)
    iso_core = paquete_iso['modelo']
    features_iso = paquete_iso['features']
    df_iso = pd.DataFrame([{
        "kg_recolectados": row_mensual["kg_recolectados"],
        "entregas": row_mensual["entregas"],
        "diversidad_materiales": row_mensual["diversidad_materiales"],
        "beneficios_redimidos": row_mensual["beneficios_redimidos"],
        "puntos_obtenidos": row_mensual["puntos_obtenidos"]
    }])
    score_iso = float(iso_core.decision_function(df_iso[features_iso])[0])
    es_anomalo = bool(iso_core.predict(df_iso[features_iso])[0] == -1)
    estado_iso = "Atípico para Revisión Operativa" if es_anomalo else "Operación Normal"

    return {
        "success": True,
        "regresion": {
            "puntos_estimados": round(puntos_pred, 2),
            "algoritmo": "RandomForestRegressor (n_estimators=100, max_depth=6)",
            "horizonte": "siguiente_observacion",
            "metricas_entrenamiento": meta.get("modelos", {}).get("regresion_puntos", {}).get("metricas_validacion", {})
        },
        "clasificacion": {
            "nivel_actividad": nivel_pred,
            "probabilidades": dict_probs,
            "algoritmo": "LogisticRegression Multinomial (Anti-Leakage)",
            "metricas_entrenamiento": meta.get("modelos", {}).get("clasificacion_actividad", {}).get("metricas_validacion", {})
        },
        "segmentacion": {
            "cluster_id": cluster_id,
            "segmento": segmento_nombre,
            "algoritmo": "KMeans (k=3)",
            "metricas_entrenamiento": meta.get("modelos", {}).get("segmentacion_recicladores", {}).get("metricas_validacion", {})
        },
        "anomalias": {
            "es_anomalo": es_anomalo,
            "estado_operativo": estado_iso,
            "score_aislamiento": round(score_iso, 4),
            "algoritmo": "IsolationForest (contamination=0.03)"
        }
    }

def main():
    try:
        raw_input = ""
        if len(sys.argv) > 2 and sys.argv[1] == '--json':
            raw_input = sys.argv[2]
        elif len(sys.argv) > 2 and sys.argv[1] == '--file':
            with open(sys.argv[2], 'r', encoding='utf-8') as f:
                raw_input = f.read()
        else:
            # Leer desde stdin
            raw_input = sys.stdin.read()

        if not raw_input.strip():
            print(json.dumps({"success": False, "error": "No se recibieron datos de entrada"}))
            sys.exit(1)

        datos = json.loads(raw_input)
        resultado = ejecutar_inferencia(datos)
        print(json.dumps(resultado, ensure_ascii=False))
        sys.exit(0)

    except Exception as e:
        import traceback
        err_msg = str(e)
        print(json.dumps({
            "success": False,
            "error": err_msg,
            "traceback": traceback.format_exc()
        }, ensure_ascii=False))
        sys.exit(1)

if __name__ == '__main__':
    main()
