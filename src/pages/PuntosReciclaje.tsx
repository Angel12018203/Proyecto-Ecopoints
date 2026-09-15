import { useState } from "react";
import { MapPlaceholder } from "@/components/Icons";

const PUNTOS = [
  { id: "centro", name: "EcoPunto Centro", dist: "500 m", open: true, address: "Calle 45 #12-34, Centro", materials: ["Plástico", "Cartón", "Latas", "Vidrio"], hours: "Lun–Sáb 8:00–18:00" },
  { id: "norte", name: "EcoPunto Norte", dist: "1.2 km", open: true, address: "Av. Norte #89-10, Norte", materials: ["Plástico", "Papel", "Cartón"], hours: "Lun–Vie 9:00–17:00" },
  { id: "sur", name: "EcoPunto Sur", dist: "2.1 km", open: false, address: "Carrera 7 #23-45, Sur", materials: ["Vidrio", "Latas", "Plástico"], hours: "Mar–Sáb 10:00–16:00" },
  { id: "occidente", name: "EcoPunto Occidente", dist: "3.4 km", open: true, address: "Cl. 80 #50-20, Occidente", materials: ["Papel", "Cartón", "Plástico", "Latas"], hours: "Lun–Dom 7:00–19:00" },
];

const FILTERS = ["Todos", "Plástico", "Cartón", "Vidrio", "Latas", "Papel"];
const MAT_ICONS: Record<string, string> = { Plástico: "🧴", Cartón: "📦", Latas: "🥫", Vidrio: "🍾", Papel: "📄" };

export default function PuntosReciclaje() {
  const [filter, setFilter] = useState("Todos");
  const [selected, setSelected] = useState(PUNTOS[0]);

  const visible = filter === "Todos" ? PUNTOS : PUNTOS.filter((p) => p.materials.includes(filter));

  return (
    <div className="p-6">
      <div className="mb-5">
        <h1 className="text-xl font-bold text-[#1A2E22]">Puntos de reciclaje</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Encuentra un punto cercano para entregar tus materiales.</p>
      </div>

      {/* Filters */}
      <div className="flex gap-2 mb-5 flex-wrap">
        {FILTERS.map((f) => (
          <button key={f} onClick={() => setFilter(f)} className={`px-4 py-1.5 rounded-full text-xs font-semibold border transition-colors ${filter === f ? "bg-[#087A3D] text-white border-[#087A3D]" : "bg-white text-[#4A6355] border-[#E0EBE4] hover:border-[#087A3D] hover:text-[#087A3D]"}`}>
            {f !== "Todos" && <span className="mr-1">{MAT_ICONS[f]}</span>}{f}
          </button>
        ))}
      </div>

      <div className="grid grid-cols-2 gap-4 h-[520px]">
        {/* Map */}
        <div className="bg-white rounded-2xl border border-[#E0EBE4] overflow-hidden relative">
          <MapPlaceholder />
          <div className="absolute top-3 left-3 bg-white/95 rounded-xl px-3 py-2 text-xs font-bold text-[#1A2E22] shadow-sm">
            {visible.length} puntos encontrados
          </div>
          {/* Selected info overlay */}
          <div className="absolute bottom-3 left-3 right-3 bg-white rounded-xl p-3 shadow-md border border-[#E0EBE4]">
            <div className="flex items-center justify-between mb-1">
              <div className="font-bold text-sm text-[#1A2E22]">{selected.name}</div>
              <div className={`flex items-center gap-1 text-xs font-medium ${selected.open ? "text-[#3CB96A]" : "text-red-400"}`}>
                <span className={`w-2 h-2 rounded-full ${selected.open ? "bg-[#3CB96A]" : "bg-red-400"}`} />
                {selected.open ? "Abierto" : "Cerrado"}
              </div>
            </div>
            <div className="text-xs text-[#4A6355] mb-2">📍 {selected.dist} · {selected.address}</div>
            <div className="flex gap-1 flex-wrap">
              {selected.materials.map((m) => (
                <span key={m} className="text-[10px] bg-[#EEF8EE] text-[#087A3D] rounded-full px-2 py-0.5 font-medium">{MAT_ICONS[m]} {m}</span>
              ))}
            </div>
          </div>
        </div>

        {/* List */}
        <div className="flex flex-col gap-3 overflow-y-auto pr-1">
          {visible.length === 0 && (
            <div className="bg-white rounded-2xl border border-[#E0EBE4] p-8 text-center">
              <div className="text-4xl mb-3">📭</div>
              <div className="font-semibold text-[#1A2E22] mb-1">Sin resultados</div>
              <div className="text-xs text-[#4A6355]">No hay puntos que acepten {filter} cerca.</div>
            </div>
          )}
          {visible.map((p) => (
            <button key={p.id} onClick={() => setSelected(p)} className={`bg-white rounded-2xl border-2 p-4 text-left transition-all hover:shadow-sm ${selected.id === p.id ? "border-[#087A3D]" : "border-[#E0EBE4] hover:border-[#2E9B50]"}`}>
              <div className="flex items-start justify-between mb-2">
                <div>
                  <div className="font-bold text-sm text-[#1A2E22]">{p.name}</div>
                  <div className="text-xs text-[#4A6355] mt-0.5">📍 {p.dist} · {p.address}</div>
                </div>
                <div className={`flex items-center gap-1 text-xs font-medium shrink-0 ${p.open ? "text-[#3CB96A]" : "text-red-400"}`}>
                  <span className={`w-2 h-2 rounded-full ${p.open ? "bg-[#3CB96A]" : "bg-red-400"}`} />
                  {p.open ? "Abierto" : "Cerrado"}
                </div>
              </div>
              <div className="text-[10px] text-[#4A6355] mb-2">🕐 {p.hours}</div>
              <div className="flex gap-1 flex-wrap mb-3">
                {p.materials.map((m) => (
                  <span key={m} className="text-[10px] bg-[#EEF8EE] text-[#087A3D] rounded-full px-2 py-0.5 font-medium">{MAT_ICONS[m]} {m}</span>
                ))}
              </div>
              <button onClick={(e) => { e.stopPropagation(); setSelected(p); }} className="w-full py-2 bg-[#087A3D] text-white text-xs font-semibold rounded-lg hover:bg-[#065C2D] transition-colors">
                Ver detalles
              </button>
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
