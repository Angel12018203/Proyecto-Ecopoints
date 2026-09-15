import { useState } from "react";
import { useApp } from "@/context/AppContext";
import { CheckIcon } from "@/components/Icons";

const MATERIALS = [
  { id: "plastico", label: "Plástico", icon: "🧴", pts: 10, color: "#3E9E6B" },
  { id: "carton", label: "Cartón", icon: "📦", pts: 8, color: "#C97D3A" },
  { id: "papel", label: "Papel", icon: "📄", pts: 6, color: "#7E9BA8" },
  { id: "latas", label: "Latas", icon: "🥫", pts: 12, color: "#6B8EA8" },
  { id: "vidrio", label: "Vidrio", icon: "🍾", pts: 9, color: "#8B6BA8" },
  { id: "otro", label: "Otro", icon: "♻️", pts: 5, color: "#087A3D" },
];

const PUNTOS = [
  { id: "centro", name: "EcoPunto Centro", dist: "500 m", open: true },
  { id: "norte", name: "EcoPunto Norte", dist: "1.2 km", open: true },
  { id: "sur", name: "EcoPunto Sur", dist: "2.1 km", open: false },
];

type Props = { navigate: (page: string) => void };

export default function Reciclar({ navigate }: Props) {
  const { addActivity } = useApp();
  const [step, setStep] = useState(1);
  const [selectedMaterial, setSelectedMaterial] = useState<typeof MATERIALS[0] | null>(null);
  const [cantidad, setCantidad] = useState("2");
  const [unit] = useState("kg");
  const [selectedPunto, setSelectedPunto] = useState<typeof PUNTOS[0] | null>(null);
  const [success, setSuccess] = useState(false);

  const earned = selectedMaterial ? Math.round(Number(cantidad) * selectedMaterial.pts) : 0;

  const confirm = () => {
    if (!selectedMaterial || !selectedPunto) return;
    const now = new Date();
    addActivity({
      material: selectedMaterial.label,
      kg: Number(cantidad),
      unit,
      punto: selectedPunto.name,
      pts: earned,
      date: `Hoy, ${now.getHours()}:${String(now.getMinutes()).padStart(2, "0")} ${now.getHours() >= 12 ? "p.m." : "a.m."}`,
      icon: selectedMaterial.icon,
      color: selectedMaterial.color,
    });
    setSuccess(true);
  };

  if (success) {
    return (
      <div className="p-6 flex items-center justify-center min-h-[70vh]">
        <div className="bg-white rounded-2xl border border-[#E0EBE4] p-10 max-w-sm w-full text-center shadow-sm">
          <div className="w-20 h-20 rounded-full bg-[#EEF8EE] flex items-center justify-center text-4xl mx-auto mb-5">🎉</div>
          <h2 className="text-2xl font-bold text-[#1A2E22] mb-2">¡Reciclaje registrado!</h2>
          <div className="text-4xl font-extrabold text-[#087A3D] my-4">+{earned}</div>
          <div className="text-sm text-[#4A6355] mb-2">Ecopuntos ganados</div>
          <div className="text-sm font-semibold text-[#1A2E22] mb-6">
            Material: {selectedMaterial?.icon} {selectedMaterial?.label} · {cantidad} {unit}<br />
            Punto: {selectedPunto?.name}
          </div>
          <button onClick={() => navigate("inicio")} className="w-full bg-[#087A3D] text-white font-semibold py-3 rounded-xl hover:bg-[#065C2D] transition-colors">
            Volver al inicio
          </button>
          <button onClick={() => { setStep(1); setSelectedMaterial(null); setCantidad("2"); setSelectedPunto(null); setSuccess(false); }} className="w-full mt-3 border border-[#E0EBE4] text-[#4A6355] font-semibold py-3 rounded-xl hover:bg-[#F4F6F5] transition-colors">
            Registrar otro reciclaje
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-xl font-bold text-[#1A2E22]">Registrar reciclaje</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Registra tus materiales y gana Ecopuntos.</p>
      </div>

      {/* Stepper */}
      <div className="flex items-center gap-0 mb-8">
        {[1, 2, 3, 4].map((s, i) => (
          <div key={s} className="flex items-center flex-1 last:flex-none">
            <div className={`w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm shrink-0 transition-colors ${step > s ? "bg-[#087A3D] text-white" : step === s ? "bg-[#087A3D] text-white ring-4 ring-[#087A3D]/20" : "bg-[#E0EBE4] text-[#4A6355]"}`}>
              {step > s ? <CheckIcon /> : s}
            </div>
            <div className="flex-1 mx-2 last:hidden">
              <div className={`h-1 rounded-full transition-colors ${step > s ? "bg-[#087A3D]" : "bg-[#E0EBE4]"}`} />
            </div>
          </div>
        ))}
        <div className="ml-4 flex gap-6 text-xs font-medium text-[#4A6355]">
          {["Material", "Cantidad", "Punto", "Confirmar"].map((l, i) => (
            <span key={l} className={step === i + 1 ? "text-[#087A3D] font-semibold" : ""}>{l}</span>
          ))}
        </div>
      </div>

      <div className="max-w-2xl">
        {/* Step 1 */}
        {step === 1 && (
          <div className="bg-white rounded-2xl border border-[#E0EBE4] p-6">
            <h2 className="text-base font-bold text-[#1A2E22] mb-1">Paso 1 — Seleccionar material</h2>
            <p className="text-xs text-[#4A6355] mb-5">¿Qué vas a reciclar hoy?</p>
            <div className="grid grid-cols-3 gap-3">
              {MATERIALS.map((m) => (
                <button key={m.id} onClick={() => setSelectedMaterial(m)} className={`flex flex-col items-center gap-2 p-5 rounded-xl border-2 transition-all ${selectedMaterial?.id === m.id ? "border-[#087A3D] bg-[#EEF8EE]" : "border-[#E0EBE4] hover:border-[#2E9B50] hover:bg-[#F4FAF6]"}`}>
                  <span className="text-3xl">{m.icon}</span>
                  <span className="text-sm font-semibold text-[#1A2E22]">{m.label}</span>
                  <span className="text-xs text-[#087A3D] font-medium">~{m.pts} pts/kg</span>
                </button>
              ))}
            </div>
            <button disabled={!selectedMaterial} onClick={() => setStep(2)} className="mt-6 w-full bg-[#087A3D] text-white font-semibold py-3 rounded-xl hover:bg-[#065C2D] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
              Continuar →
            </button>
          </div>
        )}

        {/* Step 2 */}
        {step === 2 && selectedMaterial && (
          <div className="bg-white rounded-2xl border border-[#E0EBE4] p-6">
            <h2 className="text-base font-bold text-[#1A2E22] mb-1">Paso 2 — Registrar cantidad</h2>
            <p className="text-xs text-[#4A6355] mb-5">¿Cuánto material vas a reciclar?</p>
            <div className="flex items-center gap-3 mb-4">
              <div className="w-12 h-12 rounded-xl bg-[#EEF8EE] flex items-center justify-center text-2xl">{selectedMaterial.icon}</div>
              <span className="font-semibold text-[#1A2E22]">{selectedMaterial.label}</span>
            </div>
            <div className="flex items-center gap-3 mb-6">
              <button onClick={() => setCantidad(String(Math.max(0.5, Number(cantidad) - 0.5)))} className="w-10 h-10 rounded-xl border border-[#E0EBE4] text-xl font-bold text-[#4A6355] hover:bg-[#F4F6F5] transition-colors">−</button>
              <input type="number" min="0.5" step="0.5" value={cantidad} onChange={(e) => setCantidad(e.target.value)} className="flex-1 text-center text-2xl font-bold text-[#1A2E22] border border-[#E0EBE4] rounded-xl py-3 focus:outline-none focus:border-[#087A3D]" />
              <span className="text-lg font-semibold text-[#4A6355]">kg</span>
              <button onClick={() => setCantidad(String(Number(cantidad) + 0.5))} className="w-10 h-10 rounded-xl border border-[#E0EBE4] text-xl font-bold text-[#4A6355] hover:bg-[#F4F6F5] transition-colors">+</button>
            </div>
            <div className="bg-[#EEF8EE] rounded-xl p-4 text-center mb-6">
              <div className="text-xs text-[#4A6355] mb-1">Ecopuntos estimados</div>
              <div className="text-2xl font-extrabold text-[#087A3D]">+{earned} Ecopuntos</div>
            </div>
            <div className="flex gap-3">
              <button onClick={() => setStep(1)} className="flex-1 border border-[#E0EBE4] text-[#4A6355] font-semibold py-3 rounded-xl hover:bg-[#F4F6F5] transition-colors">← Atrás</button>
              <button onClick={() => setStep(3)} className="flex-1 bg-[#087A3D] text-white font-semibold py-3 rounded-xl hover:bg-[#065C2D] transition-colors">Continuar →</button>
            </div>
          </div>
        )}

        {/* Step 3 */}
        {step === 3 && (
          <div className="bg-white rounded-2xl border border-[#E0EBE4] p-6">
            <h2 className="text-base font-bold text-[#1A2E22] mb-1">Paso 3 — Seleccionar punto de reciclaje</h2>
            <p className="text-xs text-[#4A6355] mb-5">Elige dónde vas a entregar tus materiales.</p>
            <div className="flex flex-col gap-3 mb-6">
              {PUNTOS.map((p) => (
                <button key={p.id} onClick={() => p.open && setSelectedPunto(p)} disabled={!p.open} className={`flex items-center gap-4 p-4 rounded-xl border-2 transition-all text-left ${selectedPunto?.id === p.id ? "border-[#087A3D] bg-[#EEF8EE]" : p.open ? "border-[#E0EBE4] hover:border-[#2E9B50] hover:bg-[#F4FAF6]" : "border-[#E0EBE4] opacity-50 cursor-not-allowed"}`}>
                  <span className="text-2xl">📍</span>
                  <div className="flex-1">
                    <div className="font-semibold text-sm text-[#1A2E22]">{p.name}</div>
                    <div className="text-xs text-[#4A6355]">{p.dist}</div>
                  </div>
                  <div className={`flex items-center gap-1 text-xs font-medium ${p.open ? "text-[#3CB96A]" : "text-red-400"}`}>
                    <span className={`w-2 h-2 rounded-full ${p.open ? "bg-[#3CB96A]" : "bg-red-400"}`} />
                    {p.open ? "Abierto" : "Cerrado"}
                  </div>
                  {selectedPunto?.id === p.id && <div className="w-5 h-5 rounded-full bg-[#087A3D] flex items-center justify-center"><CheckIcon /></div>}
                </button>
              ))}
            </div>
            <div className="flex gap-3">
              <button onClick={() => setStep(2)} className="flex-1 border border-[#E0EBE4] text-[#4A6355] font-semibold py-3 rounded-xl hover:bg-[#F4F6F5] transition-colors">← Atrás</button>
              <button disabled={!selectedPunto} onClick={() => setStep(4)} className="flex-1 bg-[#087A3D] text-white font-semibold py-3 rounded-xl hover:bg-[#065C2D] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">Continuar →</button>
            </div>
          </div>
        )}

        {/* Step 4 */}
        {step === 4 && selectedMaterial && selectedPunto && (
          <div className="bg-white rounded-2xl border border-[#E0EBE4] p-6">
            <h2 className="text-base font-bold text-[#1A2E22] mb-1">Paso 4 — Confirmar</h2>
            <p className="text-xs text-[#4A6355] mb-5">Revisa el resumen de tu reciclaje.</p>
            <div className="bg-[#F4F6F5] rounded-xl p-5 mb-6 space-y-3">
              {[
                ["Material", `${selectedMaterial.icon} ${selectedMaterial.label}`],
                ["Cantidad", `${cantidad} ${unit}`],
                ["Punto de reciclaje", selectedPunto.name],
              ].map(([k, v]) => (
                <div key={k} className="flex justify-between">
                  <span className="text-sm text-[#4A6355]">{k}</span>
                  <span className="text-sm font-semibold text-[#1A2E22]">{v}</span>
                </div>
              ))}
              <div className="border-t border-[#E0EBE4] pt-3 flex justify-between">
                <span className="text-sm font-bold text-[#4A6355]">Ecopuntos obtenidos</span>
                <span className="text-lg font-extrabold text-[#087A3D]">+{earned}</span>
              </div>
            </div>
            <div className="flex gap-3">
              <button onClick={() => setStep(3)} className="flex-1 border border-[#E0EBE4] text-[#4A6355] font-semibold py-3 rounded-xl hover:bg-[#F4F6F5] transition-colors">← Atrás</button>
              <button onClick={confirm} className="flex-1 bg-[#087A3D] text-white font-semibold py-3 rounded-xl hover:bg-[#065C2D] transition-colors">✅ Confirmar reciclaje</button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
