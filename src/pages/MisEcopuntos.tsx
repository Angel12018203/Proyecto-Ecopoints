import { useState } from "react";
import { useApp } from "@/context/AppContext";

const FILTERS = ["Todos", "Ganados", "Canjeados"];

export default function MisEcopuntos() {
  const { ecopuntos, activity, redeemed } = useApp();
  const [filter, setFilter] = useState("Todos");

  const nextPts = 500;
  const progress = Math.min((ecopuntos / nextPts) * 100, 100);

  const allHistory = [
    ...activity.map((a) => ({ type: "ganado" as const, label: `Reciclaje de ${a.material}`, icon: a.icon, pts: a.pts, date: a.date, color: a.color })),
    ...redeemed.map((r) => ({ type: "canjeado" as const, label: r.offer, icon: "🎁", pts: -r.pts, date: r.date, color: "#C97D3A" })),
  ].sort(() => 0.5 - Math.random());

  const filtered = filter === "Todos" ? allHistory : filter === "Ganados" ? allHistory.filter((h) => h.type === "ganado") : allHistory.filter((h) => h.type === "canjeado");

  return (
    <div className="p-6">
      <div className="mb-5">
        <h1 className="text-xl font-bold text-[#1A2E22]">Mis Ecopuntos</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Tu saldo, progreso e historial de puntos.</p>
      </div>

      {/* Balance card */}
      <div className="bg-[#087A3D] text-white rounded-2xl p-7 mb-5 relative overflow-hidden">
        <div className="absolute right-6 top-6 opacity-10 text-[120px] leading-none select-none">⭐</div>
        <div className="text-sm font-medium opacity-80 mb-2">Tu saldo actual</div>
        <div className="flex items-end gap-3 mb-4">
          <span className="text-6xl font-extrabold tracking-tight">{ecopuntos}</span>
          <span className="text-lg font-semibold opacity-70 mb-2">ECOPUNTOS</span>
        </div>
        <div className="mb-2">
          <div className="flex justify-between text-xs mb-1 opacity-80">
            <span>{ecopuntos} / {nextPts} puntos</span>
            <span>Próximo beneficio: 10% descuento</span>
          </div>
          <div className="w-full bg-white/20 rounded-full h-2.5">
            <div className="bg-white h-2.5 rounded-full transition-all duration-500" style={{ width: `${progress}%` }} />
          </div>
        </div>
        {ecopuntos < nextPts && <div className="text-sm opacity-90 mt-1">Te faltan {nextPts - ecopuntos} puntos para tu próximo beneficio.</div>}
        {ecopuntos >= nextPts && <div className="text-sm opacity-90 mt-1">🎉 ¡Ya puedes canjear tu beneficio!</div>}
      </div>

      {/* Quick stats */}
      <div className="grid grid-cols-3 gap-4 mb-6">
        {[
          { label: "Total ganados", value: activity.reduce((s, a) => s + a.pts, 0), icon: "⭐", color: "#F5B82E" },
          { label: "Total canjeados", value: redeemed.reduce((s, r) => s + r.pts, 0), icon: "🎁", color: "#C97D3A" },
          { label: "Reciclajes", value: activity.length, icon: "♻️", color: "#087A3D" },
        ].map((s) => (
          <div key={s.label} className="bg-white rounded-2xl border border-[#E0EBE4] p-4 text-center">
            <div className="text-2xl mb-1">{s.icon}</div>
            <div className="text-2xl font-extrabold" style={{ color: s.color }}>{s.value}</div>
            <div className="text-xs text-[#4A6355] mt-0.5">{s.label}</div>
          </div>
        ))}
      </div>

      {/* History */}
      <div className="bg-white rounded-2xl border border-[#E0EBE4] p-5">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-sm font-bold text-[#1A2E22]">Historial de puntos</h2>
          <div className="flex gap-1">
            {FILTERS.map((f) => (
              <button key={f} onClick={() => setFilter(f)} className={`px-3 py-1 rounded-full text-xs font-semibold transition-colors ${filter === f ? "bg-[#087A3D] text-white" : "text-[#4A6355] hover:bg-[#EEF8EE]"}`}>{f}</button>
            ))}
          </div>
        </div>
        {filtered.length === 0 && (
          <div className="text-center py-8 text-[#4A6355]">
            <div className="text-4xl mb-2">📭</div>
            <div className="text-sm">Sin registros en esta categoría</div>
          </div>
        )}
        <div className="space-y-3">
          {filtered.map((h, i) => (
            <div key={i} className="flex items-center gap-3 py-2 border-b border-[#F4F6F5] last:border-0">
              <div className="w-9 h-9 rounded-full flex items-center justify-center text-lg shrink-0" style={{ background: `${h.color}20` }}>
                {h.icon}
              </div>
              <div className="flex-1 min-w-0">
                <div className="text-xs font-semibold text-[#1A2E22]">{h.label}</div>
                <div className="text-[10px] text-[#4A6355]">{h.date}</div>
              </div>
              <div className={`text-sm font-bold ${h.pts > 0 ? "text-[#087A3D]" : "text-[#C97D3A]"}`}>
                {h.pts > 0 ? `+${h.pts}` : `${h.pts}`} pts
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
