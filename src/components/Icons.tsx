type IconProps = { size?: number; active?: boolean; color?: string };

export function LeafLogo() {
  return (
    <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
      <rect width="28" height="28" rx="8" fill="#087A3D" />
      <path d="M14 5C14 5 7 9.5 7 16C7 19.866 10.134 23 14 23C17.866 23 21 19.866 21 16C21 9.5 14 5 14 5Z" fill="white" opacity="0.9" />
      <path d="M14 9V21" stroke="#087A3D" strokeWidth="1.8" strokeLinecap="round" />
      <path d="M10.5 13.5L14 10L17.5 13.5" stroke="#087A3D" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

export function HomeIcon({ size = 18, active = false }: IconProps) {
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <path d="M3 9.5L10 3L17 9.5V17H13V13H7V17H3V9.5Z" stroke={active ? "white" : "#4A6355"} strokeWidth="1.5" strokeLinejoin="round" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
    </svg>
  );
}

export function RecycleIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <path d="M10 2.5L12.5 6.5H14.5L11.5 1.5H8.5L5.5 6.5H7.5L10 2.5Z" fill={c} />
      <path d="M5.5 6.5L3 11L5.5 15.5H8.5L6 11L8.5 6.5H5.5Z" fill={c} />
      <path d="M14.5 6.5L17 11L14.5 15.5H11.5L14 11L11.5 6.5H14.5Z" fill={c} />
      <path d="M8.5 15.5H11.5" stroke={c} strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

export function MapPinIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <path d="M10 2C7.239 2 5 4.239 5 7C5 11 10 18 10 18C10 18 15 11 15 7C15 4.239 12.761 2 10 2Z" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
      <circle cx="10" cy="7" r="2" fill={c} />
    </svg>
  );
}

export function GiftIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <rect x="3" y="8" width="14" height="10" rx="1" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
      <path d="M1 8H19V11H1V8Z" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
      <path d="M10 8V18" stroke={c} strokeWidth="1.5" />
      <path d="M10 8C10 8 8 6 8 4.5C8 3.5 8.9 3 10 3C11.1 3 12 3.5 12 4.5C12 6 10 8 10 8Z" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
    </svg>
  );
}

export function StarIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <path d="M10 2L12.09 7.26L17.8 7.63L13.65 11.29L15.18 17L10 14.15L4.82 17L6.35 11.29L2.2 7.63L7.91 7.26L10 2Z" stroke={c} strokeWidth="1.5" strokeLinejoin="round" fill={active ? "rgba(255,255,255,0.3)" : "none"} />
    </svg>
  );
}

export function ActivityIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <rect x="3" y="3" width="14" height="14" rx="2" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
      <path d="M6 13L8 10L10 12L12 8L14 11" stroke={c} strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

export function HistoryIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <circle cx="10" cy="10" r="7" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
      <path d="M10 6V10L13 12" stroke={c} strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

export function LeafIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <path d="M10 3C10 3 4 7 4 13C4 16.314 6.686 19 10 19C13.314 19 16 16.314 16 13C16 7 10 3 10 3Z" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
      <path d="M10 7L10 17" stroke={c} strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

export function SettingsIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <circle cx="10" cy="10" r="2.5" stroke={c} strokeWidth="1.5" />
      <path d="M10 2V4M10 16V18M2 10H4M16 10H18M4.22 4.22L5.64 5.64M14.36 14.36L15.78 15.78M15.78 4.22L14.36 5.64M5.64 14.36L4.22 15.78" stroke={c} strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

export function HelpIcon({ size = 18, active = false }: IconProps) {
  const c = active ? "white" : "#4A6355";
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <circle cx="10" cy="10" r="7" stroke={c} strokeWidth="1.5" fill={active ? "rgba(255,255,255,0.2)" : "none"} />
      <path d="M10 14H10.01" stroke={c} strokeWidth="2" strokeLinecap="round" />
      <path d="M8 8C8 6.9 8.9 6 10 6C11.1 6 12 6.9 12 8C12 9 10 10 10 11" stroke={c} strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

export function HamburgerIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
      <path d="M3 5H17M3 10H17M3 15H17" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

export function BellIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
      <path d="M10 2C7.239 2 5 4.239 5 7V12L3 14V15H17V14L15 12V7C15 4.239 12.761 2 10 2Z" stroke="currentColor" strokeWidth="1.5" strokeLinejoin="round" />
      <path d="M8 15C8 16.105 8.895 17 10 17C11.105 17 12 16.105 12 15" stroke="currentColor" strokeWidth="1.5" />
    </svg>
  );
}

export function ChevronIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
      <path d="M4 6L8 10L12 6" stroke="#4A6355" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

export function ArrowIcon({ color = "#087A3D" }: { color?: string }) {
  return (
    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
      <path d="M3 7H11M8 4L11 7L8 10" stroke={color} strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

export function CheckIcon({ color = "white" }: { color?: string }) {
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
      <path d="M3 8L6.5 11.5L13 4.5" stroke={color} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

export function XIcon({ size = 18 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 20 20" fill="none">
      <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

export function MapPlaceholder() {
  return (
    <svg width="100%" height="100%" viewBox="0 0 320 240" preserveAspectRatio="xMidYMid slice">
      <rect width="320" height="240" fill="#E8EFE4" />
      {[40, 80, 120, 160, 200].map((y) => <rect key={y} x="0" y={y} width="320" height="8" fill="#F0F5EC" opacity="0.9" />)}
      {[50, 110, 170, 230, 290].map((x) => <rect key={x} x={x} y="0" width="8" height="240" fill="#F0F5EC" opacity="0.9" />)}
      {[[10,10,32,22],[60,10,44,22],[120,10,44,22],[180,10,44,22],[240,10,54,22],[10,50,32,22],[60,50,44,22],[120,50,44,22],[180,50,44,22],[240,50,44,22],[10,90,32,22],[60,90,44,22],[120,90,44,22],[180,90,44,22],[240,90,44,22],[10,130,32,22],[60,130,44,22],[120,130,44,22],[180,130,44,22],[240,130,44,22],[10,170,32,22],[60,170,44,22],[120,170,44,22]].map(([x,y,w,h], i) => (
        <rect key={i} x={x} y={y} width={w} height={h} rx="3" fill={i % 2 === 0 ? "#D4E0D4" : "#C8D8C8"} />
      ))}
      {/* Primary pin */}
      <g transform="translate(85, 65)">
        <circle cx="0" cy="0" r="14" fill="#087A3D" opacity="0.15" />
        <path d="M0 -10C-4 -10 -7 -7 -7 -3C-7 3 0 11 0 11C0 11 7 3 7 -3C7 -7 4 -10 0 -10Z" fill="#087A3D" />
        <circle cx="0" cy="-3" r="3" fill="white" />
      </g>
      {/* Secondary pins */}
      {[[165, 55], [230, 100], [55, 145], [190, 160]].map(([cx, cy], i) => (
        <g key={i} transform={`translate(${cx}, ${cy})`}>
          <circle cx="0" cy="0" r="10" fill="#2E9B50" opacity="0.15" />
          <path d="M0 -8C-3 -8 -5.5 -5.5 -5.5 -2C-5.5 2.5 0 9 0 9C0 9 5.5 2.5 5.5 -2C5.5 -5.5 3 -8 0 -8Z" fill="#2E9B50" />
          <circle cx="0" cy="-2" r="2.2" fill="white" />
        </g>
      ))}
    </svg>
  );
}

export function EcoBanner() {
  return (
    <svg width="100%" height="60" viewBox="0 0 160 60" fill="none">
      <ellipse cx="80" cy="52" rx="70" ry="6" fill="#4A9B6A" opacity="0.2" />
      <circle cx="30" cy="44" r="12" fill="#4A9B6A" opacity="0.3" />
      <circle cx="50" cy="48" r="8" fill="#3E7A55" opacity="0.2" />
      <circle cx="50" cy="22" r="5" fill="#5BA87A" />
      <path d="M44 38C44 32 56 32 56 38V45H44V38Z" fill="#5BA87A" />
      <circle cx="68" cy="26" r="4" fill="#7EC49A" />
      <path d="M63 40C63 35 73 35 73 40V45H63V40Z" fill="#7EC49A" />
      <path d="M56 41L63 41" stroke="#5BA87A" strokeWidth="2" strokeLinecap="round" />
      <path d="M110 45V30" stroke="#3E7A55" strokeWidth="2" strokeLinecap="round" />
      <circle cx="110" cy="22" r="10" fill="#5BA87A" opacity="0.6" />
      <circle cx="104" cy="26" r="7" fill="#3E7A55" opacity="0.5" />
      <circle cx="116" cy="24" r="8" fill="#4A9B6A" opacity="0.5" />
    </svg>
  );
}
