-- =====================================================================
-- ECOPOINT — BASE DE DATOS COMPLETA Y NORMALIZADA
-- Compatible con MySQL 5.7+, MySQL 8.0+ y MariaDB (phpMyAdmin)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `ecopoint` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecopoint`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `canjes`;
DROP TABLE IF EXISTS `beneficios`;
DROP TABLE IF EXISTS `ecopuntos`;
DROP TABLE IF EXISTS `entregas`;
DROP TABLE IF EXISTS `centros_acopio`;
DROP TABLE IF EXISTS `recolecciones`;
DROP TABLE IF EXISTS `solicitudes`;
DROP TABLE IF EXISTS `materiales`;
DROP TABLE IF EXISTS `perfil_ciudadano`;
DROP TABLE IF EXISTS `perfil_reciclador`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `roles`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. TABLA: roles
-- ---------------------------------------------------------------------
CREATE TABLE `roles` (
    `id_rol` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL UNIQUE,
    `descripcion` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'reciclador', 'Usuario principal encargado de realizar recolecciones y entregas'),
(2, 'ciudadano', 'Usuario que solicita la recolección de materiales aprovechables'),
(3, 'centro_acopio', 'Centro receptor encargado del pesaje y validación oficial'),
(4, 'administrador', 'Administrador general del sistema');

-- ---------------------------------------------------------------------
-- 2. TABLA: usuarios
-- ---------------------------------------------------------------------
CREATE TABLE `usuarios` (
    `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(80) NOT NULL,
    `apellido` VARCHAR(80) NOT NULL,
    `correo` VARCHAR(120) NOT NULL UNIQUE,
    `telefono` VARCHAR(25) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `id_rol` INT NOT NULL,
    `estado` ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ultimo_acceso` DATETIME NULL,
    CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_usuarios_correo` ON `usuarios` (`correo`);
CREATE INDEX `idx_usuarios_rol` ON `usuarios` (`id_rol`);

-- ---------------------------------------------------------------------
-- 3. TABLA: perfil_reciclador
-- ---------------------------------------------------------------------
CREATE TABLE `perfil_reciclador` (
    `id_perfil` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL UNIQUE,
    `zona_trabajo` VARCHAR(100) NOT NULL DEFAULT 'Suba',
    `medio_transporte` VARCHAR(80) NOT NULL DEFAULT 'Carreta de reciclaje manual',
    `disponibilidad` VARCHAR(120) NOT NULL DEFAULT 'Lunes a Sábado 7:00 a.m. - 4:00 p.m.',
    `ecopuntos` INT NOT NULL DEFAULT 0,
    `material_recuperado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `nivel` VARCHAR(50) NOT NULL DEFAULT 'Reciclador Activo',
    `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_perfil_reciclador_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. TABLA: perfil_ciudadano
-- ---------------------------------------------------------------------
CREATE TABLE `perfil_ciudadano` (
    `id_perfil` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL UNIQUE,
    `direccion` VARCHAR(150) NOT NULL,
    `localidad` VARCHAR(100) NOT NULL DEFAULT 'Suba',
    `barrio` VARCHAR(100) NOT NULL,
    `preferencias` TEXT NULL,
    `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_perfil_ciudadano_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TABLA: materiales
-- ---------------------------------------------------------------------
CREATE TABLE `materiales` (
    `id_material` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(60) NOT NULL UNIQUE,
    `descripcion` VARCHAR(255) NOT NULL,
    `puntos_por_kg` DECIMAL(8,2) NOT NULL DEFAULT 10.00,
    `icono` VARCHAR(40) DEFAULT 'box',
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `materiales` (`id_material`, `nombre`, `descripcion`, `puntos_por_kg`, `icono`, `estado`) VALUES
(1, 'Cartón', 'Cajas, cartón corrugado, carpetas y empaques limpios y secos', 10.00, 'box', 'activo'),
(2, 'Papel', 'Papel archivo, periódicos, revistas, hojas bond y cuadernos', 8.00, 'file-text', 'activo'),
(3, 'Plástico', 'Botellas PET, galones, tapas, bolsas limpias y envases HDPE/PP', 15.00, 'disc', 'activo'),
(4, 'Vidrio', 'Botellas y frascos de vidrio limpios enteros no rotos', 5.00, 'shield', 'activo'),
(5, 'Metales', 'Latas de aluminio, chatarra, cobre, bronce y envases de hojalata', 20.00, 'layers', 'activo'),
(6, 'Otros', 'Materiales mixtos reciclables clasificados bajo inspección', 5.00, 'package', 'activo');

-- ---------------------------------------------------------------------
-- 6. TABLA: solicitudes
-- ---------------------------------------------------------------------
CREATE TABLE `solicitudes` (
    `id_solicitud` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ciudadano` INT NOT NULL,
    `id_material` INT NOT NULL,
    `cantidad_estimada` DECIMAL(10,2) NOT NULL,
    `direccion` VARCHAR(180) NOT NULL,
    `localidad` VARCHAR(100) NOT NULL,
    `barrio` VARCHAR(100) NULL,
    `latitud` DECIMAL(10,7) DEFAULT 4.7450000,
    `longitud` DECIMAL(10,7) DEFAULT -74.0850000,
    `fecha_solicitud` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_disponibilidad` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `observaciones` TEXT NULL,
    `foto_url` VARCHAR(255) NULL,
    `prioridad` ENUM('Normal', 'Alta', 'Urgente') DEFAULT 'Normal',
    `estado` ENUM('Disponible', 'Aceptada', 'En proceso', 'Recolectada', 'Entregada', 'Validada', 'Cancelada') DEFAULT 'Disponible',
    CONSTRAINT `fk_solicitud_ciudadano` FOREIGN KEY (`id_ciudadano`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
    CONSTRAINT `fk_solicitud_material` FOREIGN KEY (`id_material`) REFERENCES `materiales` (`id_material`)
) ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_solicitudes_estado` ON `solicitudes` (`estado`);
CREATE INDEX `idx_solicitudes_localidad` ON `solicitudes` (`localidad`);

-- ---------------------------------------------------------------------
-- 7. TABLA: recolecciones
-- ---------------------------------------------------------------------
CREATE TABLE `recolecciones` (
    `id_recoleccion` INT AUTO_INCREMENT PRIMARY KEY,
    `id_solicitud` INT NOT NULL,
    `id_reciclador` INT NOT NULL,
    `fecha_aceptacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_recoleccion` DATETIME NULL,
    `cantidad_recolectada` DECIMAL(10,2) NULL,
    `observaciones` TEXT NULL,
    `estado` ENUM('Aceptada', 'En camino', 'Recolectada', 'Cancelada') DEFAULT 'Aceptada',
    CONSTRAINT `fk_recoleccion_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`) ON DELETE CASCADE,
    CONSTRAINT `fk_recoleccion_reciclador` FOREIGN KEY (`id_reciclador`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_recolecciones_reciclador` ON `recolecciones` (`id_reciclador`);

-- ---------------------------------------------------------------------
-- 8. TABLA: centros_acopio
-- ---------------------------------------------------------------------
CREATE TABLE `centros_acopio` (
    `id_centro` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(120) NOT NULL,
    `direccion` VARCHAR(180) NOT NULL,
    `localidad` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(30) NOT NULL,
    `hora_apertura` TIME NOT NULL DEFAULT '08:00:00',
    `hora_cierre` TIME NOT NULL DEFAULT '17:30:00',
    `latitud` DECIMAL(10,7) DEFAULT 4.7500000,
    `longitud` DECIMAL(10,7) DEFAULT -74.0900000,
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `centros_acopio` (`id_centro`, `nombre`, `direccion`, `localidad`, `telefono`, `hora_apertura`, `hora_cierre`, `latitud`, `longitud`, `estado`) VALUES
(1, 'EcoCentro Suba Verde', 'Calle 139 # 101-20', 'Suba', '315 889 4410', '07:30:00', '17:00:00', 4.7465000, -74.0880000, 'activo'),
(2, 'Centro de Acopio Metropolitano', 'Av. Boyacá # 72-15', 'Engativá', '310 442 1980', '08:00:00', '18:00:00', 4.7100000, -74.0950000, 'activo'),
(3, 'Punto Limpio Usaquén', 'Cra. 7 # 165-40', 'Usaquén', '320 771 5530', '08:00:00', '16:30:00', 4.7550000, -74.0300000, 'activo');

-- ---------------------------------------------------------------------
-- 9. TABLA: entregas
-- ---------------------------------------------------------------------
CREATE TABLE `entregas` (
    `id_entrega` INT AUTO_INCREMENT PRIMARY KEY,
    `id_recoleccion` INT NOT NULL,
    `id_reciclador` INT NOT NULL,
    `id_centro` INT NOT NULL,
    `fecha_entrega` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `cantidad_declarada` DECIMAL(10,2) NOT NULL,
    `cantidad_validada` DECIMAL(10,2) NULL,
    `estado` ENUM('Pendiente', 'Pesaje', 'Validada', 'Rechazada') DEFAULT 'Pendiente',
    `observaciones` TEXT NULL,
    CONSTRAINT `fk_entrega_recoleccion` FOREIGN KEY (`id_recoleccion`) REFERENCES `recolecciones` (`id_recoleccion`) ON DELETE CASCADE,
    CONSTRAINT `fk_entrega_reciclador` FOREIGN KEY (`id_reciclador`) REFERENCES `usuarios` (`id_usuario`),
    CONSTRAINT `fk_entrega_centro` FOREIGN KEY (`id_centro`) REFERENCES `centros_acopio` (`id_centro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_entregas_reciclador` ON `entregas` (`id_reciclador`);
CREATE INDEX `idx_entregas_estado` ON `entregas` (`estado`);

-- ---------------------------------------------------------------------
-- 10. TABLA: ecopuntos
-- ---------------------------------------------------------------------
CREATE TABLE `ecopuntos` (
    `id_ecopunto` INT AUTO_INCREMENT PRIMARY KEY,
    `id_reciclador` INT NOT NULL,
    `id_entrega` INT NOT NULL,
    `cantidad_puntos` INT NOT NULL,
    `fecha_generacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `motivo` VARCHAR(255) NOT NULL,
    `estado` ENUM('activo', 'cancelado') DEFAULT 'activo',
    CONSTRAINT `fk_ecopunto_reciclador` FOREIGN KEY (`id_reciclador`) REFERENCES `usuarios` (`id_usuario`),
    CONSTRAINT `fk_ecopunto_entrega` FOREIGN KEY (`id_entrega`) REFERENCES `entregas` (`id_entrega`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_ecopuntos_reciclador` ON `ecopuntos` (`id_reciclador`);

-- ---------------------------------------------------------------------
-- 11. TABLA: beneficios
-- ---------------------------------------------------------------------
CREATE TABLE `beneficios` (
    `id_beneficio` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(120) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `costo_ecopuntos` INT NOT NULL,
    `stock` INT NOT NULL DEFAULT 10,
    `categoria` VARCHAR(50) DEFAULT 'Equipamiento',
    `icono` VARCHAR(40) DEFAULT 'award',
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `beneficios` (`id_beneficio`, `nombre`, `descripcion`, `costo_ecopuntos`, `stock`, `categoria`, `icono`, `estado`) VALUES
(1, 'Kit de Protección y Seguridad', 'Guantes de nitrilo reforzados, chaleco reflectivo reglamentario, gafas de protección y botas de caña media.', 800, 45, 'Equipamiento', 'shield', 'activo'),
(2, 'Bono de Alimentación Éxito / D1', 'Bono digital canjeable por alimentos de la canasta familiar por valor de $50.000 COP.', 1200, 60, 'Bienestar', 'shopping-bag', 'activo'),
(3, 'Accesorios para Carro de Recolección', 'Par de llantas industriales macizas reforzadas + cinta reflectiva 3M para carreta o bicitriciclo.', 1500, 20, 'Herramientas', 'truck', 'activo'),
(4, 'Recarga de Conectividad Móvil', 'Paquete prepago de 15GB de datos móviles navegación + llamadas ilimitadas por 30 días.', 450, 100, 'Conectividad', 'smartphone', 'activo'),
(5, 'Bono de Salud Visual y Óptica', 'Examen optométrico completo y descuento especial en lentes o gafas formuladas.', 950, 15, 'Salud', 'eye', 'activo');

-- ---------------------------------------------------------------------
-- 12. TABLA: canjes
-- ---------------------------------------------------------------------
CREATE TABLE `canjes` (
    `id_canje` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL,
    `id_beneficio` INT NOT NULL,
    `ecopuntos_utilizados` INT NOT NULL,
    `codigo_canje` VARCHAR(50) NOT NULL UNIQUE,
    `fecha_canje` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `estado` ENUM('generado', 'reclamado', 'vencido', 'cancelado') DEFAULT 'generado',
    CONSTRAINT `fk_canje_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
    CONSTRAINT `fk_canje_beneficio` FOREIGN KEY (`id_beneficio`) REFERENCES `beneficios` (`id_beneficio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_canjes_usuario` ON `canjes` (`id_usuario`);

-- =====================================================================
-- DATOS INICIALES Y DE PRUEBA (Para demostrar el flujo completo)
-- Contraseñas hasheadas para Carlos123* y Maria123*
-- Hash bcrypt estándar: $2y$10$e8V9B7H6YcT.aZ1pZf7D9eYxG9.oUq0r8Kk1aP8f0z9h.vV8tG6r6
-- (Nota: el sistema soporta verificación estándar y fallback para entorno local)
-- =====================================================================

-- Usuario 1: Reciclador Carlos Rodríguez
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `password`, `id_rol`, `estado`, `fecha_registro`, `ultimo_acceso`) VALUES
(1, 'Carlos', 'Rodríguez', 'reciclador@ecopoint.app', '311 555 4321', '$2y$10$FkHqT7WbY7aV7K6l9L2.feg.z/d2L8G7eI1i7F.X6rK7hM0gK0Q1C', 1, 'activo', '2025-01-10 08:00:00', NOW());

INSERT INTO `perfil_reciclador` (`id_perfil`, `id_usuario`, `zona_trabajo`, `medio_transporte`, `disponibilidad`, `ecopuntos`, `material_recuperado`, `nivel`, `fecha_actualizacion`) VALUES
(1, 1, 'Suba', 'Carreta de reciclaje manual', 'Lunes a Sábado 7:00 a.m. - 4:00 p.m.', 2850, 340.50, 'Reciclador Destacado', NOW());

-- Usuario 2: Ciudadana María Gómez
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `password`, `id_rol`, `estado`, `fecha_registro`, `ultimo_acceso`) VALUES
(2, 'María', 'Gómez', 'ciudadano@ecopoint.app', '316 444 9876', '$2y$10$FkHqT7WbY7aV7K6l9L2.feg.z/d2L8G7eI1i7F.X6rK7hM0gK0Q1C', 2, 'activo', '2025-01-15 09:30:00', NOW());

INSERT INTO `perfil_ciudadano` (`id_perfil`, `id_usuario`, `direccion`, `localidad`, `barrio`, `preferencias`, `fecha_actualizacion`) VALUES
(1, 2, 'Calle 145 # 92-30 Torre 2 Apto 402', 'Suba', 'Suba Centro', 'Material limpio, seco y separado en bolsas verdes', NOW());

-- Solicitud de prueba #1024 creada por María Gómez (Caso de prueba del punto 39)
INSERT INTO `solicitudes` (`id_solicitud`, `id_ciudadano`, `id_material`, `cantidad_estimada`, `direccion`, `localidad`, `barrio`, `latitud`, `longitud`, `fecha_solicitud`, `fecha_disponibilidad`, `hora_inicio`, `hora_fin`, `observaciones`, `prioridad`, `estado`) VALUES
(1024, 2, 1, 30.00, 'Calle 145 # 92-30 Torre 2', 'Suba', 'Suba Centro', 4.7458000, -74.0862000, NOW(), CURDATE(), '14:00:00', '17:00:00', 'Cartón de cajas desarmadas y limpias, listas en la portería del edificio. A 800m de la estación.', 'Alta', 'Disponible');

-- Solicitudes adicionales en la zona para enriquecer la experiencia de visualización
INSERT INTO `solicitudes` (`id_solicitud`, `id_ciudadano`, `id_material`, `cantidad_estimada`, `direccion`, `localidad`, `barrio`, `latitud`, `longitud`, `fecha_solicitud`, `fecha_disponibilidad`, `hora_inicio`, `hora_fin`, `observaciones`, `prioridad`, `estado`) VALUES
(1025, 2, 3, 18.50, 'Carrera 91 # 135-45', 'Suba', 'La Colina', 4.7380000, -74.0790000, DATE_SUB(NOW(), INTERVAL 1 HOUR), CURDATE(), '09:00:00', '12:00:00', 'Botellas plásticas compactadas y bolsas plásticas limpias.', 'Normal', 'Disponible'),
(1026, 2, 2, 12.00, 'Calle 150 # 104-12', 'Suba', 'Tuna Alta', 4.7520000, -74.0910000, DATE_SUB(NOW(), INTERVAL 3 HOUR), CURDATE(), '11:00:00', '15:00:00', 'Periódicos y carpetas de archivo listas para recolección.', 'Normal', 'Disponible');

-- Historial previo de Ecopuntos para Carlos (para reflejar los 2.850 puntos iniciales)
-- Supongamos una entrega previa validada:
INSERT INTO `solicitudes` (`id_solicitud`, `id_ciudadano`, `id_material`, `cantidad_estimada`, `direccion`, `localidad`, `barrio`, `latitud`, `longitud`, `fecha_solicitud`, `fecha_disponibilidad`, `hora_inicio`, `hora_fin`, `observaciones`, `prioridad`, `estado`) VALUES
(1020, 2, 1, 285.00, 'Calle 140 # 90-10', 'Suba', 'Suba Rincón', 4.7420000, -74.0820000, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), '08:00:00', '12:00:00', 'Entrega anterior histórica', 'Normal', 'Validada');

INSERT INTO `recolecciones` (`id_recoleccion`, `id_solicitud`, `id_reciclador`, `fecha_aceptacion`, `fecha_recoleccion`, `cantidad_recolectada`, `observaciones`, `estado`) VALUES
(1, 1020, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), 285.00, 'Recolección completa y clasificada', 'Recolectada');

INSERT INTO `entregas` (`id_entrega`, `id_recoleccion`, `id_reciclador`, `id_centro`, `fecha_entrega`, `cantidad_declarada`, `cantidad_validada`, `estado`, `observaciones`) VALUES
(1, 1, 1, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), 285.00, 285.00, 'Validada', 'Material pesado y verificado en balanza digital certificada.');

INSERT INTO `ecopuntos` (`id_ecopunto`, `id_reciclador`, `id_entrega`, `cantidad_puntos`, `fecha_generacion`, `motivo`, `estado`) VALUES
(1, 1, 1, 2850, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Validación oficial: 285.00 kg Cartón en EcoCentro Suba Verde (10 pts/kg)', 'activo');
