import React, { useState } from 'react';
import { CentroAcopio, TipoMaterial } from '@/models/types';
import { Modal } from '../components/Modal';
import { TruckIcon, CheckCircleIcon } from '../components/Icons';

interface RegistrarEntregaModalProps {
  centrosAcopio: CentroAcopio[];
  isOpen: boolean;
  onClose: () => void;
  onConfirmar: (datos: {
    centroAcopioId: string;
    materiales: { material: TipoMaterial; cantidadEstimadaKg: number }[];
    observaciones?: string;
  }) => void;
}

export const RegistrarEntregaModal: React.FC<RegistrarEntregaModalProps> = ({
  centrosAcopio,
  isOpen,
  onClose,
  onConfirmar,
}) => {
  const [centroId, setCentroId] = useState<string>(centrosAcopio[0]?.id || '');
  const [cartonKg, setCartonKg] = useState<number>(20);
  const [plasticoKg, setPlasticoKg] = useState<number>(10);
  const [vidrioKg, setVidrioKg] = useState<number>(5);
  const [observaciones, setObservaciones] = useState<string>(
    'Material recolectado durante la jornada matutina en cuadrante Suba.'
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const materiales: { material: TipoMaterial; cantidadEstimadaKg: number }[] = [];
    if (cartonKg > 0) materiales.push({ material: 'Cartón', cantidadEstimadaKg: Number(cartonKg) });
    if (plasticoKg > 0) materiales.push({ material: 'Plástico', cantidadEstimadaKg: Number(plasticoKg) });
    if (vidrioKg > 0) materiales.push({ material: 'Vidrio', cantidadEstimadaKg: Number(vidrioKg) });

    if (!materiales.length) {
      alert('Ingresa al menos una cantidad para un material.');
      return;
    }

    onConfirmar({
      centroAcopioId: centroId,
      materiales,
      observaciones,
    });
    onClose();
  };

  const totalEstimado = cartonKg + plasticoKg + vidrioKg;

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title="Registrar Entrega al Centro de Acopio"
      subtitle="Entrega de carga para pesaje oficial en báscula electrónica certificada"
      maxWidth="max-w-xl"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        {/* Selector de Centro de Acopio aliado */}
        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Centro de acopio aliado *
          </label>
          <select
            value={centroId}
            onChange={(e) => setCentroId(e.target.value)}
            className="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:border-[#087A3D]"
          >
            {centrosAcopio.map((c) => (
              <option key={c.id} value={c.id}>
                {c.nombre} — {c.direccion} ({c.horario})
              </option>
            ))}
          </select>
        </div>

        {/* Carga desglosada por material */}
        <div className="space-y-2">
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider">
            Materiales y cantidades estimadas a entregar (kg)
          </label>

          <div className="grid grid-cols-3 gap-3">
            {/* Cartón */}
            <div className="p-3 bg-slate-50 rounded-2xl border border-slate-200">
              <span className="text-xs font-bold text-amber-800 flex items-center gap-1 mb-1">
                <span>📦</span>
                <span>Cartón</span>
              </span>
              <input
                type="number"
                min="0"
                step="1"
                value={cartonKg}
                onChange={(e) => setCartonKg(Math.max(0, Number(e.target.value)))}
                className="w-full text-center font-black text-base py-1 bg-white border border-slate-200 rounded-lg text-slate-900"
              />
              <span className="text-[10px] text-slate-400 text-center block mt-1">10 pts/kg</span>
            </div>

            {/* Plástico */}
            <div className="p-3 bg-slate-50 rounded-2xl border border-slate-200">
              <span className="text-xs font-bold text-sky-800 flex items-center gap-1 mb-1">
                <span>🧴</span>
                <span>Plástico</span>
              </span>
              <input
                type="number"
                min="0"
                step="1"
                value={plasticoKg}
                onChange={(e) => setPlasticoKg(Math.max(0, Number(e.target.value)))}
                className="w-full text-center font-black text-base py-1 bg-white border border-slate-200 rounded-lg text-slate-900"
              />
              <span className="text-[10px] text-slate-400 text-center block mt-1">15 pts/kg</span>
            </div>

            {/* Vidrio */}
            <div className="p-3 bg-slate-50 rounded-2xl border border-slate-200">
              <span className="text-xs font-bold text-emerald-800 flex items-center gap-1 mb-1">
                <span>🍾</span>
                <span>Vidrio</span>
              </span>
              <input
                type="number"
                min="0"
                step="1"
                value={vidrioKg}
                onChange={(e) => setVidrioKg(Math.max(0, Number(e.target.value)))}
                className="w-full text-center font-black text-base py-1 bg-white border border-slate-200 rounded-lg text-slate-900"
              />
              <span className="text-[10px] text-slate-400 text-center block mt-1">8 pts/kg</span>
            </div>
          </div>

          <div className="text-right text-xs font-bold text-slate-700 pt-1">
            Total estimado a entregar: <span className="text-[#087A3D] text-sm font-black">{totalEstimado} kg</span>
          </div>
        </div>

        {/* Observaciones */}
        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Notas del reciclador para la recepción
          </label>
          <input
            type="text"
            value={observaciones}
            onChange={(e) => setObservaciones(e.target.value)}
            className="w-full px-3 py-2 bg-[#FAFCFA] border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-800"
          />
        </div>

        {/* Regla Fundamental Banner */}
        <div className="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
          <strong>Regla de Ecopoints:</strong> La entrega quedará en estado{' '}
          <span className="font-extrabold text-amber-800">"Pendiente de validación"</span>. Los Ecopuntos se
          acreditarán en tu cuenta una vez el operador del centro realice el pesaje técnico y valide el lote.
        </div>

        {/* Buttons */}
        <div className="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            className="px-5 py-2.5 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-extrabold rounded-xl transition-colors shadow-md flex items-center gap-1.5"
          >
            <TruckIcon size={16} />
            <span>Registrar entrega</span>
          </button>
        </div>
      </form>
    </Modal>
  );
};
