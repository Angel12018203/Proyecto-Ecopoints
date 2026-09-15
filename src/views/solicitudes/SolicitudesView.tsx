import React, { useState, useMemo } from 'react';
import { Solicitud, TipoMaterial, PrioridadSolicitud } from '@/models/types';
import { StatusBadge } from '../components/Badge';
import { SearchListIcon, CheckCircleIcon, MapIcon } from '../components/Icons';

interface SolicitudesViewProps {
  solicitudes: Solicitud[];
  onAceptarSolicitud: (id: string) => void;
  onVerDetalle: (solicitud: Solicitud) => void;
}

export const SolicitudesView: React.FC<SolicitudesViewProps> = ({
  solicitudes,
  onAceptarSolicitud,
  onVerDetalle,
}) => {
  const [materialFiltro, setMaterialFiltro] = useState<string>('Todos');
  const [distanciaFiltro, setDistanciaFiltro] = useState<string>('Todas');
  const [cantidadFiltro, setCantidadFiltro] = useState<string>('Todas');
  const [prioridadFiltro, setPrioridadFiltro] = useState<string>('Todas');
  const [busqueda, setBusqueda] = useState<string>('');

  const materialesOpciones = ['Todos', 'Cartón', 'Plástico', 'Vidrio', 'Metales', 'Papel', 'Otros'];
  const distanciasOpciones = ['Todas', '< 1 km', '1–3 km', '3–5 km', '+5 km'];
  const cantidadesOpciones = ['Todas', 'Menos de 10 kg', '10–30 kg', '30–50 kg', '+50 kg'];

  const solicitudesFiltradas = useMemo(() => {
    return solicitudes.filter((s) => {
      // Material
      if (materialFiltro !== 'Todos' && s.material !== materialFiltro) return false;

      // Distancia
      if (distanciaFiltro === '< 1 km' && s.distanciaMetros >= 1000) return false;
      if (distanciaFiltro === '1–3 km' && (s.distanciaMetros < 1000 || s.distanciaMetros > 3000)) return false;
      if (distanciaFiltro === '3–5 km' && (s.distanciaMetros < 3000 || s.distanciaMetros > 5000)) return false;
      if (distanciaFiltro === '+5 km' && s.distanciaMetros <= 5000) return false;

      // Cantidad
      if (cantidadFiltro === 'Menos de 10 kg' && s.cantidadAproxKg >= 10) return false;
      if (cantidadFiltro === '10–30 kg' && (s.cantidadAproxKg < 10 || s.cantidadAproxKg > 30)) return false;
      if (cantidadFiltro === '30–50 kg' && (s.cantidadAproxKg < 30 || s.cantidadAproxKg > 50)) return false;
      if (cantidadFiltro === '+50 kg' && s.cantidadAproxKg <= 50) return false;

      // Prioridad
      if (prioridadFiltro !== 'Todas' && s.prioridad !== prioridadFiltro) return false;

      // Texto
      if (busqueda.trim()) {
        const q = busqueda.toLowerCase();
        const matches =
          s.codigo.toLowerCase().includes(q) ||
          s.material.toLowerCase().includes(q) ||
          s.ubicacion.toLowerCase().includes(q) ||
          s.ciudadanoNombre.toLowerCase().includes(q) ||
          s.observaciones.toLowerCase().includes(q);
        if (!matches) return false;
      }

      return true;
    });
  }, [solicitudes, materialFiltro, distanciaFiltro, cantidadFiltro, prioridadFiltro, busqueda]);

  const disponibles = solicitudesFiltradas.filter((s) => s.estado === 'Disponible');

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Title & Stats */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
            <span>Solicitudes Cercanas</span>
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EEF8EE] text-[#087A3D]">
              {disponibles.length} disponibles
            </span>
          </h1>
          <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
            Filtra y acepta oportunidades de recolección según tu ubicación, vehículo y capacidad.
          </p>
        </div>
      </div>

      {/* Filter Section Bar */}
      <div className="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8F0] shadow-xs space-y-4">
        {/* Search Input */}
        <div className="relative">
          <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <SearchListIcon size={18} />
          </div>
          <input
            type="text"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
            placeholder="Buscar por código (#1024), material, barrio o ciudadano..."
            className="w-full pl-10 pr-4 py-2.5 bg-[#FAFCFA] border border-[#E2E8F0] rounded-2xl text-xs sm:text-sm focus:outline-none focus:border-[#087A3D] focus:ring-2 focus:ring-[#087A3D]/20 transition-all"
          />
          {busqueda && (
            <button
              onClick={() => setBusqueda('')}
              className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 hover:text-slate-600"
            >
              Limpiar
            </button>
          )}
        </div>

        {/* Filter Pills / Dropdowns */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
          {/* Material */}
          <div>
            <label className="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">
              Material
            </label>
            <select
              value={materialFiltro}
              onChange={(e) => setMaterialFiltro(e.target.value)}
              className="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#087A3D]"
            >
              {materialesOpciones.map((m) => (
                <option key={m} value={m}>
                  {m === 'Todos' ? 'Todos los materiales' : m}
                </option>
              ))}
            </select>
          </div>

          {/* Distancia */}
          <div>
            <label className="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">
              Distancia
            </label>
            <select
              value={distanciaFiltro}
              onChange={(e) => setDistanciaFiltro(e.target.value)}
              className="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#087A3D]"
            >
              {distanciasOpciones.map((d) => (
                <option key={d} value={d}>
                  {d === 'Todas' ? 'Cualquier distancia' : d}
                </option>
              ))}
            </select>
          </div>

          {/* Cantidad */}
          <div>
            <label className="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">
              Cantidad Aprox.
            </label>
            <select
              value={cantidadFiltro}
              onChange={(e) => setCantidadFiltro(e.target.value)}
              className="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#087A3D]"
            >
              {cantidadesOpciones.map((c) => (
                <option key={c} value={c}>
                  {c === 'Todas' ? 'Cualquier peso' : c}
                </option>
              ))}
            </select>
          </div>

          {/* Prioridad */}
          <div>
            <label className="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">
              Prioridad
            </label>
            <select
              value={prioridadFiltro}
              onChange={(e) => setPrioridadFiltro(e.target.value)}
              className="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#087A3D]"
            >
              <option value="Todas">Todas las prioridades</option>
              <option value="Alta">Alta prioridad</option>
              <option value="Media">Media prioridad</option>
              <option value="Baja">Baja prioridad</option>
            </select>
          </div>
        </div>

        {/* Fast material tags */}
        <div className="flex items-center gap-1.5 overflow-x-auto pb-1 pt-1 scrollbar-none">
          <span className="text-[11px] font-semibold text-slate-400 mr-1 shrink-0">Filtro rápido:</span>
          {materialesOpciones.map((mat) => (
            <button
              key={mat}
              onClick={() => setMaterialFiltro(mat)}
              className={`px-3 py-1 rounded-xl text-xs font-semibold shrink-0 transition-all ${
                materialFiltro === mat
                  ? 'bg-[#087A3D] text-white shadow-xs'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              }`}
            >
              {mat}
            </button>
          ))}
        </div>
      </div>

      {/* Solicitudes Grid */}
      {disponibles.length === 0 ? (
        <div className="bg-white rounded-3xl p-12 text-center border border-[#E2E8F0] space-y-3">
          <div className="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-2xl">
            🔍
          </div>
          <h3 className="font-bold text-base text-slate-800">
            No se encontraron solicitudes con los filtros aplicados
          </h3>
          <p className="text-xs text-slate-500 max-w-sm mx-auto">
            Prueba restableciendo los filtros de distancia, material o peso para ver más resultados.
          </p>
          <button
            onClick={() => {
              setMaterialFiltro('Todos');
              setDistanciaFiltro('Todas');
              setCantidadFiltro('Todas');
              setPrioridadFiltro('Todas');
              setBusqueda('');
            }}
            className="px-4 py-2 bg-[#087A3D] text-white text-xs font-bold rounded-xl hover:bg-[#065C2D] transition-colors"
          >
            Limpiar todos los filtros
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
          {disponibles.map((sol) => (
            <div
              key={sol.id}
              className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs hover:shadow-md transition-all flex flex-col justify-between overflow-hidden group"
            >
              <div>
                {/* Photo Header */}
                <div className="relative h-44 overflow-hidden bg-slate-100">
                  <img
                    src={sol.fotografia}
                    alt={sol.material}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <div className="absolute top-3 left-3 flex items-center gap-1.5">
                    <span className="px-2.5 py-1 bg-black/70 backdrop-blur-xs text-white text-xs font-black rounded-lg">
                      {sol.codigo}
                    </span>
                    <StatusBadge status={sol.prioridad} size="sm" />
                  </div>
                  <div className="absolute bottom-3 right-3">
                    <span className="px-2.5 py-1 bg-white/95 backdrop-blur-xs text-[#087A3D] text-xs font-black rounded-lg shadow-xs">
                      📍 {sol.distanciaMetros} m
                    </span>
                  </div>
                </div>

                {/* Body Content */}
                <div className="p-5 space-y-3">
                  <div className="flex items-start justify-between gap-2">
                    <div>
                      <h3 className="font-extrabold text-lg text-[#1E2922]">{sol.material}</h3>
                      <p className="text-xs text-[#64748B]">
                        📍 {sol.ubicacion} • <span className="font-medium text-slate-700">{sol.direccion}</span>
                      </p>
                    </div>
                    <span className="px-3 py-1 bg-amber-50 text-amber-900 border border-amber-200 text-sm font-black rounded-xl shrink-0">
                      ~{sol.cantidadAproxKg} kg
                    </span>
                  </div>

                  {/* Horario */}
                  <div className="flex items-center gap-2 p-2.5 bg-[#FAFCFA] rounded-xl border border-slate-100 text-xs text-slate-700">
                    <span>🕑</span>
                    <span className="font-semibold">Horario:</span>
                    <span>{sol.horarioDisponible}</span>
                  </div>

                  {/* Observaciones */}
                  <div className="text-xs text-[#64748B] bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span className="font-semibold text-slate-700 block mb-0.5">Nota del ciudadano:</span>
                    <p className="italic line-clamp-2">"{sol.observaciones}"</p>
                  </div>
                </div>
              </div>

              {/* Actions Footer */}
              <div className="p-5 pt-0 grid grid-cols-2 gap-3">
                <button
                  onClick={() => onVerDetalle(sol)}
                  className="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors text-center"
                >
                  Ver detalle
                </button>
                <button
                  onClick={() => onAceptarSolicitud(sol.id)}
                  className="w-full py-2.5 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs rounded-xl transition-colors shadow-xs text-center flex items-center justify-center gap-1.5"
                >
                  <CheckCircleIcon size={16} />
                  <span>Aceptar solicitud</span>
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};
