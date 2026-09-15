import React from 'react';
import { BellIcon, StarCoinIcon, UserIcon, ScaleIcon, PlusIcon } from './Icons';
import { Reciclador } from '@/models/types';
import { NavSection } from './Sidebar';

interface NavbarProps {
  onToggleSidebar: () => void;
  reciclador: Reciclador;
  onNavigate: (section: NavSection) => void;
  onAbrirSimuladorCentro: () => void;
  onAbrirModalCiudadano: () => void;
}

export const Navbar: React.FC<NavbarProps> = ({
  onToggleSidebar,
  reciclador,
  onNavigate,
  onAbrirSimuladorCentro,
  onAbrirModalCiudadano,
}) => {
  return (
    <header className="sticky top-0 z-30 h-16 bg-white/90 backdrop-blur-md border-b border-[#E2E8F0] px-4 sm:px-8 flex items-center justify-between">
      <div className="flex items-center gap-3">
        {/* Mobile menu trigger */}
        <button
          onClick={onToggleSidebar}
          className="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors"
          aria-label="Abrir menú"
        >
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="6" x2="21" y2="6" />
            <line x1="3" y1="18" x2="21" y2="18" />
          </svg>
        </button>

        {/* Dynamic header title */}
        <div>
          <div className="flex items-center gap-2">
            <h1 className="text-sm sm:text-base font-bold text-[#1E2922] flex items-center gap-1.5">
              <span>Hola, Carlos</span>
              <span className="animate-bounce-short">👋</span>
            </h1>
            <span className="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#EEF8EE] text-[#087A3D] border border-[#C2E4C9]">
              {reciclador.nivel}
            </span>
          </div>
          <p className="hidden md:block text-xs text-[#64748B]">
            Zona asignada: <strong className="text-slate-700">{reciclador.zonaTrabajo}</strong>
          </p>
        </div>
      </div>

      {/* Right controls */}
      <div className="flex items-center gap-2 sm:gap-3">
        {/* Quick simulator button */}
        <button
          onClick={onAbrirSimuladorCentro}
          className="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#EAF4FA] hover:bg-sky-100 text-[#0284C7] rounded-xl text-xs font-semibold border border-sky-200 transition-colors"
          title="Abrir báscula y pesaje en Centro de Acopio para validar entregas y calcular Ecopuntos"
        >
          <ScaleIcon size={15} />
          <span>Báscula Centro</span>
        </button>

        {/* Post citizen request */}
        <button
          onClick={onAbrirModalCiudadano}
          className="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#EEF8EE] hover:bg-emerald-100 text-[#087A3D] rounded-xl text-xs font-semibold border border-emerald-200 transition-colors"
          title="Simular publicación de solicitud desde la perspectiva del ciudadano"
        >
          <PlusIcon size={15} />
          <span>+ Solicitud</span>
        </button>

        {/* Ecopuntos Pill */}
        <button
          onClick={() => onNavigate('ecopuntos')}
          className="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 rounded-xl border border-amber-200 transition-colors"
        >
          <StarCoinIcon size={16} className="text-[#F5B82E]" />
          <span className="text-xs sm:text-sm font-black">{reciclador.ecopuntos.toLocaleString()}</span>
          <span className="text-[10px] font-semibold text-amber-700 uppercase">pts</span>
        </button>

        {/* Notification Bell */}
        <button className="relative p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-colors">
          <BellIcon size={18} />
          <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500" />
        </button>

        {/* User avatar / profile button */}
        <button
          onClick={() => onNavigate('perfil')}
          className="flex items-center gap-2 pl-2 border-l border-[#E2E8F0] hover:opacity-90 transition-opacity"
        >
          <img
            src={reciclador.foto}
            alt={reciclador.nombre}
            className="w-8 h-8 rounded-xl object-cover border border-emerald-200 shadow-xs"
          />
        </button>
      </div>
    </header>
  );
};
