import { useState } from "react";

const FAQS = [
  { q: "¿Cómo gano Ecopuntos?", a: "Ganas Ecopuntos cada vez que registras un reciclaje en la plataforma. El número de puntos depende del material y la cantidad que reciclas. Por ejemplo, reciclar 1 kg de plástico te da aproximadamente 10 puntos." },
  { q: "¿Dónde puedo reciclar?", a: "Puedes encontrar los puntos de reciclaje más cercanos en la sección \"Puntos de reciclaje\" del menú lateral. El mapa interactivo te muestra todos los EcoPuntos activos con sus horarios y los materiales que aceptan." },
  { q: "¿Cómo canjeo mis puntos?", a: "Ve a la sección \"Beneficios\" y selecciona el beneficio que deseas. Si tienes suficientes Ecopuntos, haz clic en \"Canjear beneficio\" y recibirás un código único para usar en el establecimiento aliado." },
  { q: "¿Qué materiales puedo reciclar?", a: "Ecopoint acepta Plástico, Cartón, Papel, Latas, Vidrio y otros materiales reciclables. Cada punto de recolección puede aceptar distintos materiales; verifícalo antes de ir en la sección de puntos." },
  { q: "¿Mis Ecopuntos tienen fecha de vencimiento?", a: "Actualmente los Ecopuntos no vencen. Sin embargo, te recomendamos canjarlos regularmente para aprovechar las mejores ofertas de nuestros aliados." },
  { q: "¿Cómo puedo ver mi impacto ambiental?", a: "En la sección \"Mi actividad\" puedes ver un resumen completo de tu impacto: kg reciclados, CO₂ evitado, número de reciclajes y más estadísticas de tu contribución al planeta." },
];

export default function Ayuda() {
  const [open, setOpen] = useState<number | null>(null);

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-xl font-bold text-[#1A2E22]">Centro de ayuda</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Encuentra respuestas a tus preguntas.</p>
      </div>

      <div className="max-w-2xl">
        {/* Search */}
        <div className="relative mb-6">
          <span className="absolute left-4 top-1/2 -translate-y-1/2 text-[#4A6355]">🔍</span>
          <input type="text" placeholder="Buscar en el centro de ayuda..." className="w-full pl-10 pr-4 py-3 border border-[#E0EBE4] rounded-xl text-sm focus:outline-none focus:border-[#087A3D] bg-white" />
        </div>

        {/* FAQ */}
        <h2 className="text-sm font-bold text-[#1A2E22] mb-3">Preguntas frecuentes</h2>
        <div className="space-y-2 mb-6">
          {FAQS.map((faq, i) => (
            <div key={i} className="bg-white rounded-xl border border-[#E0EBE4] overflow-hidden">
              <button onClick={() => setOpen(open === i ? null : i)} className="w-full flex items-center justify-between px-5 py-4 text-left">
                <span className="text-sm font-semibold text-[#1A2E22]">{faq.q}</span>
                <span className={`text-[#087A3D] text-lg transition-transform ${open === i ? "rotate-45" : ""}`}>+</span>
              </button>
              {open === i && (
                <div className="px-5 pb-4 text-sm text-[#4A6355] leading-relaxed border-t border-[#F4F6F5]">
                  {faq.a}
                </div>
              )}
            </div>
          ))}
        </div>

        {/* Contact support */}
        <div className="bg-gradient-to-br from-[#087A3D] to-[#2E9B50] rounded-2xl p-6 text-white text-center">
          <div className="text-3xl mb-3">💬</div>
          <h3 className="font-bold text-base mb-2">¿No encontraste lo que buscabas?</h3>
          <p className="text-sm opacity-80 mb-4">Nuestro equipo de soporte está disponible para ayudarte.</p>
          <button className="bg-white text-[#087A3D] font-semibold px-6 py-2.5 rounded-xl hover:bg-[#EEF8EE] transition-colors text-sm">
            Contactar soporte
          </button>
        </div>
      </div>
    </div>
  );
}
