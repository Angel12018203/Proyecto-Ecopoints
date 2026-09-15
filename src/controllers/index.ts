// ==========================================
// CAPA DE CONTROLADORES - ECOPOINTS (MVC)
// ==========================================
import { 
  AuthService, SolicitudService, RutaService, RecoleccionService, 
  EntregaService, PesajeService, EcopuntosService, BeneficioService, 
  EstadisticaService, FiltrosSolicitud 
} from '@/services';
import { TipoMaterial, PrioridadSolicitud, RolUsuario } from '@/models/types';

export interface ControllerResult<T = unknown> {
  success: boolean;
  message: string;
  data?: T;
  error?: string;
}

// 1. AuthController
export const AuthController = {
  cambiarRol(rol: RolUsuario): ControllerResult {
    AuthService.setRolActivo(rol);
    return {
      success: true,
      message: `Rol activo cambiado a: ${rol}`,
      data: rol,
    };
  },

  registrarReciclador(datos: {
    nombre: string;
    documento: string;
    telefono: string;
    email: string;
    zonaTrabajo: string;
    vehiculo: string;
    experiencia: string;
    foto?: string;
  }): ControllerResult {
    if (!datos.nombre.trim() || !datos.documento.trim() || !datos.zonaTrabajo.trim()) {
      return { success: false, message: 'Por favor completa los campos obligatorios.', error: 'Campos faltantes' };
    }
    const nuevo = AuthService.registrarNuevoReciclador(datos);
    return {
      success: true,
      message: `¡Bienvenido a Ecopoints, ${nuevo.nombre}! Tu cuenta de reciclador está lista.`,
      data: nuevo,
    };
  },

  actualizarPerfil(datos: { telefono?: string; vehiculo?: string; experiencia?: string; zonaTrabajo?: string }): ControllerResult {
    const actualizado = AuthService.actualizarPerfil(datos);
    return {
      success: true,
      message: 'Perfil actualizado correctamente.',
      data: actualizado,
    };
  },
};

// 2. SolicitudController
export const SolicitudController = {
  listarCercanas(filtros: FiltrosSolicitud) {
    return SolicitudService.getCercanas(filtros);
  },

  obtenerDetalle(id: string) {
    return SolicitudService.getById(id);
  },

  aceptarSolicitud(solicitudId: string, recicladorId: string): ControllerResult {
    try {
      const solicitud = SolicitudService.aceptarSolicitud(solicitudId, recicladorId);
      return {
        success: true,
        message: `Solicitud ${solicitud.codigo} agregada a tu ruta de recolección.`,
        data: solicitud,
      };
    } catch (e) {
      return {
        success: false,
        message: 'No fue posible aceptar la solicitud.',
        error: String(e),
      };
    }
  },

  rechazarSolicitud(solicitudId: string): ControllerResult {
    SolicitudService.rechazarSolicitud(solicitudId);
    return {
      success: true,
      message: 'Solicitud descartada de tu lista.',
    };
  },

  crearSolicitudCiudadano(datos: {
    ciudadanoNombre: string;
    material: TipoMaterial;
    cantidadAproxKg: number;
    ubicacion: string;
    direccion: string;
    horarioDisponible: string;
    observaciones: string;
    prioridad: PrioridadSolicitud;
  }): ControllerResult {
    if (!datos.ciudadanoNombre || !datos.material || !datos.cantidadAproxKg || !datos.ubicacion) {
      return { success: false, message: 'Faltan datos requeridos de la solicitud.' };
    }
    const nueva = SolicitudService.crearSolicitudCiudadano(datos);
    return {
      success: true,
      message: `Solicitud ${nueva.codigo} publicada con éxito. Notificando a recicladores cercanos.`,
      data: nueva,
    };
  },
};

// 3. RutaController
export const RutaController = {
  obtenerRutaHoy() {
    return RutaService.getRutaCalculada();
  },

  optimizarRuta(): ControllerResult {
    const optimizada = RutaService.optimizarRuta();
    return {
      success: true,
      message: 'Ruta optimizada por menor distancia y tiempo estimado.',
      data: optimizada,
    };
  },
};

// 4. RecoleccionController
export const RecoleccionController = {
  listarRecolecciones() {
    return RecoleccionService.getAll();
  },

  registrarRecoleccion(datos: {
    solicitudId: string;
    cantidadEstimadaKg: number;
    observaciones: string;
    fotografia?: string;
  }): ControllerResult {
    if (!datos.solicitudId || !datos.cantidadEstimadaKg) {
      return { success: false, message: 'Por favor indica la cantidad estimada recogida.' };
    }
    try {
      const rec = RecoleccionService.registrarRecoleccion(datos);
      return {
        success: true,
        message: `¡Recolección de la solicitud ${rec.solicitudCodigo} registrada exitosamente! Estado: Recolectado.`,
        data: rec,
      };
    } catch (e) {
      return { success: false, message: 'Error al registrar recolección', error: String(e) };
    }
  },
};

// 5. EntregaController
export const EntregaController = {
  listarEntregas() {
    return EntregaService.getAll();
  },

  listarCentrosAcopio() {
    return EntregaService.getCentrosAcopio();
  },

  registrarEntrega(datos: {
    centroAcopioId: string;
    materiales: { material: TipoMaterial; cantidadEstimadaKg: number }[];
    observaciones?: string;
  }): ControllerResult {
    if (!datos.centroAcopioId || !datos.materiales.length) {
      return { success: false, message: 'Selecciona el centro de acopio y al menos un material.' };
    }
    try {
      const entrega = EntregaService.registrarEntrega(datos);
      return {
        success: true,
        message: `Entrega ${entrega.codigo} registrada. Estado: Pendiente de validación en el centro de acopio.`,
        data: entrega,
      };
    } catch (e) {
      return { success: false, message: 'Error al registrar entrega', error: String(e) };
    }
  },
};

// 6. CentroAcopioController & Validación
export const CentroAcopioController = {
  validarEntrega(datos: {
    entregaId: string;
    desglosePesaje: { material: TipoMaterial; kgReal: number }[];
    operadorNombre: string;
    observacionesTecnicas: string;
  }): ControllerResult {
    if (!datos.entregaId || !datos.desglosePesaje.length) {
      return { success: false, message: 'Desglose de pesaje incompleto.' };
    }
    try {
      const res = PesajeService.validarEntrega(datos);
      return {
        success: true,
        message: `¡Entrega validada! ${res.totalKgReal} kg recuperados. Se acreditaron +${res.totalEcopuntos} Ecopuntos.`,
        data: res,
      };
    } catch (e) {
      return { success: false, message: 'Error en la validación', error: String(e) };
    }
  },
};

// 7. EcopuntosController
export const EcopuntosController = {
  obtenerMovimientos() {
    return EcopuntosService.getMovimientos();
  },
};

// 8. BeneficioController
export const BeneficioController = {
  listarBeneficios() {
    return BeneficioService.getAll();
  },

  listarCanjes() {
    return BeneficioService.getCanjes();
  },

  canjear(beneficioId: string): ControllerResult {
    try {
      const canje = BeneficioService.canjear(beneficioId);
      return {
        success: true,
        message: `¡Canje exitoso! Presenta tu código ${canje.codigoCanje} ante el aliado para reclamarlo.`,
        data: canje,
      };
    } catch (e) {
      return {
        success: false,
        message: e instanceof Error ? e.message : 'No se pudo realizar el canje.',
        error: String(e),
      };
    }
  },
};

// 9. EstadisticaController
export const EstadisticaController = {
  obtenerEstadisticas() {
    return EstadisticaService.getEstadisticasReciclador();
  },
  obtenerEstadisticasZona() {
    return EstadisticaService.getEstadisticasZona();
  },
};
