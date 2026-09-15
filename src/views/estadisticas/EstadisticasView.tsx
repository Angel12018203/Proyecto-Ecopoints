import React from 'react';
import { Reciclador, EstadisticasZona } from '@/models/types';
import { ChartIcon, StarCoinIcon, RecycleBagIcon, TruckIcon } from '../components/Icons';

interface EstadisticasViewProps {
  reciclador: Reciclador;
  estadisticasZona: EstadisticasZona;
}

export const EstadisticasView: React.FC<EstadisticasViewProps> = ({
  reciclador,
  estadisticasZona,
}) => {
  // Datos del prompt sección 20: Cartón 320, Plástico 450, Vidrio 280, Metales 200 -> 1.250 kg
  const materiales = [
    { nombre: 'Plástico', kg: 450, color: '#0284C7', bg: 'bg-[#0284C7]', porcentaje: 36, icono: '🧴' },
    { nombre: 'Cartón', kg: 320, color: '#D97706', bg: 'bg-[#D97706]', porcentaje: 26, icono: '📦' },
    { nombre: 'Vidrio', kg: 280, color: '#059669', bg: 'bg-[#059669]', porcentaje: 22, icono: '🍾' },
    { nombre: 'Metales', kg: 200, color: '#DC2626', bg: 'bg-[#DC2626]', porcentaje: 16, icono: '🥫' },
  ];

  const totalKg = 1250;

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Title */}
      <div>
        <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
          <ChartIcon size={24} className="text-[#087A3D]" />
          <span>Rendimiento y Estadísticas de Impacto</span>
        </h1>
        <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
          Resumen cuantitativo del material recuperado por Carlos Rodríguez y datos consolidados de la zona Suba.
        </p>
      </div>

      {/* 4 KPIs Clave */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500 block mb-1">Total recuperado</span>
          <div className="text-2xl sm:text-3xl font-black text-slate-900">
            {reciclador.kgRecuperados.toLocaleString()} <span className="text-sm font-semibold text-slate-500">kg</span>
          </div>
          <span className="text-[11px] text-emerald-600 font-bold mt-1 block">✓ 100% validado en báscula</span>
        </div>

        <div className="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500 block mb-1">Recolecciones en ruta</span>
          <div className="text-2xl sm:text-3xl font-black text-slate-900">
            {reciclador.recoleccionesCompletadas}
          </div>
          <span className="text-[11px] text-slate-400 mt-1 block">Solicitudes atendidas</span>
        </div>

        <div className="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500 block mb-1">Ecopuntos acumulados</span>
          <div className="text-2xl sm:text-3xl font-black text-[#F5B82E]">
            {reciclador.ecopuntos.toLocaleString()}
          </div>
          <span className="text-[11px] text-amber-800 font-semibold mt-1 block">Nivel {reciclador.nivel}</span>
        </div>

        <div className="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500 block mb-1">Material predominante</span>
          <div className="text-2xl sm:text-3xl font-black text-sky-700">Plástico</div>
          <span className="text-[11px] text-slate-400 mt-1 block">450 kg (36% del total)</span>
        </div>
      </div>

      {/* Gráfico y Desglose por Material (Sección 20) */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Barra de progreso y desglose */}
        <div className="lg:col-span-2 bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-xs space-y-6">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-extrabold text-base text-[#1E2922]">
                Distribución de Material Recuperado
              </h3>
              <p className="text-xs text-[#64748B]">
                Total acumulado en centros de acopio: <strong>{totalKg} kg</strong>
              </p>
            </div>
            <span className="px-3 py-1 rounded-full text-xs font-bold bg-[#EEF8EE] text-[#087A3D]">
              Año 2026
            </span>
          </div>

          {/* Combined Progress Bar */}
          <div className="space-y-2">
            <div className="h-6 w-full rounded-2xl overflow-hidden flex shadow-inner bg-slate-100 p-0.5">
              {materiales.map((m) => (
                <div
                  key={m.nombre}
                  style={{ width: `${m.porcentaje}%` }}
                  className={`${m.bg} h-full transition-all duration-500 hover:opacity-90`}
                  title={`${m.nombre}: ${m.kg} kg (${m.porcentaje}%)`}
                />
              ))}
            </div>

            <div className="flex items-center justify-between text-[11px] text-slate-400 px-1">
              <span>0 kg</span>
              <span>Total: {totalKg} kg</span>
            </div>
          </div>

          {/* Cards de cada material */}
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {materiales.map((m) => (
              <div
                key={m.nombre}
                className="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2"
              >
                <div className="flex items-center justify-between">
                  <span className="text-xl">{m.icono}</span>
                  <span
                    className="w-3 h-3 rounded-full"
                    style={{ backgroundColor: m.color }}
                  />
                </div>
                <div>
                  <h4 className="font-bold text-xs text-slate-600">{m.nombre}</h4>
                  <div className="text-lg font-black text-slate-900">{m.kg} kg</div>
                  <span className="text-[10px] font-bold text-slate-400">{m.porcentaje}% del total</span>
                </div>
              </div>
            ))}
          </div>

          {/* Mensual Evolution simulation */}
          <div className="pt-2">
            <h4 className="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
              Actividad Mensual (kg recuperados)
            </h4>
            <div className="flex items-end justify-between gap-2 h-32 pt-4 px-2 border-b border-slate-100">
              {[
                { mes: 'Mayo', kg: 180, pct: '45%' },
                { mes: 'Junio', kg: 240, pct: '60%' },
                { mes: 'Julio', kg: 290, pct: '72%' },
                { mes: 'Agosto', kg: 340, pct: '85%' },
                { mes: 'Septiembre', kg: 400, pct: '100%' },
              ].map((bar) => (
                <div key={bar.mes} className="flex-1 flex flex-col items-center gap-1">
                  <span className="text-[10px] font-bold text-slate-600">{bar.kg}kg</span>
                  <div className="w-full max-w-[36px] bg-slate-100 rounded-t-xl h-24 flex items-end overflow-hidden">
                    <div
                      style={{ height: bar.pct }}
                      className="w-full bg-[#087A3D] rounded-t-xl transition-all duration-700"
                    />
                  </div>
                  <span className="text-[11px] font-medium text-slate-400 mt-1">{bar.mes}</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Estadísticas de Zona Suba (Sección 26) */}
        <div className="space-y-6">
          <div className="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-6 rounded-3xl shadow-md space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold uppercase tracking-wider text-emerald-400">
                Localidad 11 • Suba
              </span>
              <span className="text-xl">📍</span>
            </div>

            <div>
              <h3 className="font-black text-xl text-white">Estadísticas de Zona</h3>
              <p className="text-xs text-slate-300 mt-0.5">
                Impacto colectivo de los recicladores en la localidad
              </p>
            </div>

            <div className="space-y-3 pt-2">
              <div className="p-3.5 bg-white/10 rounded-2xl backdrop-blur-xs border border-white/10">
                <span className="text-[11px] text-slate-300 block">Total recuperado en la zona:</span>
                <span className="text-2xl font-black text-emerald-300">
                  {estadisticasZona.kgRecuperados.toLocaleString()} kg
                </span>
              </div>

              <div className="p-3.5 bg-white/10 rounded-2xl backdrop-blur-xs border border-white/10">
                <span className="text-[11px] text-slate-300 block">Recicladores activos en Suba:</span>
                <span className="text-2xl font-black text-amber-300">
                  {estadisticasZona.recicladoresActivos} recicladores
                </span>
              </div>

              <div className="p-3.5 bg-white/10 rounded-2xl backdrop-blur-xs border border-white/10">
                <span className="text-[11px] text-slate-300 block">Solicitudes atendidas:</span>
                <span className="text-2xl font-black text-sky-300">
                  {estadisticasZona.solicitudesAtendidas.toLocaleString()}
                </span>
              </div>
            </div>
          </div>

          {/* Huella Ecológica acumulada */}
          <div className="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-xs space-y-3">
            <h4 className="font-extrabold text-sm text-slate-900">Ahorro Ambiental Total</h4>
            <div className="space-y-2 text-xs">
              <div className="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                <span className="text-slate-600">Árboles salvados (celulosa):</span>
                <strong className="text-[#087A3D] text-sm">~10 árboles</strong>
              </div>
              <div className="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                <span className="text-slate-600">Energía preservada:</span>
                <strong className="text-amber-700 text-sm">~4.800 kWh</strong>
              </div>
              <div className="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                <span className="text-slate-600">Espacio en relleno evitado:</span>
                <strong className="text-sky-700 text-sm">~3.2 m³</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
