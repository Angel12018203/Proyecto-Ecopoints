import React from 'react';
import { Reciclador, MovimientoEcopunto } from '@/models/types';
import { StarCoinIcon, ArrowRightIcon, GiftBoxIcon, RecycleBagIcon } from '../components/Icons';
import { NavSection } from '../components/Sidebar';

interface EcopuntosViewProps {
  reciclador: Reciclador;
  movimientos: MovimientoEcopunto[];
  onNavigate: (section: NavSection) => void;
  onAbrirSimuladorCentro: () => void;
}

export const EcopuntosView: React.FC<EcopuntosViewProps> = ({
  reciclador,
  movimientos,
  onNavigate,
  onAbrirSimuladorCentro,
}) => {
  // Progresión de niveles
  const niveles = [
    { nombre: 'Reciclador Activo', rango: '0 – 500 pts', icono: '🌱', min: 0, max: 500 },
    { nombre: 'EcoReciclador', rango: '501 – 1.500 pts', icono: '🌿', min: 501, max: 1500 },
    { nombre: 'Reciclador Destacado', rango: '1.501 – 3.000 pts', icono: '⭐', min: 1501, max: 3000 },
    { nombre: 'Embajador de Economía Circular', rango: '+3.000 pts', icono: '👑', min: 3001, max: 5000 },
  ];

  // Cálculo del porcentaje hacia 3.000 pts (Reciclador Destacado)
  const progresoNivel = Math.min(100, Math.round((reciclador.ecopuntos / 3000) * 100));

  return (
    <div className="space-y-8 animate-in fade-in duration-200">
      {/* Title */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
            <StarCoinIcon size={26} className="text-[#F5B82E]" />
            <span>Mis Ecopuntos & Nivel de Progreso</span>
          </h1>
          <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
            Tus puntos reflejan el material efectivamente recuperado y validado en centros de acopio.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => onNavigate('beneficios')}
            className="px-4 py-2.5 bg-[#F5B82E] hover:bg-amber-500 text-amber-950 font-black text-xs sm:text-sm rounded-xl transition-colors shadow-sm flex items-center gap-1.5"
          >
            <GiftBoxIcon size={16} />
            <span>Canjear Beneficios</span>
          </button>
        </div>
      </div>

      {/* Tarjeta de Balance & Progreso */}
      <div className="bg-gradient-to-br from-[#087A3D] via-[#108947] to-[#2E9B50] rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div className="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
          {/* Col 1: Balance actual */}
          <div className="space-y-1">
            <span className="text-xs font-semibold text-emerald-100 uppercase tracking-wider">
              Balance actual disponible
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-4xl sm:text-5xl font-black text-[#F5B82E] tracking-tight">
                {reciclador.ecopuntos.toLocaleString()}
              </span>
              <span className="text-lg font-bold text-white/90">Ecopuntos</span>
            </div>
            <p className="text-xs text-emerald-100">
              Puntos de Encaje de Beneficio generados por validación oficial.
            </p>
          </div>

          {/* Col 2: Progreso de Nivel */}
          <div className="space-y-2 bg-white/10 backdrop-blur-xs p-5 rounded-2xl border border-white/20">
            <div className="flex items-center justify-between text-xs">
              <span className="font-bold text-white">Nivel: {reciclador.nivel}</span>
              <span className="font-black text-[#F5B82E]">{progresoNivel}%</span>
            </div>

            <div className="w-full bg-black/20 h-3 rounded-full overflow-hidden p-0.5">
              <div
                className="bg-[#F5B82E] h-full rounded-full transition-all duration-700 shadow-sm"
                style={{ width: `${progresoNivel}%` }}
              />
            </div>

            <p className="text-[11px] text-emerald-100 italic">
              {reciclador.ecopuntos >= 3000 ? (
                '¡Felicidades! Has alcanzado el rango máximo de Embajador.'
              ) : (
                <>
                  Faltan <strong>{Math.max(0, 3000 - reciclador.ecopuntos)} puntos</strong> para alcanzar{' '}
                  <strong className="text-amber-200">Reciclador Destacado</strong>.
                </>
              )}
            </p>
          </div>

          {/* Col 3: Impacto total en Kg */}
          <div className="space-y-1 md:border-l md:border-white/20 md:pl-6">
            <span className="text-xs font-semibold text-emerald-100 uppercase tracking-wider">
              Material recuperado histórico
            </span>
            <div className="text-3xl sm:text-4xl font-black text-white">
              {reciclador.kgRecuperados.toLocaleString()}{' '}
              <span className="text-lg font-semibold text-emerald-200">kg</span>
            </div>
            <p className="text-xs text-emerald-100">En 48 recolecciones certificadas</p>
          </div>
        </div>
      </div>

      {/* Regla Fundamental Visual (Sección 16) */}
      <div className="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-xs space-y-3">
        <h3 className="font-extrabold text-sm uppercase tracking-wider text-slate-700">
          ¿Cómo se generan los Ecopuntos? — Principio Fundamental
        </h3>

        <div className="grid grid-cols-2 sm:grid-cols-6 gap-2 text-center pt-2">
          {[
            { step: '1. Recolección', desc: 'Recoges material en ruta', icon: '🚲' },
            { step: '2. Entrega', desc: 'Entregas en centro aliado', icon: '🏢' },
            { step: '3. Pesaje', desc: 'Pesaje oficial en báscula', icon: '⚖️' },
            { step: '4. Validación', desc: 'Aprobación del lote', icon: '✓' },
            { step: '5. Ecopuntos', desc: 'Puntos directos a tu perfil', icon: '⭐' },
            { step: '6. Beneficios', desc: 'Canjeas por recompensas', icon: '🎁' },
          ].map((item, index) => (
            <div
              key={index}
              className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col items-center justify-between"
            >
              <span className="text-2xl mb-1">{item.icon}</span>
              <span className="text-xs font-extrabold text-slate-900 leading-tight">{item.step}</span>
              <span className="text-[10px] text-slate-500 mt-1">{item.desc}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Sistema de Niveles (Sección 17) */}
      <div className="space-y-4">
        <h2 className="text-lg font-bold text-[#1E2922]">Escala de Niveles del Reciclador</h2>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {niveles.map((niv) => {
            const esActual = reciclador.nivel === niv.nombre;

            return (
              <div
                key={niv.nombre}
                className={`p-5 rounded-3xl border transition-all ${
                  esActual
                    ? 'bg-[#EEF8EE] border-[#087A3D] shadow-md ring-2 ring-[#087A3D]/20'
                    : 'bg-white border-[#E2E8F0] shadow-xs'
                }`}
              >
                <div className="flex items-center justify-between mb-3">
                  <span className="text-3xl">{niv.icono}</span>
                  {esActual && (
                    <span className="px-2.5 py-1 bg-[#087A3D] text-white text-[10px] font-black rounded-full uppercase tracking-wider">
                      Tu Rango Actual
                    </span>
                  )}
                </div>

                <h3 className="font-extrabold text-base text-slate-900 leading-snug">{niv.nombre}</h3>
                <p className="text-xs font-bold text-[#087A3D] mt-0.5">{niv.rango}</p>

                <p className="text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-100">
                  Beneficios prioritarios en canjes, bonificaciones y dotación de seguridad.
                </p>
              </div>
            );
          })}
        </div>
      </div>

      {/* Últimos Movimientos (Sección 18) */}
      <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs p-6 space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <h3 className="font-extrabold text-base text-[#1E2922]">Últimos Movimientos de Ecopuntos</h3>
            <p className="text-xs text-[#64748B]">
              Historial de puntos acreditados por entregas y puntos canjeados
            </p>
          </div>

          <button
            onClick={() => onNavigate('historial')}
            className="text-xs font-bold text-[#087A3D] hover:underline"
          >
            Ver historial completo →
          </button>
        </div>

        <div className="divide-y divide-slate-100">
          {movimientos.map((mov) => {
            const esIngreso = mov.puntos > 0;

            return (
              <div
                key={mov.id}
                className="py-4 flex items-center justify-between gap-4 hover:bg-slate-50/80 px-2 rounded-xl transition-colors"
              >
                <div className="flex items-center gap-3">
                  <div
                    className={`w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-base ${
                      esIngreso
                        ? 'bg-[#EEF8EE] text-[#087A3D]'
                        : 'bg-amber-50 text-amber-700'
                    }`}
                  >
                    {esIngreso ? '+' : '−'}
                  </div>
                  <div>
                    <h4 className="font-bold text-sm text-slate-900">{mov.concepto}</h4>
                    <span className="text-xs text-slate-400">{mov.fecha}</span>
                  </div>
                </div>

                <div
                  className={`text-base font-black ${
                    esIngreso ? 'text-[#087A3D]' : 'text-slate-600'
                  }`}
                >
                  {esIngreso ? `+${mov.puntos}` : mov.puntos} pts
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};
