import React, { useState } from 'react';
import { Recoleccion, Entrega, MovimientoEcopunto, Canje } from '@/models/types';
import { StatusBadge } from '../components/Badge';
import { HistoryClockIcon, StarCoinIcon, TruckIcon, RecycleBagIcon, GiftBoxIcon } from '../components/Icons';

interface HistorialViewProps {
  recolecciones: Recoleccion[];
  entregas: Entrega[];
  movimientos: MovimientoEcopunto[];
  canjes: Canje[];
}

export const HistorialView: React.FC<HistorialViewProps> = ({
  recolecciones,
  entregas,
  movimientos,
  canjes,
}) => {
  const [activeTab, setActiveTab] = useState<'recolecciones' | 'entregas' | 'ecopuntos' | 'beneficios'>('recolecciones');

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Title */}
      <div>
        <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
          <HistoryClockIcon size={24} className="text-[#087A3D]" />
          <span>Historial General de Actividad</span>
        </h1>
        <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
          Registro cronológico auditado de recolecciones en campo, entregas a centros, ecopuntos y canjes.
        </p>
      </div>

      {/* Tabs */}
      <div className="flex items-center gap-2 border-b border-[#E2E8F0] pb-2 overflow-x-auto scrollbar-none">
        {[
          { id: 'recolecciones', label: 'Recolecciones en Ruta', icon: RecycleBagIcon, count: recolecciones.length },
          { id: 'entregas', label: 'Entregas a Centros', icon: TruckIcon, count: entregas.length },
          { id: 'ecopuntos', label: 'Ecopuntos', icon: StarCoinIcon, count: movimientos.length },
          { id: 'beneficios', label: 'Canjes de Beneficios', icon: GiftBoxIcon, count: canjes.length },
        ].map((t) => {
          const Icon = t.icon;
          const isActive = activeTab === t.id;

          return (
            <button
              key={t.id}
              onClick={() => setActiveTab(t.id as typeof activeTab)}
              className={`px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition-all shrink-0 flex items-center gap-2 ${
                isActive
                  ? 'bg-[#EEF8EE] text-[#087A3D] shadow-xs'
                  : 'text-slate-600 hover:bg-slate-50'
              }`}
            >
              <Icon size={16} />
              <span>{t.label}</span>
              <span className={`px-2 py-0.5 rounded-full text-[10px] ${isActive ? 'bg-[#087A3D] text-white' : 'bg-slate-100 text-slate-600'}`}>
                {t.count}
              </span>
            </button>
          );
        })}
      </div>

      {/* Tab: Recolecciones */}
      {activeTab === 'recolecciones' && (
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs overflow-hidden">
          <div className="p-4 sm:p-5 border-b border-slate-100 font-extrabold text-sm text-slate-800">
            Registro de Recolecciones Realizadas
          </div>
          <div className="divide-y divide-slate-100">
            {recolecciones.map((rec) => (
              <div key={rec.id} className="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-[#EEF8EE] text-[#087A3D] flex items-center justify-center text-lg font-bold">
                    📦
                  </div>
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="font-extrabold text-sm text-slate-900">{rec.materialRecogido}</span>
                      <span className="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                        {rec.solicitudCodigo}
                      </span>
                      <StatusBadge status="Recolectada" size="sm" />
                    </div>
                    <p className="text-xs text-slate-500 mt-0.5">{rec.observaciones}</p>
                  </div>
                </div>

                <div className="text-right">
                  <div className="text-sm sm:text-base font-black text-slate-900">
                    ~{rec.cantidadEstimadaKg} kg
                  </div>
                  <span className="text-[11px] text-slate-400">{rec.fecha}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Tab: Entregas */}
      {activeTab === 'entregas' && (
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs overflow-hidden">
          <div className="p-4 sm:p-5 border-b border-slate-100 font-extrabold text-sm text-slate-800">
            Registro de Entregas al Centro de Acopio
          </div>
          <div className="divide-y divide-slate-100">
            {entregas.map((ent) => (
              <div key={ent.id} className="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center text-lg font-bold">
                    🏢
                  </div>
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="font-extrabold text-sm text-slate-900">{ent.centroAcopioNombre}</span>
                      <span className="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                        {ent.codigo}
                      </span>
                      <StatusBadge status={ent.estado} size="sm" />
                    </div>
                    <div className="text-xs text-slate-500 mt-0.5">
                      {ent.materiales.map((m) => `${m.material}: ${m.cantidadRealKg || m.cantidadEstimadaKg}kg`).join(' • ')}
                    </div>
                  </div>
                </div>

                <div className="sm:text-right">
                  <div className="text-sm sm:text-base font-black text-slate-900">
                    {ent.totalValidadoKg || ent.totalEstimadoKg} kg
                  </div>
                  {ent.ecopuntosOtorgados ? (
                    <span className="text-xs font-bold text-[#087A3D]">+{ent.ecopuntosOtorgados} pts</span>
                  ) : (
                    <span className="text-xs font-bold text-amber-600">Por validar</span>
                  )}
                  <span className="text-[11px] text-slate-400 block">{ent.fecha}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Tab: Ecopuntos */}
      {activeTab === 'ecopuntos' && (
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs overflow-hidden">
          <div className="p-4 sm:p-5 border-b border-slate-100 font-extrabold text-sm text-slate-800">
            Libro de Movimientos de Ecopuntos
          </div>
          <div className="divide-y divide-slate-100">
            {movimientos.map((mov) => {
              const esIngreso = mov.puntos > 0;
              return (
                <div key={mov.id} className="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className={`w-9 h-9 rounded-xl flex items-center justify-center font-bold ${esIngreso ? 'bg-[#EEF8EE] text-[#087A3D]' : 'bg-amber-50 text-amber-800'}`}>
                      {esIngreso ? '⭐' : '🎁'}
                    </div>
                    <div>
                      <div className="font-bold text-sm text-slate-900">{mov.concepto}</div>
                      <span className="text-xs text-slate-400">{mov.fecha}</span>
                    </div>
                  </div>
                  <div className={`text-base font-black ${esIngreso ? 'text-[#087A3D]' : 'text-slate-700'}`}>
                    {esIngreso ? `+${mov.puntos}` : mov.puntos} pts
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {/* Tab: Beneficios canjeados */}
      {activeTab === 'beneficios' && (
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs overflow-hidden">
          <div className="p-4 sm:p-5 border-b border-slate-100 font-extrabold text-sm text-slate-800">
            Beneficios Canjeados y Cupones
          </div>
          <div className="divide-y divide-slate-100">
            {canjes.map((c) => (
              <div key={c.id} className="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                <div className="flex items-center gap-3">
                  <img src={c.beneficioImagen} alt={c.beneficioNombre} className="w-12 h-12 rounded-xl object-cover" />
                  <div>
                    <div className="flex items-center gap-2">
                      <span className="font-extrabold text-sm text-slate-900">{c.beneficioNombre}</span>
                      <StatusBadge status={c.estado} size="sm" />
                    </div>
                    <div className="text-xs text-slate-400 mt-0.5">
                      Canjeado el {c.fecha} • {c.vencimiento}
                    </div>
                  </div>
                </div>

                <div className="sm:text-right">
                  <span className="px-3 py-1 bg-slate-900 text-[#F5B82E] font-black text-xs rounded-xl tracking-wider inline-block">
                    {c.codigoCanje}
                  </span>
                  <div className="text-[11px] font-bold text-slate-500 mt-1">
                    -{c.costoEcopuntos} pts
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};
