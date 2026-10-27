"""
=============================================================================
MOTOR DE MODELADO Y SIMULACIÓN OPERATIVA ECOPOINTS
=============================================================================
Combina Simulación de Eventos Discretos (SimPy) y Simulación Monte Carlo
para modelar el flujo logístico de recolección, asignación de recicladores,
traslado a centros de acopio, validación de pesaje y emisión de EcoPoints.

Reutiliza el CSV BASE_PREPARADA_Ecopoints_Mineria_Datos.csv para calibración
empírica y el modelo RandomForestRegressor entrenado para contraste predictivo.

Uso:
  python simulacion_engine.py --file "ruta/a/input.json"
  python simulacion_engine.py --json "{\"escenario\":\"A\", ...}"
=============================================================================
"""

import os
import sys
import json
import math
import random
import warnings
from typing import Dict, Any, List

warnings.filterwarnings('ignore')

# Configurar salida UTF-8 estricta para evitar errores de consola en Windows
if sys.stdout.encoding and sys.stdout.encoding.lower() != 'utf-8':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

# Importaciones científicas verificadas
import numpy as np
import pandas as pd
import simpy
import joblib

# Ubicaciones por defecto con soporte de variables de entorno
CSV_BASE_PATH = os.environ.get(
    'ECOPOINTS_CSV_PATH',
    r"C:\Users\rueda\Desktop\Proyecto_Integrador_X\Ecopoints_Estructura\01_datos\base_preparada\BASE_PREPARADA_Ecopoints_Mineria_Datos.csv"
)

MODELOS_DIR = os.environ.get(
    'ECOPOINTS_MODELOS_DIR',
    r"C:\Users\rueda\Desktop\Proyecto_Integrador_X\Ecopoints_Estructura\11_modelos_guardados"
)

# Factores oficiales de EcoPoints por kg (según tabla 'materiales' de la BD)
FACTORES_ECOPOINTS = {
    'Cartón': 10.0,
    'Papel': 8.0,
    'Plástico': 15.0,
    'Vidrio': 5.0,
    'Metales': 20.0,
    'Otros': 5.0
}

def cargar_parametros_csv(ruta_csv: str) -> Dict[str, Any]:
    """Carga y valida el CSV de minería de datos para extraer distribuciones empíricas."""
    if not os.path.exists(ruta_csv):
        # Fallback a parámetros por defecto si no se encuentra el archivo físico
        return {
            'valido': False,
            'distribucion_materiales': {'Metales': 0.22, 'Papel': 0.21, 'Cartón': 0.20, 'Plástico': 0.19, 'Vidrio': 0.18},
            'kg_media_entrega': 20.8,
            'kg_std_entrega': 11.0,
            'total_registros_csv': 0
        }

    try:
        df = pd.read_csv(ruta_csv)
        cols_requeridas = ['kg_recolectados', 'entregas', 'material_principal']
        for c in cols_requeridas:
            if c not in df.columns:
                raise ValueError(f"Falta columna requerida en CSV: {c}")

        # Proporción real de materiales
        mat_counts = df['material_principal'].value_counts(normalize=True).to_dict()
        
        # Estimación de kg por entrega individual derivada de la base mensual
        df_valid = df[(df['entregas'] > 0) & (df['kg_recolectados'] > 0)].copy()
        df_valid['kg_por_entrega'] = df_valid['kg_recolectados'] / df_valid['entregas']
        
        kg_media = float(df_valid['kg_por_entrega'].mean())
        kg_std = float(df_valid['kg_por_entrega'].std())

        return {
            'valido': True,
            'distribucion_materiales': mat_counts,
            'kg_media_entrega': round(kg_media, 2),
            'kg_std_entrega': round(kg_std, 2),
            'total_registros_csv': int(len(df))
        }
    except Exception as ex:
        return {
            'valido': False,
            'error': str(ex),
            'distribucion_materiales': {'Metales': 0.22, 'Papel': 0.21, 'Cartón': 0.20, 'Plástico': 0.19, 'Vidrio': 0.18},
            'kg_media_entrega': 20.8,
            'kg_std_entrega': 11.0,
            'total_registros_csv': 0
        }

def cargar_modelo_ml(dir_modelos: str):
    """Carga el modelo de regresión para contrastar proyecciones de EcoPoints."""
    ruta_reg = os.path.join(dir_modelos, 'modelo_regresion_ecopoints.joblib')
    if os.path.exists(ruta_reg):
        try:
            return joblib.load(ruta_reg)
        except Exception:
            return None
    return None

class SimulacionEcoPoints:
    """Modelo de Eventos Discretos en SimPy para la logística de EcoPoints."""

    def __init__(self, env: simpy.Environment, config: Dict[str, Any], params_csv: Dict[str, Any]):
        self.env = env
        self.config = config
        self.params_csv = params_csv

        # Recursos con capacidad finita
        self.recicladores = simpy.Resource(env, capacity=int(config.get('num_recicladores', 30)))
        self.puestos_pesaje = simpy.Resource(env, capacity=int(config.get('puestos_pesaje', 2)))

        # Tasa de llegada de solicitudes (por hora de operación)
        # Por defecto: 8 horas al día de operación activa
        solicitudes_dia = float(config.get('solicitudes_dia', 45.0))
        self.tasa_llegada_hora = max(0.1, solicitudes_dia / 8.0)

        # Registro de métricas individuales
        self.solicitudes_generadas = 0
        self.solicitudes_completadas = 0
        self.solicitudes_pendientes = 0
        
        self.tiempos_espera_reciclador = []
        self.tiempos_espera_pesaje = []
        self.tiempos_ciclo_total = []
        self.kg_recolectados_total = 0.0
        self.ecopuntos_generados_total = 0.0
        self.kg_por_material = {m: 0.0 for m in FACTORES_ECOPOINTS.keys()}

        # Muestreo temporal de longitud de colas
        self.registro_colas = []
        self.tiempo_ocupacion_recicladores = 0.0
        self.tiempo_ocupacion_pesaje = 0.0

        # Iniciar proceso generador y monitor de colas
        self.env.process(self.generador_solicitudes())
        self.env.process(self.monitor_colas())

    def monitor_colas(self):
        """Muestrea el estado de las colas cada hora simulada."""
        while True:
            self.registro_colas.append({
                'tiempo_h': round(float(self.env.now), 1),
                'cola_recicladores': len(self.recicladores.queue),
                'ocupacion_recicladores': self.recicladores.count,
                'cola_pesaje': len(self.puestos_pesaje.queue),
                'ocupacion_pesaje': self.puestos_pesaje.count
            })
            yield self.env.timeout(1.0) # cada 1 hora de simulación

    def generador_solicitudes(self):
        """Genera solicitudes de recolección según proceso de Poisson."""
        while True:
            # Tiempo exponencial entre llegadas
            intervalo = random.expovariate(self.tasa_llegada_hora)
            yield self.env.timeout(intervalo)
            
            self.solicitudes_generadas += 1
            sol_id = self.solicitudes_generadas
            self.env.process(self.proceso_solicitud(sol_id))

    def proceso_solicitud(self, sol_id: int):
        """Etapas del ciclo de vida de una solicitud de reciclaje."""
        tiempo_llegada = self.env.now
        
        # Muestreo de material y kilogramos basado en el CSV
        materiales_dict = self.params_csv.get('distribucion_materiales', {})
        materiales = list(materiales_dict.keys())
        pesos = list(materiales_dict.values())
        if not materiales:
            materiales = ['Cartón', 'Papel', 'Plástico', 'Vidrio', 'Metales']
            pesos = [0.2, 0.2, 0.2, 0.2, 0.2]

        material = random.choices(materiales, weights=pesos, k=1)[0]
        
        # Kilogramos de la entrega (normal truncada en min 3 kg, max 80 kg)
        media_kg = self.params_csv.get('kg_media_entrega', 20.8)
        std_kg = self.params_csv.get('kg_std_entrega', 11.0)
        kg_entrega = max(3.0, min(80.0, random.gauss(media_kg, std_kg)))

        # -------------------------------------------------------------------
        # ETAPA 1: Asignación y espera de reciclador disponible
        # -------------------------------------------------------------------
        req_reciclador = self.recicladores.request()
        yield req_reciclador

        tiempo_asignado = self.env.now
        espera_rec = (tiempo_asignado - tiempo_llegada) * 60.0 # minutos
        self.tiempos_espera_reciclador.append(espera_rec)

        # -------------------------------------------------------------------
        # ETAPA 2: Desplazamiento y Recolección en el domicilio del ciudadano
        # (Supuesto operativo: triangular 20 a 50 min)
        # -------------------------------------------------------------------
        duracion_recoleccion = random.triangular(20.0, 35.0, 50.0) / 60.0 # en horas
        yield self.env.timeout(duracion_recoleccion)

        # -------------------------------------------------------------------
        # ETAPA 3: Traslado hacia el Centro de Acopio aliado
        # (Supuesto operativo: triangular 15 a 40 min)
        # -------------------------------------------------------------------
        duracion_traslado = random.triangular(15.0, 25.0, 40.0) / 60.0
        yield self.env.timeout(duracion_traslado)

        tiempo_llegada_centro = self.env.now

        # -------------------------------------------------------------------
        # ETAPA 4: Cola y Atención en Báscula del Centro de Acopio
        # -------------------------------------------------------------------
        req_pesaje = self.puestos_pesaje.request()
        yield req_pesaje

        tiempo_inicio_pesaje = self.env.now
        espera_pesaje = (tiempo_inicio_pesaje - tiempo_llegada_centro) * 60.0
        self.tiempos_espera_pesaje.append(espera_pesaje)

        # Tiempo de inspección, pesaje y validación en báscula (8 a 18 min)
        duracion_pesaje = random.triangular(8.0, 12.0, 18.0) / 60.0
        yield self.env.timeout(duracion_pesaje)

        # Liberar báscula y reciclador
        self.puestos_pesaje.release(req_pesaje)
        self.recicladores.release(req_reciclador)

        # -------------------------------------------------------------------
        # ETAPA 5: Validación y Asignación Oficial de EcoPoints
        # -------------------------------------------------------------------
        factor = FACTORES_ECOPOINTS.get(material, 5.0)
        puntos_otorgados = round(kg_entrega * factor, 1)

        tiempo_fin = self.env.now
        ciclo_total = (tiempo_fin - tiempo_llegada) * 60.0 # minutos

        self.tiempos_ciclo_total.append(ciclo_total)
        self.kg_recolectados_total += kg_entrega
        self.ecopuntos_generados_total += puntos_otorgados
        self.kg_por_material[material] = self.kg_por_material.get(material, 0.0) + kg_entrega
        self.solicitudes_completadas += 1


def ejecutar_simulacion_monte_carlo(config: Dict[str, Any]) -> Dict[str, Any]:
    """Ejecuta experimentos Monte Carlo sobre el modelo de SimPy."""
    params_csv = cargar_parametros_csv(CSV_BASE_PATH)
    modelo_ml = cargar_modelo_ml(MODELOS_DIR)

    num_replicas = int(config.get('num_replicas', 10))
    num_replicas = max(1, min(50, num_replicas)) # Límite de seguridad para respuesta ágil
    
    semilla_base = int(config.get('semilla', 42))
    duracion_dias = float(config.get('duracion_dias', 7.0))
    horas_simulacion = duracion_dias * 8.0 # Horario operativo diurno de 8h/día

    replicas_resultados = []

    for i in range(num_replicas):
        seed_iteracion = semilla_base + (i * 101)
        random.seed(seed_iteracion)
        np.random.seed(seed_iteracion)

        env = simpy.Environment()
        sim = SimulacionEcoPoints(env, config, params_csv)
        env.run(until=horas_simulacion)

        # Cálculo de utilización teórica
        tiempo_total_disponible_rec = sim.recicladores.capacity * horas_simulacion
        tiempo_total_disponible_pes = sim.puestos_pesaje.capacity * horas_simulacion

        t_espera_rec_prom = float(np.mean(sim.tiempos_espera_reciclador)) if sim.tiempos_espera_reciclador else 0.0
        t_espera_pes_prom = float(np.mean(sim.tiempos_espera_pesaje)) if sim.tiempos_espera_pesaje else 0.0
        t_ciclo_prom = float(np.mean(sim.tiempos_ciclo_total)) if sim.tiempos_ciclo_total else 0.0

        pct_atendidas = round((sim.solicitudes_completadas / max(1, sim.solicitudes_generadas)) * 100.0, 1)

        # Estimación de EcoPoints con el modelo de Regresión de Machine Learning
        # Mapeamos la producción simulada agregada al vector de entrada del modelo
        puntos_ml_estimados = None
        if modelo_ml is not None and sim.solicitudes_completadas > 0:
            try:
                # Determinar material principal de la réplica
                mat_ppal = max(sim.kg_por_material.items(), key=lambda x: x[1])[0]
                df_entrada_ml = pd.DataFrame([{
                    'kg_recolectados': float(sim.kg_recolectados_total),
                    'entregas': int(sim.solicitudes_completadas),
                    'diversidad_materiales': float(len([k for k, v in sim.kg_por_material.items() if v > 0])),
                    'beneficios_redimidos': int(max(1, round(sim.solicitudes_completadas * 0.15))),
                    'puntos_obtenidos': float(sim.ecopuntos_generados_total),
                    'mes_num': 11,
                    'material_principal': str(mat_ppal)
                }])
                cols_reg = [
                    "kg_recolectados", "entregas", "diversidad_materiales",
                    "beneficios_redimidos", "puntos_obtenidos", "mes_num", "material_principal"
                ]
                pred = modelo_ml.predict(df_entrada_ml[cols_reg])[0]
                puntos_ml_estimados = round(float(pred), 1)
            except Exception:
                puntos_ml_estimados = None

        rep_data = {
            'replica_num': i + 1,
            'semilla': seed_iteracion,
            'solicitudes_generadas': sim.solicitudes_generadas,
            'solicitudes_completadas': sim.solicitudes_completadas,
            'solicitudes_pendientes': max(0, sim.solicitudes_generadas - sim.solicitudes_completadas),
            'pct_atendidas': pct_atendidas,
            'kg_totales': round(sim.kg_recolectados_total, 1),
            'ecopuntos_regla_directa': round(sim.ecopuntos_generados_total, 1),
            'ecopuntos_ml_proyectados': puntos_ml_estimados,
            'espera_reciclador_min': round(t_espera_rec_prom, 1),
            'espera_pesaje_min': round(t_espera_pes_prom, 1),
            'tiempo_ciclo_total_min': round(t_ciclo_prom, 1),
            'kg_por_material': {k: round(v, 1) for k, v in sim.kg_por_material.items()}
        }
        replicas_resultados.append(rep_data)

    # -------------------------------------------------------------------------
    # ANÁLISIS ESTADÍSTICO MONTE CARLO (Media, Desv Estándar, Percentiles)
    # -------------------------------------------------------------------------
    df_reps = pd.DataFrame(replicas_resultados)

    def stats_var(col: str) -> Dict[str, float]:
        vals = df_reps[col].dropna().values
        if len(vals) == 0:
            return {'media': 0.0, 'std': 0.0, 'p5': 0.0, 'mediana': 0.0, 'p95': 0.0}
        return {
            'media': round(float(np.mean(vals)), 2),
            'std': round(float(np.std(vals)), 2),
            'p5': round(float(np.percentile(vals, 5)), 2),
            'mediana': round(float(np.median(vals)), 2),
            'p95': round(float(np.percentile(vals, 95)), 2)
        }

    resumen_estadistico = {
        'solicitudes_generadas': stats_var('solicitudes_generadas'),
        'solicitudes_completadas': stats_var('solicitudes_completadas'),
        'solicitudes_pendientes': stats_var('solicitudes_pendientes'),
        'pct_atendidas': stats_var('pct_atendidas'),
        'kg_totales': stats_var('kg_totales'),
        'ecopuntos_regla_directa': stats_var('ecopuntos_regla_directa'),
        'espera_reciclador_min': stats_var('espera_reciclador_min'),
        'espera_pesaje_min': stats_var('espera_pesaje_min'),
        'tiempo_ciclo_total_min': stats_var('tiempo_ciclo_total_min')
    }

    # Diagnóstico de Cuellos de Botella Operativos
    media_esp_rec = resumen_estadistico['espera_reciclador_min']['media']
    media_esp_pes = resumen_estadistico['espera_pesaje_min']['media']
    pct_cumplimiento = resumen_estadistico['pct_atendidas']['media']

    if pct_cumplimiento < 75.0:
        if media_esp_rec > media_esp_pes * 1.5:
            cuello_botella = "Déficit de Recicladores en Ruta (Tiempo de espera en asignación crítico)"
            recomendacion = "Incrementar la flota de recicladores activos o sectorizar zonas de atención para reducir tiempos de desplazamiento."
        else:
            cuello_botella = "Congestión en Báscula de Centros de Acopio (Saturación de puestos de pesaje)"
            recomendacion = "Aumentar puestos de pesaje en centros aliados o habilitar ventanas horarias programadas para entrega."
    elif pct_cumplimiento < 90.0:
        cuello_botella = "Operación en Nivel de Advertencia (Capacidad ajustada ante picos de demanda)"
        recomendacion = "La capacidad es viable pero vulnerable a fluctuaciones de demanda. Se recomienda reserva operativa del 15%."
    else:
        cuello_botella = "Flujo Operativo Fluido y Equilibrado (Sin congestión crítica)"
        recomendacion = "El balance entre recicladores y capacidad de acopio es óptimo para la demanda evaluada."

    return {
        'success': True,
        'escenario': config.get('escenario', 'A'),
        'nombre_escenario': config.get('nombre_escenario', 'Escenario de Simulación'),
        'configuracion': {
            'duracion_dias': duracion_dias,
            'horas_operativas_totales': horas_simulacion,
            'solicitudes_dia': float(config.get('solicitudes_dia', 45)),
            'num_recicladores': int(config.get('num_recicladores', 30)),
            'puestos_pesaje': int(config.get('puestos_pesaje', 2)),
            'num_replicas': num_replicas,
            'semilla_base': semilla_base
        },
        'calibracion_csv': params_csv,
        'resumen_monte_carlo': resumen_estadistico,
        'diagnostico_cuello_botella': {
            'identificado': cuello_botella,
            'recomendacion': recomendacion,
            'cumplimiento_pct': pct_cumplimiento
        },
        'modelo_ml_integrado': {
            'disponible': modelo_ml is not None,
            'algoritmo': "RandomForestRegressor (n_estimators=100, max_depth=6)",
            'ecopuntos_proyectados_media': round(float(df_reps['ecopuntos_ml_proyectados'].dropna().mean()), 1) if not df_reps['ecopuntos_ml_proyectados'].dropna().empty else None,
            'nota_explicativa': "Contraste predictivo del modelo de minería sobre el consolidado simulado. No reemplaza el cálculo reglamentario por pesaje."
        },
        'replicas_detalle': replicas_resultados,
        # Serie temporal de colas de la última réplica para graficación
        'serie_colas_ejemplo': sim.registro_colas[:30] if 'sim' in locals() else []
    }

def generar_comparativa_escenarios(config_base: Dict[str, Any]) -> Dict[str, Any]:
    """Genera automáticamente la comparativa entre los Escenarios A, B y C."""
    # 1. Escenario A: Referencia Histórica
    cfg_a = dict(config_base)
    cfg_a.update({
        'escenario': 'A',
        'nombre_escenario': 'Escenario A: Referencia Histórica',
        'solicitudes_dia': 40.0,
        'num_recicladores': 30,
        'puestos_pesaje': 2,
        'num_replicas': 5
    })
    res_a = ejecutar_simulacion_monte_carlo(cfg_a)

    # 2. Escenario B: Incremento de la Demanda (+75%)
    cfg_b = dict(config_base)
    cfg_b.update({
        'escenario': 'B',
        'nombre_escenario': 'Escenario B: Alta Demanda (+75%)',
        'solicitudes_dia': 70.0,
        'num_recicladores': 30,
        'puestos_pesaje': 2,
        'num_replicas': 5
    })
    res_b = ejecutar_simulacion_monte_carlo(cfg_b)

    # 3. Escenario C: Aumento de Capacidad Operativa (+50% recic, +100% básculas)
    cfg_c = dict(config_base)
    cfg_c.update({
        'escenario': 'C',
        'nombre_escenario': 'Escenario C: Expansión de Capacidad',
        'solicitudes_dia': 70.0,
        'num_recicladores': 45,
        'puestos_pesaje': 4,
        'num_replicas': 5
    })
    res_c = ejecutar_simulacion_monte_carlo(cfg_c)

    return {
        'success': True,
        'comparativa': {
            'escenario_a': {
                'nombre': res_a['nombre_escenario'],
                'solicitudes_completadas': res_a['resumen_monte_carlo']['solicitudes_completadas']['media'],
                'pct_atendidas': res_a['resumen_monte_carlo']['pct_atendidas']['media'],
                'kg_totales': res_a['resumen_monte_carlo']['kg_totales']['media'],
                'espera_rec_min': res_a['resumen_monte_carlo']['espera_reciclador_min']['media'],
                'espera_pesaje_min': res_a['resumen_monte_carlo']['espera_pesaje_min']['media']
            },
            'escenario_b': {
                'nombre': res_b['nombre_escenario'],
                'solicitudes_completadas': res_b['resumen_monte_carlo']['solicitudes_completadas']['media'],
                'pct_atendidas': res_b['resumen_monte_carlo']['pct_atendidas']['media'],
                'kg_totales': res_b['resumen_monte_carlo']['kg_totales']['media'],
                'espera_rec_min': res_b['resumen_monte_carlo']['espera_reciclador_min']['media'],
                'espera_pesaje_min': res_b['resumen_monte_carlo']['espera_pesaje_min']['media']
            },
            'escenario_c': {
                'nombre': res_c['nombre_escenario'],
                'solicitudes_completadas': res_c['resumen_monte_carlo']['solicitudes_completadas']['media'],
                'pct_atendidas': res_c['resumen_monte_carlo']['pct_atendidas']['media'],
                'kg_totales': res_c['resumen_monte_carlo']['kg_totales']['media'],
                'espera_rec_min': res_c['resumen_monte_carlo']['espera_reciclador_min']['media'],
                'espera_pesaje_min': res_c['resumen_monte_carlo']['espera_pesaje_min']['media']
            }
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
            raw_input = sys.stdin.read()

        config = json.loads(raw_input) if raw_input.strip() else {}
        
        # Verificar modo de ejecución
        modo = config.get('modo', 'simulacion')
        if modo == 'comparativa_global':
            resultado = generar_comparativa_escenarios(config)
        else:
            resultado = ejecutar_simulacion_monte_carlo(config)
            # Agregar comparativa ligera si se solicita
            if config.get('incluir_comparativa', True):
                try:
                    comp = generar_comparativa_escenarios(config)
                    resultado['comparativa_escenarios'] = comp['comparativa']
                except Exception:
                    resultado['comparativa_escenarios'] = {}

        print(json.dumps(resultado, ensure_ascii=False))
        sys.exit(0)

    except Exception as e:
        import traceback
        print(json.dumps({
            "success": False,
            "error": str(e),
            "traceback": traceback.format_exc()
        }, ensure_ascii=False))
        sys.exit(1)

if __name__ == '__main__':
    main()
