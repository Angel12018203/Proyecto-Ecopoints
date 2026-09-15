// ==========================================
// CAPA DATABASE / REPOSITORY REACTIVO - ECOPOINTS (MVC)
// ==========================================
import { 
  Reciclador, Solicitud, CentroAcopio, Entrega, 
  Beneficio, Canje, MovimientoEcopunto, Recoleccion, 
  EstadisticasZona, Material, RolUsuario 
} from '@/models/types';

const STORAGE_KEY = 'ecopoints_db_v2';

export interface DatabaseSchema {
  rolActivo: RolUsuario;
  reciclador: Reciclador;
  solicitudes: Solicitud[];
  recolecciones: Recoleccion[];
  entregas: Entrega[];
  beneficios: Beneficio[];
  canjes: Canje[];
  movimientosEcopuntos: MovimientoEcopunto[];
  centrosAcopio: CentroAcopio[];
  materiales: Material[];
  estadisticasZona: EstadisticasZona;
}

export const MATERIALES_CONFIG: Material[] = [
  { id: 'mat-1', nombre: 'Cartón', icono: '📦', color: '#D97706', puntosPorKg: 10, descripcion: 'Cajas limpias, tubos y cartón corrugado seco y desarmado' },
  { id: 'mat-2', nombre: 'Plástico', icono: '🧴', color: '#0284C7', puntosPorKg: 15, descripcion: 'Botellas PET transparentes, envases PEAD limpios y aplastados' },
  { id: 'mat-3', nombre: 'Vidrio', icono: '🍾', color: '#059669', puntosPorKg: 8, descripcion: 'Botellas y frascos enteros de color verde, transparente o ámbar' },
  { id: 'mat-4', nombre: 'Metales', icono: '🥫', color: '#DC2626', puntosPorKg: 20, descripcion: 'Latas de aluminio, chatarra ferrosa y piezas de cobre o bronce' },
  { id: 'mat-5', nombre: 'Papel', icono: '📄', color: '#4B5563', puntosPorKg: 10, descripcion: 'Papel de archivo, periódicos, revistas y sobres sin plástico' },
  { id: 'mat-6', nombre: 'Otros', icono: '♻️', color: '#7C3AED', puntosPorKg: 5, descripcion: 'Empaques multicapa tipo Tetra Pak y residuos limpios' },
];

const INITIAL_DB: DatabaseSchema = {
  rolActivo: 'reciclador',
  reciclador: {
    id: 'rec-carlos-1',
    usuarioId: 'usr-carlos-1',
    nombre: 'Carlos Rodríguez',
    documento: 'C.C. 80.456.789',
    telefono: '+57 312 456 7890',
    email: 'carlos.rodriguez@ecopoints.org',
    foto: 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=200&h=200&fit=crop&crop=faces&auto=format',
    zonaTrabajo: 'Suba, Bogotá D.C.',
    vehiculo: 'Bicicarro de recolección reforzado con tolva ecológica',
    experiencia: '5 años recorriendo cuadrantes de Suba Centro y Rincón',
    ecopuntos: 2850,
    kgRecuperados: 1250,
    recoleccionesCompletadas: 48,
    entregasPendientes: 1,
    nivel: 'EcoReciclador',
    proximoNivel: 'Reciclador Destacado',
    puntosParaSiguienteNivel: 150, // 3000 - 2850 = 150 pts
  },
  solicitudes: [
    {
      id: 'sol-1024',
      codigo: '#1024',
      ciudadanoId: 'ciu-1',
      ciudadanoNombre: 'Mariana Ospina',
      material: 'Cartón',
      cantidadAproxKg: 30,
      ubicacion: 'Suba',
      direccion: 'Carrera 92 #146B-12, Conjunto Alameda',
      distanciaMetros: 800,
      horarioDisponible: '2:00 p.m. – 5:00 p.m.',
      prioridad: 'Media',
      estado: 'Disponible',
      observaciones: 'Material separado y listo para recoger en portería. Cajas dobladas y secas.',
      fotografia: 'https://images.unsplash.com/photo-1530587191325-3db32d826c18?w=500&h=350&fit=crop&auto=format',
      fechaCreacion: 'Hoy, hace 35 min',
      coordenadas: { lat: 4.7432, lng: -74.0854 },
    },
    {
      id: 'sol-1028',
      codigo: '#1028',
      ciudadanoId: 'ciu-2',
      ciudadanoNombre: 'Almacén Don Pedro',
      material: 'Plástico',
      cantidadAproxKg: 15,
      ubicacion: 'Suba La Campiña',
      direccion: 'Calle 145 #98-24',
      distanciaMetros: 1200,
      horarioDisponible: '1:00 p.m. – 4:00 p.m.',
      prioridad: 'Alta',
      estado: 'Disponible',
      observaciones: 'Botellas PET transparentes limpias y comprimidas en 3 bolsas gigantes.',
      fotografia: 'https://images.unsplash.com/photo-1595278069441-2cf29f8005a4?w=500&h=350&fit=crop&auto=format',
      fechaCreacion: 'Hoy, hace 1 hora',
      coordenadas: { lat: 4.7481, lng: -74.0912 },
    },
    {
      id: 'sol-1030',
      codigo: '#1030',
      ciudadanoId: 'ciu-3',
      ciudadanoNombre: 'Conjunto Residencial El Pinar',
      material: 'Vidrio',
      cantidadAproxKg: 40,
      ubicacion: 'Suba Rincón',
      direccion: 'Carrera 90 #132A-45',
      distanciaMetros: 1800,
      horarioDisponible: '3:00 p.m. – 6:00 p.m.',
      prioridad: 'Media',
      estado: 'Disponible',
      observaciones: 'Botellas de bebidas clasificadas por color en canastillas plásticas.',
      fotografia: 'https://images.unsplash.com/photo-1516253593875-bd7ba052fbc5?w=500&h=350&fit=crop&auto=format',
      fechaCreacion: 'Hoy, hace 2 horas',
      coordenadas: { lat: 4.7391, lng: -74.0811 },
    },
    {
      id: 'sol-1035',
      codigo: '#1035',
      ciudadanoId: 'ciu-4',
      ciudadanoNombre: 'Taller Mecánico Los Andes',
      material: 'Metales',
      cantidadAproxKg: 25,
      ubicacion: 'Suba Tibabuyes',
      direccion: 'Calle 139 #112-10',
      distanciaMetros: 2400,
      horarioDisponible: '10:00 a.m. – 1:00 p.m.',
      prioridad: 'Baja',
      estado: 'Disponible',
      observaciones: 'Laminillas de aluminio y piezas de chatarra seleccionada en canasta.',
      fotografia: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&h=350&fit=crop&auto=format',
      fechaCreacion: 'Ayer',
      coordenadas: { lat: 4.7315, lng: -74.1023 },
    },
    {
      id: 'sol-1042',
      codigo: '#1042',
      ciudadanoId: 'ciu-5',
      ciudadanoNombre: 'Papelería y Oficina Central',
      material: 'Papel',
      cantidadAproxKg: 35,
      ubicacion: 'Suba Centro',
      direccion: 'Carrera 88 #147-30',
      distanciaMetros: 950,
      horarioDisponible: '2:30 p.m. – 5:30 p.m.',
      prioridad: 'Alta',
      estado: 'Disponible',
      observaciones: 'Papel blanco de archivo limpio en cajas de resma apiladas.',
      fotografia: 'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?w=500&h=350&fit=crop&auto=format',
      fechaCreacion: 'Hoy, hace 40 min',
      coordenadas: { lat: 4.7460, lng: -74.0870 },
    },
  ],
  recolecciones: [
    {
      id: 'rec-prev-1',
      solicitudId: 'sol-1015',
      solicitudCodigo: '#1015',
      recicladorId: 'rec-carlos-1',
      materialRecogido: 'Cartón',
      cantidadEstimadaKg: 35,
      fecha: 'Ayer, 3:30 p.m.',
      fotografia: 'https://images.unsplash.com/photo-1530587191325-3db32d826c18?w=500&h=350&fit=crop&auto=format',
      observaciones: 'Recogido en portería. Cartón limpio y amarrado.',
      estado: 'Recolectado',
    },
    {
      id: 'rec-prev-2',
      solicitudId: 'sol-1018',
      solicitudCodigo: '#1018',
      recicladorId: 'rec-carlos-1',
      materialRecogido: 'Plástico',
      cantidadEstimadaKg: 20,
      fecha: 'Ayer, 4:45 p.m.',
      fotografia: 'https://images.unsplash.com/photo-1595278069441-2cf29f8005a4?w=500&h=350&fit=crop&auto=format',
      observaciones: 'Envases PET seleccionados sin tapas.',
      estado: 'Recolectado',
    },
  ],
  entregas: [
    {
      id: 'ent-1',
      codigo: 'ENT-2026-081',
      recicladorId: 'rec-carlos-1',
      recicladorNombre: 'Carlos Rodríguez',
      centroAcopioId: 'centro-suba-1',
      centroAcopioNombre: 'Centro Verde Suba',
      materiales: [
        { material: 'Cartón', cantidadEstimadaKg: 35, cantidadRealKg: 35, puntosGenerados: 350 },
      ],
      totalEstimadoKg: 35,
      totalValidadoKg: 35,
      ecopuntosOtorgados: 350,
      fecha: '12 de Sep, 4:10 p.m.',
      fechaValidacion: '12 de Sep, 4:25 p.m.',
      estado: 'Validada',
      observacionesReciclador: 'Material recogido en ruta cuadrante Suba Centro.',
      observacionesCentro: 'Cartón corrugado seco calificado Grado A. Validado por Ing. Gómez.',
    },
    {
      id: 'ent-2',
      codigo: 'ENT-2026-094',
      recicladorId: 'rec-carlos-1',
      recicladorNombre: 'Carlos Rodríguez',
      centroAcopioId: 'centro-suba-1',
      centroAcopioNombre: 'Centro Verde Suba',
      materiales: [
        { material: 'Cartón', cantidadEstimadaKg: 20 },
        { material: 'Plástico', cantidadEstimadaKg: 10 },
        { material: 'Vidrio', cantidadEstimadaKg: 5 },
      ],
      totalEstimadoKg: 35,
      fecha: 'Hoy, 10:15 a.m.',
      estado: 'Pendiente',
      observacionesReciclador: 'Carga lista en tolva para pesaje oficial en báscula.',
    },
  ],
  beneficios: [
    {
      id: 'ben-1',
      nombre: 'Kit de protección profesional',
      categoria: 'Protección y Trabajo',
      descripcion: 'Guantes de nitrilo reforzados nivel industrial + chaleco reflectivo reglamentario de alta visibilidad con bolsillos.',
      costoEcopuntos: 1500,
      imagen: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&h=260&fit=crop&auto=format',
      disponibles: 18,
      proveedor: 'Alianza ARL Segura & Co.',
      destacado: true,
    },
    {
      id: 'ben-2',
      nombre: 'Bono de alimentación canasta básica',
      categoria: 'Alimentación',
      descripcion: 'Bono digital por $60.000 COP válido en cadenas aliadas (Éxito, D1, Ara) para compra de víveres y alimentos.',
      costoEcopuntos: 2000,
      imagen: 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=260&fit=crop&auto=format',
      disponibles: 25,
      proveedor: 'Red Supermercados Unidos',
      destacado: true,
    },
    {
      id: 'ben-3',
      nombre: 'Llantas antipinchazos para bicicarrito',
      categoria: 'Transporte',
      descripcion: 'Juego de 2 llantas reforzadas 26" con banda interna de kevlar para resistir vidrios y clavos en el pavimento.',
      costoEcopuntos: 1800,
      imagen: 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=400&h=260&fit=crop&auto=format',
      disponibles: 8,
      proveedor: 'CicloPartes Bogotá',
      destacado: false,
    },
    {
      id: 'ben-4',
      nombre: 'Termo de hidratación acero térmico 1L',
      categoria: 'Salud y Hogar',
      descripcion: 'Botella térmica de doble pared que mantiene bebidas frías hasta por 18 horas durante la jornada de recolección.',
      costoEcopuntos: 600,
      imagen: 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=400&h=260&fit=crop&auto=format',
      disponibles: 30,
      proveedor: 'EcoTermos Colombia',
      destacado: false,
    },
    {
      id: 'ben-5',
      nombre: 'Botiquín de primeros auxilios portable',
      categoria: 'Protección y Trabajo',
      descripcion: 'Estuche impermeable con gasas estériles, desinfectante, vendajes elásticos, tijeras médicas y suero.',
      costoEcopuntos: 800,
      imagen: 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=260&fit=crop&auto=format',
      disponibles: 14,
      proveedor: 'Cruz Vital',
      destacado: false,
    },
    {
      id: 'ben-6',
      nombre: 'Paquete de datos móviles 15GB + Minutos',
      categoria: 'Tecnología',
      descripcion: 'Recarga mensual de conectividad de alta velocidad para navegación continua en la app Ecopoints en campo.',
      costoEcopuntos: 1000,
      imagen: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=260&fit=crop&auto=format',
      disponibles: 50,
      proveedor: 'Operador Móvil Solidario',
      destacado: false,
    },
  ],
  canjes: [
    {
      id: 'can-1',
      codigoCanje: 'ECO-9482',
      recicladorId: 'rec-carlos-1',
      beneficioId: 'ben-4',
      beneficioNombre: 'Termo de hidratación acero térmico 1L',
      beneficioImagen: 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=400&h=260&fit=crop&auto=format',
      costoEcopuntos: 600,
      fecha: '28 de Ago, 2026',
      estado: 'Entregado',
      vencimiento: '28 de Sep, 2026',
    },
  ],
  movimientosEcopuntos: [
    {
      id: 'mov-1',
      recicladorId: 'rec-carlos-1',
      concepto: 'Entrega de cartón validada (35 kg)',
      puntos: 350,
      tipo: 'ingreso',
      fecha: '12 de Sep, 4:25 p.m.',
      entregaId: 'ent-1',
    },
    {
      id: 'mov-2',
      recicladorId: 'rec-carlos-1',
      concepto: 'Entrega de plástico PET validada (20 kg)',
      puntos: 300,
      tipo: 'ingreso',
      fecha: '08 de Sep, 11:30 a.m.',
    },
    {
      id: 'mov-3',
      recicladorId: 'rec-carlos-1',
      concepto: 'Entrega de vidrio seleccionado (25 kg)',
      puntos: 200,
      tipo: 'ingreso',
      fecha: '02 de Sep, 5:10 p.m.',
    },
    {
      id: 'mov-4',
      recicladorId: 'rec-carlos-1',
      concepto: 'Canje de Termo de hidratación acero 1L',
      puntos: -600,
      tipo: 'canje',
      fecha: '28 de Ago, 3:00 p.m.',
      canjeId: 'can-1',
    },
  ],
  centrosAcopio: [
    {
      id: 'centro-suba-1',
      nombre: 'Centro Verde Suba',
      direccion: 'Carrera 91 #145-20, Suba Centro',
      zona: 'Suba, Bogotá',
      horario: 'Lunes a Sábado: 7:00 a.m. – 5:30 p.m.',
      telefono: '+57 (601) 682-9900',
      encargado: 'Ing. Mauricio Gómez (Báscula Certificada)',
      materialesAceptados: ['Cartón', 'Plástico', 'Vidrio', 'Metales', 'Papel'],
      coordenadas: { lat: 4.7445, lng: -74.0862 },
    },
    {
      id: 'centro-suba-2',
      nombre: 'EcoCentro Bogotá Norte',
      direccion: 'Av. Suba #128-45',
      zona: 'Niza - Suba',
      horario: 'Lunes a Domingo: 8:00 a.m. – 4:00 p.m.',
      telefono: '+57 (601) 689-1122',
      encargado: 'Dra. Claudia Restrepo',
      materialesAceptados: ['Cartón', 'Plástico', 'Papel', 'Otros'],
      coordenadas: { lat: 4.7210, lng: -74.0725 },
    },
  ],
  materiales: MATERIALES_CONFIG,
  estadisticasZona: {
    zona: 'Suba',
    kgRecuperados: 8500,
    recicladoresActivos: 120,
    solicitudesAtendidas: 2400,
  },
};

// ==========================================
// STORAGE SINGLETON & EVENT EMITTER
// ==========================================
type Listener = () => void;

class Database {
  private data: DatabaseSchema;
  private listeners: Set<Listener> = new Set();

  constructor() {
    this.data = this.load();
  }

  private load(): DatabaseSchema {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        return JSON.parse(stored);
      }
    } catch (e) {
      console.warn('Error al leer de localStorage, usando base de datos semilla', e);
    }
    this.save(INITIAL_DB);
    return INITIAL_DB;
  }

  private save(data: DatabaseSchema): void {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) {
      console.warn('Error al persistir en localStorage', e);
    }
  }

  public get(): DatabaseSchema {
    return this.data;
  }

  public update(updater: (prev: DatabaseSchema) => DatabaseSchema): DatabaseSchema {
    this.data = updater(this.data);
    this.save(this.data);
    this.notify();
    return this.data;
  }

  public subscribe(listener: Listener): () => void {
    this.listeners.add(listener);
    return () => this.listeners.delete(listener);
  }

  private notify(): void {
    this.listeners.forEach((fn) => fn());
  }

  public reset(): void {
    this.data = { ...INITIAL_DB };
    this.save(this.data);
    this.notify();
  }
}

export const db = new Database();
