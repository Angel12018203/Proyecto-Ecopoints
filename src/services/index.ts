// ==========================================
// SERVICIOS DE NEGOCIO - ECOPOINTS (MVC)
// ==========================================
import { db, MATERIALES_CONFIG } from '@/database/storage';
import { 
  Solicitud, Recoleccion, Entrega, Beneficio, Canje, 
  ParadaRuta, Ruta, TipoMaterial, PrioridadSolicitud, 
  RolUsuario, NivelReciclador, MovimientoEcopunto 
} from '@/models/types';

// ----------------------------------------------------
// 1. AuthService
// ----------------------------------------------------
export const AuthService = {
  getRolActivo(): RolUsuario {
    return db.get().rolActivo;
  },

  setRolActivo(rol: RolUsuario): void {
    db.update((prev) => ({ ...prev, rolActivo: rol }));
  },

  getReciclador() {
    return db.get().reciclador;
  },

  actualizarPerfil(datos: Partial<typeof db extends { get: () => { reciclador: infer R } } ? R : never>) {
    db.update((prev) => ({
      ...prev,
      reciclador: { ...prev.reciclador, ...datos },
    }));
    return db.get().reciclador;
  },

  registrarNuevoReciclador(datos: {
    nombre: string;
    documento: string;
    telefono: string;
    email: string;
    zonaTrabajo: string;
    vehiculo: string;
    experiencia: string;
    foto?: string;
  }) {
    const nuevo = {
      id: `rec-${Date.now()}`,
      usuarioId: `usr-${Date.now()}`,
      nombre: datos.nombre,
      documento: datos.documento,
      telefono: datos.telefono,
      email: datos.email,
      foto: datos.foto || 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=200&h=200&fit=crop&crop=faces&auto=format',
      zonaTrabajo: datos.zonaTrabajo,
      vehiculo: datos.vehiculo,
      experiencia: datos.experiencia,
      ecopuntos: 0,
      kgRecuperados: 0,
      recoleccionesCompletadas: 0,
      entregasPendientes: 0,
      nivel: 'Reciclador Activo' as NivelReciclador,
      proximoNivel: 'EcoReciclador' as NivelReciclador,
      puntosParaSiguienteNivel: 501,
    };

    db.update((prev) => ({
      ...prev,
      reciclador: nuevo,
      rolActivo: 'reciclador',
    }));
    return nuevo;
  },
};

// ----------------------------------------------------
// 2. SolicitudService
// ----------------------------------------------------
export interface FiltrosSolicitud {
  material?: string;
  distancia?: string; // '< 1 km' | '1–3 km' | '3–5 km' | '+5 km'
  cantidad?: string;  // 'Menos de 10 kg' | '10–30 kg' | '30–50 kg' | '+50 kg'
  prioridad?: PrioridadSolicitud;
  busqueda?: string;
}

export const SolicitudService = {
  getAll(): Solicitud[] {
    return db.get().solicitudes;
  },

  getById(id: string): Solicitud | undefined {
    return db.get().solicitudes.find((s) => s.id === id);
  },

  getCercanas(filtros: FiltrosSolicitud = {}): Solicitud[] {
    let result = db.get().solicitudes.filter((s) => s.estado === 'Disponible');

    if (filtros.material && filtros.material !== 'Todos') {
      result = result.filter((s) => s.material === filtros.material);
    }

    if (filtros.prioridad) {
      result = result.filter((s) => s.prioridad === filtros.prioridad);
    }

    if (filtros.busqueda) {
      const q = filtros.busqueda.toLowerCase();
      result = result.filter(
        (s) =>
          s.codigo.toLowerCase().includes(q) ||
          s.material.toLowerCase().includes(q) ||
          s.ubicacion.toLowerCase().includes(q) ||
          s.ciudadanoNombre.toLowerCase().includes(q) ||
          s.observaciones.toLowerCase().includes(q)
      );
    }

    if (filtros.distancia && filtros.distancia !== 'Todas') {
      switch (filtros.distancia) {
        case '< 1 km':
          result = result.filter((s) => s.distanciaMetros < 1000);
          break;
        case '1–3 km':
          result = result.filter((s) => s.distanciaMetros >= 1000 && s.distanciaMetros <= 3000);
          break;
        case '3–5 km':
          result = result.filter((s) => s.distanciaMetros > 3000 && s.distanciaMetros <= 5000);
          break;
        case '+5 km':
          result = result.filter((s) => s.distanciaMetros > 5000);
          break;
      }
    }

    if (filtros.cantidad && filtros.cantidad !== 'Todas') {
      switch (filtros.cantidad) {
        case 'Menos de 10 kg':
          result = result.filter((s) => s.cantidadAproxKg < 10);
          break;
        case '10–30 kg':
          result = result.filter((s) => s.cantidadAproxKg >= 10 && s.cantidadAproxKg <= 30);
          break;
        case '30–50 kg':
          result = result.filter((s) => s.cantidadAproxKg > 30 && s.cantidadAproxKg <= 50);
          break;
        case '+50 kg':
          result = result.filter((s) => s.cantidadAproxKg > 50);
          break;
      }
    }

    return result;
  },

  aceptarSolicitud(solicitudId: string, recicladorId: string): Solicitud {
    let updated: Solicitud | undefined;
    db.update((prev) => {
      const solicitudes = prev.solicitudes.map((s) => {
        if (s.id === solicitudId) {
          updated = { ...s, estado: 'Aceptada' as const, recicladorAsignadoId: recicladorId };
          return updated;
        }
        return s;
      });
      return { ...prev, solicitudes };
    });
    if (!updated) throw new Error('Solicitud no encontrada');
    return updated;
  },

  rechazarSolicitud(solicitudId: string): void {
    // Si la rechaza, simplemente sigue disponible o se desasigna
    db.update((prev) => ({
      ...prev,
      solicitudes: prev.solicitudes.map((s) =>
        s.id === solicitudId ? { ...s, estado: 'Disponible' as const, recicladorAsignadoId: undefined } : s
      ),
    }));
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
    fotografia?: string;
  }): Solicitud {
    const codigoNumero = 1045 + Math.floor(Math.random() * 800);
    const nueva: Solicitud = {
      id: `sol-${Date.now()}`,
      codigo: `#${codigoNumero}`,
      ciudadanoId: `ciu-${Date.now()}`,
      ciudadanoNombre: datos.ciudadanoNombre,
      material: datos.material,
      cantidadAproxKg: Number(datos.cantidadAproxKg),
      ubicacion: datos.ubicacion,
      direccion: datos.direccion,
      distanciaMetros: Math.floor(600 + Math.random() * 2200),
      horarioDisponible: datos.horarioDisponible,
      prioridad: datos.prioridad,
      estado: 'Disponible',
      observaciones: datos.observaciones,
      fotografia:
        datos.fotografia ||
        'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=500&h=350&fit=crop&auto=format',
      fechaCreacion: 'Hace un momento',
      coordenadas: {
        lat: 4.743 + (Math.random() - 0.5) * 0.02,
        lng: -74.085 + (Math.random() - 0.5) * 0.02,
      },
    };

    db.update((prev) => ({
      ...prev,
      solicitudes: [nueva, ...prev.solicitudes],
    }));
    return nueva;
  },
};

// ----------------------------------------------------
// 3. RutaService
// ----------------------------------------------------
export const RutaService = {
  getSolicitudesEnRuta(): Solicitud[] {
    return db
      .get()
      .solicitudes.filter((s) => s.estado === 'Aceptada' || s.estado === 'En recolección');
  },

  getRutaCalculada(): Ruta {
    const aceptadas = this.getSolicitudesEnRuta();
    let distanciaAcumulada = 0;
    const paradas: ParadaRuta[] = aceptadas.map((sol, index) => {
      const distKm = Number((sol.distanciaMetros / 1000).toFixed(1));
      distanciaAcumulada += distKm;
      return {
        orden: index + 1,
        solicitud: sol,
        distanciaParcialKm: distKm,
        tiempoEstimadoMin: Math.max(10, Math.round(distKm * 8 + 15)),
      };
    });

    const tiempoTotalMin = paradas.reduce((acc, p) => acc + p.tiempoEstimadoMin, 0);

    return {
      id: 'ruta-hoy',
      recicladorId: db.get().reciclador.id,
      fecha: 'Hoy',
      paradas,
      distanciaTotalKm: Number(distanciaAcumulada.toFixed(1)),
      tiempoTotalMin,
    };
  },

  optimizarRuta(): Ruta {
    // Ordena las paradas por menor distancia
    const aceptadas = [...this.getSolicitudesEnRuta()].sort(
      (a, b) => a.distanciaMetros - b.distanciaMetros
    );
    // Asigna el nuevo orden
    let distanciaAcumulada = 0;
    const paradas: ParadaRuta[] = aceptadas.map((sol, index) => {
      const distKm = Number((sol.distanciaMetros / 1000).toFixed(1));
      distanciaAcumulada += distKm;
      return {
        orden: index + 1,
        solicitud: sol,
        distanciaParcialKm: distKm,
        tiempoEstimadoMin: Math.max(10, Math.round(distKm * 7 + 12)),
      };
    });

    return {
      id: 'ruta-optimizada',
      recicladorId: db.get().reciclador.id,
      fecha: 'Hoy',
      paradas,
      distanciaTotalKm: Number(distanciaAcumulada.toFixed(1)),
      tiempoTotalMin: paradas.reduce((acc, p) => acc + p.tiempoEstimadoMin, 0),
    };
  },
};

// ----------------------------------------------------
// 4. RecoleccionService
// ----------------------------------------------------
export const RecoleccionService = {
  getAll(): Recoleccion[] {
    return db.get().recolecciones;
  },

  registrarRecoleccion(datos: {
    solicitudId: string;
    cantidadEstimadaKg: number;
    observaciones: string;
    fotografia?: string;
  }): Recoleccion {
    const solicitud = SolicitudService.getById(datos.solicitudId);
    if (!solicitud) throw new Error('Solicitud no encontrada');

    const nuevaRecoleccion: Recoleccion = {
      id: `rec-${Date.now()}`,
      solicitudId: solicitud.id,
      solicitudCodigo: solicitud.codigo,
      recicladorId: db.get().reciclador.id,
      materialRecogido: solicitud.material,
      cantidadEstimadaKg: Number(datos.cantidadEstimadaKg),
      fecha: 'Hoy, hace un momento',
      fotografia: datos.fotografia || solicitud.fotografia,
      observaciones: datos.observaciones || 'Material recogido y cargado en el vehículo.',
      estado: 'Recolectado',
    };

    db.update((prev) => {
      // 1. Cambiar estado de solicitud a 'Recolectada'
      const solicitudes = prev.solicitudes.map((s) =>
        s.id === solicitud.id ? { ...s, estado: 'Recolectada' as const } : s
      );

      // 2. Incrementar recolecciones completadas del reciclador
      const reciclador = {
        ...prev.reciclador,
        recoleccionesCompletadas: prev.reciclador.recoleccionesCompletadas + 1,
      };

      return {
        ...prev,
        solicitudes,
        recolecciones: [nuevaRecoleccion, ...prev.recolecciones],
        reciclador,
      };
    });

    return nuevaRecoleccion;
  },
};

// ----------------------------------------------------
// 5. EntregaService
// ----------------------------------------------------
export const EntregaService = {
  getAll(): Entrega[] {
    return db.get().entregas;
  },

  getCentrosAcopio() {
    return db.get().centrosAcopio;
  },

  registrarEntrega(datos: {
    centroAcopioId: string;
    materiales: { material: TipoMaterial; cantidadEstimadaKg: number }[];
    observaciones?: string;
  }): Entrega {
    const centro = db.get().centrosAcopio.find((c) => c.id === datos.centroAcopioId);
    if (!centro) throw new Error('Centro de acopio no válido');

    const totalEstimado = datos.materiales.reduce((acc, m) => acc + Number(m.cantidadEstimadaKg), 0);
    const codigoEntrega = `ENT-2026-${Math.floor(100 + Math.random() * 900)}`;

    const nueva: Entrega = {
      id: `ent-${Date.now()}`,
      codigo: codigoEntrega,
      recicladorId: db.get().reciclador.id,
      recicladorNombre: db.get().reciclador.nombre,
      centroAcopioId: centro.id,
      centroAcopioNombre: centro.nombre,
      materiales: datos.materiales,
      totalEstimadoKg: totalEstimado,
      fecha: 'Hoy, hace un momento',
      estado: 'Pendiente',
      observacionesReciclador: datos.observaciones || 'Carga lista para pesaje oficial en centro de acopio.',
    };

    db.update((prev) => ({
      ...prev,
      entregas: [nueva, ...prev.entregas],
      reciclador: {
        ...prev.reciclador,
        entregasPendientes: prev.reciclador.entregasPendientes + 1,
      },
    }));

    return nueva;
  },
};

// ----------------------------------------------------
// 6. PesajeService & Validación (Centro de Acopio)
// Regla Central: Los Ecopuntos se calculan SOLO tras Pesaje y Validación
// ----------------------------------------------------
export const PesajeService = {
  validarEntrega(datos: {
    entregaId: string;
    desglosePesaje: { material: TipoMaterial; kgReal: number }[];
    operadorNombre: string;
    observacionesTecnicas: string;
  }) {
    const entrega = db.get().entregas.find((e) => e.id === datos.entregaId);
    if (!entrega) throw new Error('Entrega no encontrada');

    let totalKgReal = 0;
    let totalEcopuntos = 0;

    const materialesValidados = datos.desglosePesaje.map((item) => {
      const matConfig = MATERIALES_CONFIG.find((m) => m.nombre === item.material);
      const factor = matConfig ? matConfig.puntosPorKg : 10;
      const pts = Math.round(Number(item.kgReal) * factor);
      totalKgReal += Number(item.kgReal);
      totalEcopuntos += pts;

      return {
        material: item.material,
        cantidadEstimadaKg: item.kgReal,
        cantidadRealKg: item.kgReal,
        puntosGenerados: pts,
      };
    });

    // Actualizar entrega, acreditar puntos al reciclador y registrar movimiento
    db.update((prev) => {
      const entregas = prev.entregas.map((e) => {
        if (e.id === datos.entregaId) {
          return {
            ...e,
            materiales: materialesValidados,
            totalValidadoKg: totalKgReal,
            ecopuntosOtorgados: totalEcopuntos,
            fechaValidacion: 'Hoy, hace un momento',
            estado: 'Validada' as const,
            observacionesCentro: datos.observacionesTecnicas || 'Pesaje electrónico oficial certificado por el centro.',
          };
        }
        return e;
      });

      // Cálculo del nuevo balance y nivel del reciclador
      const nuevoTotalPuntos = prev.reciclador.ecopuntos + totalEcopuntos;
      const nuevoTotalKg = prev.reciclador.kgRecuperados + totalKgReal;
      const entregasPend = Math.max(0, prev.reciclador.entregasPendientes - 1);

      const progresoNivel = EcopuntosService.calcularNivel(nuevoTotalPuntos);

      const recicladorActualizado = {
        ...prev.reciclador,
        ecopuntos: nuevoTotalPuntos,
        kgRecuperados: nuevoTotalKg,
        entregasPendientes: entregasPend,
        nivel: progresoNivel.nivel,
        proximoNivel: progresoNivel.proximoNivel,
        puntosParaSiguienteNivel: progresoNivel.puntosParaSiguienteNivel,
      };

      // Nuevo movimiento en el historial
      const nuevoMovimiento: MovimientoEcopunto = {
        id: `mov-${Date.now()}`,
        recicladorId: prev.reciclador.id,
        concepto: `Entrega ${entrega.codigo} validada (${totalKgReal} kg recuperados)`,
        puntos: totalEcopuntos,
        tipo: 'ingreso',
        fecha: 'Hoy, hace un momento',
        entregaId: entrega.id,
      };

      return {
        ...prev,
        entregas,
        reciclador: recicladorActualizado,
        movimientosEcopuntos: [nuevoMovimiento, ...prev.movimientosEcopuntos],
      };
    });

    return {
      entregaId: datos.entregaId,
      totalKgReal,
      totalEcopuntos,
      reciclador: db.get().reciclador,
    };
  },
};

// ----------------------------------------------------
// 7. EcopuntosService
// ----------------------------------------------------
export const EcopuntosService = {
  calcularNivel(puntos: number): {
    nivel: NivelReciclador;
    proximoNivel: NivelReciclador;
    puntosParaSiguienteNivel: number;
    progresoPorcentaje: number;
  } {
    if (puntos <= 500) {
      return {
        nivel: 'Reciclador Activo',
        proximoNivel: 'EcoReciclador',
        puntosParaSiguienteNivel: 501 - puntos,
        progresoPorcentaje: Math.min(100, Math.round((puntos / 500) * 100)),
      };
    } else if (puntos <= 1500) {
      return {
        nivel: 'EcoReciclador',
        proximoNivel: 'Reciclador Destacado',
        puntosParaSiguienteNivel: 1501 - puntos,
        progresoPorcentaje: Math.min(100, Math.round(((puntos - 500) / 1000) * 100)),
      };
    } else if (puntos <= 3000) {
      return {
        nivel: 'Reciclador Destacado',
        proximoNivel: 'Embajador de Economía Circular',
        puntosParaSiguienteNivel: 3001 - puntos,
        progresoPorcentaje: Math.min(100, Math.round(((puntos - 1500) / 1500) * 100)),
      };
    } else {
      return {
        nivel: 'Embajador de Economía Circular',
        proximoNivel: 'Embajador de Economía Circular',
        puntosParaSiguienteNivel: 0,
        progresoPorcentaje: 100,
      };
    }
  },

  getMovimientos(): MovimientoEcopunto[] {
    return db.get().movimientosEcopuntos;
  },
};

// ----------------------------------------------------
// 8. BeneficioService
// ----------------------------------------------------
export const BeneficioService = {
  getAll(): Beneficio[] {
    return db.get().beneficios;
  },

  getCanjes(): Canje[] {
    return db.get().canjes;
  },

  canjear(beneficioId: string): Canje {
    const beneficio = db.get().beneficios.find((b) => b.id === beneficioId);
    if (!beneficio) throw new Error('Beneficio no encontrado');

    const reciclador = db.get().reciclador;
    if (reciclador.ecopuntos < beneficio.costoEcopuntos) {
      throw new Error(`Puntos insuficientes. Tienes ${reciclador.ecopuntos} y requieres ${beneficio.costoEcopuntos}`);
    }

    const codigoCanje = `ECO-${Math.floor(1000 + Math.random() * 9000)}`;

    const nuevoCanje: Canje = {
      id: `can-${Date.now()}`,
      codigoCanje,
      recicladorId: reciclador.id,
      beneficioId: beneficio.id,
      beneficioNombre: beneficio.nombre,
      beneficioImagen: beneficio.imagen,
      costoEcopuntos: beneficio.costoEcopuntos,
      fecha: 'Hoy, hace un momento',
      estado: 'Disponible',
      vencimiento: 'Vence en 30 días',
    };

    db.update((prev) => {
      const nuevoBalance = prev.reciclador.ecopuntos - beneficio.costoEcopuntos;
      const progreso = EcopuntosService.calcularNivel(nuevoBalance);

      const nuevoMovimiento: MovimientoEcopunto = {
        id: `mov-${Date.now()}`,
        recicladorId: prev.reciclador.id,
        concepto: `Canje: ${beneficio.nombre}`,
        puntos: -beneficio.costoEcopuntos,
        tipo: 'canje',
        fecha: 'Hoy, hace un momento',
        canjeId: nuevoCanje.id,
      };

      const beneficios = prev.beneficios.map((b) =>
        b.id === beneficio.id ? { ...b, disponibles: Math.max(0, b.disponibles - 1) } : b
      );

      return {
        ...prev,
        beneficios,
        canjes: [nuevoCanje, ...prev.canjes],
        movimientosEcopuntos: [nuevoMovimiento, ...prev.movimientosEcopuntos],
        reciclador: {
          ...prev.reciclador,
          ecopuntos: nuevoBalance,
          nivel: progreso.nivel,
          proximoNivel: progreso.proximoNivel,
          puntosParaSiguienteNivel: progreso.puntosParaSiguienteNivel,
        },
      };
    });

    return nuevoCanje;
  },
};

// ----------------------------------------------------
// 9. EstadisticaService
// ----------------------------------------------------
export const EstadisticaService = {
  getEstadisticasReciclador() {
    const rec = db.get().reciclador;
    // Cálculos de desglose por material basados en entregas validadas
    const desglose: Record<string, number> = {
      Cartón: 480,
      Plástico: 370,
      Vidrio: 220,
      Metales: 180,
    };

    const total = Object.values(desglose).reduce((a, b) => a + b, 0);

    return {
      reciclador: rec,
      desgloseMateriales: [
        { material: 'Cartón', kg: 480, color: '#D97706', porcentaje: 38 },
        { material: 'Plástico', kg: 370, color: '#0284C7', porcentaje: 30 },
        { material: 'Vidrio', kg: 220, color: '#059669', porcentaje: 18 },
        { material: 'Metales', kg: 180, color: '#DC2626', porcentaje: 14 },
      ],
      totalRecuperadoKg: total,
      co2EvitadoKg: Math.round(rec.kgRecuperados * 1.8),
      arbolesSalvados: Math.round((rec.kgRecuperados * 0.4) / 50),
      aguaAhorradaLitros: Math.round(rec.kgRecuperados * 26),
    };
  },

  getEstadisticasZona() {
    return db.get().estadisticasZona;
  },
};
