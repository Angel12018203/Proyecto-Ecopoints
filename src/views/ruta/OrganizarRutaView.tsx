import React, { useState } from 'react';
import { Ruta, ParadaRuta, Solicitud } from '@/models/types';
import { StatusBadge } from '../components/Badge';
import { RouteIcon, CheckCircleIcon, ArrowRightIcon } from '../components/Icons';
import { NavSection } from '../components/Sidebar';

interface OrganizarRutaViewProps {
  ruta: Ruta;
  onOptimizar: () => void;
  onNavigate: (section: NavSection) => void;
  onIniciarRecoleccion: (solicitud: Solicitud) => void;
}

export const OrganizarRutaView: React.FC<OrganizarRutaViewProps> = ({
  ruta,
  onOptimizar,
  onNavigate,
  onIniciarRecoleccion,
}) => {
  const [paradas, setParadas] = useState<ParadaRuta[]>(ruta.paradas);

  const moverArriba = (index: number) => {
    if (index === 0) return;
    const nuevas = [...paradas];
    const temp = nuevas[index];
    nuevas[index] = nuevas[index - 1];
    nuevas[index - 1] = temp;
    // actualizar orden
    nuevas.forEach((p, i) => (p.orden = i + 1));
    setParadas(nuevas);
  };

  const moverAbajo = (index: number) => {
    if (index === paradas.length - 1) return;
    const nuevas = [...paradas];
    const temp = nuevas[index];
    nuevas[index] = nuevas[index + 1];
    nuevas[index + 1] = temp;
    nuevas.forEach((p, i) => (p.orden = i + 1));
    setParadas(nuevas);
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Title & Controls */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
            <RouteIcon size={24} className="text-[#087A3D]" />
            <span>Mi Ruta de Recolección de Hoy</span>
          </h1>
          <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
            Organiza el orden de paradas según distancias y horarios para ahorrar tiempo y esfuerzo físico.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => {
              onOptimizar();
            }}
            className="px-4 py-2 bg-[#EEF8EE] hover:bg-emerald-100 text-[#087A3D] font-extrabold text-xs rounded-xl transition-colors border border-[#C2E4C9] flex items-center gap-1.5"
          >
            <span>⚡ Optimizar por cercanía</span>
          </button>

          <button
            onClick={() => onNavigate('mapa')}
            className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors"
          >
            Ver en mapa
          </button>
        </div>
      </div>

      {/* Metric Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500 block mb-1">Paradas programadas</span>
          <div className="text-2xl font-black text-slate-900">{paradas.length} paradas</div>
          <span className="text-[11px] text-emerald-600 font-semibold mt-1 block">
            Cuadrante Suba Centro y Rincón
          </span>
        </div>

        <div className="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500 block mb-1">Distancia estimada de ruta</span>
          <div className="text-2xl font-black text-slate-900">
            {ruta.distanciaTotalKm || 3.8} <span className="text-sm font-semibold text-slate-500">km</span>
          </div>
          <span className="text-[11px] text-slate-400 mt-1 block">Ahorro de ~25% con ruta optimizada</span>
        </div>

        <div className="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500 block mb-1">Tiempo de recorrido</span>
          <div className="text-2xl font-black text-slate-900">
            ~{ruta.tiempoTotalMin || 85} <span className="text-sm font-semibold text-slate-500">min</span>
          </div>
          <span className="text-[11px] text-slate-400 mt-1 block">Incluye carga y amarre de materiales</span>
        </div>
      </div>

      {/* Lista de Paradas */}
      {paradas.length === 0 ? (
        <div className="bg-white rounded-3xl p-12 text-center border border-[#E2E8F0] space-y-3">
          <div className="text-3xl">📦</div>
          <h3 className="font-extrabold text-base text-slate-800">No hay paradas en tu ruta de hoy</h3>
          <p className="text-xs text-slate-500 max-w-sm mx-auto">
            Acepta solicitudes cercanas para planificar tu recorrido de recolección en Suba.
          </p>
          <button
            onClick={() => onNavigate('solicitudes')}
            className="px-4 py-2 bg-[#087A3D] text-white text-xs font-bold rounded-xl"
          >
            Explorar Solicitudes
          </button>
        </div>
      ) : (
        <div className="space-y-3">
          <div className="flex items-center justify-between text-xs font-bold text-slate-500 px-2">
            <span>ORDEN RECOMENDADO DE VISITA</span>
            <span>ACCIONES</span>
          </div>

          <div className="space-y-3">
            {paradas.map((parada, index) => {
              const sol = parada.solicitud;

              return (
                <div
                  key={sol.id}
                  className="bg-white rounded-2xl border border-[#E2E8F0] p-4 sm:p-5 shadow-xs hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                  <div className="flex items-start sm:items-center gap-4">
                    {/* Position Number */}
                    <div className="w-10 h-10 rounded-2xl bg-[#EEF8EE] text-[#087A3D] font-black text-base flex items-center justify-center shrink-0 border border-[#C2E4C9]">
                      #{index + 1}
                    </div>

                    <img
                      src={sol.fotografia}
                      alt={sol.material}
                      className="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0"
                    />

                    <div className="space-y-1">
                      <div className="flex items-center gap-2">
                        <span className="font-black text-sm text-slate-900">{sol.codigo}</span>
                        <span className="font-extrabold text-sm text-[#087A3D]">{sol.material}</span>
                        <span className="px-2 py-0.5 bg-amber-50 text-amber-900 font-bold text-xs rounded-md">
                          ~{sol.cantidadAproxKg} kg
                        </span>
                        <StatusBadge status={sol.prioridad} size="sm" />
                      </div>

                      <div className="text-xs text-slate-600 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span>📍 {sol.direccion}</span>
                        <span>•</span>
                        <span>🕑 {sol.horarioDisponible}</span>
                        <span>•</span>
                        <span className="font-bold text-[#0284C7]">{sol.distanciaMetros} m</span>
                      </div>
                    </div>
                  </div>

                  {/* Ordering and action buttons */}
                  <div className="flex items-center justify-end gap-2 border-t md:border-t-0 pt-2 md:pt-0">
                    <button
                      onClick={() => moverArriba(index)}
                      disabled={index === 0}
                      className="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-xs font-bold"
                      title="Mover parada antes"
                    >
                      ▲
                    </button>
                    <button
                      onClick={() => moverAbajo(index)}
                      disabled={index === paradas.length - 1}
                      className="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors text-xs font-bold"
                      title="Mover parada después"
                    >
                      ▼
                    </button>

                    <button
                      onClick={() => onIniciarRecoleccion(sol)}
                      className="px-4 py-2 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-bold rounded-xl transition-colors shadow-xs flex items-center gap-1.5"
                    >
                      <CheckCircleIcon size={16} />
                      <span>Registrar recolección</span>
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};
