import React from 'react';
import {
  LeafLogo,
  HomeIcon,
  SearchListIcon,
  MapIcon,
  RecycleBagIcon,
  TruckIcon,
  StarCoinIcon,
  GiftBoxIcon,
  ChartIcon,
  HistoryClockIcon,
  UserIcon,
  SettingsIcon,
  HelpIcon,
  ScaleIcon,
  PlusIcon,
  XIcon,
} from './Icons';
import { RolUsuario } from '@/models/types';

export type NavSection =
  | 'inicio'
  | 'solicitudes'
  | 'mapa'
  | 'recolecciones'
  | 'entregas'
  | 'ecopuntos'
  | 'beneficios'
  | 'estadisticas'
  | 'historial'
  | 'perfil'
  | 'configuracion'
  | 'ayuda';

interface SidebarProps {
  activeSection: NavSection;
  onSelectSection: (section: NavSection) => void;
  isOpen: boolean;
  onClose: () => void;
  rolActivo: RolUsuario;
  onCambiarRol: (rol: RolUsuario) => void;
  onAbrirSimuladorCentro: () => void;
  onAbrirModalCiudadano: () => void;
  ecopuntos: number;
  solicitudesCount: number;
  entregasPendientesCount: number;
}

export const Sidebar: React.FC<SidebarProps> = ({
  activeSection,
  onSelectSection,
  isOpen,
  onClose,
  rolActivo,
  onCambiarRol,
  onAbrirSimuladorCentro,
  onAbrirModalCiudadano,
  ecopuntos,
  solicitudesCount,
  entregasPendientesCount,
}) => {
  const navItems = [
    { id: 'inicio', label: 'Inicio', icon: HomeIcon },
    {
      id: 'solicitudes',
      label: 'Solicitudes cercanas',
      icon: SearchListIcon,
      badge: solicitudesCount > 0 ? `${solicitudesCount}` : undefined,
    },
    { id: 'mapa', label: 'Mapa', icon: MapIcon },
    { id: 'recolecciones', label: 'Mis recolecciones', icon: RecycleBagIcon },
    {
      id: 'entregas',
      label: 'Entregas',
      icon: TruckIcon,
      badge: entregasPendientesCount > 0 ? `${entregasPendientesCount} pend.` : undefined,
    },
    { id: 'ecopuntos', label: 'Ecopuntos', icon: StarCoinIcon, highlight: true },
    { id: 'beneficios', label: 'Beneficios', icon: GiftBoxIcon },
    { id: 'estadisticas', label: 'Estadísticas', icon: ChartIcon },
    { id: 'historial', label: 'Historial', icon: HistoryClockIcon },
    { id: 'perfil', label: 'Perfil', icon: UserIcon },
  ] as const;

  return (
    <>
      {/* Mobile backdrop */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black/40 z-40 lg:hidden backdrop-blur-xs"
          onClick={onClose}
        />
      )}

      {/* Main Sidebar */}
      <aside
        className={`fixed top-0 bottom-0 left-0 z-40 w-64 bg-white border-r border-[#E2E8F0] flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        {/* Brand Header */}
        <div className="h-20 flex items-center justify-between px-6 border-b border-[#E2E8F0]">
          <div className="flex items-center gap-3">
            <LeafLogo size={36} />
            <div>
              <div className="flex items-center gap-1.5">
                <span className="font-extrabold text-xl tracking-tight text-[#087A3D]">ECO</span>
                <span className="font-extrabold text-xl tracking-tight text-[#F5B82E]">POINTS</span>
              </div>
              <p className="text-[10px] font-semibold tracking-wider text-[#64748B] uppercase">
                Portal del Reciclador
              </p>
            </div>
          </div>

          <button
            onClick={onClose}
            className="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100"
          >
            <XIcon size={18} />
          </button>
        </div>

        {/* Ecopuntos mini card */}
        <div className="px-4 pt-4 pb-2">
          <div className="bg-gradient-to-r from-[#087A3D] to-[#2E9B50] rounded-2xl p-3.5 text-white shadow-md relative overflow-hidden">
            <div className="absolute right-0 top-0 translate-x-3 -translate-y-2 opacity-15">
              <StarCoinIcon size={64} />
            </div>
            <div className="text-[11px] font-medium text-emerald-100">Balance disponible</div>
            <div className="flex items-baseline gap-1 mt-0.5">
              <span className="text-2xl font-black tracking-tight text-[#F5B82E]">
                {ecopuntos.toLocaleString()}
              </span>
              <span className="text-xs font-semibold text-emerald-100">pts</span>
            </div>
            <div className="mt-2 pt-2 border-t border-white/15 flex items-center justify-between text-[11px]">
              <span className="text-white/80">EcoReciclador</span>
              <button
                onClick={() => {
                  onSelectSection('ecopuntos');
                  onClose();
                }}
                className="text-amber-300 font-semibold hover:underline"
              >
                Ver nivel →
              </button>
            </div>
          </div>
        </div>

        {/* Navigation Items (Scrollable) */}
        <nav className="flex-1 px-3 py-2 space-y-1 overflow-y-auto">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = activeSection === item.id;

            return (
              <button
                key={item.id}
                onClick={() => {
                  onSelectSection(item.id as NavSection);
                  onClose();
                }}
                className={`w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all group ${
                  isActive
                    ? 'bg-[#EEF8EE] text-[#087A3D] font-bold shadow-xs'
                    : 'text-[#475569] hover:bg-slate-50 hover:text-[#1E2922]'
                }`}
              >
                <div className="flex items-center gap-3">
                  <Icon
                    size={19}
                    className={`transition-colors ${
                      isActive ? 'text-[#087A3D]' : 'text-slate-400 group-hover:text-slate-600'
                    }`}
                  />
                  <span>{item.label}</span>
                </div>

                {item.badge && (
                  <span
                    className={`px-2 py-0.5 text-[11px] font-semibold rounded-full ${
                      isActive
                        ? 'bg-[#087A3D] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'
                    }`}
                  >
                    {item.badge}
                  </span>
                )}
              </button>
            );
          })}

          <div className="pt-2 pb-1">
            <div className="border-t border-[#E2E8F0] my-2" />
            <div className="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
              Acciones Rápidas
            </div>
          </div>

          {/* Quick Action: Báscula Centro de Acopio (Interactive testing) */}
          <button
            onClick={() => {
              onAbrirSimuladorCentro();
              onClose();
            }}
            className="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#0284C7] bg-[#EAF4FA] hover:bg-sky-100 transition-colors"
          >
            <ScaleIcon size={16} />
            <span>Validar en Centro de Acopio</span>
          </button>

          {/* Quick Action: Simular Solicitud Ciudadano */}
          <button
            onClick={() => {
              onAbrirModalCiudadano();
              onClose();
            }}
            className="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition-colors"
          >
            <PlusIcon size={16} />
            <span>Nueva Solicitud (Ciudadano)</span>
          </button>
        </nav>

        {/* Bottom Section */}
        <div className="p-3 border-t border-[#E2E8F0] space-y-1 bg-[#FAFCFA]">
          <button
            onClick={() => {
              onSelectSection('configuracion');
              onClose();
            }}
            className={`w-full flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium ${
              activeSection === 'configuracion'
                ? 'bg-[#EEF8EE] text-[#087A3D]'
                : 'text-slate-500 hover:bg-slate-100'
            }`}
          >
            <SettingsIcon size={16} />
            <span>Configuración</span>
          </button>

          <button
            onClick={() => {
              onSelectSection('ayuda');
              onClose();
            }}
            className={`w-full flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium ${
              activeSection === 'ayuda'
                ? 'bg-[#EEF8EE] text-[#087A3D]'
                : 'text-slate-500 hover:bg-slate-100'
            }`}
          >
            <HelpIcon size={16} />
            <span>Ayuda y Soporte</span>
          </button>

          <div className="pt-2">
            <div className="px-3 py-1.5 bg-slate-100 rounded-xl flex items-center justify-between text-[11px] text-slate-600">
              <span className="font-medium truncate">Carlos Rodríguez</span>
              <span className="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="En línea" />
            </div>
          </div>
        </div>
      </aside>
    </>
  );
};
