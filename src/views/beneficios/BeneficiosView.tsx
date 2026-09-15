import React, { useState } from 'react';
import { Beneficio, Canje, Reciclador } from '@/models/types';
import { GiftBoxIcon, StarCoinIcon, CheckCircleIcon, XIcon } from '../components/Icons';
import { Modal } from '../components/Modal';

interface BeneficiosViewProps {
  beneficios: Beneficio[];
  canjes: Canje[];
  reciclador: Reciclador;
  onCanjear: (beneficioId: string) => void;
}

export const BeneficiosView: React.FC<BeneficiosViewProps> = ({
  beneficios,
  canjes,
  reciclador,
  onCanjear,
}) => {
  const [categoriaSeleccionada, setCategoriaSeleccionada] = useState<string>('Todas');
  const [beneficioACanjear, setBeneficioACanjear] = useState<Beneficio | null>(null);
  const [canjeExitoso, setCanjeExitoso] = useState<Canje | null>(null);

  const categorias = [
    'Todas',
    'Protección y Trabajo',
    'Alimentación',
    'Transporte',
    'Salud y Hogar',
    'Tecnología',
  ];

  const beneficiosFiltrados = beneficios.filter((b) => {
    if (categoriaSeleccionada !== 'Todas' && b.categoria !== categoriaSeleccionada) return false;
    return true;
  });

  const handleConfirmarCanje = () => {
    if (!beneficioACanjear) return;
    try {
      onCanjear(beneficioACanjear.id);
      // Simular código de canje emitido
      setCanjeExitoso({
        id: `can-${Date.now()}`,
        codigoCanje: `ECO-${Math.floor(1000 + Math.random() * 9000)}`,
        recicladorId: reciclador.id,
        beneficioId: beneficioACanjear.id,
        beneficioNombre: beneficioACanjear.nombre,
        beneficioImagen: beneficioACanjear.imagen,
        costoEcopuntos: beneficioACanjear.costoEcopuntos,
        fecha: 'Hoy, hace un momento',
        estado: 'Disponible',
        vencimiento: 'Vence en 30 días',
      });
      setBeneficioACanjear(null);
    } catch (e) {
      alert(e);
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Title & Balance Banner */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
            <GiftBoxIcon size={24} className="text-[#087A3D]" />
            <span>Beneficios & Recompensas</span>
          </h1>
          <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
            Canjea tus Ecopuntos por herramientas de trabajo, bonos de alimentación y bienestar.
          </p>
        </div>

        <div className="flex items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-2xl">
          <StarCoinIcon size={20} className="text-[#F5B82E]" />
          <div>
            <span className="text-[10px] font-bold text-amber-800 uppercase block leading-none">
              Tu saldo disponible
            </span>
            <span className="text-lg font-black text-amber-950">
              {reciclador.ecopuntos.toLocaleString()} pts
            </span>
          </div>
        </div>
      </div>

      {/* Category Pills */}
      <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        {categorias.map((cat) => (
          <button
            key={cat}
            onClick={() => setCategoriaSeleccionada(cat)}
            className={`px-4 py-2 rounded-2xl text-xs font-bold shrink-0 transition-all ${
              categoriaSeleccionada === cat
                ? 'bg-[#087A3D] text-white shadow-xs'
                : 'bg-white border border-[#E2E8F0] text-slate-600 hover:bg-slate-50'
            }`}
          >
            {cat}
          </button>
        ))}
      </div>

      {/* Grid of Benefits */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {beneficiosFiltrados.map((ben) => {
          const tieneSaldo = reciclador.ecopuntos >= ben.costoEcopuntos;

          return (
            <div
              key={ben.id}
              className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs hover:shadow-md transition-all overflow-hidden flex flex-col justify-between"
            >
              <div>
                {/* Photo & Category Tag */}
                <div className="relative h-44 overflow-hidden bg-slate-100">
                  <img
                    src={ben.imagen}
                    alt={ben.nombre}
                    className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                  />
                  <div className="absolute top-3 left-3 bg-black/70 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-lg">
                    {ben.categoria}
                  </div>
                  <div className="absolute bottom-3 right-3 bg-white/95 backdrop-blur-xs text-[#087A3D] text-xs font-black px-2.5 py-1 rounded-xl shadow-xs">
                    {ben.disponibles} disponibles
                  </div>
                </div>

                {/* Content */}
                <div className="p-5 space-y-2">
                  <div className="flex items-start justify-between gap-2">
                    <h3 className="font-extrabold text-base text-[#1E2922] leading-snug">
                      {ben.nombre}
                    </h3>
                  </div>

                  <p className="text-xs text-[#64748B] line-clamp-3 leading-relaxed">
                    {ben.descripcion}
                  </p>

                  <div className="text-[11px] font-medium text-slate-400 pt-1">
                    Aliado oficial: <span className="text-slate-700 font-semibold">{ben.proveedor}</span>
                  </div>
                </div>
              </div>

              {/* Price & Canjear Button */}
              <div className="p-5 pt-0 flex items-center justify-between gap-3 border-t border-slate-50 mt-3">
                <div className="flex items-center gap-1.5">
                  <StarCoinIcon size={20} className="text-[#F5B82E]" />
                  <span className="text-xl font-black text-slate-900">
                    {ben.costoEcopuntos.toLocaleString()}
                  </span>
                  <span className="text-xs font-bold text-slate-400">pts</span>
                </div>

                <button
                  onClick={() => setBeneficioACanjear(ben)}
                  disabled={!tieneSaldo || ben.disponibles <= 0}
                  className={`px-5 py-2.5 rounded-xl font-black text-xs transition-all shadow-xs ${
                    tieneSaldo && ben.disponibles > 0
                      ? 'bg-[#F5B82E] hover:bg-amber-500 text-amber-950 cursor-pointer'
                      : 'bg-slate-100 text-slate-400 cursor-not-allowed'
                  }`}
                >
                  {tieneSaldo ? 'Canjear' : 'Puntos insuficientes'}
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {/* Modal de Confirmación de Canje */}
      {beneficioACanjear && (
        <Modal
          isOpen={true}
          onClose={() => setBeneficioACanjear(null)}
          title="Confirmar Canje de Beneficio"
          subtitle="Se descontarán los Ecopuntos de tu balance y se generará tu código de reclamo"
          maxWidth="max-w-md"
        >
          <div className="space-y-4">
            <div className="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200">
              <img
                src={beneficioACanjear.imagen}
                alt={beneficioACanjear.nombre}
                className="w-16 h-16 rounded-xl object-cover"
              />
              <div>
                <h4 className="font-extrabold text-sm text-slate-900">{beneficioACanjear.nombre}</h4>
                <div className="flex items-center gap-1 text-xs font-black text-[#087A3D] mt-0.5">
                  <StarCoinIcon size={14} className="text-[#F5B82E]" />
                  <span>{beneficioACanjear.costoEcopuntos.toLocaleString()} Ecopuntos</span>
                </div>
              </div>
            </div>

            <div className="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
              Tu nuevo balance será de{' '}
              <strong>{(reciclador.ecopuntos - beneficioACanjear.costoEcopuntos).toLocaleString()} pts</strong>.
            </div>

            <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
              <button
                onClick={() => setBeneficioACanjear(null)}
                className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Cancelar
              </button>
              <button
                onClick={handleConfirmarCanje}
                className="px-5 py-2.5 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-black rounded-xl shadow-md"
              >
                Confirmar y canjear
              </button>
            </div>
          </div>
        </Modal>
      )}

      {/* Modal de Canje Exitoso con Código Cupón */}
      {canjeExitoso && (
        <Modal
          isOpen={true}
          onClose={() => setCanjeExitoso(null)}
          title="¡Canje Exitoso!"
          subtitle="Presenta este cupón en el punto de atención del aliado"
          maxWidth="max-w-md"
        >
          <div className="text-center space-y-4 py-2">
            <div className="w-16 h-16 rounded-full bg-[#EEF8EE] text-[#087A3D] flex items-center justify-center text-3xl mx-auto border-2 border-[#C2E4C9]">
              🎁
            </div>

            <div>
              <h3 className="text-lg font-black text-slate-900">{canjeExitoso.beneficioNombre}</h3>
              <p className="text-xs text-slate-500 mt-0.5">Válido por 30 días calendario</p>
            </div>

            {/* Voucher Box */}
            <div className="p-4 bg-slate-900 text-white rounded-2xl border-2 border-dashed border-amber-400 space-y-1">
              <span className="text-[10px] text-amber-300 font-bold uppercase tracking-wider">
                Código de Reclamo Oficial
              </span>
              <div className="text-2xl font-black tracking-widest text-[#F5B82E]">
                {canjeExitoso.codigoCanje}
              </div>
              <span className="text-[10px] text-slate-400 block">
                Titular: Carlos Rodríguez • Suba
              </span>
            </div>

            <button
              onClick={() => setCanjeExitoso(null)}
              className="w-full py-3 bg-[#087A3D] hover:bg-[#065C2D] text-white font-extrabold text-xs rounded-xl shadow-md"
            >
              Listo, guardar en mi historial
            </button>
          </div>
        </Modal>
      )}
    </div>
  );
};
