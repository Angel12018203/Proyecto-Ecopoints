import { useState } from "react";
import { useApp } from "@/context/AppContext";
import { XIcon } from "@/components/Icons";

const ALL_BENEFITS = [
  { id: 1, brand: "Café Verde", offer: "2x1 en bebidas", pts: 400, cat: "Descuentos", desc: "Disfruta un 2x1 en cualquier bebida del menú de Café Verde. Válido en todas las sedes.", img: "https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=400&h=200&fit=crop&auto=format", color: "#2D5A3D" },
  { id: 2, brand: "EcoMarket", offer: "15% de descuento", pts: 600, cat: "Descuentos", desc: "15% de descuento en toda tu compra en EcoMarket. Productos orgánicos y sostenibles.", img: "https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=200&fit=crop&auto=format", color: "#4A7C59" },
  { id: 3, brand: "Librería Natura", offer: "10% de descuento", pts: 500, cat: "Productos", desc: "10% de descuento en libros, papelería y artículos de arte. Librería comprometida con el medio ambiente.", img: "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&h=200&fit=crop&auto=format", color: "#6B4E3D" },
  { id: 4, brand: "Cine Planet", offer: "Entrada 2D", pts: 800, cat: "Experiencias", desc: "Una entrada para cualquier película 2D en cualquier sede de Cine Planet. ¡Disfruta el cine!", img: "https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=400&h=200&fit=crop&auto=format", color: "#1A2A4A" },
  { id: 5, brand: "FarmaBio", offer: "20% en suplementos", pts: 450, cat: "Descuentos", desc: "20% de descuento en suplementos naturales y vitaminas en todas las tiendas FarmaBio.", img: "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=200&fit=crop&auto=format", color: "#3D6B5A" },
  { id: 6, brand: "Yoga Verde", offer: "Clase gratis", pts: 350, cat: "Experiencias", desc: "Una clase de yoga gratis en el estudio Yoga Verde. Conecta con la naturaleza y tu bienestar.", img: "https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400&h=200&fit=crop&auto=format", color: "#5A3D6B" },
];

const CATS = ["Todos", "Descuentos", "Productos", "Experiencias"];

export default function Beneficios() {
  const { ecopuntos, addRedeemed, spendPoints } = useApp();
  const [cat, setCat] = useState("Todos");
  const [modal, setModal] = useState<typeof ALL_BENEFITS[0] | null>(null);
  const [redeemSuccess, setRedeemSuccess] = useState(false);

  const visible = cat === "Todos" ? ALL_BENEFITS : ALL_BENEFITS.filter((b) => b.cat === cat);

  const redeem = () => {
    if (!modal) return;
    spendPoints(modal.pts);
    addRedeemed({
      brand: modal.brand,
      offer: modal.offer,
      pts: modal.pts,
      date: new Date().toLocaleDateString("es-CO", { day: "numeric", month: "long", year: "numeric" }),
      code: `ECO-${Math.floor(1000 + Math.random() * 9000)}`,
      used: false,
      img: modal.img,
    });
    setRedeemSuccess(true);
  };

  return (
    <div className="p-6">
      <div className="mb-5 flex items-start justify-between">
        <div>
          <h1 className="text-xl font-bold text-[#1A2E22]">Beneficios</h1>
          <p className="text-sm text-[#4A6355] mt-0.5">Canjea tus Ecopuntos por beneficios.</p>
        </div>
        <div className="bg-[#087A3D] text-white rounded-xl px-4 py-2 flex items-center gap-2">
          <span className="text-lg">⭐</span>
          <div>
            <div className="text-lg font-extrabold leading-none">{ecopuntos}</div>
            <div className="text-[10px] opacity-80">Ecopuntos disponibles</div>
          </div>
        </div>
      </div>

      {/* Category filters */}
      <div className="flex gap-2 mb-6">
        {CATS.map((c) => (
          <button key={c} onClick={() => setCat(c)} className={`px-4 py-1.5 rounded-full text-xs font-semibold border transition-colors ${cat === c ? "bg-[#087A3D] text-white border-[#087A3D]" : "bg-white text-[#4A6355] border-[#E0EBE4] hover:border-[#087A3D] hover:text-[#087A3D]"}`}>{c}</button>
        ))}
      </div>

      {/* Grid */}
      <div className="grid grid-cols-3 gap-4">
        {visible.map((b) => {
          const canRedeem = ecopuntos >= b.pts;
          return (
            <button key={b.id} onClick={() => { setModal(b); setRedeemSuccess(false); }} className="bg-white rounded-2xl border border-[#E0EBE4] overflow-hidden hover:shadow-md transition-shadow text-left group">
              <div className="relative h-36 overflow-hidden">
                <img src={b.img} alt={b.brand} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                <div className="absolute bottom-3 left-3">
                  <div className="font-bold text-white text-sm">{b.offer}</div>
                  <div className="text-white/80 text-xs">{b.brand}</div>
                </div>
                <div className={`absolute top-3 right-3 text-xs font-bold px-2 py-1 rounded-full ${canRedeem ? "bg-[#087A3D] text-white" : "bg-white/90 text-[#4A6355]"}`}>
                  {b.cat}
                </div>
              </div>
              <div className="p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-1">
                    <span>⭐</span>
                    <span className="font-bold text-sm text-[#1A2E22]">{b.pts} puntos</span>
                  </div>
                  <span className={`text-xs font-semibold ${canRedeem ? "text-[#087A3D]" : "text-[#C97D3A]"}`}>
                    {canRedeem ? "✓ Disponible" : `Faltan ${b.pts - ecopuntos} pts`}
                  </span>
                </div>
              </div>
            </button>
          );
        })}
      </div>

      {/* Modal */}
      {modal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={() => { setModal(null); setRedeemSuccess(false); }}>
          <div className="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-xl" onClick={(e) => e.stopPropagation()}>
            <div className="relative h-48">
              <img src={modal.img} alt={modal.brand} className="w-full h-full object-cover" />
              <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
              <button onClick={() => { setModal(null); setRedeemSuccess(false); }} className="absolute top-3 right-3 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/40 transition-colors">
                <XIcon size={16} />
              </button>
              <div className="absolute bottom-4 left-4">
                <div className="text-white font-bold text-xl">{modal.offer}</div>
                <div className="text-white/80 text-sm">{modal.brand}</div>
              </div>
            </div>
            <div className="p-6">
              {redeemSuccess ? (
                <div className="text-center py-4">
                  <div className="text-4xl mb-3">🎉</div>
                  <h3 className="text-xl font-bold text-[#1A2E22] mb-2">¡Beneficio canjeado!</h3>
                  <p className="text-sm text-[#4A6355] mb-4">Usa este código en el establecimiento:</p>
                  <div className="bg-[#EEF8EE] border-2 border-dashed border-[#087A3D] rounded-xl py-4 px-6 mb-4">
                    <div className="text-2xl font-extrabold text-[#087A3D] tracking-widest">ECO-4827</div>
                    <div className="text-xs text-[#4A6355] mt-1">Código de canje único</div>
                  </div>
                  <button onClick={() => { setModal(null); setRedeemSuccess(false); }} className="w-full bg-[#087A3D] text-white font-semibold py-3 rounded-xl hover:bg-[#065C2D] transition-colors">
                    ¡Perfecto!
                  </button>
                </div>
              ) : (
                <>
                  <p className="text-sm text-[#4A6355] mb-5">{modal.desc}</p>
                  <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center gap-2">
                      <span>⭐</span>
                      <span className="font-bold text-[#1A2E22]">{modal.pts} Ecopuntos</span>
                    </div>
                    <div className="flex items-center gap-2 text-sm text-[#4A6355]">
                      Tu saldo: <span className="font-bold text-[#087A3D]">{ecopuntos} pts</span>
                    </div>
                  </div>
                  {ecopuntos < modal.pts && (
                    <div className="bg-[#FFF8EC] border border-[#F5B82E]/40 rounded-xl p-3 mb-4 text-sm text-[#C97D3A] font-medium">
                      ⚠️ Te faltan {modal.pts - ecopuntos} puntos para obtener este beneficio.
                    </div>
                  )}
                  <button disabled={ecopuntos < modal.pts} onClick={redeem} className="w-full bg-[#087A3D] text-white font-semibold py-3 rounded-xl hover:bg-[#065C2D] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    {ecopuntos >= modal.pts ? "Canjear beneficio" : "Puntos insuficientes"}
                  </button>
                </>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
