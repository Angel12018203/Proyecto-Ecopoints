import React from 'react';
import { Reciclador, Solicitud } from '@/models/types';
import { StatusBadge } from '../components/Badge';
import { StarCoinIcon, TruckIcon, RecycleBagIcon, MapIcon, ArrowRightIcon, ScaleIcon } from '../components/Icons';
import { NavSection } from '../components/Sidebar';

interface DashboardViewProps {
  reciclador: Reciclador;
  solicitudes: Solicitud[];
  onAceptarSolicitud: (id: string) => void;
  onVerDetalle: (solicitud: Solicitud) => void;
  onNavigate: (section: NavSection) => void;
  onAbrirSimuladorCentro: () => void;
}

export const DashboardView: React.FC<DashboardViewProps> = ({
  reciclador,
  solicitudes,
  onAceptarSolicitud,
  onVerDetalle,
  onNavigate,
  onAbrirSimuladorCentro,
}) => {
  const disponibles = solicitudes.filter((s) => s.estado === 'Disponible').slice(0, 4);

  return (
    <div className="space-y-8 animate-in fade-in duration-200">
      {/* 1. Header Banner & Saludo */}
      <div className="bg-gradient-to-br from-[#087A3D] via-[#108947] to-[#2E9B50] rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        {/* Ecological background elements */}
        <div className="absolute right-0 bottom-0 translate-x-8 translate-y-8 opacity-10 pointer-events-none">
          <RecycleBagIcon size={240} />
        </div>

        <div className="relative z-10 max-w-2xl">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xs text-xs font-semibold text-emerald-100 mb-3">
            <span>🌱</span>
            <span>Economía Circular Activa • Cuadrante Suba</span>
          </div>

          <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Hola, {reciclador.nombre.split(' ')[0]} 👋
          </h1>
          <p className="text-sm sm:text-base text-emerald-100 mt-1 font-normal">
            Estas son las oportunidades disponibles cerca de ti para tu jornada de recolección.
          </p>

          <div className="mt-5 flex flex-wrap items-center gap-3">
            <button
              onClick={() => onNavigate('solicitudes')}
              className="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-[#087A3D] font-bold text-xs sm:text-sm rounded-xl hover:bg-emerald-50 transition-colors shadow-sm"
            >
              <span>Explorar {solicitudes.filter((s) => s.estado === 'Disponible').length} Solicitudes</span>
              <ArrowRightIcon size={16} />
            </button>

            <button
              onClick={() => onNavigate('mapa')}
              className="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-900/40 hover:bg-emerald-900/60 text-white font-semibold text-xs sm:text-sm rounded-xl border border-white/20 transition-colors"
            >
              <MapIcon size={16} />
              <span>Ver en Mapa</span>
            </button>
          </div>
        </div>
      </div>

      {/* 2. Resumen del Reciclador (KPI Cards) */}
      <div className="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        {/* Puntos */}
        <div className="bg-white p-4 sm:p-5 rounded-2xl border border-[#E2E8F0] shadow-xs flex flex-col justify-between hover:border-amber-300 transition-colors">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-medium text-[#64748B]">Ecopuntos</span>
            <div className="p-2 rounded-xl bg-amber-50 text-[#F5B82E]">
              <StarCoinIcon size={18} />
            </div>
          </div>
          <div>
            <div className="text-xl sm:text-2xl font-black text-[#1E2922] tracking-tight">
              {reciclador.ecopuntos.toLocaleString()}
            </div>
            <p className="text-[11px] text-emerald-600 font-semibold mt-0.5">
              +{reciclador.puntosParaSiguienteNivel} pts para siguiente nivel
            </p>
          </div>
        </div>

        {/* Kg Recuperados */}
        <div className="bg-white p-4 sm:p-5 rounded-2xl border border-[#E2E8F0] shadow-xs flex flex-col justify-between hover:border-emerald-300 transition-colors">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-medium text-[#64748B]">Kg recuperados</span>
            <div className="p-2 rounded-xl bg-[#EEF8EE] text-[#087A3D]">
              <RecycleBagIcon size={18} />
            </div>
          </div>
          <div>
            <div className="text-xl sm:text-2xl font-black text-[#1E2922] tracking-tight">
              {reciclador.kgRecuperados.toLocaleString()} <span className="text-sm font-semibold text-slate-500">kg</span>
            </div>
            <p className="text-[11px] text-[#64748B] mt-0.5">Material validado en balanza</p>
          </div>
        </div>

        {/* Recolecciones */}
        <div className="bg-white p-4 sm:p-5 rounded-2xl border border-[#E2E8F0] shadow-xs flex flex-col justify-between hover:border-blue-300 transition-colors">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-medium text-[#64748B]">Recolecciones</span>
            <div className="p-2 rounded-xl bg-[#EAF4FA] text-[#0284C7]">
              <RecycleBagIcon size={18} />
            </div>
          </div>
          <div>
            <div className="text-xl sm:text-2xl font-black text-[#1E2922] tracking-tight">
              {reciclador.recoleccionesCompletadas}
            </div>
            <p className="text-[11px] text-slate-500 mt-0.5">Rutas completadas</p>
          </div>
        </div>

        {/* Entregas Pendientes */}
        <div className="bg-white p-4 sm:p-5 rounded-2xl border border-[#E2E8F0] shadow-xs flex flex-col justify-between hover:border-sky-300 transition-colors">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-medium text-[#64748B]">Entregas pendientes</span>
            <div className="p-2 rounded-xl bg-sky-50 text-sky-700">
              <TruckIcon size={18} />
            </div>
          </div>
          <div>
            <div className="text-xl sm:text-2xl font-black text-[#1E2922] tracking-tight flex items-baseline gap-2">
              <span>{reciclador.entregasPendientes}</span>
              {reciclador.entregasPendientes > 0 && (
                <span className="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                  Por pesar
                </span>
              )}
            </div>
            <button
              onClick={onAbrirSimuladorCentro}
              className="text-[11px] text-[#0284C7] font-semibold hover:underline mt-0.5 block"
            >
              Simular pesaje →
            </button>
          </div>
        </div>

        {/* Nivel Actual */}
        <div className="bg-gradient-to-br from-[#FAFCFA] to-[#EEF8EE] p-4 sm:p-5 rounded-2xl border border-[#C2E4C9] shadow-xs flex flex-col justify-between col-span-2 lg:col-span-1">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-medium text-[#087A3D]">Rango actual</span>
            <span className="text-lg">🎖️</span>
          </div>
          <div>
            <div className="text-base sm:text-lg font-black text-[#087A3D] leading-tight">
              {reciclador.nivel}
            </div>
            <div className="w-full bg-slate-200 h-1.5 rounded-full mt-2 overflow-hidden">
              <div
                className="bg-[#087A3D] h-full rounded-full transition-all duration-500"
                style={{ width: `${Math.min(100, (reciclador.ecopuntos / 3000) * 100)}%` }}
              />
            </div>
          </div>
        </div>
      </div>

      {/* 3. Sección de Solicitudes Cercanas Destacadas */}
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-lg sm:text-xl font-bold text-[#1E2922]">
              Solicitudes cercanas prioritarias
            </h2>
            <p className="text-xs text-[#64748B]">
              Materiales separados listos para recolección en tu cuadrante
            </p>
          </div>
          <button
            onClick={() => onNavigate('solicitudes')}
            className="text-xs sm:text-sm font-semibold text-[#087A3D] hover:text-[#065C2D] flex items-center gap-1"
          >
            <span>Ver todas</span>
            <ArrowRightIcon size={14} />
          </button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
          {disponibles.map((sol) => (
            <div
              key={sol.id}
              className="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group"
            >
              <div>
                {/* Photo preview */}
                <div className="relative h-36 overflow-hidden bg-slate-100">
                  <img
                    src={sol.fotografia}
                    alt={sol.material}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <div className="absolute top-3 left-3 flex items-center gap-1.5">
                    <span className="px-2.5 py-1 bg-black/60 backdrop-blur-xs text-white text-xs font-bold rounded-lg">
                      {sol.codigo}
                    </span>
                    <StatusBadge status={sol.prioridad} size="sm" />
                  </div>
                  <div className="absolute bottom-3 right-3">
                    <span className="px-2 py-0.5 bg-white/90 backdrop-blur-xs text-[#087A3D] text-[11px] font-black rounded-md shadow-xs">
                      {sol.distanciaMetros} m
                    </span>
                  </div>
                </div>

                {/* Card Content */}
                <div className="p-4 space-y-3">
                  <div className="flex items-start justify-between gap-2">
                    <div>
                      <h3 className="font-bold text-base text-[#1E2922]">{sol.material}</h3>
                      <p className="text-xs text-[#64748B]">📍 {sol.ubicacion}</p>
                    </div>
                    <span className="px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200 text-xs font-black rounded-xl">
                      ~{sol.cantidadAproxKg} kg
                    </span>
                  </div>

                  <div className="text-xs text-[#475569] bg-[#FAFCFA] p-2 rounded-xl border border-slate-100 flex items-center gap-1.5">
                    <span>🕑</span>
                    <span className="truncate">{sol.horarioDisponible}</span>
                  </div>

                  <p className="text-xs text-[#64748B] line-clamp-2 italic">
                    "{sol.observaciones}"
                  </p>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="p-4 pt-0 grid grid-cols-2 gap-2">
                <button
                  onClick={() => onVerDetalle(sol)}
                  className="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors text-center"
                >
                  Ver detalle
                </button>
                <button
                  onClick={() => onAceptarSolicitud(sol.id)}
                  className="w-full py-2 bg-[#087A3D] hover:bg-[#065C2D] text-white font-bold text-xs rounded-xl transition-colors shadow-xs text-center"
                >
                  Aceptar
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* 4. Mini Mapa interactivo & Resumen de impacto */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Mapa preview */}
        <div className="lg:col-span-2 bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs space-y-3">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-bold text-base text-[#1E2922] flex items-center gap-2">
                <MapIcon size={18} className="text-[#087A3D]" />
                <span>Mapa de solicitudes y centros aliados</span>
              </h3>
              <p className="text-xs text-[#64748B]">
                Cuadrante Suba • Puntos ordenados por proximidad
              </p>
            </div>
            <button
              onClick={() => onNavigate('mapa')}
              className="text-xs font-bold text-[#087A3D] hover:underline"
            >
              Pantalla completa →
            </button>
          </div>

          {/* Interactive visual canvas map */}
          <div
            onClick={() => onNavigate('mapa')}
            className="relative h-64 bg-[#EAF4FA] rounded-2xl overflow-hidden border border-sky-200 cursor-pointer group"
          >
            {/* Grid & mock roads */}
            <div
              className="absolute inset-0 opacity-40"
              style={{
                backgroundImage:
                  'radial-gradient(#0284C7 1px, transparent 1px), linear-gradient(to right, #bae6fd 1px, transparent 1px), linear-gradient(to bottom, #bae6fd 1px, transparent 1px)',
                backgroundSize: '24px 24px, 48px 48px, 48px 48px',
              }}
            />

            {/* Mock Route line */}
            <svg className="absolute inset-0 w-full h-full pointer-events-none">
              <path
                d="M 120 180 Q 240 100 360 140 T 520 80"
                fill="none"
                stroke="#087A3D"
                strokeWidth="3"
                strokeDasharray="6,6"
              />
            </svg>

            {/* Recycler current position pin */}
            <div className="absolute top-[55%] left-[22%] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
              <div className="w-9 h-9 rounded-full bg-[#087A3D] text-white flex items-center justify-center shadow-lg border-2 border-white ring-4 ring-emerald-300/50 animate-pulse">
                🚲
              </div>
              <span className="mt-1 px-2 py-0.5 bg-white text-[10px] font-bold text-[#087A3D] rounded-full shadow-md">
                Tú estás aquí
              </span>
            </div>

            {/* Request Pin 1024 */}
            <div className="absolute top-[32%] left-[45%] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
              <div className="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-md border-2 border-white">
                📦
              </div>
              <span className="mt-1 px-1.5 py-0.5 bg-black/75 text-white text-[9px] font-bold rounded-md">
                #1024 (800m)
              </span>
            </div>

            {/* Centro de Acopio Pin */}
            <div className="absolute top-[20%] left-[75%] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
              <div className="w-8 h-8 rounded-full bg-[#0284C7] text-white flex items-center justify-center shadow-md border-2 border-white">
                🏢
              </div>
              <span className="mt-1 px-1.5 py-0.5 bg-sky-900 text-white text-[9px] font-bold rounded-md">
                Centro Verde Suba
              </span>
            </div>

            <div className="absolute bottom-3 left-3 bg-white/90 backdrop-blur-xs px-3 py-1.5 rounded-xl text-xs text-slate-700 font-medium shadow-xs">
              Haz clic para explorar el mapa interactivo con filtros
            </div>
          </div>
        </div>

        {/* Resumen de Impacto Ecológico */}
        <div className="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-xs flex flex-col justify-between space-y-4">
          <div>
            <div className="flex items-center justify-between mb-3">
              <h3 className="font-bold text-base text-[#1E2922]">Tu Impacto Ambiental</h3>
              <span className="text-lg">🌍</span>
            </div>
            <p className="text-xs text-[#64748B]">
              Contribución de Carlos Rodríguez a la economía circular
            </p>

            <div className="mt-5 space-y-3">
              <div className="flex items-center justify-between p-3 rounded-2xl bg-[#EEF8EE]">
                <div className="flex items-center gap-3">
                  <span className="text-2xl">🌱</span>
                  <div>
                    <div className="text-xs text-slate-500">Huella de CO₂ evitada</div>
                    <div className="text-base font-black text-[#087A3D]">2.250 kg de CO₂</div>
                  </div>
                </div>
              </div>

              <div className="flex items-center justify-between p-3 rounded-2xl bg-sky-50">
                <div className="flex items-center gap-3">
                  <span className="text-2xl">💧</span>
                  <div>
                    <div className="text-xs text-slate-500">Agua ahorrada</div>
                    <div className="text-base font-black text-sky-700">32.500 Litros</div>
                  </div>
                </div>
              </div>

              <div className="flex items-center justify-between p-3 rounded-2xl bg-amber-50">
                <div className="flex items-center gap-3">
                  <span className="text-2xl">📦</span>
                  <div>
                    <div className="text-xs text-slate-500">Material predominante</div>
                    <div className="text-base font-black text-amber-800">Cartón (38%)</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span className="text-slate-500">Zona Suba: 8.500 kg totales</span>
            <button
              onClick={() => onNavigate('estadisticas')}
              className="text-[#087A3D] font-bold hover:underline"
            >
              Ver estadísticas →
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
