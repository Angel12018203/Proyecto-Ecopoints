import { useState } from "react";

const MATERIALS = [
  {
    id: "plastico", icon: "🧴", title: "Plástico", color: "#3E9E6B", bg: "#EEF8EE",
    steps: ["Enjuaga los envases antes de reciclar.", "Retira tapas y etiquetas si es posible.", "Aplana botellas para ahorrar espacio.", "Busca el símbolo ♻ con número en el envase."],
    fact: "Reciclar 1 kg de plástico ahorra hasta 2 kg de CO₂.",
  },
  {
    id: "carton", icon: "📦", title: "Cartón", color: "#C97D3A", bg: "#FFF4EB",
    steps: ["Dobla las cajas para que ocupen menos espacio.", "Retira cintas adhesivas y grapas metálicas.", "Asegúrate de que esté seco y limpio.", "Separa el cartón corrugado del cartón fino."],
    fact: "Una tonelada de cartón reciclado salva 17 árboles.",
  },
  {
    id: "vidrio", icon: "🍾", title: "Vidrio", color: "#8B6BA8", bg: "#F4EEFF",
    steps: ["Enjuaga bien los envases de vidrio.", "Retira tapas metálicas o plásticas.", "No incluyas vidrio roto o de ventanas.", "Separa por color si el punto lo requiere."],
    fact: "El vidrio puede reciclarse infinitas veces sin perder calidad.",
  },
  {
    id: "latas", icon: "🥫", title: "Latas", color: "#6B8EA8", bg: "#EAF4FA",
    steps: ["Enjuaga y aplana las latas.", "Incluye latas de alimentos y bebidas.", "Retira etiquetas de papel si es posible.", "No incluyas aerosoles bajo presión."],
    fact: "Reciclar aluminio usa 95% menos energía que producirlo nuevo.",
  },
  {
    id: "papel", icon: "📄", title: "Papel", color: "#7E9BA8", bg: "#EEF5FA",
    steps: ["Evita papel encerado, metalizado o manchado.", "Retira clips, grapas y elementos plásticos.", "Los periódicos y revistas son bienvenidos.", "El papel mojado pierde calidad para reciclar."],
    fact: "Reciclar papel reduce el consumo de agua en un 60%.",
  },
];

const TIPS = [
  { icon: "🏠", tip: "Separa en casa con cubos de colores: verde (vidrio), azul (papel/cartón), amarillo (plástico/latas)." },
  { icon: "🚿", tip: "Enjuaga siempre los envases antes de reciclar para evitar contaminar otros materiales." },
  { icon: "📱", tip: "Usa la app para encontrar el punto de reciclaje más cercano a tu hogar o trabajo." },
  { icon: "👨‍👩‍👧", tip: "Involucra a tu familia en el reciclaje. Pequeños hábitos generan grandes impactos." },
  { icon: "🛍️", tip: "Usa bolsas reutilizables y lleva tu propio recipiente para reducir residuos desde la raíz." },
];

export default function Educacion() {
  const [active, setActive] = useState<string | null>(null);

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-xl font-bold text-[#1A2E22]">Educación ambiental</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Aprende a reciclar correctamente.</p>
      </div>

      {/* Material cards */}
      <h2 className="text-sm font-bold text-[#1A2E22] mb-3">¿Cómo reciclar cada material?</h2>
      <div className="grid grid-cols-5 gap-3 mb-6">
        {MATERIALS.map((m) => (
          <button key={m.id} onClick={() => setActive(active === m.id ? null : m.id)} className={`rounded-2xl border-2 p-4 text-center transition-all ${active === m.id ? "border-[#087A3D] shadow-md" : "border-[#E0EBE4] hover:border-[#2E9B50]"}`} style={{ background: active === m.id ? m.bg : "white" }}>
            <div className="text-3xl mb-2">{m.icon}</div>
            <div className="text-xs font-bold text-[#1A2E22]">{m.title}</div>
          </button>
        ))}
      </div>

      {/* Expanded material */}
      {active && (() => {
        const m = MATERIALS.find((x) => x.id === active)!;
        return (
          <div className="rounded-2xl border border-[#E0EBE4] p-6 mb-6" style={{ background: m.bg }}>
            <div className="flex items-center gap-3 mb-4">
              <span className="text-4xl">{m.icon}</span>
              <div>
                <div className="font-bold text-lg text-[#1A2E22]">Cómo reciclar {m.title}</div>
                <div className="text-xs" style={{ color: m.color }}>Sigue estos pasos</div>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-3 mb-4">
              {m.steps.map((s, i) => (
                <div key={i} className="flex items-start gap-2 bg-white rounded-xl p-3 border border-white/60">
                  <div className="w-5 h-5 rounded-full text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5" style={{ background: m.color }}>{i + 1}</div>
                  <span className="text-xs text-[#2D3A35]">{s}</span>
                </div>
              ))}
            </div>
            <div className="bg-white rounded-xl p-3 flex items-start gap-2 border border-[#E0EBE4]">
              <span className="text-lg">💡</span>
              <div>
                <div className="text-xs font-bold text-[#087A3D] mb-0.5">¿Sabías que?</div>
                <div className="text-xs text-[#4A6355]">{m.fact}</div>
              </div>
            </div>
          </div>
        );
      })()}

      {/* Tips */}
      <h2 className="text-sm font-bold text-[#1A2E22] mb-3">Consejos para reciclar mejor</h2>
      <div className="space-y-3">
        {TIPS.map((t, i) => (
          <div key={i} className="bg-white rounded-xl border border-[#E0EBE4] p-4 flex items-start gap-3">
            <span className="text-2xl shrink-0">{t.icon}</span>
            <p className="text-sm text-[#2D3A35]">{t.tip}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
