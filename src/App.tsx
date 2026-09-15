import React, { useState, useEffect } from 'react';
import { db } from '@/database/storage';
import { Solicitud, TipoMaterial } from '@/models/types';
import {
  SolicitudController,
  RecoleccionController,
  EntregaController,
  CentroAcopioController,
  BeneficioController,
  AuthController,
  RutaController,
} from '@/controllers';

import { Sidebar, NavSection } from '@/views/components/Sidebar';
import { Navbar } from '@/views/components/Navbar';
import { Toast, ToastData } from '@/views/components/Toast';

import { DashboardView } from '@/views/dashboard/DashboardView';
import { SolicitudesView } from '@/views/solicitudes/SolicitudesView';
import { DetalleSolicitudModal } from '@/views/solicitudes/DetalleSolicitudModal';
import { MapaView } from '@/views/mapa/MapaView';
import { MisRecoleccionesView } from '@/views/recolecciones/MisRecoleccionesView';
import { RegistrarRecoleccionModal } from '@/views/recolecciones/RegistrarRecoleccionModal';
import { OrganizarRutaView } from '@/views/ruta/OrganizarRutaView';
import { EntregasView } from '@/views/entregas/EntregasView';
import { RegistrarEntregaModal } from '@/views/entregas/RegistrarEntregaModal';
import { CentroAcopioModal } from '@/views/centro_acopio/CentroAcopioModal';
import { EcopuntosView } from '@/views/ecopuntos/EcopuntosView';
import { BeneficiosView } from '@/views/beneficios/BeneficiosView';
import { EstadisticasView } from '@/views/estadisticas/EstadisticasView';
import { HistorialView } from '@/views/historial/HistorialView';
import { PerfilView } from '@/views/perfil/PerfilView';
import { ConfiguracionView } from '@/views/configuracion/ConfiguracionView';
import { AyudaView } from '@/views/ayuda/AyudaView';
import { CiudadanoCrearSolicitudModal } from '@/views/ciudadano/CiudadanoCrearSolicitudModal';

export default function App() {
  // Reactive Database State
  const [store, setStore] = useState(db.get());

  useEffect(() => {
    // Subscribe to any changes from Services / Controllers
    const unsubscribe = db.subscribe(() => {
      setStore({ ...db.get() });
    });
    return () => unsubscribe();
  }, []);

  // UI Navigation State
  const [activeSection, setActiveSection] = useState<NavSection>('inicio');
  const [isSidebarOpen, setIsSidebarOpen] = useState<boolean>(false);

  // Modals & Feedback State
  const [detalleSolicitud, setDetalleSolicitud] = useState<Solicitud | null>(null);
  const [solicitudARecolectar, setSolicitudARecolectar] = useState<Solicitud | null>(null);
  const [isRegistrarEntregaOpen, setIsRegistrarEntregaOpen] = useState<boolean>(false);
  const [isSimuladorCentroOpen, setIsSimuladorCentroOpen] = useState<boolean>(false);
  const [isCiudadanoModalOpen, setIsCiudadanoModalOpen] = useState<boolean>(false);
  const [toast, setToast] = useState<ToastData | null>(null);

  const showToast = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
    setToast({
      id: String(Date.now()),
      message,
      type,
    });
  };

  // ----------------------------------------------------
  // Controller Action Handlers (MVC Flow)
  // ----------------------------------------------------
  const handleAceptarSolicitud = (id: string) => {
    const res = SolicitudController.aceptarSolicitud(id, store.reciclador.id);
    if (res.success) {
      showToast(res.message, 'success');
    } else {
      showToast(res.message, 'error');
    }
  };

  const handleRechazarSolicitud = (id: string) => {
    const res = SolicitudController.rechazarSolicitud(id);
    showToast(res.message, 'info');
  };

  const handleConfirmarRecoleccion = (datos: {
    solicitudId: string;
    cantidadEstimadaKg: number;
    observaciones: string;
    fotografia?: string;
  }) => {
    const res = RecoleccionController.registrarRecoleccion(datos);
    if (res.success) {
      showToast(res.message, 'success');
    } else {
      showToast(res.message, 'error');
    }
  };

  const handleConfirmarEntrega = (datos: {
    centroAcopioId: string;
    materiales: { material: TipoMaterial; cantidadEstimadaKg: number }[];
    observaciones?: string;
  }) => {
    const res = EntregaController.registrarEntrega(datos);
    if (res.success) {
      showToast(res.message, 'success');
      // Suggest opening the scale validator
      setTimeout(() => {
        setIsSimuladorCentroOpen(true);
      }, 700);
    } else {
      showToast(res.message, 'error');
    }
  };

  const handleValidarEntregaEnCentro = (datos: {
    entregaId: string;
    desglosePesaje: { material: TipoMaterial; kgReal: number }[];
    operadorNombre: string;
    observacionesTecnicas: string;
  }) => {
    const res = CentroAcopioController.validarEntrega(datos);
    if (res.success) {
      showToast(res.message, 'success');
    } else {
      showToast(res.message, 'error');
    }
  };

  const handleCanjearBeneficio = (beneficioId: string) => {
    const res = BeneficioController.canjear(beneficioId);
    if (res.success) {
      showToast(res.message, 'success');
    } else {
      showToast(res.message, 'error');
    }
  };

  const handleCrearSolicitudCiudadano = (datos: Parameters<typeof SolicitudController.crearSolicitudCiudadano>[0]) => {
    const res = SolicitudController.crearSolicitudCiudadano(datos);
    if (res.success) {
      showToast(res.message, 'success');
    } else {
      showToast(res.message, 'error');
    }
  };

  const handleOptimizarRuta = () => {
    const res = RutaController.optimizarRuta();
    showToast(res.message, 'success');
  };

  const handleActualizarPerfil = (datos: {
    telefono?: string;
    vehiculo?: string;
    experiencia?: string;
    zonaTrabajo?: string;
  }) => {
    const res = AuthController.actualizarPerfil(datos);
    showToast(res.message, 'success');
  };

  // Computed data
  const solicitudesDisponibles = store.solicitudes.filter((s) => s.estado === 'Disponible');
  const entregasPendientes = store.entregas.filter((e) => e.estado === 'Pendiente');
  const rutaCalculada = RutaController.obtenerRutaHoy();

  return (
    <div className="min-h-screen bg-[#FAFCFA] text-[#1E2922] flex">
      {/* 1. Sidebar de 250px (Fijo en Desktop, Drawer en Móvil) */}
      <Sidebar
        activeSection={activeSection}
        onSelectSection={(sec) => setActiveSection(sec)}
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        rolActivo={store.rolActivo}
        onCambiarRol={(rol) => AuthController.cambiarRol(rol)}
        onAbrirSimuladorCentro={() => setIsSimuladorCentroOpen(true)}
        onAbrirModalCiudadano={() => setIsCiudadanoModalOpen(true)}
        ecopuntos={store.reciclador.ecopuntos}
        solicitudesCount={solicitudesDisponibles.length}
        entregasPendientesCount={entregasPendientes.length}
      />

      {/* 2. Main Shell Layout */}
      <div className="flex-1 lg:pl-64 flex flex-col min-h-screen">
        {/* Navbar superior */}
        <Navbar
          onToggleSidebar={() => setIsSidebarOpen(!isSidebarOpen)}
          reciclador={store.reciclador}
          onNavigate={(sec) => setActiveSection(sec)}
          onAbrirSimuladorCentro={() => setIsSimuladorCentroOpen(true)}
          onAbrirModalCiudadano={() => setIsCiudadanoModalOpen(true)}
        />

        {/* Content Area */}
        <main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
          {activeSection === 'inicio' && (
            <DashboardView
              reciclador={store.reciclador}
              solicitudes={store.solicitudes}
              onAceptarSolicitud={handleAceptarSolicitud}
              onVerDetalle={(sol) => setDetalleSolicitud(sol)}
              onNavigate={(sec) => setActiveSection(sec)}
              onAbrirSimuladorCentro={() => setIsSimuladorCentroOpen(true)}
            />
          )}

          {activeSection === 'solicitudes' && (
            <SolicitudesView
              solicitudes={store.solicitudes}
              onAceptarSolicitud={handleAceptarSolicitud}
              onVerDetalle={(sol) => setDetalleSolicitud(sol)}
            />
          )}

          {activeSection === 'mapa' && (
            <MapaView
              solicitudes={store.solicitudes}
              centrosAcopio={store.centrosAcopio}
              onAceptarSolicitud={handleAceptarSolicitud}
              onVerDetalle={(sol) => setDetalleSolicitud(sol)}
            />
          )}

          {activeSection === 'recolecciones' && (
            <MisRecoleccionesView
              solicitudes={store.solicitudes}
              recolecciones={store.recolecciones}
              onIniciarRecoleccion={(sol) => setSolicitudARecolectar(sol)}
              onNavigate={(sec) => setActiveSection(sec)}
              onAbrirRegistrarEntrega={() => setIsRegistrarEntregaOpen(true)}
            />
          )}

          {activeSection === 'entregas' && (
            <EntregasView
              entregas={store.entregas}
              centrosAcopio={store.centrosAcopio}
              onAbrirRegistrarEntrega={() => setIsRegistrarEntregaOpen(true)}
              onAbrirSimuladorCentro={() => setIsSimuladorCentroOpen(true)}
            />
          )}

          {activeSection === 'ecopuntos' && (
            <EcopuntosView
              reciclador={store.reciclador}
              movimientos={store.movimientosEcopuntos}
              onNavigate={(sec) => setActiveSection(sec)}
              onAbrirSimuladorCentro={() => setIsSimuladorCentroOpen(true)}
            />
          )}

          {activeSection === 'beneficios' && (
            <BeneficiosView
              beneficios={store.beneficios}
              canjes={store.canjes}
              reciclador={store.reciclador}
              onCanjear={handleCanjearBeneficio}
            />
          )}

          {activeSection === 'estadisticas' && (
            <EstadisticasView
              reciclador={store.reciclador}
              estadisticasZona={store.estadisticasZona}
            />
          )}

          {activeSection === 'historial' && (
            <HistorialView
              recolecciones={store.recolecciones}
              entregas={store.entregas}
              movimientos={store.movimientosEcopuntos}
              canjes={store.canjes}
            />
          )}

          {activeSection === 'perfil' && (
            <PerfilView
              reciclador={store.reciclador}
              onActualizarPerfil={handleActualizarPerfil}
            />
          )}

          {activeSection === 'configuracion' && <ConfiguracionView />}

          {activeSection === 'ayuda' && <AyudaView />}
        </main>

        {/* Global Footer note */}
        <footer className="py-4 px-6 border-t border-[#E2E8F0] text-center text-xs text-slate-400 bg-white">
          Ecopoints © 2026 — Plataforma de Economía Circular e Inclusión para Recicladores de Suba.
        </footer>
      </div>

      {/* 3. MODALES GLOBALES INTERACTIVOS */}
      {/* Detalle Solicitud */}
      <DetalleSolicitudModal
        solicitud={detalleSolicitud}
        isOpen={Boolean(detalleSolicitud)}
        onClose={() => setDetalleSolicitud(null)}
        onAceptar={(id) => handleAceptarSolicitud(id)}
        onRechazar={(id) => handleRechazarSolicitud(id)}
      />

      {/* Registrar Recolección */}
      <RegistrarRecoleccionModal
        solicitud={solicitudARecolectar}
        isOpen={Boolean(solicitudARecolectar)}
        onClose={() => setSolicitudARecolectar(null)}
        onConfirmar={handleConfirmarRecoleccion}
      />

      {/* Registrar Entrega a Centro de Acopio */}
      <RegistrarEntregaModal
        centrosAcopio={store.centrosAcopio}
        isOpen={isRegistrarEntregaOpen}
        onClose={() => setIsRegistrarEntregaOpen(false)}
        onConfirmar={handleConfirmarEntrega}
      />

      {/* Báscula y Validación en Centro de Acopio */}
      <CentroAcopioModal
        isOpen={isSimuladorCentroOpen}
        onClose={() => setIsSimuladorCentroOpen(false)}
        entregasPendientes={entregasPendientes}
        onValidarEntrega={handleValidarEntregaEnCentro}
      />

      {/* Publicar Solicitud Ciudadano */}
      <CiudadanoCrearSolicitudModal
        isOpen={isCiudadanoModalOpen}
        onClose={() => setIsCiudadanoModalOpen(false)}
        onCrear={handleCrearSolicitudCiudadano}
      />

      {/* Toast Notifier */}
      <Toast toast={toast} onClose={() => setToast(null)} />
    </div>
  );
}
