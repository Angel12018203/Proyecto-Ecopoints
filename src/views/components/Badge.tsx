import React from 'react';
import { EstadoSolicitud, EstadoEntrega, PrioridadSolicitud } from '@/models/types';

interface BadgeProps {
  status: EstadoSolicitud | EstadoEntrega | PrioridadSolicitud | string;
  size?: 'sm' | 'md';
}

export const StatusBadge: React.FC<BadgeProps> = ({ status, size = 'md' }) => {
  let bg = 'bg-gray-100 text-gray-700 border-gray-200';

  switch (status) {
    case 'Disponible':
      bg = 'bg-[#EEF8EE] text-[#087A3D] border-[#C2E4C9] font-semibold';
      break;
    case 'Aceptada':
      bg = 'bg-[#EAF4FA] text-[#0284C7] border-[#BAE6FD] font-semibold';
      break;
    case 'En recolección':
      bg = 'bg-amber-50 text-amber-800 border-amber-200 font-semibold animate-pulse-subtle';
      break;
    case 'Recolectada':
      bg = 'bg-emerald-100 text-emerald-800 border-emerald-300 font-semibold';
      break;
    case 'Pendiente':
      bg = 'bg-yellow-50 text-yellow-800 border-yellow-200 font-semibold';
      break;
    case 'Validada':
      bg = 'bg-[#EEF8EE] text-[#087A3D] border-[#2E9B50] font-bold shadow-xs';
      break;
    case 'Alta':
      bg = 'bg-red-50 text-red-700 border-red-200 font-bold';
      break;
    case 'Media':
      bg = 'bg-amber-50 text-amber-700 border-amber-200 font-semibold';
      break;
    case 'Baja':
      bg = 'bg-blue-50 text-blue-700 border-blue-200 font-medium';
      break;
    default:
      bg = 'bg-slate-100 text-slate-700 border-slate-200';
  }

  const sizeCls = size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs';

  return (
    <span className={`inline-flex items-center gap-1.5 rounded-full border ${sizeCls} ${bg}`}>
      <span className="w-1.5 h-1.5 rounded-full bg-current opacity-80" />
      {status}
    </span>
  );
};
