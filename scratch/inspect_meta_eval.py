import os
import sys
import json
import pandas as pd

sys.stdout.reconfigure(encoding='utf-8')
base_dir = r"C:\Users\rueda\Desktop\Proyecto_Integrador_X\Ecopoints_Estructura"

print("--- METADATA MODELOS ---")
meta_path = os.path.join(base_dir, "11_modelos_guardados", "metadata_modelos.json")
if os.path.exists(meta_path):
    with open(meta_path, 'r', encoding='utf-8') as f:
        meta = json.load(f)
        print(json.dumps(meta, indent=2, ensure_ascii=False))

print("\n--- REGLAS DE ASOCIACIÓN ---")
reglas_path = os.path.join(base_dir, "09_reglas_asociacion", "reglas_asociacion_descubiertas.csv")
if os.path.exists(reglas_path):
    df_reglas = pd.read_csv(reglas_path)
    print(df_reglas.to_string())

print("\n--- TABLA COMPARATIVA REGRESIÓN ---")
comp_reg = os.path.join(base_dir, "10_evaluacion", "tabla_comparativa_modelos.csv")
if os.path.exists(comp_reg):
    df_cr = pd.read_csv(comp_reg)
    print(df_cr.to_string())

print("\n--- TABLA COMPARATIVA CLASIFICACIÓN ---")
comp_clf = os.path.join(base_dir, "10_evaluacion", "tabla_comparativa_clasificacion.csv")
if os.path.exists(comp_clf):
    df_cc = pd.read_csv(comp_clf)
    print(df_cc.to_string())
