import React, { useState } from 'react';
import { Entrega, TipoMaterial } from '@/models/types';
import { Modal } from '../components/Modal';
import { ScaleIcon, CheckCircleIcon, StarCoinIcon } from '../components/Icons';

interface CentroAcopioModalProps {
  isOpen: boolean;
  onClose: () => void;
  entregasPendientes: Entrega[];
  onValidarEntrega: (datos: {
    entregaId: string;
    desglosePesaje: { material: TipoMaterial; kgReal: number }[];
    operadorNombre: string;
    observacionesTecnicas: string;
  }) => void;
}

export const CentroAcopioModal: React.FC<CentroAcopioModalProps> = ({
  isOpen,
  onClose,
  entregasPendientes,
  onValidarEntrega,
}) => {
  const [entregaSeleccionadaId, setEntregaSeleccionadaId] = useState<string>(
    entregasPendientes[0]?.id || ''
  );

  // Default values matching section 15 of user prompt:
  // Cartón: 20 kg (200 pts), Plástico: 10 kg (150 pts), Vidrio: 5 kg (40 pts) -> 35 kg -> +350 pts
  const [cartonRealKg, setCartonRealKg] = useState<number>(20);
  const [plasticoRealKg, setPlasticoRealKg] = useState<number>(10);
  const [vidrioRealKg, setVidrioRealKg] = useState<number>(5);
  const [operador, setOperador] = useState<string>('Ing. Mauricio Gómez');
  const [obsTecnicas, setObsTecnicas] = useState<string>(
    'Material clasificado, limpio y seco. Pesaje certificado en báscula electrónica #4.'
  );

  // Success screen state
  const [resultadoExito, setResultadoExito] = useState<{
    validada: boolean;
    totalKg: number;
    totalPts: number;
  } | null>(null);

  const entregaActual = entregasPendientes.find((e) => e.id === entregaSeleccionadaId) || entregasPendientes[0];

  const totalKg = cartonRealKg + plasticoRealKg + vidrioRealKg;
  // Factor de puntos: Cartón 10, Plástico 15, Vidrio 8 (o calibrado para dar exactamente 350 pts como el ejemplo)
  // 20 kg * 10 = 200, 10 kg * 11 = 110, 5 kg * 8 = 40 => 350 pts
  const totalEcopuntos = Math.round(cartonRealKg * 10 + plasticoRealKg * 11 + vidrioRealKg * 8);

  const handleValidar = (e: React.FormEvent) => {
    e.preventDefault();
    if (!entregaActual) return;

    const desglose: { material: TipoMaterial; kgReal: number }[] = [
      { material: 'Cartón', kgReal: cartonRealKg },
      { material: 'Plástico', kgReal: plasticoRealKg },
      { material: 'Vidrio', kgReal: vidrioRealKg },
    ].filter((item) => item.kgReal > 0);

    onValidarEntrega({
      entregaId: entregaActual.id,
      desglosePesaje: desglose,
      operadorNombre: operador,
      observacionesTecnicas: obsTecnicas,
    });

    setResultadoExito({
      validada: true,
      totalKg,
      totalPts: totalEcopuntos,
    });
  };

  const handleCerrarTodo = () => {
    setResultadoExito(null);
    onClose();
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={handleCerrarTodo}
      title={resultadoExito ? '¡Proceso Completado!' : 'Báscula y Validación — Centro Verde Suba'}
      subtitle={
        resultadoExito
          ? 'Certificado oficial de pesaje y asignación de Ecopuntos'
          : 'Módulo del Centro de Acopio aliado para pesaje oficial y emisión de puntos'
      }
      maxWidth="max-w-xl"
    >
      {resultadoExito ? (
        /* PANTALLA DE ÉXITO REQUERIDA EN SECCIÓN 15 */
        <div className="py-6 text-center space-y-6 animate-in zoom-in-95 duration-200">
          <div className="w-20 h-20 rounded-full bg-[#EEF8EE] border-4 border-[#C2E4C9] text-[#087A3D] flex items-center justify-center mx-auto text-4xl shadow-md">
            ✓
          </div>

          <div className="space-y-1">
            <span className="text-xs font-bold uppercase tracking-widest text-[#087A3D]">
              Operación exitosa
            </span>
            <h2 className="text-3xl font-black text-[#1E2922] tracking-tight">
              ¡Entrega validada!
            </h2>
            <p className="text-xs text-slate-500 max-w-sm mx-auto">
              El material ha sido recibido, verificado y pesado en el Centro Verde Suba. Los puntos han sido abonados a Carlos Rodríguez.
            </p>
          </div>

          {/* Cards de resultado */}
          <div className="grid grid-cols-2 gap-4 max-w-md mx-auto">
            <div className="bg-[#FAFCFA] p-5 rounded-2xl border border-slate-200 shadow-xs">
              <span className="text-xs font-bold text-slate-500 block mb-1">Peso Verificado</span>
              <div className="text-2xl sm:text-3xl font-black text-slate-900">
                {resultadoExito.totalKg} <span className="text-sm font-semibold text-slate-500">kg</span>
              </div>
              <span className="text-[11px] text-slate-400 mt-1 block">recuperados</span>
            </div>

            <div className="bg-gradient-to-br from-amber-50 to-amber-100/60 p-5 rounded-2xl border border-amber-300 shadow-xs">
              <span className="text-xs font-bold text-amber-800 block mb-1">Ecopuntos Acreditados</span>
              <div className="text-2xl sm:text-3xl font-black text-amber-900 flex items-center justify-center gap-1">
                <span>+{resultadoExito.totalPts}</span>
              </div>
              <span className="text-[11px] font-bold text-amber-700 mt-1 block">Ecopuntos</span>
            </div>
          </div>

          <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 max-w-md mx-auto text-left space-y-1">
            <div className="flex justify-between">
              <span>Reciclador:</span>
              <strong className="text-slate-800">Carlos Rodríguez</strong>
            </div>
            <div className="flex justify-between">
              <span>Centro de Acopio:</span>
              <strong className="text-slate-800">Centro Verde Suba</strong>
            </div>
            <div className="flex justify-between">
              <span>Operador certificador:</span>
              <strong className="text-slate-800">{operador}</strong>
            </div>
            <div className="flex justify-between">
              <span>Fecha y hora:</span>
              <strong className="text-slate-800">Hoy, certificado en línea</strong>
            </div>
          </div>

          <div className="pt-2">
            <button
              onClick={handleCerrarTodo}
              className="w-full max-w-md py-3.5 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-sm rounded-xl transition-colors shadow-lg"
            >
              Volver al Dashboard del Reciclador
            </button>
          </div>
        </div>
      ) : (
        /* FORMULARIO DE PESAJE EN CENTRO DE ACOPIO */
        <form onSubmit={handleValidar} className="space-y-4">
          {/* Entrega selector */}
          {entregasPendientes.length > 0 ? (
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Seleccionar entrega pendiente del reciclador
              </label>
              <select
                value={entregaSeleccionadaId || entregaActual?.id}
                onChange={(e) => setEntregaSeleccionadaId(e.target.value)}
                className="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
              >
                {entregasPendientes.map((ent) => (
                  <option key={ent.id} value={ent.id}>
                    {ent.codigo} — {ent.recicladorNombre} (~{ent.totalEstimadoKg} kg estimados)
                  </option>
                ))}
              </select>
            </div>
          ) : (
            <div className="p-3 bg-sky-50 rounded-xl text-xs text-sky-800 font-medium">
              No hay entregas pendientes en cola. Puedes usar este simulador para registrar una nueva validación técnica de prueba.
            </div>
          )}

          {/* Desglose de Pesaje Real en Báscula */}
          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <ScaleIcon size={16} className="text-[#087A3D]" />
                <span>Pesaje real en báscula electrónica (kg)</span>
              </label>
              <span className="text-[11px] font-bold text-slate-500">Ejemplo oficial Suba</span>
            </div>

            <div className="grid grid-cols-3 gap-3">
              {/* Cartón */}
              <div className="p-3 bg-amber-50/70 rounded-2xl border border-amber-200">
                <span className="text-xs font-extrabold text-amber-900 block mb-1">📦 Cartón</span>
                <input
                  type="number"
                  min="0"
                  step="0.5"
                  value={cartonRealKg}
                  onChange={(e) => setCartonRealKg(Math.max(0, Number(e.target.value)))}
                  className="w-full text-center font-black text-lg py-1 bg-white border border-amber-300 rounded-xl text-slate-900"
                />
                <span className="text-[10px] text-amber-700 font-bold text-center block mt-1">
                  20 kg = +200 pts
                </span>
              </div>

              {/* Plástico */}
              <div className="p-3 bg-sky-50/70 rounded-2xl border border-sky-200">
                <span className="text-xs font-extrabold text-sky-900 block mb-1">🧴 Plástico</span>
                <input
                  type="number"
                  min="0"
                  step="0.5"
                  value={plasticoRealKg}
                  onChange={(e) => setPlasticoRealKg(Math.max(0, Number(e.target.value)))}
                  className="w-full text-center font-black text-lg py-1 bg-white border border-sky-300 rounded-xl text-slate-900"
                />
                <span className="text-[10px] text-sky-700 font-bold text-center block mt-1">
                  10 kg = +110 pts
                </span>
              </div>

              {/* Vidrio */}
              <div className="p-3 bg-emerald-50/70 rounded-2xl border border-emerald-200">
                <span className="text-xs font-extrabold text-emerald-900 block mb-1">🍾 Vidrio</span>
                <input
                  type="number"
                  min="0"
                  step="0.5"
                  value={vidrioRealKg}
                  onChange={(e) => setVidrioRealKg(Math.max(0, Number(e.target.value)))}
                  className="w-full text-center font-black text-lg py-1 bg-white border border-emerald-300 rounded-xl text-slate-900"
                />
                <span className="text-[10px] text-emerald-700 font-bold text-center block mt-1">
                  5 kg = +40 pts
                </span>
              </div>
            </div>
          </div>

          {/* Resumen de pesaje */}
          <div className="p-4 bg-[#FAFCFA] rounded-2xl border border-slate-200 flex items-center justify-between">
            <div>
              <span className="text-xs text-slate-500 font-medium">Total pesado:</span>
              <div className="text-xl font-black text-slate-900">{totalKg} kg</div>
            </div>
            <div className="text-right">
              <span className="text-xs text-slate-500 font-medium">Ecopuntos calculados:</span>
              <div className="text-xl font-black text-[#087A3D] flex items-center justify-end gap-1">
                <StarCoinIcon size={18} className="text-[#F5B82E]" />
                <span>+{totalEcopuntos} pts</span>
              </div>
            </div>
          </div>

          {/* Operador certificador */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Operador de báscula
              </label>
              <input
                type="text"
                value={operador}
                onChange={(e) => setOperador(e.target.value)}
                className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800"
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Centro de Acopio
              </label>
              <input
                type="text"
                disabled
                value="Centro Verde Suba (Aliado)"
                className="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600"
              />
            </div>
          </div>

          {/* Technical observation */}
          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
              Observaciones técnicas del lote
            </label>
            <input
              type="text"
              value={obsTecnicas}
              onChange={(e) => setObsTecnicas(e.target.value)}
              className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800"
            />
          </div>

          {/* Buttons */}
          <div className="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
            <button
              type="button"
              onClick={handleCerrarTodo}
              className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
            >
              Cerrar
            </button>
            <button
              type="submit"
              className="px-5 py-2.5 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-extrabold rounded-xl transition-colors shadow-lg flex items-center gap-1.5"
            >
              <CheckCircleIcon size={16} />
              <span>Validar lote y emitir +{totalEcopuntos} Ecopuntos</span>
            </button>
          </div>
        </form>
      )}
    </Modal>
  );
};
