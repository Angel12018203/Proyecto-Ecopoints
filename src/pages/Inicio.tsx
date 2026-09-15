import { useApp } from "@/context/AppContext";
import { ArrowIcon, MapPlaceholder } from "@/components/Icons";

const BENEFITS = [
  { brand: "Café Verde", offer: "2x1 en bebidas", pts: 400, img: "https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=200&h=140&fit=crop&auto=format" },
  { brand: "EcoMarket", offer: "15% de descuento", pts: 600, img: "https://images.unsplash.com/photo-1542838132-92c53300491e?w=200&h=140&fit=crop&auto=format" },
  { brand: "Librería Natura", offer: "10% de descuento", pts: 500, img: "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=200&h=140&fit=crop&auto=format" },
  { brand: "Cine Planet", offer: "Entrada 2D", pts: 800, img: "https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=200&h=140&fit=crop&auto=format" },
];

type Props = { navigate: (page: string) => void };

export default function Inicio({ navigate }: Props) {
  const { ecopuntos, activity } = useApp();
  const nextBenefitPts = 500;
  const progress = Math.min((ecopuntos / nextBenefitPts) * 100, 100);
  const faltaPts = Math.max(nextBenefitPts - ecopuntos, 0);

  return (
    <div className="p-6">
      <div className="mb-5">
        <h1 className="text-xl font-bold text-[#1A2E22]">¡Bienvenido de nuevo! 👋</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Sigue reciclando y acumulando Ecopuntos.</p>
      </div>

      {/* Stats row */}
      <div className="grid grid-cols-3 gap-4 mb-5">
        <div className="bg-[#087A3D] rounded-2xl p-5 relative overflow-hidden text-white">
          <div className="text-xs font-medium opacity-80 mb-3">Tus Ecopuntos</div>
          <div className="flex items-center gap-3">
            <span className="text-3xl">⭐</span>
            <span className="text-5xl font-extrabold tracking-tight">{ecopuntos}</span>
          </div>
          <div className="text-xs font-semibold tracking-widest mt-1 opacity-70">ECOPUNTOS</div>
          <div className="absolute right-3 top-3 opacity-20 text-6xl select-none">🌿</div>
        </div>

        <div className="bg-white rounded-2xl p-5 border border-[#E0EBE4]">
          <div className="flex items-center gap-2 text-xs font-semibold text-[#087A3D] mb-3">
            🎁 Próximo beneficio
          </div>
          <div className="flex items-center gap-3 mb-3">
            <div className="w-10 h-10 rounded-xl bg-[#EEF8EE] flex items-center justify-center text-xl">🎁</div>
            <div>
              <div className="text-base font-bold text-[#1A2E22]">10% de descuento</div>
              <div className="text-xs text-[#4A6355]">en tiendas aliadas</div>
            </div>
          </div>
          <div className="w-full bg-[#E0EBE4] rounded-full h-2 mb-1">
            <div className="bg-[#087A3D] h-2 rounded-full transition-all duration-500" style={{ width: `${progress}%` }} />
          </div>
          <div className="text-xs text-[#4A6355]">{ecopuntos} / {nextBenefitPts} puntos</div>
          {faltaPts > 0 && <div className="text-xs font-medium text-[#087A3D] mt-1">¡Te faltan {faltaPts} puntos!</div>}
          {faltaPts === 0 && <div className="text-xs font-medium text-[#087A3D] mt-1">¡Ya puedes canjearlo! 🎉</div>}
        </div>

        <div className="bg-white rounded-2xl p-5 border border-[#E0EBE4]">
          <div className="flex items-center gap-2 text-xs font-semibold text-[#087A3D] mb-3">
            🌿 Impacto ambiental
          </div>
          <p className="text-sm text-[#2D3A35] font-medium mb-4">Gracias por hacer<br />la diferencia</p>
          <div className="flex gap-4">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-[#EEF8EE] flex items-center justify-center text-sm">🌿</div>
              <div>
                <div className="text-base font-bold text-[#1A2E22]">12 kg</div>
                <div className="text-[10px] text-[#4A6355]">Reciclados</div>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-[#F0F0F5] flex items-center justify-center text-xs font-bold text-[#4A4A6A]">CO₂</div>
              <div>
                <div className="text-base font-bold text-[#1A2E22]">8.5 kg</div>
                <div className="text-[10px] text-[#4A6355]">CO₂ evitado</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Actions + Map */}
      <div className="grid grid-cols-2 gap-4 mb-5">
        <div className="bg-white rounded-2xl p-5 border border-[#E0EBE4]">
          <h2 className="text-sm font-bold text-[#1A2E22] mb-4">¿Qué quieres hacer hoy?</h2>
          <div className="grid grid-cols-3 gap-3">
            {[
              { icon: "♻️", title: "Registrar\nreciclaje", desc: "Registra los materiales que has reciclado", page: "reciclar", hoverBorder: "#2E9B50", hoverBg: "#F0FAF3" },
              { icon: "📍", title: "Encontrar\npunto cercano", desc: "Ubica puntos de reciclaje cerca de ti", page: "puntos", hoverBorder: "#2B7FD4", hoverBg: "#F0F7FF" },
              { icon: "🎁", title: "Ver\nbeneficios", desc: "Canjea tus Ecopuntos por grandes beneficios", page: "beneficios", hoverBorder: "#D4992B", hoverBg: "#FFFBF0" },
            ].map((a) => (
              <button key={a.page} onClick={() => navigate(a.page)} className="flex flex-col items-center text-center gap-2 p-4 rounded-xl border-2 border-[#E0EBE4] hover:border-[var(--hb)] hover:bg-[var(--bg)] transition-all" style={{ "--hb": a.hoverBorder, "--bg": a.hoverBg } as React.CSSProperties}>
                <div className="w-12 h-12 rounded-full bg-[#EEF8EE] flex items-center justify-center text-2xl">{a.icon}</div>
                <div className="text-xs font-bold text-[#1A2E22] whitespace-pre-line">{a.title}</div>
                <div className="text-[10px] text-[#4A6355]">{a.desc}</div>
                <ArrowIcon />
              </button>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-2xl border border-[#E0EBE4] overflow-hidden flex">
          <div className="flex-1 relative min-h-[200px]">
            <MapPlaceholder />
            <div className="absolute top-2 left-2 right-2 flex justify-between items-center">
              <span className="text-xs font-bold text-[#1A2E22] bg-white/90 rounded-lg px-3 py-1.5">Punto más cercano</span>
              <button onClick={() => navigate("puntos")} className="text-xs font-semibold text-[#087A3D] bg-white/90 rounded-lg px-2 py-1.5 hover:bg-white transition-colors">Ver todos</button>
            </div>
          </div>
          <div className="w-44 p-4 flex flex-col gap-3 border-l border-[#E0EBE4]">
            <div>
              <div className="font-bold text-sm text-[#1A2E22]">EcoPunto Centro</div>
              <div className="flex items-center gap-1 mt-1">
                <span className="w-2 h-2 rounded-full bg-[#3CB96A] inline-block" />
                <span className="text-xs text-[#3CB96A] font-medium">Abierto ahora</span>
              </div>
            </div>
            <div className="text-xs text-[#4A6355]">📍 A 500 metros</div>
            <div>
              <div className="text-xs text-[#4A6355] mb-2">Recibe:</div>
              <div className="grid grid-cols-4 gap-1">
                {["🧴", "📦", "🥫", "🍾"].map((e, i) => (
                  <div key={i} className="flex flex-col items-center gap-0.5">
                    <div className="w-7 h-7 rounded-lg bg-[#EEF8EE] flex items-center justify-center text-sm">{e}</div>
                  </div>
                ))}
              </div>
            </div>
            <button onClick={() => navigate("puntos")} className="w-full bg-[#087A3D] text-white text-xs font-semibold py-2 rounded-lg hover:bg-[#065C2D] transition-colors mt-auto">
              📍 Cómo llegar
            </button>
          </div>
        </div>
      </div>

      {/* Activity + Benefits */}
      <div className="grid grid-cols-2 gap-4">
        <div className="bg-white rounded-2xl p-5 border border-[#E0EBE4]">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-sm font-bold text-[#1A2E22]">Actividad reciente</h2>
            <button onClick={() => navigate("actividad")} className="text-xs font-semibold text-[#087A3D] hover:underline">Ver todo</button>
          </div>
          <div className="flex flex-col gap-3">
            {activity.slice(0, 3).map((item, i) => (
              <div key={i} className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-full flex items-center justify-center text-lg shrink-0" style={{ background: `${item.color}20` }}>
                  {item.icon}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="text-xs font-semibold text-[#1A2E22]">Reciclaje de {item.material}</div>
                  <div className="text-[10px] text-[#4A6355]">{item.kg} {item.unit} • {item.punto}</div>
                </div>
                <div className="text-right shrink-0">
                  <div className="text-xs font-bold text-[#087A3D]">+{item.pts} Ecopuntos</div>
                  <div className="text-[10px] text-[#4A6355]">{item.date}</div>
                </div>
              </div>
            ))}
          </div>
          <button onClick={() => navigate("actividad")} className="w-full mt-4 py-2 border border-[#E0EBE4] rounded-xl text-xs font-semibold text-[#087A3D] hover:bg-[#EEF8EE] transition-colors flex items-center justify-center gap-2">
            Ver todo mi historial <ArrowIcon />
          </button>
        </div>

        <div className="bg-white rounded-2xl p-5 border border-[#E0EBE4]">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-sm font-bold text-[#1A2E22]">Beneficios destacados</h2>
            <button onClick={() => navigate("beneficios")} className="text-xs font-semibold text-[#087A3D] hover:underline">Ver todos</button>
          </div>
          <div className="grid grid-cols-4 gap-2">
            {BENEFITS.map((b, i) => (
              <button key={i} onClick={() => navigate("beneficios")} className="rounded-xl overflow-hidden border border-[#E0EBE4] hover:shadow-md transition-shadow text-left group">
                <div className="relative h-20 overflow-hidden">
                  <img src={b.img} alt={b.brand} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                  <div className="absolute inset-0 bg-black/30" />
                </div>
                <div className="p-2">
                  <div className="text-[10px] font-bold text-[#1A2E22] leading-tight">{b.offer}</div>
                  <div className="text-[10px] text-[#4A6355] mt-0.5">⭐ {b.pts} pts</div>
                </div>
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
