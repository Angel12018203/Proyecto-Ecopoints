import React, { useState } from 'react';
import { Reciclador } from '@/models/types';
import { StarCoinIcon, RecycleBagIcon, CheckCircleIcon, UserIcon } from '../components/Icons';

interface PerfilViewProps {
  reciclador: Reciclador;
  onActualizarPerfil: (datos: {
    telefono?: string;
    vehiculo?: string;
    experiencia?: string;
    zonaTrabajo?: string;
  }) => void;
}

export const PerfilView: React.FC<PerfilViewProps> = ({
  reciclador,
  onActualizarPerfil,
}) => {
  const [editando, setEditando] = useState<boolean>(false);
  const [telefono, setTelefono] = useState<string>(reciclador.telefono);
  const [vehiculo, setVehiculo] = useState<string>(reciclador.vehiculo);
  const [experiencia, setExperiencia] = useState<string>(reciclador.experiencia);
  const [zonaTrabajo, setZonaTrabajo] = useState<string>(reciclador.zonaTrabajo);

  const handleGuardar = (e: React.FormEvent) => {
    e.preventDefault();
    onActualizarPerfil({
      telefono,
      vehiculo,
      experiencia,
      zonaTrabajo,
    });
    setEditando(false);
  };

  const logros = [
    { titulo: 'Pionero de Suba', desc: '+1.000 kg recuperados en Suba Centro', icono: '🏆', desbloqueado: true },
    { titulo: 'Maestro del Cartón', desc: '+400 kg de cartón limpio entregado', icono: '📦', desbloqueado: true },
    { titulo: 'Ruta Puntual', desc: '40 recolecciones realizadas a tiempo', icono: '⚡', desbloqueado: true },
    { titulo: 'Embajador Verde', desc: 'Alcanzar 3.000 Ecopuntos oficiales', icono: '👑', desbloqueado: reciclador.ecopuntos >= 3000 },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-200 max-w-5xl">
      {/* Profile Header Banner */}
      <div className="bg-gradient-to-br from-[#087A3D] via-[#108947] to-[#2E9B50] rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div className="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
          {/* Avatar */}
          <div className="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl overflow-hidden border-4 border-white/50 shadow-2xl shrink-0 bg-white/20">
            <img
              src={reciclador.foto}
              alt={reciclador.nombre}
              className="w-full h-full object-cover"
            />
          </div>

          <div className="space-y-2 flex-1">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-xs text-xs font-bold text-white">
              <span>🎖️</span>
              <span>{reciclador.nivel}</span>
            </div>

            <h1 className="text-2xl sm:text-3xl font-black tracking-tight">{reciclador.nombre}</h1>
            <p className="text-xs sm:text-sm text-emerald-100">
              📍 {reciclador.zonaTrabajo} • <strong className="text-white">{reciclador.documento}</strong>
            </p>

            <div className="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-emerald-100">
              <span>
                Ecopuntos: <strong className="text-[#F5B82E] font-black text-sm">{reciclador.ecopuntos.toLocaleString()}</strong>
              </span>
              <span>•</span>
              <span>
                Material validado: <strong className="text-white font-bold">{reciclador.kgRecuperados.toLocaleString()} kg</strong>
              </span>
              <span>•</span>
              <span>
                Recolecciones: <strong className="text-white font-bold">{reciclador.recoleccionesCompletadas}</strong>
              </span>
            </div>
          </div>

          <div>
            <button
              onClick={() => setEditando(!editando)}
              className="px-4 py-2 bg-white text-[#087A3D] font-extrabold text-xs rounded-xl shadow-md hover:bg-emerald-50 transition-colors"
            >
              {editando ? 'Cancelar' : '✏️ Editar Datos'}
            </button>
          </div>
        </div>
      </div>

      {/* Main Grid: Info + Logros */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Basic and Operational Info */}
        <div className="lg:col-span-2 bg-white rounded-3xl border border-[#E2E8F0] shadow-xs p-6 space-y-6">
          <div className="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 className="font-extrabold text-base text-slate-900">
              Información Operativa del Reciclador
            </h3>
            <span className="text-xs text-slate-400">Verificado oficial</span>
          </div>

          {editando ? (
            <form onSubmit={handleGuardar} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Teléfono móvil</label>
                  <input
                    type="text"
                    value={telefono}
                    onChange={(e) => setTelefono(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Zona habitual de trabajo</label>
                  <input
                    type="text"
                    value={zonaTrabajo}
                    onChange={(e) => setZonaTrabajo(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Vehículo / Medio de transporte</label>
                <input
                  type="text"
                  value={vehiculo}
                  onChange={(e) => setVehiculo(e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Experiencia y trayectoria</label>
                <textarea
                  rows={2}
                  value={experiencia}
                  onChange={(e) => setExperiencia(e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setEditando(false)}
                  className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-bold rounded-xl shadow-xs"
                >
                  Guardar cambios
                </button>
              </div>
            </form>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                <span className="text-slate-400 font-bold block text-[10px] uppercase">Documento</span>
                <span className="font-extrabold text-slate-900 text-sm">{reciclador.documento}</span>
              </div>

              <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                <span className="text-slate-400 font-bold block text-[10px] uppercase">Teléfono de contacto</span>
                <span className="font-extrabold text-slate-900 text-sm">{reciclador.telefono}</span>
              </div>

              <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 sm:col-span-2">
                <span className="text-slate-400 font-bold block text-[10px] uppercase">Vehículo de recolección</span>
                <span className="font-bold text-slate-800 text-sm">🚲 {reciclador.vehiculo}</span>
              </div>

              <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 sm:col-span-2">
                <span className="text-slate-400 font-bold block text-[10px] uppercase">Zona de trabajo asignada</span>
                <span className="font-bold text-slate-800 text-sm">📍 {reciclador.zonaTrabajo}</span>
              </div>

              <div className="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 sm:col-span-2">
                <span className="text-slate-400 font-bold block text-[10px] uppercase">Trayectoria</span>
                <p className="text-slate-700 font-medium text-xs mt-0.5">{reciclador.experiencia}</p>
              </div>
            </div>
          )}
        </div>

        {/* Logros y Reconocimientos */}
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs p-6 space-y-4">
          <div className="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 className="font-extrabold text-base text-slate-900">Medallas & Logros</h3>
            <span className="text-xs text-amber-600 font-bold">4 disponibles</span>
          </div>

          <div className="space-y-3">
            {logros.map((logro) => (
              <div
                key={logro.titulo}
                className={`p-3.5 rounded-2xl border flex items-center gap-3 transition-all ${
                  logro.desbloqueado
                    ? 'bg-[#EEF8EE] border-[#C2E4C9]'
                    : 'bg-slate-50 border-slate-200 opacity-60'
                }`}
              >
                <div className="text-2xl shrink-0">{logro.icono}</div>
                <div>
                  <h4 className="font-extrabold text-xs text-slate-900 leading-tight">
                    {logro.titulo}
                  </h4>
                  <p className="text-[11px] text-slate-500 mt-0.5">{logro.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
