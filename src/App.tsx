import { useState } from "react";
import { AppProvider, useApp } from "@/context/AppContext";
import {
  LeafLogo, HomeIcon, RecycleIcon, MapPinIcon, GiftIcon, StarIcon,
  ActivityIcon, HistoryIcon, LeafIcon, SettingsIcon, HelpIcon,
  HamburgerIcon, BellIcon, ChevronIcon, EcoBanner, XIcon,
} from "@/components/Icons";

import Inicio from "@/pages/Inicio";
import Reciclar from "@/pages/Reciclar";
import PuntosReciclaje from "@/pages/PuntosReciclaje";
import Beneficios from "@/pages/Beneficios";
import MisEcopuntos from "@/pages/MisEcopuntos";
import MiActividad from "@/pages/MiActividad";
import HistorialBeneficios from "@/pages/HistorialBeneficios";
import Educacion from "@/pages/Educacion";
import Configuracion from "@/pages/Configuracion";
import Ayuda from "@/pages/Ayuda";

type Page = "inicio" | "reciclar" | "puntos" | "beneficios" | "ecopuntos" | "actividad" | "historial" | "educacion" | "config" | "ayuda";

const NAV_ITEMS: { id: Page; label: string; icon: React.ComponentType<{ size?: number; active?: boolean }> }[] = [
  { id: "inicio", label: "Inicio", icon: HomeIcon },
  { id: "reciclar", label: "Reciclar", icon: RecycleIcon },
  { id: "puntos", label: "Puntos de reciclaje", icon: MapPinIcon },
  { id: "beneficios", label: "Beneficios", icon: GiftIcon },
  { id: "ecopuntos", label: "Mis Ecopuntos", icon: StarIcon },
  { id: "actividad", label: "Mi actividad", icon: ActivityIcon },
  { id: "historial", label: "Historial de beneficios", icon: HistoryIcon },
  { id: "educacion", label: "Educación ambiental", icon: LeafIcon },
];

const BOTTOM_NAV: { id: Page; label: string; icon: React.ComponentType<{ size?: number; active?: boolean }> }[] = [
  { id: "config", label: "Configuración", icon: SettingsIcon },
  { id: "ayuda", label: "Ayuda", icon: HelpIcon },
];

function PerfilModal({ onClose }: { onClose: () => void }) {
  const [editing, setEditing] = useState(false);
  const [name, setName] = useState("Angel David");
  const [email, setEmail] = useState("angel.david@ecopoint.co");

  return (
    <div className="fixed inset-0 bg-black/40 z-50 flex items-start justify-end" onClick={onClose}>
      <div className="bg-white w-80 min-h-screen shadow-2xl" onClick={(e) => e.stopPropagation()}>
        <div className="bg-gradient-to-br from-[#087A3D] to-[#2E9B50] p-6 text-white">
          <div className="flex items-center justify-between mb-5">
            <h2 className="font-bold text-base">Mi perfil</h2>
            <button onClick={onClose} className="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
              <XIcon size={14} />
            </button>
          </div>
          <div className="flex flex-col items-center">
            <div className="w-20 h-20 rounded-full overflow-hidden border-4 border-white/40 mb-3">
              <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=160&h=160&fit=crop&auto=format&face" alt="Angel David" className="w-full h-full object-cover" />
            </div>
            {editing ? (
              <input value={name} onChange={(e) => setName(e.target.value)} className="text-center font-bold text-lg bg-white/20 rounded-lg px-2 py-1 text-white placeholder-white/60 w-full mb-1 focus:outline-none" />
            ) : (
              <div className="font-bold text-lg mb-1">{name}</div>
            )}
            <div className="text-sm opacity-75">{email}</div>
          </div>
        </div>

        <div className="p-5 space-y-1">
          {[
            { icon: "✏️", label: "Editar perfil", action: () => setEditing(!editing) },
            { icon: "🔑", label: "Cambiar contraseña", action: () => {} },
            { icon: "⚙️", label: "Preferencias", action: () => {} },
          ].map((item) => (
            <button key={item.label} onClick={item.action} className="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-[#2D3A35] hover:bg-[#EEF8EE] transition-colors text-left">
              <span>{item.icon}</span>
              <span className="font-medium">{item.label}</span>
            </button>
          ))}
          <div className="border-t border-[#E0EBE4] my-2" />
          <button className="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-red-500 hover:bg-red-50 transition-colors text-left">
            <span>🚪</span>
            <span className="font-medium">Cerrar sesión</span>
          </button>
        </div>

        {editing && (
          <div className="px-5 pb-5">
            <button onClick={() => setEditing(false)} className="w-full bg-[#087A3D] text-white font-semibold py-2.5 rounded-xl hover:bg-[#065C2D] transition-colors text-sm">
              Guardar cambios
            </button>
          </div>
        )}
      </div>
    </div>
  );
}

function Shell() {
  const { ecopuntos } = useApp();
  const [page, setPage] = useState<Page>("inicio");
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [showPerfil, setShowPerfil] = useState(false);
  const [showNotifs, setShowNotifs] = useState(false);

  const navigate = (p: string) => setPage(p as Page);

  const PAGES: Record<Page, React.ReactNode> = {
    inicio: <Inicio navigate={navigate} />,
    reciclar: <Reciclar navigate={navigate} />,
    puntos: <PuntosReciclaje />,
    beneficios: <Beneficios />,
    ecopuntos: <MisEcopuntos />,
    actividad: <MiActividad />,
    historial: <HistorialBeneficios />,
    educacion: <Educacion />,
    config: <Configuracion />,
    ayuda: <Ayuda />,
  };

  return (
    <div className="flex h-screen w-full bg-[#FAFCFA] overflow-hidden">
      {/* Sidebar */}
      <aside className="flex flex-col bg-white border-r border-[#E0EBE4] shrink-0 transition-all duration-300 overflow-hidden" style={{ width: sidebarOpen ? 248 : 0 }}>
        {/* Logo */}
        <div className="flex items-center gap-2 px-5 py-5 border-b border-[#E0EBE4]">
          <LeafLogo />
          <span className="font-bold text-[#087A3D] text-xl tracking-tight">Eco<span className="text-[#1A2E22]">point</span></span>
        </div>

        {/* Nav */}
        <nav className="flex-1 py-4 overflow-y-auto flex flex-col gap-0.5">
          {NAV_ITEMS.map(({ id, label, icon: Icon }) => (
            <button key={id} onClick={() => setPage(id)} className={`flex items-center gap-3 px-4 mx-2 py-2.5 rounded-xl text-sm font-medium transition-all w-[calc(100%-16px)] text-left ${page === id ? "bg-[#087A3D] text-white shadow-sm" : "text-[#4A6355] hover:bg-[#EEF8EE] hover:text-[#087A3D]"}`}>
              <Icon size={18} active={page === id} />
              <span className="whitespace-nowrap">{label}</span>
            </button>
          ))}
        </nav>

        {/* Eco banner */}
        <div className="mx-3 mb-3 rounded-xl bg-[#EEF8EE] p-4 text-center">
          <div className="text-xs font-semibold text-[#087A3D]">Juntos cuidamos</div>
          <div className="text-xs font-semibold text-[#087A3D] mb-1">nuestro planeta</div>
          <div className="text-[10px] text-[#4A6355]">Cada acción cuenta 🌍</div>
          <EcoBanner />
        </div>

        {/* Bottom nav */}
        <div className="border-t border-[#E0EBE4] py-3 flex flex-col gap-0.5">
          {BOTTOM_NAV.map(({ id, label, icon: Icon }) => (
            <button key={id} onClick={() => setPage(id)} className={`flex items-center gap-3 px-4 mx-2 py-2.5 rounded-xl text-sm font-medium transition-all w-[calc(100%-16px)] text-left ${page === id ? "bg-[#087A3D] text-white shadow-sm" : "text-[#4A6355] hover:bg-[#EEF8EE] hover:text-[#087A3D]"}`}>
              <Icon size={18} active={page === id} />
              <span>{label}</span>
            </button>
          ))}
        </div>
      </aside>

      {/* Main */}
      <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
        {/* Header */}
        <header className="flex items-center gap-4 px-6 py-4 bg-white border-b border-[#E0EBE4] shrink-0">
          <button onClick={() => setSidebarOpen(!sidebarOpen)} className="text-[#4A6355] hover:text-[#087A3D] transition-colors">
            <HamburgerIcon />
          </button>
          {!sidebarOpen && (
            <div className="flex items-center gap-2">
              <LeafLogo />
              <span className="font-bold text-[#087A3D] text-lg tracking-tight">Eco<span className="text-[#1A2E22]">point</span></span>
            </div>
          )}
          <div className="flex-1" />

          {/* Ecopuntos pill */}
          <div className="flex items-center gap-1.5 bg-[#EEF8EE] text-[#087A3D] px-3 py-1.5 rounded-full text-xs font-bold">
            <span>⭐</span>
            <span>{ecopuntos} pts</span>
          </div>

          {/* Notifications */}
          <div className="relative">
            <button onClick={() => setShowNotifs(!showNotifs)} className="w-10 h-10 rounded-full bg-[#F4F6F5] flex items-center justify-center text-[#4A6355] hover:bg-[#EEF8EE] transition-colors">
              <BellIcon />
            </button>
            <span className="absolute -top-0.5 -right-0.5 bg-[#087A3D] text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">3</span>
            {showNotifs && (
              <div className="absolute right-0 top-12 w-72 bg-white rounded-2xl shadow-xl border border-[#E0EBE4] z-40 overflow-hidden">
                <div className="px-4 py-3 border-b border-[#F4F6F5] font-bold text-sm text-[#1A2E22]">Notificaciones</div>
                {[
                  { icon: "♻️", msg: "¡Nuevo reciclaje registrado! +20 Ecopuntos", time: "Hace 2h" },
                  { icon: "🎁", msg: "Nuevo beneficio disponible: Café Verde 2x1", time: "Hace 5h" },
                  { icon: "⭐", msg: "Estás cerca de tu próximo beneficio", time: "Ayer" },
                ].map((n, i) => (
                  <div key={i} className="flex items-start gap-3 px-4 py-3 hover:bg-[#F4F6F5] transition-colors border-b border-[#F4F6F5] last:border-0">
                    <span className="text-lg shrink-0">{n.icon}</span>
                    <div>
                      <div className="text-xs text-[#1A2E22]">{n.msg}</div>
                      <div className="text-[10px] text-[#4A6355] mt-0.5">{n.time}</div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Profile */}
          <button onClick={() => setShowPerfil(true)} className="flex items-center gap-2 hover:bg-[#EEF8EE] rounded-xl px-2 py-1 transition-colors">
            <div className="w-9 h-9 rounded-full overflow-hidden border-2 border-[#E0EBE4]">
              <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=72&h=72&fit=crop&auto=format&face" alt="Angel" className="w-full h-full object-cover" />
            </div>
            <span className="text-sm font-medium text-[#2D3A35]">¡Hola, Angel!</span>
            <ChevronIcon />
          </button>
        </header>

        {/* Page content */}
        <main className="flex-1 overflow-y-auto">
          {PAGES[page]}
        </main>

        {/* Footer */}
        <footer className="bg-[#F0F5F2] border-t border-[#E0EBE4] py-2.5 px-6 text-center text-xs text-[#4A6355] font-medium flex items-center justify-center gap-2 shrink-0">
          🌿 Recicla hoy, gana mañana, transforma el planeta siempre. ♻️
        </footer>
      </div>

      {/* Perfil modal */}
      {showPerfil && <PerfilModal onClose={() => setShowPerfil(false)} />}

      {/* Notif backdrop */}
      {showNotifs && <div className="fixed inset-0 z-30" onClick={() => setShowNotifs(false)} />}
    </div>
  );
}

export default function App() {
  return (
    <AppProvider>
      <Shell />
    </AppProvider>
  );
}
