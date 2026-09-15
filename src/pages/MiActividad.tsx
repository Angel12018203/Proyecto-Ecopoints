import { useApp } from "@/context/AppContext";

const MONTHLY = [
  { mes: "Ene", kg: 8 },
  { mes: "Feb", kg: 12 },
  { mes: "Mar", kg: 18 },
  { mes: "Abr", kg: 25 },
  { mes: "May", kg: 22 },
  { mes: "Jun", kg: 30 },
  { mes: "Jul", kg: 28 },
  { mes: "Ago", kg: 38 },
];

const MAT_COLORS: Record<string, string> = {
  Plástico: "#3E9E6B",
  Cartón: "#C97D3A",
  Latas: "#6B8EA8",
  Papel: "#7E9BA8",
  Vidrio: "#8B6BA8",
  Otro: "#087A3D",
};

export default function MiActividad() {
  const { ecopuntos, activity } = useApp();

  const totalKg = activity.reduce((s, a) => s + a.kg, 0);
  const totalCO2 = +(totalKg * 0.65).toFixed(1);

  const maxKg = Math.max(...MONTHLY.map((m) => m.kg));

  const materialCounts: Record<string, number> = {};
  activity.forEach((a) => { materialCounts[a.material] = (materialCounts[a.material] || 0) + a.kg; });
  const topMaterials = Object.entries(materialCounts).sort((a, b) => b[1] - a[1]).slice(0, 5);
  const totalTop = topMaterials.reduce((s, [, v]) => s + v, 0);

  return (
    <div className="p-6">
      <div className="mb-5">
        <h1 className="text-xl font-bold text-[#1A2E22]">Mi actividad</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Consulta tu progreso y el impacto que estás generando.</p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-4 gap-4 mb-5">
        {[
          { icon: "♻️", label: "Reciclajes", value: activity.length, color: "#087A3D", bg: "#EEF8EE" },
          { icon: "📦", label: "Material reciclado", value: `${totalKg} kg`, color: "#C97D3A", bg: "#FFF4EB" },
          { icon: "⭐", label: "Ecopuntos obtenidos", value: ecopuntos, color: "#F5B82E", bg: "#FFFBEC" },
          { icon: "🌱", label: "CO₂ evitado", value: `${totalCO2} kg`, color: "#2E9B50", bg: "#EEF8EE" },
        ].map((s) => (
          <div key={s.label} className="bg-white rounded-2xl border border-[#E0EBE4] p-5">
            <div className="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3" style={{ background: s.bg }}>{s.icon}</div>
            <div className="text-2xl font-extrabold" style={{ color: s.color }}>{s.value}</div>
            <div className="text-xs text-[#4A6355] mt-0.5">{s.label}</div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-3 gap-4 mb-5">
        {/* Bar chart */}
        <div className="col-span-2 bg-white rounded-2xl border border-[#E0EBE4] p-5">
          <h2 className="text-sm font-bold text-[#1A2E22] mb-5">Material reciclado por mes (kg)</h2>
          <div className="flex items-end gap-3 h-40">
            {MONTHLY.map((m, i) => {
              const h = (m.kg / maxKg) * 100;
              const isLast = i === MONTHLY.length - 1;
              return (
                <div key={m.mes} className="flex-1 flex flex-col items-center gap-1 group">
                  <div className="text-[9px] font-bold text-[#087A3D] opacity-0 group-hover:opacity-100 transition-opacity">{m.kg}</div>
                  <div className="w-full rounded-t-lg transition-all duration-300 hover:opacity-90" style={{ height: `${h}%`, background: isLast ? "#087A3D" : "#2E9B50", minHeight: 4 }} />
                  <div className="text-[9px] text-[#4A6355] font-medium">{m.mes}</div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Material breakdown */}
        <div className="bg-white rounded-2xl border border-[#E0EBE4] p-5">
          <h2 className="text-sm font-bold text-[#1A2E22] mb-4">Por material</h2>
          {topMaterials.length === 0 ? (
            <div className="text-center text-xs text-[#4A6355] py-4">Sin datos aún</div>
          ) : (
            <div className="space-y-3">
              {topMaterials.map(([mat, kg]) => {
                const pct = totalTop > 0 ? (kg / totalTop) * 100 : 0;
                return (
                  <div key={mat}>
                    <div className="flex justify-between text-xs mb-1">
                      <span className="font-medium text-[#1A2E22]">{mat}</span>
                      <span className="text-[#4A6355]">{kg} kg</span>
                    </div>
                    <div className="w-full bg-[#E0EBE4] rounded-full h-1.5">
                      <div className="h-1.5 rounded-full" style={{ width: `${pct}%`, background: MAT_COLORS[mat] || "#087A3D" }} />
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>

      {/* Environmental impact */}
      <div className="bg-gradient-to-br from-[#087A3D] to-[#2E9B50] rounded-2xl p-6 text-white">
        <div className="flex items-center gap-2 mb-4">
          <span className="text-2xl">🌍</span>
          <h2 className="font-bold text-base">Impacto ambiental</h2>
        </div>
        <div className="grid grid-cols-2 gap-4">
          <div className="bg-white/10 rounded-xl p-4">
            <div className="text-sm opacity-80 mb-1">Has contribuido a recuperar</div>
            <div className="text-2xl font-extrabold">{totalKg} kg</div>
            <div className="text-sm opacity-80">de materiales reciclables</div>
          </div>
          <div className="bg-white/10 rounded-xl p-4">
            <div className="text-sm opacity-80 mb-1">Tu participación evita</div>
            <div className="text-2xl font-extrabold">{totalCO2} kg</div>
            <div className="text-sm opacity-80">de CO₂ en la atmósfera</div>
          </div>
        </div>
        <p className="text-sm opacity-80 mt-4">Tu participación ayuda a reducir residuos y fomentar la economía circular. ¡Sigue así! 🌱</p>
      </div>
    </div>
  );
}
