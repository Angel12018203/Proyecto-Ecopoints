import { useState } from "react";

type Toggle = { label: string; desc: string; key: string };

const SECTIONS: { title: string; items: Toggle[] }[] = [
  {
    title: "Notificaciones",
    items: [
      { label: "Notificaciones push", desc: "Recibe alertas sobre tus Ecopuntos y reciclajes.", key: "push" },
      { label: "Correo electrónico", desc: "Actualizaciones y boletines de Ecopoint.", key: "email" },
      { label: "Recordatorios de reciclaje", desc: "Te avisamos cuando pasen muchos días sin reciclar.", key: "reminders" },
    ],
  },
  {
    title: "Privacidad",
    items: [
      { label: "Perfil público", desc: "Tu actividad puede ser visible en el ranking.", key: "public" },
      { label: "Compartir impacto", desc: "Permite compartir tus logros en redes sociales.", key: "share" },
    ],
  },
  {
    title: "Preferencias",
    items: [
      { label: "Modo oscuro", desc: "Cambia la apariencia de la plataforma.", key: "dark" },
      { label: "Unidades métricas", desc: "Mostrar cantidades en kg en lugar de libras.", key: "metric" },
    ],
  },
];

export default function Configuracion() {
  const [toggles, setToggles] = useState<Record<string, boolean>>({
    push: true, email: true, reminders: false, public: true, share: true, dark: false, metric: true,
  });
  const [idioma, setIdioma] = useState("Español");

  const toggle = (key: string) => setToggles((prev) => ({ ...prev, [key]: !prev[key] }));

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-xl font-bold text-[#1A2E22]">Configuración</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Personaliza tu experiencia en Ecopoint.</p>
      </div>

      <div className="max-w-2xl space-y-4">
        {SECTIONS.map((s) => (
          <div key={s.title} className="bg-white rounded-2xl border border-[#E0EBE4] p-5">
            <h2 className="text-sm font-bold text-[#1A2E22] mb-4">{s.title}</h2>
            <div className="space-y-4">
              {s.items.map((item) => (
                <div key={item.key} className="flex items-center justify-between">
                  <div>
                    <div className="text-sm font-medium text-[#1A2E22]">{item.label}</div>
                    <div className="text-xs text-[#4A6355]">{item.desc}</div>
                  </div>
                  <button onClick={() => toggle(item.key)} className={`relative w-11 h-6 rounded-full transition-colors ${toggles[item.key] ? "bg-[#087A3D]" : "bg-[#D0D8D4]"}`}>
                    <div className={`absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform ${toggles[item.key] ? "translate-x-5" : "translate-x-0.5"}`} />
                  </button>
                </div>
              ))}
            </div>
          </div>
        ))}

        {/* Language */}
        <div className="bg-white rounded-2xl border border-[#E0EBE4] p-5">
          <h2 className="text-sm font-bold text-[#1A2E22] mb-4">Idioma</h2>
          <div className="flex gap-2">
            {["Español", "English", "Português"].map((l) => (
              <button key={l} onClick={() => setIdioma(l)} className={`px-4 py-2 rounded-xl border text-sm font-medium transition-colors ${idioma === l ? "bg-[#087A3D] text-white border-[#087A3D]" : "border-[#E0EBE4] text-[#4A6355] hover:border-[#087A3D]"}`}>{l}</button>
            ))}
          </div>
        </div>

        {/* Danger zone */}
        <div className="bg-white rounded-2xl border border-[#E0EBE4] p-5">
          <h2 className="text-sm font-bold text-[#1A2E22] mb-4">Cuenta</h2>
          <div className="space-y-2">
            <button className="w-full text-left px-4 py-3 rounded-xl border border-[#E0EBE4] text-sm text-[#4A6355] hover:bg-[#F4F6F5] transition-colors">🔒 Cambiar contraseña</button>
            <button className="w-full text-left px-4 py-3 rounded-xl border border-[#FDDCDC] text-sm text-red-500 hover:bg-[#FFF5F5] transition-colors">🚪 Cerrar sesión</button>
          </div>
        </div>
      </div>
    </div>
  );
}
