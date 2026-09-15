import { createContext, useContext, useState, ReactNode } from "react";

export type ActivityItem = {
  material: string;
  kg: number;
  unit: string;
  punto: string;
  pts: number;
  date: string;
  icon: string;
  color: string;
};

export type RedeemedBenefit = {
  brand: string;
  offer: string;
  pts: number;
  date: string;
  code: string;
  used: boolean;
  img: string;
};

type AppContextType = {
  ecopuntos: number;
  activity: ActivityItem[];
  redeemed: RedeemedBenefit[];
  addActivity: (item: ActivityItem) => void;
  addRedeemed: (b: RedeemedBenefit) => void;
  spendPoints: (pts: number) => void;
};

const AppContext = createContext<AppContextType | null>(null);

const INITIAL_ACTIVITY: ActivityItem[] = [
  { material: "Plástico", kg: 2, unit: "kg", punto: "EcoPunto Centro", pts: 20, date: "Hoy, 10:30 a.m.", icon: "♻️", color: "#3E9E6B" },
  { material: "Cartón", kg: 1.5, unit: "kg", punto: "EcoPunto Norte", pts: 12, date: "Ayer, 4:15 p.m.", icon: "📦", color: "#C97D3A" },
  { material: "Latas", kg: 1, unit: "kg", punto: "EcoPunto Centro", pts: 15, date: "12 may, 9:20 a.m.", icon: "🥫", color: "#6B8EA8" },
];

const INITIAL_REDEEMED: RedeemedBenefit[] = [
  { brand: "Café Verde", offer: "2x1 en bebidas", pts: 400, date: "15 de agosto de 2026", code: "ECO-4827", used: false, img: "https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=200&h=140&fit=crop&auto=format" },
  { brand: "Cine Planet", offer: "Entrada 2D", pts: 800, date: "2 de agosto de 2026", code: "ECO-3912", used: true, img: "https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=200&h=140&fit=crop&auto=format" },
];

export function AppProvider({ children }: { children: ReactNode }) {
  const [ecopuntos, setEcopuntos] = useState(350);
  const [activity, setActivity] = useState<ActivityItem[]>(INITIAL_ACTIVITY);
  const [redeemed, setRedeemed] = useState<RedeemedBenefit[]>(INITIAL_REDEEMED);

  const addActivity = (item: ActivityItem) => {
    setActivity((prev) => [item, ...prev]);
    setEcopuntos((p) => p + item.pts);
  };

  const addRedeemed = (b: RedeemedBenefit) => {
    setRedeemed((prev) => [b, ...prev]);
  };

  const spendPoints = (pts: number) => {
    setEcopuntos((p) => Math.max(0, p - pts));
  };

  return (
    <AppContext.Provider value={{ ecopuntos, activity, redeemed, addActivity, addRedeemed, spendPoints }}>
      {children}
    </AppContext.Provider>
  );
}

export function useApp() {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error("useApp must be used within AppProvider");
  return ctx;
}
