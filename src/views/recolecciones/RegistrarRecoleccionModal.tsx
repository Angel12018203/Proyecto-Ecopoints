import React, { useState } from 'react';
import { Solicitud } from '@/models/types';
import { Modal } from '../components/Modal';
import { RecycleBagIcon, CheckCircleIcon } from '../components/Icons';

interface RegistrarRecoleccionModalProps {
  solicitud: Solicitud | null;
  isOpen: boolean;
  onClose: () => void;
  onConfirmar: (datos: {
    solicitudId: string;
    cantidadEstimadaKg: number;
    observaciones: string;
    fotografia?: string;
  }) => void;
}

export const RegistrarRecoleccionModal: React.FC<RegistrarRecoleccionModalProps> = ({
  solicitud,
  isOpen,
  onClose,
  onConfirmar,
}) => {
  if (!solicitud) return null;

  const [cantidad, setCantidad] = useState<number>(solicitud.cantidadAproxKg);
  const [observaciones, setObservaciones] = useState<string>(
    'Material verificado y cargado en el vehículo en buen estado de limpieza.'
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onConfirmar({
      solicitudId: solicitud.id,
      cantidadEstimadaKg: Number(cantidad),
      observaciones,
      fotografia: solicitud.fotografia,
    });
    onClose();
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title="Registrar Recolección Realizada"
      subtitle={`Solicitud ${solicitud.codigo} • ${solicitud.material} • ${solicitud.ubicacion}`}
      maxWidth="max-w-lg"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        {/* Solicitud summary header */}
        <div className="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200">
          <img
            src={solicitud.fotografia}
            alt={solicitud.material}
            className="w-14 h-14 rounded-xl object-cover"
          />
          <div>
            <span className="text-xs font-bold text-slate-500">{solicitud.codigo}</span>
            <h4 className="font-extrabold text-sm text-[#1E2922]">{solicitud.material}</h4>
            <p className="text-xs text-slate-500">📍 {solicitud.direccion}</p>
          </div>
        </div>

        {/* Input Peso estimado */}
        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Peso estimado recolectado (kg) *
          </label>
          <div className="relative">
            <input
              type="number"
              step="0.5"
              min="0.5"
              required
              value={cantidad}
              onChange={(e) => setCantidad(Number(e.target.value))}
              className="w-full px-4 py-2.5 bg-[#FAFCFA] border border-slate-300 rounded-xl text-sm font-extrabold text-slate-900 focus:outline-none focus:border-[#087A3D]"
            />
            <span className="absolute right-4 top-2.5 text-sm font-bold text-slate-400">kg</span>
          </div>
          <p className="text-[11px] text-slate-500 mt-1">
            Este valor es preliminar para tu control de carga. El pesaje exacto se realiza en el Centro de Acopio.
          </p>
        </div>

        {/* Observaciones */}
        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Observaciones de la recolección
          </label>
          <textarea
            rows={3}
            value={observaciones}
            onChange={(e) => setObservaciones(e.target.value)}
            className="w-full px-3 py-2 bg-[#FAFCFA] border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-[#087A3D]"
            placeholder="Ej: Material recibido en portería, cartón seco y bien amarrado."
          />
        </div>

        {/* State transition info */}
        <div className="p-3 bg-[#EEF8EE] rounded-xl border border-[#C2E4C9] text-xs text-[#087A3D]">
          <strong>Flujo del sistema:</strong> Al registrar, la solicitud pasará a estado{' '}
          <span className="font-extrabold">"Recolectada"</span> y quedará lista en tu carga para registrar
          la entrega al centro de acopio.
        </div>

        {/* Buttons */}
        <div className="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            className="px-5 py-2.5 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-extrabold rounded-xl transition-colors shadow-md flex items-center gap-1.5"
          >
            <CheckCircleIcon size={16} />
            <span>Confirmar recolección</span>
          </button>
        </div>
      </form>
    </Modal>
  );
};
