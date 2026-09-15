import React, { useState } from 'react';
import { TipoMaterial, PrioridadSolicitud } from '@/models/types';
import { Modal } from '../components/Modal';
import { PlusIcon, CheckCircleIcon } from '../components/Icons';

interface CiudadanoCrearSolicitudModalProps {
  isOpen: boolean;
  onClose: () => void;
  onCrear: (datos: {
    ciudadanoNombre: string;
    material: TipoMaterial;
    cantidadAproxKg: number;
    ubicacion: string;
    direccion: string;
    horarioDisponible: string;
    observaciones: string;
    prioridad: PrioridadSolicitud;
  }) => void;
}

export const CiudadanoCrearSolicitudModal: React.FC<CiudadanoCrearSolicitudModalProps> = ({
  isOpen,
  onClose,
  onCrear,
}) => {
  const [nombre, setNombre] = useState<string>('Familia Gómez Suba');
  const [material, setMaterial] = useState<TipoMaterial>('Cartón');
  const [cantidad, setCantidad] = useState<number>(25);
  const [ubicacion, setUbicacion] = useState<string>('Suba Centro');
  const [direccion, setDireccion] = useState<string>('Carrera 91 #147-15, Edificio Los Pinos');
  const [horario, setHorario] = useState<string>('2:00 p.m. – 5:00 p.m.');
  const [prioridad, setPrioridad] = useState<PrioridadSolicitud>('Media');
  const [observaciones, setObservaciones] = useState<string>(
    'Material separado y listo para recoger en recepción. Cajas dobladas y atadas.'
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onCrear({
      ciudadanoNombre: nombre,
      material,
      cantidadAproxKg: Number(cantidad),
      ubicacion,
      direccion,
      horarioDisponible: horario,
      observaciones,
      prioridad,
    });
    onClose();
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title="Publicar Solicitud de Recolección (Ciudadano)"
      subtitle="Los recicladores de tu cuadrante en Suba podrán verla y aceptarla de inmediato"
      maxWidth="max-w-lg"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Nombre del ciudadano o comercio *
          </label>
          <input
            type="text"
            required
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
          />
        </div>

        <div className="grid grid-cols-2 gap-3">
          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
              Material a entregar *
            </label>
            <select
              value={material}
              onChange={(e) => setMaterial(e.target.value as TipoMaterial)}
              className="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800"
            >
              <option value="Cartón">Cartón corrugado</option>
              <option value="Plástico">Plástico PET / PEAD</option>
              <option value="Vidrio">Vidrio por color</option>
              <option value="Metales">Metales / Latas</option>
              <option value="Papel">Papel blanco / Archivo</option>
              <option value="Otros">Otros limpios</option>
            </select>
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
              Cantidad aproximada (kg) *
            </label>
            <input
              type="number"
              min="1"
              step="1"
              required
              value={cantidad}
              onChange={(e) => setCantidad(Number(e.target.value))}
              className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800"
            />
          </div>
        </div>

        <div className="grid grid-cols-2 gap-3">
          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
              Barrio o Sector *
            </label>
            <input
              type="text"
              required
              value={ubicacion}
              onChange={(e) => setUbicacion(e.target.value)}
              className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
              Prioridad
            </label>
            <select
              value={prioridad}
              onChange={(e) => setPrioridad(e.target.value as PrioridadSolicitud)}
              className="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800"
            >
              <option value="Alta">Alta (Recoger hoy)</option>
              <option value="Media">Media (Normal)</option>
              <option value="Baja">Baja (Flexible)</option>
            </select>
          </div>
        </div>

        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Dirección exacta de recogida *
          </label>
          <input
            type="text"
            required
            value={direccion}
            onChange={(e) => setDireccion(e.target.value)}
            className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
          />
        </div>

        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Horario de disponibilidad *
          </label>
          <input
            type="text"
            required
            value={horario}
            onChange={(e) => setHorario(e.target.value)}
            className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800"
          />
        </div>

        <div>
          <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Observaciones e instrucciones de entrega
          </label>
          <textarea
            rows={2}
            value={observaciones}
            onChange={(e) => setObservaciones(e.target.value)}
            className="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800"
          />
        </div>

        <div className="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
          >
            Cancelar
          </button>
          <button
            type="submit"
            className="px-5 py-2.5 bg-[#087A3D] hover:bg-[#065C2D] text-white text-xs font-black rounded-xl shadow-md flex items-center gap-1.5"
          >
            <CheckCircleIcon size={16} />
            <span>Publicar Solicitud de Recolección</span>
          </button>
        </div>
      </form>
    </Modal>
  );
};
