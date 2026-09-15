import React from 'react';
import { Solicitud } from '@/models/types';
import { Modal } from '../components/Modal';
import { StatusBadge } from '../components/Badge';
import { CheckCircleIcon, XIcon, MapIcon } from '../components/Icons';

interface DetalleSolicitudModalProps {
  solicitud: Solicitud | null;
  isOpen: boolean;
  onClose: () => void;
  onAceptar: (id: string) => void;
  onRechazar: (id: string) => void;
}

export const DetalleSolicitudModal: React.FC<DetalleSolicitudModalProps> = ({
  solicitud,
  isOpen,
  onClose,
  onAceptar,
  onRechazar,
}) => {
  if (!solicitud) return null;

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={`Detalle de Solicitud ${solicitud.codigo}`}
      subtitle={`Publicado por ${solicitud.ciudadanoNombre} • ${solicitud.fechaCreacion}`}
      maxWidth="max-w-xl"
    >
      <div className="space-y-6">
        {/* Photo with badges */}
        <div className="relative h-56 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200">
          <img
            src={solicitud.fotografia}
            alt={solicitud.material}
            className="w-full h-full object-cover"
          />
          <div className="absolute top-3 left-3 flex items-center gap-2">
            <StatusBadge status={solicitud.estado} />
            <StatusBadge status={solicitud.prioridad} />
          </div>
          <div className="absolute bottom-3 right-3 bg-black/70 backdrop-blur-xs text-white text-xs px-3 py-1 rounded-lg font-bold">
            📍 {solicitud.distanciaMetros} metros de distancia
          </div>
        </div>

        {/* Data specs grid */}
        <div className="grid grid-cols-2 gap-3">
          <div className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
            <span className="text-[11px] font-semibold text-[#64748B] block">Material disponible</span>
            <span className="text-base font-extrabold text-[#1E2922]">{solicitud.material}</span>
          </div>

          <div className="p-3.5 rounded-2xl bg-amber-50 border border-amber-200">
            <span className="text-[11px] font-semibold text-amber-800 block">Cantidad aproximada</span>
            <span className="text-base font-black text-amber-950">~{solicitud.cantidadAproxKg} kg</span>
          </div>

          <div className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
            <span className="text-[11px] font-semibold text-[#64748B] block">Ubicación y Barrio</span>
            <span className="text-sm font-bold text-[#1E2922]">{solicitud.ubicacion}</span>
            <span className="text-xs text-slate-500 block truncate">{solicitud.direccion}</span>
          </div>

          <div className="p-3.5 rounded-2xl bg-[#EEF8EE] border border-[#C2E4C9]">
            <span className="text-[11px] font-semibold text-[#087A3D] block">Horario disponible</span>
            <span className="text-xs sm:text-sm font-bold text-[#087A3D]">
              {solicitud.horarioDisponible}
            </span>
          </div>
        </div>

        {/* Citizen observations */}
        <div className="p-4 rounded-2xl bg-[#FAFCFA] border border-slate-200 space-y-1">
          <span className="text-xs font-bold text-slate-700 flex items-center gap-1.5">
            <span>💬</span>
            <span>Observaciones del ciudadano:</span>
          </span>
          <p className="text-xs sm:text-sm text-slate-600 italic">
            "{solicitud.observaciones}"
          </p>
        </div>

        {/* Action Buttons */}
        <div className="pt-2 grid grid-cols-2 gap-3 border-t border-slate-100">
          <button
            onClick={() => {
              onRechazar(solicitud.id);
              onClose();
            }}
            className="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-xl transition-colors flex items-center justify-center gap-2"
          >
            <XIcon size={16} />
            <span>Rechazar</span>
          </button>

          <button
            onClick={() => {
              onAceptar(solicitud.id);
              onClose();
            }}
            className="w-full py-3 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs sm:text-sm rounded-xl transition-colors shadow-md flex items-center justify-center gap-2"
          >
            <CheckCircleIcon size={18} />
            <span>Aceptar solicitud</span>
          </button>
        </div>
      </div>
    </Modal>
  );
};
