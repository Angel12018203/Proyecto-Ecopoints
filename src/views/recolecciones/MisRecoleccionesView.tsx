import React, { useState } from 'react';
import { Solicitud, Recoleccion } from '@/models/types';
import { StatusBadge } from '../components/Badge';
import { RecycleBagIcon, RouteIcon, TruckIcon, CheckCircleIcon } from '../components/Icons';
import { NavSection } from '../components/Sidebar';

interface MisRecoleccionesViewProps {
  solicitudes: Solicitud[];
  recolecciones: Recoleccion[];
  onIniciarRecoleccion: (solicitud: Solicitud) => void;
  onNavigate: (section: NavSection) => void;
  onAbrirRegistrarEntrega: () => void;
}

export const MisRecoleccionesView: React.FC<MisRecoleccionesViewProps> = ({
  solicitudes,
  recolecciones,
  onIniciarRecoleccion,
  onNavigate,
  onAbrirRegistrarEntrega,
}) => {
  const [tab, setTab] = useState<'activas' | 'historial'>('activas');

  const enRuta = solicitudes.filter(
    (s) => s.estado === 'Aceptada' || s.estado === 'En recolección'
  );
  const recolectadasHoy = solicitudes.filter((s) => s.estado === 'Recolectada');

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Header & Quick Action Buttons */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
            <RecycleBagIcon size={24} className="text-[#087A3D]" />
            <span>Mis Recolecciones</span>
          </h1>
          <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
            Gestiona las solicitudes que has aceptado, organízalas en tu ruta y regístralas al recogerlas.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => onNavigate('mapa')}
            className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors flex items-center gap-1.5"
          >
            <RouteIcon size={16} />
            <span>Organizar Ruta</span>
          </button>

          <button
            onClick={onAbrirRegistrarEntrega}
            className="px-4 py-2 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs rounded-xl transition-colors shadow-sm flex items-center gap-1.5"
          >
            <TruckIcon size={16} />
            <span>Entregar al Centro de Acopio</span>
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
        <button
          onClick={() => setTab('activas')}
          className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 ${
            tab === 'activas'
              ? 'bg-[#EEF8EE] text-[#087A3D]'
              : 'text-slate-500 hover:text-slate-800'
          }`}
        >
          <span>Solicitudes en Ruta</span>
          <span className="px-2 py-0.5 rounded-full text-[10px] bg-[#087A3D] text-white">
            {enRuta.length}
          </span>
        </button>

        <button
          onClick={() => setTab('historial')}
          className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center gap-2 ${
            tab === 'historial'
              ? 'bg-[#EEF8EE] text-[#087A3D]'
              : 'text-slate-500 hover:text-slate-800'
          }`}
        >
          <span>Recolectadas (En Tolva)</span>
          <span className="px-2 py-0.5 rounded-full text-[10px] bg-emerald-700 text-white">
            {recolectadasHoy.length + recolecciones.length}
          </span>
        </button>
      </div>

      {/* Tab Content: Solicitudes en Ruta */}
      {tab === 'activas' ? (
        enRuta.length === 0 ? (
          <div className="bg-white rounded-3xl p-12 text-center border border-[#E2E8F0] space-y-4">
            <div className="w-16 h-16 rounded-full bg-[#EEF8EE] text-[#087A3D] flex items-center justify-center mx-auto text-2xl">
              🚲
            </div>
            <div className="max-w-md mx-auto">
              <h3 className="font-extrabold text-base text-slate-800">
                No tienes solicitudes pendientes en tu ruta de hoy
              </h3>
              <p className="text-xs text-slate-500 mt-1">
                Explora las solicitudes cercanas en Suba y acéptalas para agregarlas a tu recorrido diario.
              </p>
            </div>
            <button
              onClick={() => onNavigate('solicitudes')}
              className="px-5 py-2.5 bg-[#087A3D] text-white font-bold text-xs rounded-xl hover:bg-[#065C2D] transition-colors shadow-sm"
            >
              Explorar Solicitudes Cercanas
            </button>
          </div>
        ) : (
          <div className="space-y-4">
            <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between text-xs text-amber-900">
              <div className="flex items-center gap-2">
                <span>📍</span>
                <span>
                  Tienes <strong>{enRuta.length} paradas programadas</strong>. Realiza la recolección física y
                  presiona "Registrar recolección".
                </span>
              </div>
              <button
                onClick={() => onNavigate('mapa')}
                className="font-bold text-[#087A3D] hover:underline shrink-0"
              >
                Ver mapa de ruta →
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
              {enRuta.map((sol, index) => (
                <div
                  key={sol.id}
                  className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs hover:shadow-md transition-all overflow-hidden flex flex-col justify-between"
                >
                  <div className="p-5 space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="px-2.5 py-1 bg-black/75 text-white font-black text-xs rounded-lg">
                        Parada #{index + 1} • {sol.codigo}
                      </span>
                      <StatusBadge status={sol.estado} />
                    </div>

                    <div className="flex items-center gap-3">
                      <img
                        src={sol.fotografia}
                        alt={sol.material}
                        className="w-16 h-16 rounded-2xl object-cover border border-slate-200"
                      />
                      <div>
                        <h4 className="font-extrabold text-base text-slate-900">{sol.material}</h4>
                        <span className="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                          ~{sol.cantidadAproxKg} kg estimados
                        </span>
                      </div>
                    </div>

                    <div className="space-y-1 text-xs text-slate-600 bg-slate-50 p-3 rounded-2xl">
                      <div>
                        <strong className="text-slate-800">Dirección:</strong> {sol.direccion}
                      </div>
                      <div>
                        <strong className="text-slate-800">Horario:</strong> {sol.horarioDisponible}
                      </div>
                      <div>
                        <strong className="text-slate-800">Ciudadano:</strong> {sol.ciudadanoNombre}
                      </div>
                    </div>

                    <p className="text-xs text-slate-500 italic line-clamp-2">
                      "{sol.observaciones}"
                    </p>
                  </div>

                  <div className="p-5 pt-0">
                    <button
                      onClick={() => onIniciarRecoleccion(sol)}
                      className="w-full py-3 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs sm:text-sm rounded-xl transition-colors shadow-md flex items-center justify-center gap-2"
                    >
                      <CheckCircleIcon size={18} />
                      <span>Registrar recolección</span>
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )
      ) : (
        /* Tab Content: Recolectadas */
        <div className="space-y-4">
          <div className="bg-[#EEF8EE] border border-[#C2E4C9] rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-[#087A3D]">
            <div>
              <strong>Carga acumulada en vehículo:</strong> Tienes materiales listos para entregar en el Centro de Acopio y recibir tu pesaje oficial con Ecopuntos.
            </div>
            <button
              onClick={onAbrirRegistrarEntrega}
              className="px-4 py-2 bg-[#087A3D] hover:bg-[#065C2D] text-white font-bold rounded-xl shrink-0 transition-colors shadow-xs"
            >
              Registrar Entrega Oficial
            </button>
          </div>

          <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div className="divide-y divide-slate-100">
              {[...recolectadasHoy, ...recolecciones].map((item, idx) => {
                const isSol = 'material' in item;
                const mat = isSol ? (item as Solicitud).material : (item as Recoleccion).materialRecogido;
                const peso = isSol
                  ? (item as Solicitud).cantidadAproxKg
                  : (item as Recoleccion).cantidadEstimadaKg;
                const cod = isSol ? (item as Solicitud).codigo : (item as Recoleccion).solicitudCodigo;
                const obs = isSol
                  ? (item as Solicitud).observaciones
                  : (item as Recoleccion).observaciones;
                const fecha = isSol ? 'Hoy, en ruta' : (item as Recoleccion).fecha;

                return (
                  <div key={idx} className="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors">
                    <div className="flex items-center gap-3">
                      <div className="w-12 h-12 rounded-2xl bg-[#EEF8EE] text-[#087A3D] flex items-center justify-center text-xl font-bold">
                        {mat === 'Cartón' ? '📦' : mat === 'Plástico' ? '🧴' : mat === 'Vidrio' ? '🍾' : '🥫'}
                      </div>
                      <div>
                        <div className="flex items-center gap-2">
                          <span className="font-extrabold text-sm text-slate-900">{mat}</span>
                          <span className="text-[11px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                            {cod}
                          </span>
                          <StatusBadge status="Recolectada" size="sm" />
                        </div>
                        <p className="text-xs text-slate-500 mt-0.5">{obs}</p>
                      </div>
                    </div>

                    <div className="text-right">
                      <div className="text-base font-black text-slate-900">~{peso} kg</div>
                      <span className="text-[11px] text-slate-400">{fecha}</span>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
