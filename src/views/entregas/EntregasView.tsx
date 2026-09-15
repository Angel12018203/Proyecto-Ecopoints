import React from 'react';
import { Entrega, CentroAcopio } from '@/models/types';
import { StatusBadge } from '../components/Badge';
import { TruckIcon, ScaleIcon, CheckCircleIcon, StarCoinIcon, ArrowRightIcon } from '../components/Icons';

interface EntregasViewProps {
  entregas: Entrega[];
  centrosAcopio: CentroAcopio[];
  onAbrirRegistrarEntrega: () => void;
  onAbrirSimuladorCentro: () => void;
}

export const EntregasView: React.FC<EntregasViewProps> = ({
  entregas,
  centrosAcopio,
  onAbrirRegistrarEntrega,
  onAbrirSimuladorCentro,
}) => {
  const pendientes = entregas.filter((e) => e.estado === 'Pendiente');
  const validadas = entregas.filter((e) => e.estado === 'Validada');

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Header & Main Action */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
            <TruckIcon size={24} className="text-[#087A3D]" />
            <span>Entregas a Centros de Acopio</span>
          </h1>
          <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
            Historial de entregas realizadas, estado de validación y acreditación oficial de Ecopuntos.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={onAbrirSimuladorCentro}
            className="px-3.5 py-2 bg-[#EAF4FA] hover:bg-sky-100 text-[#0284C7] font-bold text-xs rounded-xl transition-colors border border-sky-200 flex items-center gap-1.5"
            title="Abrir báscula del centro para pesar y validar"
          >
            <ScaleIcon size={16} />
            <span>Validar en Báscula</span>
          </button>

          <button
            onClick={onAbrirRegistrarEntrega}
            className="px-4 py-2 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs rounded-xl transition-colors shadow-sm flex items-center gap-1.5"
          >
            <span>+ Registrar Nueva Entrega</span>
          </button>
        </div>
      </div>

      {/* Regla Central Banner */}
      <div className="bg-gradient-to-r from-[#EEF8EE] to-[#EAF4FA] p-5 rounded-3xl border border-[#C2E4C9] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-2xl bg-white text-[#087A3D] flex items-center justify-center text-2xl shadow-xs shrink-0">
            ⚖️
          </div>
          <div>
            <h4 className="font-extrabold text-sm text-[#087A3D]">
              Flujo de Valor: Recolección → Entrega → Pesaje → Validación → Ecopuntos
            </h4>
            <p className="text-xs text-slate-600 mt-0.5">
              Los Ecopuntos no se generan simplemente por aceptar una solicitud, sino al entregar los materiales en el centro aliado y completar la validación técnica en báscula.
            </p>
          </div>
        </div>

        {pendientes.length > 0 && (
          <button
            onClick={onAbrirSimuladorCentro}
            className="px-4 py-2 bg-[#0284C7] hover:bg-sky-700 text-white font-bold text-xs rounded-xl transition-colors shrink-0 shadow-xs flex items-center gap-1.5"
          >
            <span>Pesar entrega pendiente ahora</span>
            <ArrowRightIcon size={14} />
          </button>
        )}
      </div>

      {/* Pendientes vs Validadas */}
      <div className="space-y-4">
        <h2 className="text-base font-bold text-[#1E2922]">
          Entregas Registradas ({entregas.length})
        </h2>

        <div className="grid grid-cols-1 gap-4">
          {entregas.map((ent) => {
            const isValidada = ent.estado === 'Validada';

            return (
              <div
                key={ent.id}
                className={`bg-white rounded-3xl border p-5 sm:p-6 shadow-xs transition-all space-y-4 ${
                  isValidada ? 'border-[#C2E4C9]' : 'border-amber-200'
                }`}
              >
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                  <div className="flex items-center gap-3">
                    <span className="font-black text-sm text-slate-900 bg-slate-100 px-3 py-1 rounded-xl">
                      {ent.codigo}
                    </span>
                    <StatusBadge status={ent.estado} />
                    <span className="text-xs text-slate-400">• {ent.fecha}</span>
                  </div>

                  {isValidada ? (
                    <div className="flex items-center gap-1.5 text-xs font-black text-[#087A3D] bg-[#EEF8EE] px-3 py-1 rounded-full border border-[#C2E4C9]">
                      <StarCoinIcon size={16} className="text-[#F5B82E]" />
                      <span>+{ent.ecopuntosOtorgados} Ecopuntos Acreditados</span>
                    </div>
                  ) : (
                    <button
                      onClick={onAbrirSimuladorCentro}
                      className="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-extrabold rounded-lg transition-colors"
                    >
                      <ScaleIcon size={14} />
                      <span>Realizar pesaje técnico →</span>
                    </button>
                  )}
                </div>

                {/* Details Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                  {/* Centro de Acopio */}
                  <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <span className="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">
                      Centro de Acopio Aliado
                    </span>
                    <strong className="text-sm text-slate-900 block">{ent.centroAcopioNombre}</strong>
                    <span className="text-xs text-slate-500 block">Suba Centro, Bogotá</span>
                  </div>

                  {/* Materiales y Pesos */}
                  <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <span className="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">
                      Desglose de Materiales
                    </span>
                    <div className="flex flex-wrap gap-1.5 mt-1">
                      {ent.materiales.map((m, idx) => (
                        <span
                          key={idx}
                          className="px-2 py-0.5 bg-white border border-slate-200 rounded-md text-xs font-semibold text-slate-700"
                        >
                          {m.material}: {m.cantidadRealKg || m.cantidadEstimadaKg} kg
                        </span>
                      ))}
                    </div>
                  </div>

                  {/* Peso Total */}
                  <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                    <span className="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">
                      {isValidada ? 'Peso Validado en Báscula' : 'Peso Estimado'}
                    </span>
                    <div className="text-xl font-black text-slate-900">
                      {ent.totalValidadoKg || ent.totalEstimadoKg}{' '}
                      <span className="text-sm font-semibold text-slate-500">kg</span>
                    </div>
                  </div>
                </div>

                {/* Observaciones */}
                <div className="text-xs text-slate-500 pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-t border-slate-100">
                  <span>
                    <strong>Observación:</strong>{' '}
                    {ent.observacionesCentro || ent.observacionesReciclador}
                  </span>
                  {isValidada && (
                    <span className="text-emerald-700 font-semibold">
                      ✓ Validado por operador oficial en báscula
                    </span>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};
