-- ============================================================================
-- MIGRACIÓN SQL: FASE 2 — SOLICITUDES DE RECOLECCIÓN
-- ============================================================================
-- Actualización del catálogo de estados para incluir 'Pendiente' como estado
-- inicial por defecto, manteniendo compatibilidad con todas las vistas y modelos.

ALTER TABLE `solicitudes` 
MODIFY COLUMN `estado` ENUM(
    'Pendiente',
    'Disponible',
    'Aceptada',
    'En proceso',
    'Recolectada',
    'Recogida',
    'Entregada',
    'Validada',
    'Completada',
    'Cancelada'
) NOT NULL DEFAULT 'Pendiente';

-- Índices para optimización de consultas por ciudadano y estado
ALTER TABLE `solicitudes` ADD INDEX `idx_solicitudes_ciudadano` (`id_ciudadano`);
ALTER TABLE `solicitudes` ADD INDEX `idx_solicitudes_estado` (`estado`);
ALTER TABLE `solicitudes` ADD INDEX `idx_solicitudes_fecha` (`fecha_disponibilidad`);
