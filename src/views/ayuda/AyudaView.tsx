import React from 'react';
import { HelpIcon } from '../components/Icons';

export const AyudaView: React.FC = () => {
  const faqs = [
    {
      q: '¿Por qué no recibo Ecopuntos inmediatamente al aceptar una solicitud?',
      a: 'Por regla fundamental de Ecopoints, los puntos representan material efectivamente recuperado. Se acreditan únicamente después de que realizas la entrega física en el centro de acopio aliado y se valida el pesaje en báscula electrónica.',
    },
    {
      q: '¿Cómo funciona la organización de mi ruta diaria?',
      a: 'Al aceptar solicitudes, se agregan a "Mis Recolecciones" y "Mi Ruta de Hoy". Puedes reordenar las paradas o pulsar "Optimizar por cercanía" para que el sistema organice el recorrido con el menor kilometraje.',
    },
    {
      q: '¿Dónde puedo validar mis entregas en Suba?',
      a: 'El centro principal es "Centro Verde Suba" (Cra 91 #145-20). Atiende de lunes a sábado con báscula certificada por la Secretaría de Ambiente.',
    },
    {
      q: '¿Cómo canjeo los beneficios ganados con mis Ecopuntos?',
      a: 'Ingresa al módulo "Beneficios", selecciona el producto o bono que desees y pulsa "Canjear". El sistema emitirá un cupón con código oficial (ej. ECO-8924) que puedes presentar ante el aliado comercial.',
    },
    {
      q: '¿Qué hago si un ciudadano cancela o no entrega el material?',
      a: 'Puedes reportar la novedad desde el detalle de la solicitud en tu ruta o descartarla para continuar con la siguiente parada sin penalización.',
    },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-200 max-w-4xl">
      <div>
        <h1 className="text-xl sm:text-2xl font-extrabold text-[#1E2922] flex items-center gap-2">
          <HelpIcon size={24} className="text-[#087A3D]" />
          <span>Centro de Ayuda y Soporte al Reciclador</span>
        </h1>
        <p className="text-xs sm:text-sm text-[#64748B] mt-0.5">
          Preguntas frecuentes sobre el flujo de recolección, pesajes oficiales, Ecopuntos y beneficios.
        </p>
      </div>

      {/* Emergency / Line of Support Banner */}
      <div className="bg-gradient-to-r from-[#EEF8EE] to-[#EAF4FA] p-5 rounded-3xl border border-[#C2E4C9] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span className="text-xs font-bold text-[#087A3D] uppercase tracking-wider block">
            Línea de Asistencia en Terreno
          </span>
          <h4 className="text-base font-black text-slate-900 mt-0.5">
            Línea Solidaria Suba: +57 (601) 682-9900
          </h4>
          <p className="text-xs text-slate-600">
            Atención prioritaria para recicladores durante jornada de recolección (6:00 a.m. a 6:00 p.m.)
          </p>
        </div>
        <a
          href="tel:+576016829900"
          className="px-4 py-2.5 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-extrabold rounded-xl transition-colors shadow-xs text-center shrink-0"
        >
          Llamar a Soporte
        </a>
      </div>

      {/* FAQs */}
      <div className="bg-white rounded-3xl border border-[#E2E8F0] shadow-xs p-6 space-y-4">
        <h3 className="font-extrabold text-base text-slate-900">Preguntas Frecuentes</h3>

        <div className="space-y-3">
          {faqs.map((faq, index) => (
            <div
              key={index}
              className="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5"
            >
              <h4 className="font-extrabold text-xs sm:text-sm text-[#087A3D] flex items-center gap-2">
                <span>❓</span>
                <span>{faq.q}</span>
              </h4>
              <p className="text-xs sm:text-sm text-slate-600 leading-relaxed pl-6">
                {faq.a}
              </p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
