// ==========================================
// MODELOS DE DOMINIO - ECOPOINTS (MVC)
// ==========================================

export type RolUsuario = 'reciclador' | 'ciudadano' | 'centro_acopio' | 'admin';

export type NivelReciclador = 
  | 'Reciclador Activo'               // 0 - 500 pts
  | 'EcoReciclador'                   // 501 - 1.500 pts
  | 'Reciclador Destacado'            // 1.501 - 3.000 pts
  | 'Embajador de Economía Circular'; // +3.000 pts

export type TipoMaterial = 'Cartón' | 'Plástico' | 'Vidrio' | 'Metales' | 'Papel' | 'Otros';

export type PrioridadSolicitud = 'Alta' | 'Media' | 'Baja';

export type EstadoSolicitud = 
  | 'Disponible' 
  | 'Aceptada' 
  | 'En recolección' 
  | 'Recolectada' 
  | 'Cancelada';

export type EstadoEntrega = 
  | 'Pendiente' 
  | 'Recibida' 
  | 'Pesada' 
  | 'Validada' 
  | 'Rechazada';

export type EstadoBeneficio = 
  | 'Disponible' 
  | 'Canjeado' 
  | 'Pendiente' 
  | 'Entregado';

// 1. Modelo Usuario
export interface Usuario {
  id: string;
  nombre: string;
  email: string;
  telefono: string;
  rol: RolUsuario;
  foto: string;
  fechaRegistro: string;
}

// 2. Modelo Reciclador (Actor Principal)
export interface Reciclador {
  id: string;
  usuarioId: string;
  nombre: string;
  documento: string;
  telefono: string;
  email: string;
  foto: string;
  zonaTrabajo: string;
  vehiculo: string;
  experiencia: string;
  ecopuntos: number;
  kgRecuperados: number;
  recoleccionesCompletadas: number;
  entregasPendientes: number;
  nivel: NivelReciclador;
  proximoNivel: NivelReciclador;
  puntosParaSiguienteNivel: number;
}

// 3. Modelo Ciudadano
export interface Ciudadano {
  id: string;
  usuarioId: string;
  nombre: string;
  direccion: string;
  barrio: string;
  telefono: string;
}

// 4. Modelo Material
export interface Material {
  id: string;
  nombre: TipoMaterial;
  icono: string;
  color: string;
  puntosPorKg: number;
  descripcion: string;
}

// 5. Modelo Solicitud
export interface Solicitud {
  id: string;
  codigo: string; // ej: "#1024"
  ciudadanoId: string;
  ciudadanoNombre: string;
  material: TipoMaterial;
  cantidadAproxKg: number;
  ubicacion: string; // ej: "Suba"
  direccion: string;
  distanciaMetros: number;
  horarioDisponible: string; // ej: "2:00 p.m. – 5:00 p.m."
  prioridad: PrioridadSolicitud;
  estado: EstadoSolicitud;
  observaciones: string;
  fotografia: string;
  fechaCreacion: string;
  coordenadas: { lat: number; lng: number };
  recicladorAsignadoId?: string;
}

// 6. Modelo Recolección
export interface Recoleccion {
  id: string;
  solicitudId: string;
  solicitudCodigo: string;
  recicladorId: string;
  materialRecogido: TipoMaterial;
  cantidadEstimadaKg: number;
  fecha: string;
  fotografia?: string;
  observaciones: string;
  estado: 'Recolectado';
}

// 7. Modelo Parada / Ruta
export interface ParadaRuta {
  orden: number;
  solicitud: Solicitud;
  distanciaParcialKm: number;
  tiempoEstimadoMin: number;
}

export interface Ruta {
  id: string;
  recicladorId: string;
  fecha: string;
  paradas: ParadaRuta[];
  distanciaTotalKm: number;
  tiempoTotalMin: number;
}

// 8. Modelo Centro de Acopio
export interface CentroAcopio {
  id: string;
  nombre: string; // ej: "Centro Verde Suba"
  direccion: string;
  zona: string;
  horario: string;
  telefono: string;
  encargado: string;
  materialesAceptados: TipoMaterial[];
  coordenadas: { lat: number; lng: number };
}

// 9. Modelo Entrega
export interface DetalleMaterialEntrega {
  material: TipoMaterial;
  cantidadEstimadaKg: number;
  cantidadRealKg?: number;
  puntosGenerados?: number;
}

export interface Entrega {
  id: string;
  codigo: string; // ej: "ENT-2026-081"
  recicladorId: string;
  recicladorNombre: string;
  centroAcopioId: string;
  centroAcopioNombre: string;
  materiales: DetalleMaterialEntrega[];
  totalEstimadoKg: number;
  totalValidadoKg?: number;
  ecopuntosOtorgados?: number;
  fecha: string;
  fechaValidacion?: string;
  estado: EstadoEntrega;
  observacionesReciclador?: string;
  observacionesCentro?: string;
}

// 10. Modelo Pesaje y Validación Técnica
export interface RegistroPesaje {
  id: string;
  entregaId: string;
  fechaPesaje: string;
  operadorCentro: string;
  items: {
    material: TipoMaterial;
    kgReal: number;
    factorPuntos: number;
    ecopuntos: number;
  }[];
  totalKg: number;
  totalEcopuntos: number;
  observacionesTecnicas: string;
}

// 11. Modelo Ecopunto (Transacciones)
export interface MovimientoEcopunto {
  id: string;
  recicladorId: string;
  concepto: string; // ej: "Entrega de cartón validada en Centro Verde Suba"
  puntos: number;
  tipo: 'ingreso' | 'canje';
  fecha: string;
  entregaId?: string;
  canjeId?: string;
}

// 12. Modelo Beneficio
export interface Beneficio {
  id: string;
  nombre: string;
  categoria: 'Protección y Trabajo' | 'Alimentación' | 'Transporte' | 'Salud y Hogar' | 'Tecnología';
  descripcion: string;
  costoEcopuntos: number;
  imagen: string;
  disponibles: number;
  proveedor: string;
  destacado?: boolean;
}

// 13. Modelo Canje
export interface Canje {
  id: string;
  codigoCanje: string; // ej: "BEN-8924"
  recicladorId: string;
  beneficioId: string;
  beneficioNombre: string;
  beneficioImagen: string;
  costoEcopuntos: number;
  fecha: string;
  estado: EstadoBeneficio;
  vencimiento: string;
}

// 14. Estadísticas de Zona
export interface EstadisticasZona {
  zona: string; // "Suba"
  kgRecuperados: number; // 8.500 kg
  recicladoresActivos: number; // 120
  solicitudesAtendidas: number; // 2.400
}
