import React, { useState } from 'react';
import { Solicitud, CentroAcopio } from '@/models/types';
import { StatusBadge } from '../components/Badge';
import { MapIcon, CheckCircleIcon, ArrowRightIcon } from '../components/Icons';

interface MapaViewProps {
  solicitudes: Solicitud[];
  centrosAcopio: CentroAcopio[];
  onAceptarSolicitud: (id: string) => void;
  onVerDetalle: (solicitud: Solicitud) => void;
}

export const MapaView: React.FC<MapaViewProps> = ({
  solicitudes,
  centrosAcopio,
  onAceptarSolicitud,
  onVerDetalle,
}) => {
  const [selectedSol, setSelectedSol] = useState<Solicitud | null>(
    solicitudes.find((s) => s.codigo === '#1024') || solicitudes[0] || null
  );
  const [selectedCentro, setSelectedCentro] = useState<CentroAcopio | null>(null);
  const [filtroPrioridad, setFiltroPrioridad] = useState<string>('Todas');

  const solicitudesFiltradas = solicitudes.filter((s) => {
    if (s.estado !== 'Disponible' && s.estado !== 'Aceptada') return false;
    if (filtroPrioridad !== 'Todas' && s.prioridad !== filtroPrioridad) return false;
    return true;
  });

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Title & Quick Info */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
            <MapIcon size={24} className="text-[#087A3D]" />
            <span>Mapa de Oportunidades y Rutas</span>
          </h1>
          <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
            Geolocalización en tiempo real en la localidad de Suba. Marcadores por prioridad y centros de acopio.
          </p>
        </div>

        {/* Priority Filter */}
        <div className="flex items-center gap-2 bg-white px-3 py-1.5 rounded-2xl border border-[#E2E8F0] shadow-xs">
          <span className="text-xs font-semibold text-slate-500">Filtrar:</span>
          {['Todas', 'Alta', 'Media', 'Baja'].map((prio) => (
            <button
              key={prio}
              onClick={() => setFiltroPrioridad(prio)}
              className={`px-2.5 py-1 rounded-xl text-xs font-bold transition-all ${
                filtroPrioridad === prio
                  ? 'bg-[#087A3D] text-white shadow-xs'
                  : 'text-slate-600 hover:bg-slate-100'
              }`}
            >
              {prio}
            </button>
          ))}
        </div>
      </div>

      {/* Main Map Container */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Interactive Map Canvas */}
        <div className="lg:col-span-2 bg-[#EAF4FA] rounded-3xl border border-sky-200 overflow-hidden shadow-sm relative min-h-[500px] flex flex-col justify-between p-4">
          {/* Street & grid background simulation */}
          <div
            className="absolute inset-0 opacity-40 pointer-events-none"
            style={{
              backgroundImage:
                'radial-gradient(#0284C7 1.2px, transparent 1.2px), linear-gradient(to right, #bae6fd 1px, transparent 1px), linear-gradient(to bottom, #bae6fd 1px, transparent 1px)',
              backgroundSize: '24px 24px, 64px 64px, 64px 64px',
            }}
          />

          {/* Map roads mock lines */}
          <svg className="absolute inset-0 w-full h-full pointer-events-none opacity-60">
            {/* Main avenues */}
            <path d="M 50 120 L 700 160" stroke="#CBD5E1" strokeWidth="12" strokeLinecap="round" />
            <path d="M 280 40 L 320 500" stroke="#CBD5E1" strokeWidth="14" strokeLinecap="round" />
            <path d="M 120 420 L 650 360" stroke="#E2E8F0" strokeWidth="10" strokeLinecap="round" />
            {/* Route dashed path */}
            <path
              d="M 180 320 Q 320 220 420 180 T 560 110"
              fill="none"
              stroke="#087A3D"
              strokeWidth="4"
              strokeDasharray="8,6"
            />
          </svg>

          {/* Top Info Bar */}
          <div className="relative z-10 flex items-center justify-between bg-white/90 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-sky-200 shadow-xs text-xs">
            <div className="flex items-center gap-4">
              <span className="flex items-center gap-1.5 font-bold text-slate-700">
                <span className="w-3 h-3 rounded-full bg-red-500 border border-white" /> Alta prioridad
              </span>
              <span className="flex items-center gap-1.5 font-bold text-slate-700">
                <span className="w-3 h-3 rounded-full bg-amber-500 border border-white" /> Media prioridad
              </span>
              <span className="flex items-center gap-1.5 font-bold text-slate-700">
                <span className="w-3 h-3 rounded-full bg-blue-500 border border-white" /> Baja prioridad
              </span>
              <span className="flex items-center gap-1.5 font-bold text-slate-700">
                <span className="w-3 h-3 rounded-full bg-[#087A3D] border border-white" /> Centro de acopio
              </span>
            </div>
            <span className="hidden sm:inline text-slate-400 font-medium">Localidad 11 • Suba</span>
          </div>

          {/* MAP PINS */}
          <div className="relative z-10 w-full h-[400px]">
            {/* Recycler Current Position Pin */}
            <div
              style={{ left: '26%', top: '64%' }}
              className="absolute -translate-x-1/2 -translate-y-1/2 flex flex-col items-center group cursor-pointer"
            >
              <div className="w-11 h-11 rounded-full bg-[#087A3D] text-white flex items-center justify-center text-xl shadow-xl border-3 border-white ring-4 ring-emerald-300/60 animate-pulse">
                🚲
              </div>
              <span className="mt-1 px-2 py-0.5 bg-[#087A3D] text-white text-[10px] font-black rounded-full shadow-md whitespace-nowrap">
                Carlos (Tú)
              </span>
            </div>

            {/* Solicitudes Pins */}
            {solicitudesFiltradas.map((sol, i) => {
              const isSelected = selectedSol?.id === sol.id;
              // Deterministic spread around Suba
              const positions = [
                { left: '44%', top: '42%' }, // #1024
                { left: '60%', top: '28%' }, // #1028
                { left: '35%', top: '80%' }, // #1030
                { left: '78%', top: '55%' }, // #1035
                { left: '50%', top: '68%' }, // #1042
              ];
              const pos = positions[i % positions.length];

              let pinColor = 'bg-amber-500';
              if (sol.prioridad === 'Alta') pinColor = 'bg-red-500';
              if (sol.prioridad === 'Baja') pinColor = 'bg-blue-500';

              return (
                <div
                  key={sol.id}
                  style={{ left: pos.left, top: pos.top }}
                  onClick={() => {
                    setSelectedSol(sol);
                    setSelectedCentro(null);
                  }}
                  className={`absolute -translate-x-1/2 -translate-y-1/2 flex flex-col items-center cursor-pointer transition-transform hover:scale-115 ${
                    isSelected ? 'scale-120 z-30' : 'z-20'
                  }`}
                >
                  <div
                    className={`w-9 h-9 rounded-2xl ${pinColor} text-white flex items-center justify-center font-bold text-xs shadow-lg border-2 border-white`}
                  >
                    {sol.material === 'Cartón'
                      ? '📦'
                      : sol.material === 'Plástico'
                      ? '🧴'
                      : sol.material === 'Vidrio'
                      ? '🍾'
                      : sol.material === 'Metales'
                      ? '🥫'
                      : '📄'}
                  </div>
                  <span
                    className={`mt-1 px-2 py-0.5 text-[10px] font-black rounded-lg shadow-md whitespace-nowrap ${
                      isSelected
                        ? 'bg-black text-white ring-2 ring-emerald-400'
                        : 'bg-white/95 text-slate-800'
                    }`}
                  >
                    {sol.codigo} (~{sol.cantidadAproxKg}kg)
                  </span>
                </div>
              );
            })}

            {/* Centro de Acopio Pin */}
            {centrosAcopio.map((centro) => (
              <div
                key={centro.id}
                style={{ left: '75%', top: '24%' }}
                onClick={() => {
                  setSelectedCentro(centro);
                  setSelectedSol(null);
                }}
                className="absolute -translate-x-1/2 -translate-y-1/2 flex flex-col items-center cursor-pointer z-20 hover:scale-115 transition-transform"
              >
                <div className="w-10 h-10 rounded-full bg-[#0284C7] text-white flex items-center justify-center text-lg shadow-xl border-2 border-white ring-4 ring-sky-200">
                  🏢
                </div>
                <span className="mt-1 px-2 py-0.5 bg-sky-900 text-white text-[10px] font-bold rounded-lg shadow-md whitespace-nowrap">
                  {centro.nombre}
                </span>
              </div>
            ))}
          </div>

          {/* Bottom helper */}
          <div className="relative z-10 bg-white/85 backdrop-blur-xs p-3 rounded-2xl border border-sky-200 text-xs text-slate-600 flex items-center justify-between">
            <span>💡 Selecciona un marcador en el mapa para inspeccionar sus datos y distancia.</span>
            <span className="font-bold text-[#087A3D]">Modo Ruta Activo</span>
          </div>
        </div>

        {/* Marker Detail Sidebar Card */}
        <div className="space-y-4">
          {selectedSol ? (
            <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm overflow-hidden p-5 space-y-4 animate-in fade-in duration-150">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">
                  Punto de recolección seleccionado
                </span>
                <StatusBadge status={selectedSol.prioridad} size="sm" />
              </div>

              {/* Photo preview */}
              <div className="h-36 rounded-2xl overflow-hidden bg-slate-100 relative">
                <img
                  src={selectedSol.fotografia}
                  alt={selectedSol.material}
                  className="w-full h-full object-cover"
                />
                <div className="absolute top-2 left-2 bg-black/70 text-white font-black text-xs px-2.5 py-1 rounded-lg">
                  {selectedSol.codigo}
                </div>
                <div className="absolute bottom-2 right-2 bg-white/95 text-[#087A3D] font-black text-xs px-2 py-0.5 rounded-md shadow-xs">
                  {selectedSol.distanciaMetros} m
                </div>
              </div>

              {/* Details */}
              <div>
                <h3 className="font-extrabold text-xl text-[#1E2922]">{selectedSol.material}</h3>
                <p className="text-xs text-[#64748B] mt-0.5">
                  📍 {selectedSol.ubicacion} • {selectedSol.direccion}
                </p>
              </div>

              <div className="grid grid-cols-2 gap-2 text-xs">
                <div className="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                  <span className="text-slate-400 block text-[10px] uppercase font-bold">Cantidad</span>
                  <span className="font-extrabold text-slate-800 text-sm">
                    ~{selectedSol.cantidadAproxKg} kg
                  </span>
                </div>
                <div className="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                  <span className="text-slate-400 block text-[10px] uppercase font-bold">Horario</span>
                  <span className="font-bold text-slate-800 truncate block">
                    {selectedSol.horarioDisponible}
                  </span>
                </div>
              </div>

              <div className="p-3 rounded-xl bg-[#FAFCFA] border border-slate-100 text-xs text-slate-600">
                <span className="font-bold text-slate-700 block mb-0.5">Observación:</span>
                <p className="italic">"{selectedSol.observaciones}"</p>
              </div>

              <div className="pt-2 space-y-2">
                {selectedSol.estado === 'Disponible' ? (
                  <button
                    onClick={() => onAceptarSolicitud(selectedSol.id)}
                    className="w-full py-3 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs sm:text-sm rounded-xl transition-colors shadow-md flex items-center justify-center gap-2"
                  >
                    <CheckCircleIcon size={18} />
                    <span>Aceptar y agregar a mi ruta</span>
                  </button>
                ) : (
                  <div className="p-2.5 bg-sky-50 text-sky-800 border border-sky-200 rounded-xl text-center text-xs font-bold">
                    ✓ Ya está en tu ruta activa
                  </div>
                )}

                <button
                  onClick={() => onVerDetalle(selectedSol)}
                  className="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors text-center"
                >
                  Ver ficha completa
                </button>
              </div>
            </div>
          ) : selectedCentro ? (
            <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-sm p-5 space-y-4">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 rounded-2xl bg-sky-100 text-[#0284C7] flex items-center justify-center text-2xl">
                  🏢
                </div>
                <div>
                  <h3 className="font-extrabold text-base text-slate-900">{selectedCentro.nombre}</h3>
                  <p className="text-xs text-slate-500">Centro de Acopio Aliado Oficial</p>
                </div>
              </div>

              <div className="space-y-2 text-xs text-slate-600">
                <div className="p-3 bg-slate-50 rounded-xl">
                  <strong className="block text-slate-800">Dirección:</strong>
                  {selectedCentro.direccion}
                </div>
                <div className="p-3 bg-slate-50 rounded-xl">
                  <strong className="block text-slate-800">Horario de báscula:</strong>
                  {selectedCentro.horario}
                </div>
                <div className="p-3 bg-slate-50 rounded-xl">
                  <strong className="block text-slate-800">Encargado certificado:</strong>
                  {selectedCentro.encargado}
                </div>
              </div>

              <div className="p-3 bg-[#EEF8EE] rounded-xl text-xs text-[#087A3D]">
                ✓ Báscula electrónica certificada por la Secretaría de Ambiente.
              </div>
            </div>
          ) : null}
        </div>
      </div>
    </div>
  );
};
