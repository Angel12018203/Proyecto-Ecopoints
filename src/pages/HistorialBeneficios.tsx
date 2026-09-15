import { useApp } from "@/context/AppContext";

export default function HistorialBeneficios() {
  const { redeemed } = useApp();

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-xl font-bold text-[#1A2E22]">Historial de beneficios</h1>
        <p className="text-sm text-[#4A6355] mt-0.5">Beneficios que has canjeado con tus Ecopuntos.</p>
      </div>

      {redeemed.length === 0 ? (
        <div className="bg-white rounded-2xl border border-[#E0EBE4] p-12 text-center">
          <div className="text-5xl mb-4">🎁</div>
          <div className="font-bold text-[#1A2E22] mb-2">Aún no has canjeado beneficios</div>
          <div className="text-sm text-[#4A6355]">Acumula Ecopuntos reciclando y canjéalos por increíbles beneficios.</div>
        </div>
      ) : (
        <div className="space-y-3">
          {redeemed.map((r, i) => (
            <div key={i} className="bg-white rounded-2xl border border-[#E0EBE4] p-5 flex gap-4 items-start">
              <div className="relative w-20 h-16 rounded-xl overflow-hidden shrink-0">
                <img src={r.img} alt={r.brand} className="w-full h-full object-cover" />
              </div>
              <div className="flex-1 min-w-0">
                <div className="flex items-start justify-between mb-1">
                  <div>
                    <div className="font-bold text-sm text-[#1A2E22]">🎁 {r.brand}</div>
                    <div className="text-xs text-[#4A6355]">{r.offer}</div>
                  </div>
                  <div className={`flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full ${r.used ? "bg-[#F4F6F5] text-[#4A6355]" : "bg-[#EEF8EE] text-[#087A3D]"}`}>
                    <span className={`w-1.5 h-1.5 rounded-full ${r.used ? "bg-[#9AB0A4]" : "bg-[#087A3D]"}`} />
                    {r.used ? "Utilizado" : "Disponible"}
                  </div>
                </div>
                <div className="text-[10px] text-[#4A6355] mb-2">Canjeado el {r.date}</div>
                <div className="flex items-center justify-between">
                  <div className="bg-[#EEF8EE] border-2 border-dashed border-[#087A3D]/40 rounded-lg px-3 py-1">
                    <span className="text-xs font-bold text-[#087A3D] tracking-widest">{r.code}</span>
                  </div>
                  <div className="flex items-center gap-1 text-xs text-[#4A6355]">
                    <span>⭐</span>
                    <span>{r.pts} puntos</span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
