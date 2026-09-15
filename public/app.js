/* ═══════════════════════════════════════════════════════════
   ECOPOINT — Vanilla JS SPA
   ═══════════════════════════════════════════════════════════ */

/* ─── Global State ──────────────────────────────────────── */
const state = {
  page: 'inicio',
  ecopuntos: 350,
  sidebarOpen: true,
  notifOpen: false,
  perfilOpen: false,
  activity: [
    { material: 'Plástico', kg: 2, unit: 'kg', punto: 'EcoPunto Centro', pts: 20, date: 'Hoy, 10:30 a.m.', icon: '♻️', color: '#3E9E6B' },
    { material: 'Cartón',   kg: 1.5, unit: 'kg', punto: 'EcoPunto Norte', pts: 12, date: 'Ayer, 4:15 p.m.', icon: '📦', color: '#C97D3A' },
    { material: 'Latas',    kg: 1,   unit: 'kg', punto: 'EcoPunto Centro', pts: 15, date: '12 may, 9:20 a.m.', icon: '🥫', color: '#6B8EA8' },
  ],
  redeemed: [
    { brand:'Café Verde',  offer:'2x1 en bebidas', pts:400, date:'15 de agosto de 2026', code:'ECO-4827', used:false, img:'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=200&h=140&fit=crop&auto=format' },
    { brand:'Cine Planet', offer:'Entrada 2D',      pts:800, date:'2 de agosto de 2026',  code:'ECO-3912', used:true,  img:'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=200&h=140&fit=crop&auto=format' },
  ],
  reciclar: { step: 1, material: null, cantidad: 2, punto: null },
  benefFilter: 'Todos',
  puntosFilter: 'Todos',
  ecoptsFilter: 'Todos',
  eduActive: null,
  faqOpen: null,
  configToggles: { push: true, email: true, reminders: false, public: true, share: true, dark: false, metric: true },
  configLang: 'Español',
  selectedPunto: 'centro',
};

/* ─── Data ───────────────────────────────────────────────── */
const MATERIALS = [
  { id:'plastico', label:'Plástico', icon:'🧴', pts:10, color:'#3E9E6B' },
  { id:'carton',   label:'Cartón',   icon:'📦', pts:8,  color:'#C97D3A' },
  { id:'papel',    label:'Papel',    icon:'📄', pts:6,  color:'#7E9BA8' },
  { id:'latas',    label:'Latas',    icon:'🥫', pts:12, color:'#6B8EA8' },
  { id:'vidrio',   label:'Vidrio',   icon:'🍾', pts:9,  color:'#8B6BA8' },
  { id:'otro',     label:'Otro',     icon:'♻️', pts:5,  color:'#087A3D' },
];
const PUNTOS = [
  { id:'centro',    name:'EcoPunto Centro',    dist:'500 m',  open:true,  addr:'Calle 45 #12-34, Centro', mats:['Plástico','Cartón','Latas','Vidrio'],       hours:'Lun–Sáb 8:00–18:00' },
  { id:'norte',     name:'EcoPunto Norte',     dist:'1.2 km', open:true,  addr:'Av. Norte #89-10, Norte', mats:['Plástico','Papel','Cartón'],                 hours:'Lun–Vie 9:00–17:00' },
  { id:'sur',       name:'EcoPunto Sur',       dist:'2.1 km', open:false, addr:'Carrera 7 #23-45, Sur',   mats:['Vidrio','Latas','Plástico'],                 hours:'Mar–Sáb 10:00–16:00' },
  { id:'occidente', name:'EcoPunto Occidente', dist:'3.4 km', open:true,  addr:'Cl. 80 #50-20, Occidente',mats:['Papel','Cartón','Plástico','Latas'],         hours:'Lun–Dom 7:00–19:00' },
];
const ALL_BENEFITS = [
  { id:1, brand:'Café Verde',      offer:'2x1 en bebidas',     pts:400, cat:'Descuentos',  desc:'Disfruta un 2x1 en cualquier bebida del menú de Café Verde.', img:'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=400&h=200&fit=crop&auto=format' },
  { id:2, brand:'EcoMarket',       offer:'15% de descuento',   pts:600, cat:'Descuentos',  desc:'15% de descuento en toda tu compra en EcoMarket.',            img:'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=200&fit=crop&auto=format' },
  { id:3, brand:'Librería Natura', offer:'10% de descuento',   pts:500, cat:'Productos',   desc:'10% de descuento en libros, papelería y artículos de arte.',  img:'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&h=200&fit=crop&auto=format' },
  { id:4, brand:'Cine Planet',     offer:'Entrada 2D',         pts:800, cat:'Experiencias',desc:'Una entrada para cualquier película 2D en Cine Planet.',      img:'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=400&h=200&fit=crop&auto=format' },
  { id:5, brand:'FarmaBio',        offer:'20% en suplementos', pts:450, cat:'Descuentos',  desc:'20% de descuento en suplementos naturales y vitaminas.',      img:'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=200&fit=crop&auto=format' },
  { id:6, brand:'Yoga Verde',      offer:'Clase gratis',       pts:350, cat:'Experiencias',desc:'Una clase de yoga gratis en Yoga Verde.',                     img:'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400&h=200&fit=crop&auto=format' },
];
const EDU_MATS = [
  { id:'plastico', icon:'🧴', title:'Plástico', color:'#3E9E6B', bg:'#EEF8EE',
    steps:['Enjuaga los envases antes de reciclar.','Retira tapas y etiquetas.','Aplana botellas para ahorrar espacio.','Busca el símbolo ♻ en el envase.'],
    fact:'Reciclar 1 kg de plástico ahorra hasta 2 kg de CO₂.' },
  { id:'carton',  icon:'📦', title:'Cartón',   color:'#C97D3A', bg:'#FFF4EB',
    steps:['Dobla las cajas para ocupar menos espacio.','Retira cintas adhesivas y grapas.','Asegúrate de que esté seco y limpio.','Separa cartón corrugado del fino.'],
    fact:'Una tonelada de cartón reciclado salva 17 árboles.' },
  { id:'vidrio',  icon:'🍾', title:'Vidrio',   color:'#8B6BA8', bg:'#F4EEFF',
    steps:['Enjuaga bien los envases de vidrio.','Retira tapas metálicas o plásticas.','No incluyas vidrio roto o de ventanas.','Separa por color si el punto lo requiere.'],
    fact:'El vidrio puede reciclarse infinitas veces sin perder calidad.' },
  { id:'latas',   icon:'🥫', title:'Latas',    color:'#6B8EA8', bg:'#EAF4FA',
    steps:['Enjuaga y aplana las latas.','Incluye latas de alimentos y bebidas.','Retira etiquetas de papel si es posible.','No incluyas aerosoles bajo presión.'],
    fact:'Reciclar aluminio usa 95% menos energía que producirlo nuevo.' },
  { id:'papel',   icon:'📄', title:'Papel',    color:'#7E9BA8', bg:'#EEF5FA',
    steps:['Evita papel encerado o manchado.','Retira clips y grapas metálicas.','Periódicos y revistas son bienvenidos.','El papel mojado pierde calidad.'],
    fact:'Reciclar papel reduce el consumo de agua en un 60%.' },
];
const FAQS = [
  { q:'¿Cómo gano Ecopuntos?',               a:'Ganas Ecopuntos cada vez que registras un reciclaje. El número depende del material y cantidad. Por ejemplo, 1 kg de plástico da ~10 puntos.' },
  { q:'¿Dónde puedo reciclar?',              a:'Encuentra los puntos más cercanos en "Puntos de reciclaje". El mapa muestra todos los EcoPuntos con horarios y materiales aceptados.' },
  { q:'¿Cómo canjeo mis puntos?',            a:'Ve a "Beneficios", selecciona uno y haz clic en "Canjear beneficio". Recibirás un código único para usar en el establecimiento aliado.' },
  { q:'¿Qué materiales puedo reciclar?',     a:'Plástico, Cartón, Papel, Latas, Vidrio y más. Cada punto acepta distintos materiales; verifícalo antes de ir.' },
  { q:'¿Mis Ecopuntos vencen?',              a:'Actualmente los Ecopuntos no vencen. Te recomendamos canjarlos regularmente para aprovechar las mejores ofertas.' },
  { q:'¿Cómo ver mi impacto ambiental?',     a:'En "Mi actividad" encuentras kg reciclados, CO₂ evitado, número de reciclajes y estadísticas de tu contribución.' },
];
const MONTHLY_DATA = [
  {mes:'Ene',kg:8},{mes:'Feb',kg:12},{mes:'Mar',kg:18},{mes:'Abr',kg:25},
  {mes:'May',kg:22},{mes:'Jun',kg:30},{mes:'Jul',kg:28},{mes:'Ago',kg:38},
];

/* ─── Helpers ────────────────────────────────────────────── */
const $ = id => document.getElementById(id);
const earned = () => {
  const m = MATERIALS.find(x => x.id === state.reciclar.material);
  return m ? Math.round(state.reciclar.cantidad * m.pts) : 0;
};
function statusDot(open) {
  return `<span style="width:8px;height:8px;border-radius:50%;background:${open?'#3CB96A':'#E53E3E'};display:inline-block;margin-right:4px;"></span>
          <span style="color:${open?'#3CB96A':'#E53E3E'};font-size:.75rem;font-weight:600;">${open?'Abierto':'Cerrado'}</span>`;
}
function mapSVG() {
  return `<svg viewBox="0 0 320 240" preserveAspectRatio="xMidYMid slice" width="100%" height="100%" style="display:block;">
    <rect width="320" height="240" fill="#E8EFE4"/>
    ${[40,80,120,160,200].map(y=>`<rect x="0" y="${y}" width="320" height="8" fill="#F0F5EC" opacity=".9"/>`).join('')}
    ${[50,110,170,230,290].map(x=>`<rect x="${x}" y="0" width="8" height="240" fill="#F0F5EC" opacity=".9"/>`).join('')}
    ${[[10,10,32,22],[60,10,44,22],[120,10,44,22],[180,10,44,22],[240,10,54,22],[10,50,32,22],[60,50,44,22],[120,50,44,22],[180,50,44,22],[240,50,44,22],[10,90,32,22],[60,90,44,22],[120,90,44,22],[180,90,44,22],[10,130,32,22],[60,130,44,22],[120,130,44,22],[180,130,44,22],[240,130,44,22],[10,170,32,22],[60,170,44,22]].map(([x,y,w,h],i)=>`<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="3" fill="${i%2===0?'#D4E0D4':'#C8D8C8'}"/>`).join('')}
    <g transform="translate(85,65)"><circle cx="0" cy="0" r="14" fill="#087A3D" opacity=".15"/><path d="M0-10C-4-10-7-7-7-3C-7 3 0 11 0 11C0 11 7 3 7-3C7-7 4-10 0-10Z" fill="#087A3D"/><circle cx="0" cy="-3" r="3" fill="white"/></g>
    <g transform="translate(165,55)"><circle cx="0" cy="0" r="10" fill="#2E9B50" opacity=".15"/><path d="M0-8C-3-8-5.5-5.5-5.5-2C-5.5 2.5 0 9 0 9C0 9 5.5 2.5 5.5-2C5.5-5.5 3-8 0-8Z" fill="#2E9B50"/><circle cx="0" cy="-2" r="2.2" fill="white"/></g>
    <g transform="translate(230,100)"><circle cx="0" cy="0" r="10" fill="#2E9B50" opacity=".15"/><path d="M0-8C-3-8-5.5-5.5-5.5-2C-5.5 2.5 0 9 0 9C0 9 5.5 2.5 5.5-2C5.5-5.5 3-8 0-8Z" fill="#2E9B50"/><circle cx="0" cy="-2" r="2.2" fill="white"/></g>
    <g transform="translate(55,145)"><circle cx="0" cy="0" r="10" fill="#2E9B50" opacity=".15"/><path d="M0-8C-3-8-5.5-5.5-5.5-2C-5.5 2.5 0 9 0 9C0 9 5.5 2.5 5.5-2C5.5-5.5 3-8 0-8Z" fill="#2E9B50"/><circle cx="0" cy="-2" r="2.2" fill="white"/></g>
  </svg>`;
}

/* ─── Render engine ──────────────────────────────────────── */
function render() {
  updateHeader();
  updateSidebar();
  $('page-content').innerHTML = pages[state.page]();
  attachPageHandlers();
}

function updateHeader() {
  $('pts-display').textContent = state.ecopuntos + ' pts';
  $('header-logo').style.display = state.sidebarOpen ? 'none' : 'flex';
}

function updateSidebar() {
  $('sidebar').className = state.sidebarOpen ? '' : 'collapsed';
  document.querySelectorAll('.nav-item').forEach(el => {
    el.classList.toggle('active', el.dataset.page === state.page);
  });
}

/* ─── Pages ──────────────────────────────────────────────── */
const pages = {

  /* ── INICIO ───────────────────────────────────────────── */
  inicio() {
    const nextPts = 500;
    const pct = Math.min((state.ecopuntos / nextPts) * 100, 100);
    const falta = Math.max(nextPts - state.ecopuntos, 0);
    const totalKg = state.activity.reduce((s,a)=>s+a.kg,0);
    const co2 = +(totalKg * 0.65).toFixed(1);
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">¡Bienvenido de nuevo! 👋</div>
        <div class="page-sub">Sigue reciclando y acumulando Ecopuntos.</div>
      </div>

      <div class="grid-3" style="margin-bottom:16px;">
        <!-- Ecopuntos -->
        <div class="stat-card-main">
          <div class="deco">🌿</div>
          <div style="font-size:.75rem;font-weight:500;opacity:.75;margin-bottom:10px;">Tus Ecopuntos</div>
          <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:2rem;">⭐</span>
            <span class="ecopts-num">${state.ecopuntos}</span>
          </div>
          <div class="ecopts-label">ECOPUNTOS</div>
        </div>
        <!-- Próximo beneficio -->
        <div class="card card-p">
          <div style="font-size:.72rem;font-weight:600;color:#087A3D;margin-bottom:12px;">🎁 Próximo beneficio</div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="width:40px;height:40px;background:#EEF8EE;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">🎁</div>
            <div>
              <div style="font-weight:700;font-size:.9rem;">10% de descuento</div>
              <div style="font-size:.72rem;color:#4A6355;">en tiendas aliadas</div>
            </div>
          </div>
          <div class="progress-wrap" style="margin-bottom:6px;"><div class="progress-bar" style="width:${pct}%;"></div></div>
          <div style="font-size:.7rem;color:#4A6355;">${state.ecopuntos} / ${nextPts} puntos</div>
          <div style="font-size:.72rem;font-weight:600;color:#087A3D;margin-top:4px;">${falta>0?'¡Te faltan '+falta+' puntos!':'¡Ya puedes canjearlo! 🎉'}</div>
        </div>
        <!-- Impacto -->
        <div class="card card-p">
          <div style="font-size:.72rem;font-weight:600;color:#087A3D;margin-bottom:12px;">🌿 Impacto ambiental</div>
          <div style="font-size:.85rem;font-weight:500;color:#2D3A35;margin-bottom:14px;">Gracias por hacer<br>la diferencia</div>
          <div style="display:flex;gap:14px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:32px;height:32px;background:#EEF8EE;border-radius:8px;display:flex;align-items:center;justify-content:center;">🌿</div>
              <div><div style="font-weight:700;">${totalKg} kg</div><div style="font-size:.65rem;color:#4A6355;">Reciclados</div></div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:32px;height:32px;background:#F0F0F5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#4A4A6A;">CO₂</div>
              <div><div style="font-weight:700;">${co2} kg</div><div style="font-size:.65rem;color:#4A6355;">CO₂ evitado</div></div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid-2" style="margin-bottom:16px;">
        <!-- Acciones -->
        <div class="card card-p">
          <div style="font-weight:700;font-size:.88rem;margin-bottom:14px;">¿Qué quieres hacer hoy?</div>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
            <button class="action-card green" data-nav="reciclar">
              <div class="action-icon" style="background:#EEF8EE;">♻️</div>
              <div class="action-title">Registrar<br>reciclaje</div>
              <div class="action-desc">Registra los materiales que has reciclado</div>
              <span style="color:#087A3D;">→</span>
            </button>
            <button class="action-card blue" data-nav="puntos">
              <div class="action-icon" style="background:#E8F2FF;">📍</div>
              <div class="action-title">Encontrar<br>punto cercano</div>
              <div class="action-desc">Ubica puntos de reciclaje cerca de ti</div>
              <span style="color:#2B7FD4;">→</span>
            </button>
            <button class="action-card gold" data-nav="beneficios">
              <div class="action-icon" style="background:#FFF3D0;">🎁</div>
              <div class="action-title">Ver<br>beneficios</div>
              <div class="action-desc">Canjea tus Ecopuntos por grandes beneficios</div>
              <span style="color:#D4992B;">→</span>
            </button>
          </div>
        </div>
        <!-- Mapa -->
        <div class="card" style="overflow:hidden;display:flex;min-height:220px;">
          <div style="flex:1;position:relative;">${mapSVG()}
            <div style="position:absolute;top:10px;left:10px;right:10px;display:flex;justify-content:space-between;align-items:center;">
              <span style="font-size:.72rem;font-weight:700;background:rgba(255,255,255,.92);padding:5px 10px;border-radius:8px;">Punto más cercano</span>
              <button class="btn btn-sm" style="background:rgba(255,255,255,.92);color:#087A3D;" data-nav="puntos">Ver todos</button>
            </div>
          </div>
          <div style="width:170px;padding:14px;border-left:1px solid #E0EBE4;display:flex;flex-direction:column;gap:10px;">
            <div>
              <div style="font-weight:700;font-size:.88rem;">EcoPunto Centro</div>
              <div style="display:flex;align-items:center;gap:4px;margin-top:4px;">${statusDot(true)}</div>
            </div>
            <div style="font-size:.72rem;color:#4A6355;">📍 A 500 metros</div>
            <div>
              <div style="font-size:.7rem;color:#4A6355;margin-bottom:6px;">Recibe:</div>
              <div style="display:flex;gap:4px;">
                ${['🧴','📦','🥫','🍾'].map(e=>`<div style="width:28px;height:28px;background:#EEF8EE;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.9rem;">${e}</div>`).join('')}
              </div>
            </div>
            <button class="btn btn-primary btn-sm btn-full" data-nav="puntos" style="margin-top:auto;">📍 Cómo llegar</button>
          </div>
        </div>
      </div>

      <div class="grid-2">
        <!-- Actividad reciente -->
        <div class="card card-p">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div style="font-weight:700;font-size:.88rem;">Actividad reciente</div>
            <button class="btn btn-sm" style="background:none;color:#087A3D;padding:0;" data-nav="actividad">Ver todo</button>
          </div>
          ${state.activity.slice(0,3).map(a=>`
          <div class="activity-item">
            <div class="activity-icon" style="background:${a.color}20;">${a.icon}</div>
            <div style="flex:1;min-width:0;">
              <div class="activity-name">Reciclaje de ${a.material}</div>
              <div class="activity-meta">${a.kg} ${a.unit} · ${a.punto}</div>
            </div>
            <div style="text-align:right;">
              <div class="activity-pts">+${a.pts} Ecopuntos</div>
              <div class="activity-date">${a.date}</div>
            </div>
          </div>`).join('')}
          <button class="btn btn-outline btn-full" style="margin-top:14px;" data-nav="actividad">Ver todo mi historial →</button>
        </div>
        <!-- Beneficios destacados -->
        <div class="card card-p">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <div style="font-weight:700;font-size:.88rem;">Beneficios destacados</div>
            <button class="btn btn-sm" style="background:none;color:#087A3D;padding:0;" data-nav="beneficios">Ver todos</button>
          </div>
          <div class="grid-4">
            ${ALL_BENEFITS.slice(0,4).map(b=>`
            <div class="benefit-thumb" data-nav="beneficios">
              <img src="${b.img}" alt="${b.brand}" loading="lazy">
              <div class="bt-body">
                <div class="bt-offer">${b.offer}</div>
                <div class="bt-pts">⭐ ${b.pts} pts</div>
              </div>
            </div>`).join('')}
          </div>
        </div>
      </div>
    </div>`;
  },

  /* ── RECICLAR ─────────────────────────────────────────── */
  reciclar() {
    if (state.reciclar.success) return this._reciclarSuccess();
    const s = state.reciclar.step;
    const stepHTML = [null, this._step1, this._step2, this._step3, this._step4][s].call(this);
    const stepsInfo = ['Material','Cantidad','Punto','Confirmar'];
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Registrar reciclaje</div>
        <div class="page-sub">Registra tus materiales y gana Ecopuntos.</div>
      </div>
      <div class="stepper" style="margin-bottom:28px;">
        ${[1,2,3,4].map((n,i)=>`
          <div class="step-circle ${s>n?'done':s===n?'active':'pending'}">${s>n?'✓':n}</div>
          ${i<3?`<div class="step-line ${s>n?'done':'pending'}"></div>`:''}`).join('')}
        <div style="display:flex;gap:20px;margin-left:14px;">
          ${stepsInfo.map((l,i)=>`<span style="font-size:.72rem;font-weight:${s===i+1?700:500};color:${s===i+1?'#087A3D':'#4A6355'}">${l}</span>`).join('')}
        </div>
      </div>
      <div style="max-width:560px;">${stepHTML}</div>
    </div>`;
  },
  _step1() {
    return `<div class="card card-p">
      <div style="font-weight:700;font-size:.92rem;margin-bottom:4px;">Paso 1 — Seleccionar material</div>
      <div style="font-size:.78rem;color:#4A6355;margin-bottom:16px;">¿Qué vas a reciclar hoy?</div>
      <div class="material-grid">
        ${MATERIALS.map(m=>`
        <button class="material-card ${state.reciclar.material===m.id?'selected':''}" data-mat="${m.id}">
          <span class="mat-icon">${m.icon}</span>
          <span class="mat-label">${m.label}</span>
          <span class="mat-pts">~${m.pts} pts/kg</span>
        </button>`).join('')}
      </div>
      <button class="btn btn-primary btn-full" style="margin-top:18px;" id="step1-next" ${!state.reciclar.material?'disabled':''}>Continuar →</button>
    </div>`;
  },
  _step2() {
    const m = MATERIALS.find(x=>x.id===state.reciclar.material);
    const est = earned();
    return `<div class="card card-p">
      <div style="font-weight:700;font-size:.92rem;margin-bottom:4px;">Paso 2 — Registrar cantidad</div>
      <div style="font-size:.78rem;color:#4A6355;margin-bottom:16px;">¿Cuánto material vas a reciclar?</div>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <div style="width:44px;height:44px;background:#EEF8EE;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">${m.icon}</div>
        <span style="font-weight:600;">${m.label}</span>
      </div>
      <div class="qty-row" style="margin-bottom:16px;">
        <button class="qty-btn" id="qty-minus">−</button>
        <input class="qty-input" id="qty-val" type="number" min="0.5" step="0.5" value="${state.reciclar.cantidad}">
        <span style="font-weight:600;color:#4A6355;font-size:1rem;">kg</span>
        <button class="qty-btn" id="qty-plus">+</button>
      </div>
      <div style="background:#EEF8EE;border-radius:12px;padding:14px;text-align:center;margin-bottom:18px;">
        <div style="font-size:.72rem;color:#4A6355;margin-bottom:4px;">Ecopuntos estimados</div>
        <div style="font-size:1.6rem;font-weight:800;color:#087A3D;" id="est-pts">+${est} Ecopuntos</div>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-outline" style="flex:1;" id="step-back">← Atrás</button>
        <button class="btn btn-primary" style="flex:1;" id="step2-next">Continuar →</button>
      </div>
    </div>`;
  },
  _step3() {
    return `<div class="card card-p">
      <div style="font-weight:700;font-size:.92rem;margin-bottom:4px;">Paso 3 — Seleccionar punto de reciclaje</div>
      <div style="font-size:.78rem;color:#4A6355;margin-bottom:16px;">Elige dónde vas a entregar tus materiales.</div>
      ${PUNTOS.map(p=>`
      <div class="punto-card ${!p.open?'disabled':''} ${state.reciclar.punto===p.id&&p.open?'selected':''}" data-punto="${p.id}" ${!p.open?'aria-disabled="true"':''}>
        <span style="font-size:1.5rem;">📍</span>
        <div style="flex:1;">
          <div style="font-weight:600;font-size:.88rem;">${p.name}</div>
          <div style="font-size:.72rem;color:#4A6355;">${p.dist}</div>
        </div>
        <div style="display:flex;align-items:center;">${statusDot(p.open)}</div>
        ${state.reciclar.punto===p.id&&p.open?'<span style="color:#087A3D;font-size:1.1rem;">✓</span>':''}
      </div>`).join('')}
      <div style="display:flex;gap:10px;margin-top:8px;">
        <button class="btn btn-outline" style="flex:1;" id="step-back">← Atrás</button>
        <button class="btn btn-primary" style="flex:1;" id="step3-next" ${!state.reciclar.punto?'disabled':''}>Continuar →</button>
      </div>
    </div>`;
  },
  _step4() {
    const m = MATERIALS.find(x=>x.id===state.reciclar.material);
    const p = PUNTOS.find(x=>x.id===state.reciclar.punto);
    const e = earned();
    return `<div class="card card-p">
      <div style="font-weight:700;font-size:.92rem;margin-bottom:4px;">Paso 4 — Confirmar</div>
      <div style="font-size:.78rem;color:#4A6355;margin-bottom:16px;">Revisa el resumen de tu reciclaje.</div>
      <div class="summary-box" style="margin-bottom:18px;">
        <div class="summary-row"><span class="summary-key">Material</span><span class="summary-val">${m.icon} ${m.label}</span></div>
        <div class="summary-row"><span class="summary-key">Cantidad</span><span class="summary-val">${state.reciclar.cantidad} kg</span></div>
        <div class="summary-row"><span class="summary-key">Punto de reciclaje</span><span class="summary-val">${p.name}</span></div>
        <div class="summary-row"><span class="summary-key" style="font-weight:700;">Ecopuntos obtenidos</span><span class="summary-pts-val">+${e}</span></div>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-outline" style="flex:1;" id="step-back">← Atrás</button>
        <button class="btn btn-primary" style="flex:1;" id="confirm-reciclar">✅ Confirmar reciclaje</button>
      </div>
    </div>`;
  },
  _reciclarSuccess() {
    const m = MATERIALS.find(x=>x.id===state.reciclar.material);
    const p = PUNTOS.find(x=>x.id===state.reciclar.punto);
    const e = state.reciclar.lastEarned || 0;
    return `<div class="page success-screen">
      <div class="card card-p success-card">
        <div class="success-icon">🎉</div>
        <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:8px;">¡Reciclaje registrado!</h2>
        <div class="success-pts">+${e}</div>
        <div style="font-size:.85rem;color:#4A6355;margin-bottom:6px;">Ecopuntos ganados</div>
        <div style="font-size:.82rem;font-weight:600;color:#2D3A35;margin-bottom:24px;line-height:1.7;">
          Material: ${m.icon} ${m.label} · ${state.reciclar.cantidad} kg<br>
          Punto: ${p.name}
        </div>
        <button class="btn btn-primary btn-full" style="margin-bottom:10px;" id="success-inicio">Volver al inicio</button>
        <button class="btn btn-outline btn-full" id="success-otro">Registrar otro reciclaje</button>
      </div>
    </div>`;
  },

  /* ── PUNTOS DE RECICLAJE ──────────────────────────────── */
  puntos() {
    const mats = ['Todos','Plástico','Cartón','Vidrio','Latas','Papel'];
    const matIcons = {Plástico:'🧴',Cartón:'📦',Latas:'🥫',Vidrio:'🍾',Papel:'📄'};
    const visible = state.puntosFilter==='Todos' ? PUNTOS : PUNTOS.filter(p=>p.mats.includes(state.puntosFilter));
    const sel = PUNTOS.find(p=>p.id===state.selectedPunto) || PUNTOS[0];
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Puntos de reciclaje</div>
        <div class="page-sub">Encuentra un punto cercano para entregar tus materiales.</div>
      </div>
      <div class="filter-row">
        ${mats.map(f=>`<button class="filter-pill ${state.puntosFilter===f?'active':''}" data-puntos-filter="${f}">${f!=='Todos'?matIcons[f]+' ':''}${f}</button>`).join('')}
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;height:520px;">
        <div class="card" style="overflow:hidden;position:relative;min-height:300px;">
          ${mapSVG()}
          <div style="position:absolute;top:10px;left:10px;background:rgba(255,255,255,.93);padding:6px 12px;border-radius:10px;font-size:.72rem;font-weight:700;">${visible.length} puntos encontrados</div>
          <div class="map-info-overlay">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="font-weight:700;font-size:.88rem;">${sel.name}</span>
              <span>${statusDot(sel.open)}</span>
            </div>
            <div style="font-size:.72rem;color:#4A6355;margin-bottom:8px;">📍 ${sel.dist} · ${sel.addr}</div>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              ${sel.mats.map(m=>`<span class="mat-chip">${matIcons[m]||''} ${m}</span>`).join('')}
            </div>
          </div>
        </div>
        <div style="overflow-y:auto;padding-right:4px;">
          ${visible.length===0?`<div class="card card-p" style="text-align:center;padding:40px;">
            <div style="font-size:2.5rem;margin-bottom:10px;">📭</div>
            <div style="font-weight:600;color:#1A2E22;">Sin resultados</div>
            <div style="font-size:.78rem;color:#4A6355;margin-top:4px;">No hay puntos que acepten ${state.puntosFilter} cerca.</div>
          </div>`:''}
          ${visible.map(p=>`
          <div class="punto-list-card ${state.selectedPunto===p.id?'selected':''}" data-select-punto="${p.id}">
            <div class="pl-header">
              <div>
                <div class="pl-name">${p.name}</div>
                <div class="pl-addr">📍 ${p.dist} · ${p.addr}</div>
              </div>
              <div style="display:flex;align-items:center;">${statusDot(p.open)}</div>
            </div>
            <div style="font-size:.7rem;color:#4A6355;margin-bottom:8px;">🕐 ${p.hours}</div>
            <div class="pl-mats">${p.mats.map(m=>`<span class="mat-chip">${matIcons[m]||''} ${m}</span>`).join('')}</div>
            <button class="btn btn-primary btn-sm btn-full">Ver detalles</button>
          </div>`).join('')}
        </div>
      </div>
    </div>`;
  },

  /* ── BENEFICIOS ───────────────────────────────────────── */
  beneficios() {
    const cats = ['Todos','Descuentos','Productos','Experiencias'];
    const visible = state.benefFilter==='Todos' ? ALL_BENEFITS : ALL_BENEFITS.filter(b=>b.cat===state.benefFilter);
    return `<div class="page">
      <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <div class="page-title">Beneficios</div>
          <div class="page-sub">Canjea tus Ecopuntos por beneficios.</div>
        </div>
        <div style="background:#087A3D;color:#fff;border-radius:14px;padding:10px 16px;display:flex;align-items:center;gap:8px;">
          <span style="font-size:1.3rem;">⭐</span>
          <div>
            <div style="font-size:1.2rem;font-weight:800;line-height:1;">${state.ecopuntos}</div>
            <div style="font-size:.65rem;opacity:.8;">Ecopuntos disponibles</div>
          </div>
        </div>
      </div>
      <div class="filter-row">${cats.map(c=>`<button class="filter-pill ${state.benefFilter===c?'active':''}" data-benef-filter="${c}">${c}</button>`).join('')}</div>
      <div class="grid-3">
        ${visible.map(b=>{
          const can = state.ecopuntos >= b.pts;
          return `<div class="benefit-card" data-benef-id="${b.id}">
            <div class="benefit-card-img-wrap">
              <img src="${b.img}" alt="${b.brand}" loading="lazy" style="width:100%;height:140px;object-fit:cover;display:block;">
              <div class="benefit-card-overlay"></div>
              <div class="benefit-card-over-text">
                <div class="bc-offer">${b.offer}</div>
                <div class="bc-brand">${b.brand}</div>
              </div>
              <div class="benefit-cat-badge"><span class="badge ${can?'badge-green':'badge-gray'}">${b.cat}</span></div>
            </div>
            <div class="benefit-card-body">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <div style="font-size:.82rem;font-weight:700;">⭐ ${b.pts} puntos</div>
                <span class="${can?'benefit-avail':'benefit-need'}">${can?'✓ Disponible':'Faltan '+(b.pts-state.ecopuntos)+' pts'}</span>
              </div>
            </div>
          </div>`;
        }).join('')}
      </div>
      <!-- Modal -->
      <div class="modal-overlay" id="benef-modal">
        <div class="modal" id="benef-modal-inner"></div>
      </div>
    </div>`;
  },

  /* ── MIS ECOPUNTOS ────────────────────────────────────── */
  ecopuntos() {
    const nextPts=500, pct=Math.min((state.ecopuntos/nextPts)*100,100), falta=Math.max(nextPts-state.ecopuntos,0);
    const totalGanados = state.activity.reduce((s,a)=>s+a.pts,0);
    const totalCanjeados = state.redeemed.reduce((s,r)=>s+r.pts,0);
    const filters=['Todos','Ganados','Canjeados'];
    const all=[
      ...state.activity.map(a=>({type:'ganado',label:`Reciclaje de ${a.material}`,icon:a.icon,pts:a.pts,date:a.date,color:a.color})),
      ...state.redeemed.map(r=>({type:'canjeado',label:r.offer,icon:'🎁',pts:-r.pts,date:r.date,color:'#C97D3A'})),
    ];
    const filtered = state.ecoptsFilter==='Todos' ? all : all.filter(h=>h.type===(state.ecoptsFilter==='Ganados'?'ganado':'canjeado'));
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Mis Ecopuntos</div>
        <div class="page-sub">Tu saldo, progreso e historial de puntos.</div>
      </div>
      <div class="balance-card">
        <div class="deco">⭐</div>
        <div style="font-size:.82rem;opacity:.75;margin-bottom:8px;">Tu saldo actual</div>
        <div style="display:flex;align-items:flex-end;gap:10px;margin-bottom:16px;">
          <span class="balance-num">${state.ecopuntos}</span>
          <span style="font-size:1rem;font-weight:600;opacity:.7;margin-bottom:6px;">ECOPUNTOS</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.72rem;opacity:.8;margin-bottom:6px;">
          <span>${state.ecopuntos} / ${nextPts} puntos</span>
          <span>Próximo: 10% descuento</span>
        </div>
        <div style="background:rgba(255,255,255,.2);border-radius:999px;height:10px;"><div style="background:#fff;height:10px;border-radius:999px;width:${pct}%;transition:width .5s;"></div></div>
        <div style="font-size:.82rem;opacity:.88;margin-top:8px;">${falta>0?'Te faltan '+falta+' puntos para tu próximo beneficio.':'🎉 ¡Ya puedes canjear tu beneficio!'}</div>
      </div>
      <div class="grid-3" style="margin-bottom:20px;">
        ${[
          {label:'Total ganados',   val:totalGanados,    icon:'⭐', color:'#F5B82E'},
          {label:'Total canjeados', val:totalCanjeados,  icon:'🎁', color:'#C97D3A'},
          {label:'Reciclajes',      val:state.activity.length, icon:'♻️', color:'#087A3D'},
        ].map(s=>`<div class="card card-p" style="text-align:center;">
          <div style="font-size:2rem;margin-bottom:6px;">${s.icon}</div>
          <div style="font-size:1.8rem;font-weight:800;color:${s.color};">${s.val}</div>
          <div style="font-size:.72rem;color:#4A6355;margin-top:2px;">${s.label}</div>
        </div>`).join('')}
      </div>
      <div class="card card-p">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <div style="font-weight:700;font-size:.88rem;">Historial de puntos</div>
          <div style="display:flex;gap:4px;">
            ${filters.map(f=>`<button class="filter-pill btn-sm ${state.ecoptsFilter===f?'active':''}" data-ecpts-filter="${f}">${f}</button>`).join('')}
          </div>
        </div>
        ${filtered.length===0?`<div style="text-align:center;padding:30px;color:#4A6355;"><div style="font-size:2rem;margin-bottom:8px;">📭</div><div>Sin registros en esta categoría</div></div>`:''}
        ${filtered.map(h=>`
        <div class="activity-item">
          <div class="activity-icon" style="background:${h.color}20;">${h.icon}</div>
          <div style="flex:1;min-width:0;">
            <div class="activity-name">${h.label}</div>
            <div class="activity-meta">${h.date}</div>
          </div>
          <div style="font-weight:700;font-size:.82rem;color:${h.pts>0?'#087A3D':'#C97D3A'};">${h.pts>0?'+'+h.pts:h.pts} pts</div>
        </div>`).join('')}
      </div>
    </div>`;
  },

  /* ── MI ACTIVIDAD ─────────────────────────────────────── */
  actividad() {
    const totalKg=state.activity.reduce((s,a)=>s+a.kg,0);
    const totalCO2=+(totalKg*0.65).toFixed(1);
    const maxKg=Math.max(...MONTHLY_DATA.map(m=>m.kg));
    const matCounts={};
    state.activity.forEach(a=>{ matCounts[a.material]=(matCounts[a.material]||0)+a.kg; });
    const topMats=Object.entries(matCounts).sort((a,b)=>b[1]-a[1]).slice(0,5);
    const totalTop=topMats.reduce((s,[,v])=>s+v,0);
    const matColors={Plástico:'#3E9E6B',Cartón:'#C97D3A',Latas:'#6B8EA8',Papel:'#7E9BA8',Vidrio:'#8B6BA8',Otro:'#087A3D'};
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Mi actividad</div>
        <div class="page-sub">Consulta tu progreso y el impacto que estás generando.</div>
      </div>
      <div class="grid-4" style="margin-bottom:16px;">
        ${[
          {icon:'♻️',label:'Reciclajes',         val:state.activity.length, color:'#087A3D', bg:'#EEF8EE'},
          {icon:'📦',label:'Material reciclado',  val:totalKg+' kg',         color:'#C97D3A', bg:'#FFF4EB'},
          {icon:'⭐',label:'Ecopuntos obtenidos', val:state.ecopuntos,        color:'#F5B82E', bg:'#FFFBEC'},
          {icon:'🌱',label:'CO₂ evitado',         val:totalCO2+' kg',         color:'#2E9B50', bg:'#EEF8EE'},
        ].map(s=>`<div class="card card-p">
          <div style="width:40px;height:40px;background:${s.bg};border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:10px;">${s.icon}</div>
          <div style="font-size:1.6rem;font-weight:800;color:${s.color};">${s.val}</div>
          <div style="font-size:.72rem;color:#4A6355;margin-top:2px;">${s.label}</div>
        </div>`).join('')}
      </div>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;">
        <div class="card card-p">
          <div style="font-weight:700;font-size:.88rem;margin-bottom:18px;">Material reciclado por mes (kg)</div>
          <div class="chart-bars">
            ${MONTHLY_DATA.map((m,i)=>{
              const h=(m.kg/maxKg)*100;
              const last=i===MONTHLY_DATA.length-1;
              return `<div class="chart-bar-wrap">
                <div class="chart-val" style="opacity:${last?1:.6};">${m.kg}</div>
                <div class="chart-bar" style="height:${h}%;background:${last?'#087A3D':'#2E9B50'};"></div>
                <div class="chart-label">${m.mes}</div>
              </div>`;
            }).join('')}
          </div>
        </div>
        <div class="card card-p">
          <div style="font-weight:700;font-size:.88rem;margin-bottom:16px;">Por material</div>
          ${topMats.length===0?`<div style="font-size:.78rem;color:#4A6355;text-align:center;padding:20px;">Sin datos aún</div>`:''}
          ${topMats.map(([mat,kg])=>{
            const pct=totalTop>0?(kg/totalTop)*100:0;
            return `<div style="margin-bottom:12px;">
              <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:4px;">
                <span style="font-weight:500;">${mat}</span><span style="color:#4A6355;">${kg} kg</span>
              </div>
              <div class="progress-wrap"><div class="progress-bar" style="width:${pct}%;background:${matColors[mat]||'#087A3D'};"></div></div>
            </div>`;
          }).join('')}
        </div>
      </div>
      <div style="background:linear-gradient(135deg,#087A3D,#2E9B50);border-radius:20px;padding:24px;color:#fff;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-weight:700;font-size:.95rem;">🌍 Impacto ambiental</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
          <div style="background:rgba(255,255,255,.12);border-radius:14px;padding:14px;">
            <div style="font-size:.8rem;opacity:.8;margin-bottom:4px;">Has contribuido a recuperar</div>
            <div style="font-size:1.6rem;font-weight:800;">${totalKg} kg</div>
            <div style="font-size:.8rem;opacity:.8;">de materiales reciclables</div>
          </div>
          <div style="background:rgba(255,255,255,.12);border-radius:14px;padding:14px;">
            <div style="font-size:.8rem;opacity:.8;margin-bottom:4px;">Tu participación evita</div>
            <div style="font-size:1.6rem;font-weight:800;">${totalCO2} kg</div>
            <div style="font-size:.8rem;opacity:.8;">de CO₂ en la atmósfera</div>
          </div>
        </div>
        <p style="font-size:.82rem;opacity:.82;">Tu participación ayuda a reducir residuos y fomentar la economía circular. ¡Sigue así! 🌱</p>
      </div>
    </div>`;
  },

  /* ── HISTORIAL BENEFICIOS ─────────────────────────────── */
  historial() {
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Historial de beneficios</div>
        <div class="page-sub">Beneficios que has canjeado con tus Ecopuntos.</div>
      </div>
      ${state.redeemed.length===0?`<div class="card card-p" style="text-align:center;padding:60px;">
        <div style="font-size:3rem;margin-bottom:12px;">🎁</div>
        <div style="font-weight:700;color:#1A2E22;margin-bottom:6px;">Aún no has canjeado beneficios</div>
        <div style="font-size:.82rem;color:#4A6355;">Acumula Ecopuntos reciclando y canjéalos por increíbles beneficios.</div>
      </div>`:''}
      ${state.redeemed.map(r=>`
      <div class="hist-card">
        <img class="hist-img" src="${r.img}" alt="${r.brand}" loading="lazy">
        <div style="flex:1;min-width:0;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px;">
            <div>
              <div style="font-weight:700;font-size:.88rem;">🎁 ${r.brand}</div>
              <div style="font-size:.75rem;color:#4A6355;">${r.offer}</div>
            </div>
            <span class="badge ${r.used?'badge-gray':'badge-green'}">
              <span style="width:6px;height:6px;border-radius:50%;background:${r.used?'#9AB0A4':'#087A3D'};display:inline-block;"></span>
              ${r.used?'Utilizado':'Disponible'}
            </span>
          </div>
          <div style="font-size:.68rem;color:#4A6355;margin-bottom:8px;">Canjeado el ${r.date}</div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="hist-code">${r.code}</span>
            <span style="font-size:.72rem;color:#4A6355;">⭐ ${r.pts} puntos</span>
          </div>
        </div>
      </div>`).join('')}
    </div>`;
  },

  /* ── EDUCACIÓN ────────────────────────────────────────── */
  educacion() {
    const sel = EDU_MATS.find(m=>m.id===state.eduActive);
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Educación ambiental</div>
        <div class="page-sub">Aprende a reciclar correctamente.</div>
      </div>
      <div style="font-weight:700;font-size:.88rem;margin-bottom:12px;">¿Cómo reciclar cada material?</div>
      <div class="mat-selector">
        ${EDU_MATS.map(m=>`
        <button class="mat-btn ${state.eduActive===m.id?'active':''}" data-edu="${m.id}" style="${state.eduActive===m.id?'background:'+m.bg+';':''}" >
          <span class="mat-btn-icon">${m.icon}</span>
          <span class="mat-btn-label">${m.title}</span>
        </button>`).join('')}
      </div>
      ${sel?`
      <div class="edu-expanded" style="background:${sel.bg};">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
          <span style="font-size:2.5rem;">${sel.icon}</span>
          <div>
            <div style="font-size:1rem;font-weight:700;">Cómo reciclar ${sel.title}</div>
            <div style="font-size:.72rem;color:${sel.color};">Sigue estos pasos</div>
          </div>
        </div>
        <div class="edu-steps">
          ${sel.steps.map((s,i)=>`
          <div class="edu-step">
            <div class="edu-step-num" style="background:${sel.color};">${i+1}</div>
            <span class="edu-step-text">${s}</span>
          </div>`).join('')}
        </div>
        <div class="fact-box">
          <span style="font-size:1.2rem;">💡</span>
          <div>
            <div style="font-size:.75rem;font-weight:700;color:#087A3D;margin-bottom:2px;">¿Sabías que?</div>
            <div style="font-size:.75rem;color:#4A6355;">${sel.fact}</div>
          </div>
        </div>
      </div>`:''}
      <div style="font-weight:700;font-size:.88rem;margin-bottom:12px;">Consejos para reciclar mejor</div>
      ${[
        {icon:'🏠',tip:'Separa en casa con cubos de colores: verde (vidrio), azul (papel/cartón), amarillo (plástico/latas).'},
        {icon:'🚿',tip:'Enjuaga siempre los envases antes de reciclar para evitar contaminar otros materiales.'},
        {icon:'📱',tip:'Usa la app para encontrar el punto de reciclaje más cercano a tu hogar o trabajo.'},
        {icon:'👨‍👩‍👧',tip:'Involucra a tu familia en el reciclaje. Pequeños hábitos generan grandes impactos.'},
        {icon:'🛍️',tip:'Usa bolsas reutilizables y lleva tu propio recipiente para reducir residuos desde la raíz.'},
      ].map(t=>`
      <div class="card" style="display:flex;align-items:flex-start;gap:12px;padding:14px;margin-bottom:10px;">
        <span style="font-size:1.6rem;flex-shrink:0;">${t.icon}</span>
        <p style="font-size:.82rem;color:#2D3A35;line-height:1.6;">${t.tip}</p>
      </div>`).join('')}
    </div>`;
  },

  /* ── CONFIGURACIÓN ────────────────────────────────────── */
  config() {
    const sections=[
      {title:'Notificaciones',items:[
        {key:'push',    label:'Notificaciones push',    desc:'Recibe alertas sobre tus Ecopuntos y reciclajes.'},
        {key:'email',   label:'Correo electrónico',     desc:'Actualizaciones y boletines de Ecopoint.'},
        {key:'reminders',label:'Recordatorios',         desc:'Te avisamos cuando pasen muchos días sin reciclar.'},
      ]},
      {title:'Privacidad',items:[
        {key:'public',  label:'Perfil público',         desc:'Tu actividad puede ser visible en el ranking.'},
        {key:'share',   label:'Compartir impacto',      desc:'Permite compartir tus logros en redes sociales.'},
      ]},
      {title:'Preferencias',items:[
        {key:'dark',    label:'Modo oscuro',            desc:'Cambia la apariencia de la plataforma.'},
        {key:'metric',  label:'Unidades métricas',      desc:'Mostrar cantidades en kg en lugar de libras.'},
      ]},
    ];
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Configuración</div>
        <div class="page-sub">Personaliza tu experiencia en Ecopoint.</div>
      </div>
      <div style="max-width:580px;">
        ${sections.map(s=>`
        <div class="config-section">
          <h3>${s.title}</h3>
          ${s.items.map((item,i)=>`
          ${i>0?'<hr>':''}
          <div class="toggle-wrap">
            <div class="toggle-info">
              <div class="toggle-label">${item.label}</div>
              <div class="toggle-desc">${item.desc}</div>
            </div>
            <button class="toggle ${state.configToggles[item.key]?'on':''}" data-toggle="${item.key}"></button>
          </div>`).join('')}
        </div>`).join('')}
        <div class="config-section">
          <h3>Idioma</h3>
          <div style="display:flex;gap:8px;">
            ${['Español','English','Português'].map(l=>`
            <button class="btn ${state.configLang===l?'btn-primary':'btn-outline'}" data-lang="${l}">${l}</button>`).join('')}
          </div>
        </div>
        <div class="config-section">
          <h3>Cuenta</h3>
          <button class="btn btn-outline btn-full" style="margin-bottom:8px;justify-content:flex-start;">🔒 Cambiar contraseña</button>
          <button class="btn btn-danger btn-full" style="justify-content:flex-start;">🚪 Cerrar sesión</button>
        </div>
      </div>
    </div>`;
  },

  /* ── AYUDA ────────────────────────────────────────────── */
  ayuda() {
    return `<div class="page">
      <div class="page-header">
        <div class="page-title">Centro de ayuda</div>
        <div class="page-sub">Encuentra respuestas a tus preguntas.</div>
      </div>
      <div style="max-width:580px;">
        <div style="position:relative;margin-bottom:20px;">
          <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);">🔍</span>
          <input type="text" placeholder="Buscar en el centro de ayuda..." style="width:100%;padding:12px 14px 12px 38px;border:1px solid #E0EBE4;border-radius:12px;font-family:Poppins,sans-serif;font-size:.85rem;outline:none;" onfocus="this.style.borderColor='#087A3D'" onblur="this.style.borderColor='#E0EBE4'">
        </div>
        <div style="font-weight:700;font-size:.88rem;margin-bottom:10px;">Preguntas frecuentes</div>
        ${FAQS.map((faq,i)=>`
        <div class="faq-item">
          <button class="faq-q" data-faq="${i}">
            <span>${faq.q}</span>
            <span class="faq-icon ${state.faqOpen===i?'open':''}">+</span>
          </button>
          <div class="faq-a ${state.faqOpen===i?'open':''}">${faq.a}</div>
        </div>`).join('')}
        <div style="background:linear-gradient(135deg,#087A3D,#2E9B50);border-radius:20px;padding:24px;text-align:center;color:#fff;margin-top:20px;">
          <div style="font-size:2rem;margin-bottom:8px;">💬</div>
          <div style="font-weight:700;margin-bottom:6px;">¿No encontraste lo que buscabas?</div>
          <div style="font-size:.82rem;opacity:.82;margin-bottom:14px;">Nuestro equipo está disponible para ayudarte.</div>
          <button class="btn" style="background:#fff;color:#087A3D;">Contactar soporte</button>
        </div>
      </div>
    </div>`;
  },
};

/* ─── Event handlers after render ────────────────────────── */
function attachPageHandlers() {
  // Nav via data-nav
  document.querySelectorAll('[data-nav]').forEach(el => {
    el.addEventListener('click', () => navigate(el.dataset.nav));
  });

  // ── Reciclar
  document.querySelectorAll('[data-mat]').forEach(el => {
    el.addEventListener('click', () => {
      state.reciclar.material = el.dataset.mat;
      render();
    });
  });
  const step1Next = $('step1-next');
  if (step1Next) step1Next.addEventListener('click', () => { state.reciclar.step=2; render(); });

  const qtyMinus=$('qty-minus'), qtyPlus=$('qty-plus'), qtyVal=$('qty-val'), estPts=$('est-pts');
  if (qtyMinus) {
    qtyMinus.addEventListener('click',()=>{ state.reciclar.cantidad=Math.max(0.5,+(+state.reciclar.cantidad-0.5).toFixed(1)); qtyVal.value=state.reciclar.cantidad; estPts.textContent='+'+earned()+' Ecopuntos'; });
    qtyPlus.addEventListener('click',()=>{ state.reciclar.cantidad=+(+state.reciclar.cantidad+0.5).toFixed(1); qtyVal.value=state.reciclar.cantidad; estPts.textContent='+'+earned()+' Ecopuntos'; });
    qtyVal.addEventListener('input',()=>{ state.reciclar.cantidad=+qtyVal.value||0.5; estPts.textContent='+'+earned()+' Ecopuntos'; });
  }
  const step2Next=$('step2-next');
  if (step2Next) step2Next.addEventListener('click',()=>{ state.reciclar.step=3; render(); });

  document.querySelectorAll('[data-punto]').forEach(el=>{
    if(el.getAttribute('aria-disabled')) return;
    el.addEventListener('click',()=>{ state.reciclar.punto=el.dataset.punto; render(); });
  });
  const step3Next=$('step3-next');
  if (step3Next) step3Next.addEventListener('click',()=>{ state.reciclar.step=4; render(); });

  const confirm=$('confirm-reciclar');
  if (confirm) confirm.addEventListener('click',()=>{
    const e=earned();
    const m=MATERIALS.find(x=>x.id===state.reciclar.material);
    const p=PUNTOS.find(x=>x.id===state.reciclar.punto);
    const now=new Date();
    const hrs=now.getHours(), mins=String(now.getMinutes()).padStart(2,'0'), ampm=hrs>=12?'p.m.':'a.m.';
    state.activity.unshift({material:m.label,kg:state.reciclar.cantidad,unit:'kg',punto:p.name,pts:e,date:`Hoy, ${hrs}:${mins} ${ampm}`,icon:m.icon,color:m.color});
    state.ecopuntos+=e;
    state.reciclar.lastEarned=e;
    state.reciclar.success=true;
    render();
  });
  const stepBack=$('step-back');
  if (stepBack) stepBack.addEventListener('click',()=>{ state.reciclar.step--; render(); });
  const succInicio=$('success-inicio');
  if(succInicio) succInicio.addEventListener('click',()=>navigate('inicio'));
  const succOtro=$('success-otro');
  if(succOtro) succOtro.addEventListener('click',()=>{ state.reciclar={step:1,material:null,cantidad:2,punto:null}; render(); });

  // ── Puntos de reciclaje filters
  document.querySelectorAll('[data-puntos-filter]').forEach(el=>{
    el.addEventListener('click',()=>{ state.puntosFilter=el.dataset.puntosFilter; render(); });
  });
  document.querySelectorAll('[data-select-punto]').forEach(el=>{
    el.addEventListener('click',()=>{ state.selectedPunto=el.dataset.selectPunto; render(); });
  });

  // ── Beneficios filters & modal
  document.querySelectorAll('[data-benef-filter]').forEach(el=>{
    el.addEventListener('click',()=>{ state.benefFilter=el.dataset.benefFilter; render(); });
  });
  document.querySelectorAll('[data-benef-id]').forEach(el=>{
    el.addEventListener('click',()=>{ openBenefModal(+el.dataset.benefId); });
  });
  const modal=$('benef-modal');
  if(modal) modal.addEventListener('click',(e)=>{ if(e.target===modal) modal.classList.remove('open'); });

  // ── Ecopuntos filters
  document.querySelectorAll('[data-ecpts-filter]').forEach(el=>{
    el.addEventListener('click',()=>{ state.ecoptsFilter=el.dataset.ecptsFilter; render(); });
  });

  // ── Educación
  document.querySelectorAll('[data-edu]').forEach(el=>{
    el.addEventListener('click',()=>{ state.eduActive=state.eduActive===el.dataset.edu?null:el.dataset.edu; render(); });
  });

  // ── FAQ
  document.querySelectorAll('[data-faq]').forEach(el=>{
    el.addEventListener('click',()=>{ const i=+el.dataset.faq; state.faqOpen=state.faqOpen===i?null:i; render(); });
  });

  // ── Config toggles
  document.querySelectorAll('[data-toggle]').forEach(el=>{
    el.addEventListener('click',()=>{ const k=el.dataset.toggle; state.configToggles[k]=!state.configToggles[k]; el.classList.toggle('on',state.configToggles[k]); });
  });
  document.querySelectorAll('[data-lang]').forEach(el=>{
    el.addEventListener('click',()=>{ state.configLang=el.dataset.lang; render(); });
  });
}

/* ─── Benefit Modal ─────────────────────────────────────── */
function openBenefModal(id) {
  const b = ALL_BENEFITS.find(x=>x.id===id);
  if (!b) return;
  const can = state.ecopuntos >= b.pts;
  const modal=$('benef-modal'), inner=$('benef-modal-inner');
  inner.innerHTML=`
    <div class="modal-img-wrap">
      <img class="modal-img" src="${b.img}" alt="${b.brand}">
      <div class="modal-img-overlay"></div>
      <div class="modal-img-text">
        <div style="font-size:1.1rem;font-weight:700;">${b.offer}</div>
        <div style="font-size:.82rem;opacity:.8;">${b.brand}</div>
      </div>
      <button class="modal-close" id="modal-close-btn">✕</button>
    </div>
    <div class="modal-body" id="modal-body-inner">
      <p style="font-size:.85rem;color:#4A6355;margin-bottom:16px;">${b.desc}</p>
      <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
        <span style="font-weight:700;">⭐ ${b.pts} Ecopuntos</span>
        <span style="font-size:.85rem;color:#4A6355;">Tu saldo: <strong style="color:#087A3D;">${state.ecopuntos} pts</strong></span>
      </div>
      ${!can?`<div style="background:#FFF8EC;border:1px solid rgba(245,184,46,.4);border-radius:12px;padding:10px 12px;font-size:.82rem;color:#C97D3A;font-weight:500;margin-bottom:12px;">⚠️ Te faltan ${b.pts-state.ecopuntos} puntos para obtener este beneficio.</div>`:''}
      <button class="btn btn-primary btn-full" id="redeem-btn" ${!can?'disabled':''}>
        ${can?'Canjear beneficio':'Puntos insuficientes'}
      </button>
    </div>`;
  modal.classList.add('open');
  $('modal-close-btn').addEventListener('click',()=>modal.classList.remove('open'));
  const redeemBtn=$('redeem-btn');
  if(can && redeemBtn) redeemBtn.addEventListener('click',()=>{
    state.ecopuntos-=b.pts;
    const code='ECO-'+Math.floor(1000+Math.random()*9000);
    state.redeemed.unshift({brand:b.brand,offer:b.offer,pts:b.pts,date:new Date().toLocaleDateString('es-CO',{day:'numeric',month:'long',year:'numeric'}),code,used:false,img:b.img});
    $('modal-body-inner').innerHTML=`
      <div style="text-align:center;padding:10px 0;">
        <div style="font-size:3rem;margin-bottom:10px;">🎉</div>
        <h3 style="font-weight:700;font-size:1.1rem;margin-bottom:8px;">¡Beneficio canjeado!</h3>
        <p style="font-size:.82rem;color:#4A6355;margin-bottom:14px;">Usa este código en el establecimiento:</p>
        <div class="code-box"><div class="code-val">${code}</div><div style="font-size:.7rem;color:#4A6355;margin-top:4px;">Código de canje único</div></div>
        <button class="btn btn-primary btn-full" onclick="document.getElementById('benef-modal').classList.remove('open');renderHeader();">¡Perfecto!</button>
      </div>`;
    updateHeader();
  });
}

/* ─── Navigation ─────────────────────────────────────────── */
function navigate(page) {
  state.page = page;
  if(state.notifOpen) state.notifOpen=false;
  render();
  $('page-content').scrollTop=0;
}

/* ─── Header & Sidebar controls ─────────────────────────── */
function renderHeader() {
  $('pts-display').textContent=state.ecopuntos+' pts';
}

/* ─── Init ───────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Sidebar nav items
  document.querySelectorAll('.nav-item').forEach(el=>{
    el.addEventListener('click',()=>navigate(el.dataset.page));
  });
  // Hamburger
  $('hamburger').addEventListener('click',()=>{
    state.sidebarOpen=!state.sidebarOpen;
    $('sidebar').className=state.sidebarOpen?'':'collapsed';
    $('header-logo').style.display=state.sidebarOpen?'none':'flex';
  });
  // Notifications
  $('notif-btn').addEventListener('click',(e)=>{
    e.stopPropagation();
    state.notifOpen=!state.notifOpen;
    $('notif-dropdown').classList.toggle('open',state.notifOpen);
  });
  document.addEventListener('click',()=>{
    if(state.notifOpen){ state.notifOpen=false; $('notif-dropdown').classList.remove('open'); }
    if(state.perfilOpen){ state.perfilOpen=false; $('perfil-panel').classList.remove('open'); }
  });
  // Perfil
  $('profile-btn').addEventListener('click',(e)=>{
    e.stopPropagation();
    state.perfilOpen=!state.perfilOpen;
    $('perfil-panel').classList.toggle('open',state.perfilOpen);
  });
  $('perfil-panel').addEventListener('click',e=>e.stopPropagation());
  $('close-perfil').addEventListener('click',()=>{ state.perfilOpen=false; $('perfil-panel').classList.remove('open'); });
  // Perfil options
  document.querySelectorAll('.profile-option').forEach(el=>{
    el.addEventListener('click',()=>{
      if(el.dataset.action==='logout') alert('Sesión cerrada.');
    });
  });

  // Initial render
  render();
});
