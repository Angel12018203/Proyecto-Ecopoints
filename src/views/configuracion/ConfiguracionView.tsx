import React, { useState } from 'react';
import { SettingsIcon, CheckCircleIcon } from '../components/Icons';
import { db } from '@/database/storage';

export const ConfiguracionView: React.FC = () => {
  const [notifPush, setNotifPush] = useState<boolean>(true);
  const [notifAltaPrioridad, setNotifAltaPrioridad] = useState<boolean>(true);
  const [radioDistancia, setRadioDistancia] = useState<number>(3);
  const [guardado, setGuardado] = useState<boolean>(false);

  const handleGuardar = (e: React.FormEvent) => {
    e.preventDefault();
    setGuardado(true);
    setTimeout(() => setGuardado(false), 3000);
  };

  const handleReset = () => {
    if (confirm('¿Restablecer los datos de demostración a su estado inicial?')) {
      db.reset();
      window.location.reload();
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200 max-w-4xl">
      <div>
        <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
          <SettingsIcon size={24} className="text-[#087A3D]" />
          <span>Configuración del Sistema</span>
        </h1>
        <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
          Preferencias de alertas en ruta, radio de búsqueda de solicitudes y almacenamiento.
        </p>
      </div>

      {guardado && (
        <div className="p-3 bg-[#EEF8EE] border border-[#C2E4C9] rounded-2xl text-xs font-bold text-[#087A3D] flex items-center gap-2">
          <CheckCircleIcon size={16} />
          <span>Preferencias actualizadas con éxito.</span>
        </div>
      )}

      <form onSubmit={handleGuardar} className="space-y-6">
        {/* Alertas de Ruta */}
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs p-6 space-y-4">
          <h3 className="font-extrabold text-base text-slate-900">Notificaciones y Alertas en Ruta</h3>

          <div className="space-y-3">
            <label className="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl cursor-pointer hover:bg-slate-100 transition-colors">
              <div>
                <span className="font-bold text-xs sm:text-sm text-slate-800 block">
                  Alertas de nuevas solicitudes cercanas
                </span>
                <span className="text-xs text-slate-500">
                  Avisar con sonido cuando un ciudadano publique una solicitud a menos de 1 km
                </span>
              </div>
              <input
                type="checkbox"
                checked={notifPush}
                onChange={(e) => setNotifPush(e.target.checked)}
                className="w-5 h-5 accent-[#087A3D]"
              />
            </label>

            <label className="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl cursor-pointer hover:bg-slate-100 transition-colors">
              <div>
                <span className="font-bold text-xs sm:text-sm text-slate-800 block">
                  Prioridad Alta inmediata
                </span>
                <span className="text-xs text-slate-500">
                  Notificar en pantalla bloqueada solicitudes con urgencia de retiro
                </span>
              </div>
              <input
                type="checkbox"
                checked={notifAltaPrioridad}
                onChange={(e) => setNotifAltaPrioridad(e.target.checked)}
                className="w-5 h-5 accent-[#087A3D]"
              />
            </label>
          </div>
        </div>

        {/* Radio Geográfico */}
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs p-6 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-extrabold text-base text-slate-900">Radio de Búsqueda Geográfica</h3>
            <span className="text-sm font-black text-[#087A3D]">{radioDistancia} km</span>
          </div>

          <p className="text-xs text-slate-500">
            Ajusta la distancia máxima en la que deseas recibir solicitudes dentro de la localidad de Suba.
          </p>

          <input
            type="range"
            min="1"
            max="10"
            step="0.5"
            value={radioDistancia}
            onChange={(e) => setRadioDistancia(Number(e.target.value))}
            className="w-full accent-[#087A3D] cursor-pointer"
          />
          <div className="flex justify-between text-[11px] text-slate-400 font-bold">
            <span>1 km (Caminata / Carreta)</span>
            <span>5 km (Bicicarro)</span>
            <span>10 km (Vehículo motorizado)</span>
          </div>
        </div>

        {/* Base de Datos Local */}
        <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs p-6 space-y-4">
          <h3 className="font-extrabold text-base text-slate-900">Gestión de Datos</h3>
          <p className="text-xs text-slate-500">
            Ecopoints almacena tu sesión y registros localmente. Puedes restablecer los datos de demostración en cualquier momento.
          </p>

          <button
            type="button"
            onClick={handleReset}
            className="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-xl border border-red-200 transition-colors"
          >
            Restablecer datos semilla (Carlos Rodríguez, Suba)
          </button>
        </div>

        <div className="flex justify-end">
          <button
            type="submit"
            className="px-6 py-3 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md transition-colors"
          >
            Guardar preferencias
          </button>
        </div>
      </form>
    </div>
  );
};
