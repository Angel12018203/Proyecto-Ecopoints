-- =====================================================================
-- ECOPOINT — BASE DE DATOS COMPLETA Y NORMALIZADA
-- Compatible con MySQL 5.7+, MySQL 8.0+ y MariaDB (phpMyAdmin / XAMPP)
-- Incluye: Catálogo oficial de 20 localidades de Bogotá, roles,
-- perfiles normalizados (Reciclador, Ciudadano, Centro de Acopio),
-- materiales, solicitudes, recolecciones, entregas, ecopuntos y beneficios.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `ecopoint` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecopoint`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `canjes`;
DROP TABLE IF EXISTS `beneficios`;
DROP TABLE IF EXISTS `ecopuntos`;
DROP TABLE IF EXISTS `entregas`;
DROP TABLE IF EXISTS `recolecciones`;
DROP TABLE IF EXISTS `solicitudes`;
DROP TABLE IF EXISTS `centros_acopio`;
DROP TABLE IF EXISTS `materiales`;
DROP TABLE IF EXISTS `perfil_ciudadano`;
DROP TABLE IF EXISTS `perfil_reciclador`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `localidades`;
DROP TABLE IF EXISTS `roles`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. TABLA: roles
-- Define los tipos de cuenta en el sistema
-- ---------------------------------------------------------------------
CREATE TABLE `roles` (
    `id_rol` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL UNIQUE,
    `descripcion` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'reciclador', 'Actor principal: recolecta, transporta y entrega materiales'),
(2, 'ciudadano', 'Generador: solicita recolección de materiales reciclables'),
(3, 'centro_acopio', 'Receptor oficial: pesa, valida entregas y genera Ecopoints'),
(4, 'administrador', 'Administrador general del sistema');

-- ---------------------------------------------------------------------
-- 2. TABLA: localidades
-- Catálogo maestro normalizado de las 20 localidades de Bogotá D.C.
-- ---------------------------------------------------------------------
CREATE TABLE `localidades` (
    `id_localidad` INT AUTO_INCREMENT PRIMARY KEY,
    `numero` INT NOT NULL UNIQUE,
    `nombre` VARCHAR(60) NOT NULL UNIQUE,
    `activo` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `localidades` (`id_localidad`, `numero`, `nombre`) VALUES
(1, 1, 'Usaquén'),
(2, 2, 'Chapinero'),
(3, 3, 'Santa Fe'),
(4, 4, 'San Cristóbal'),
(5, 5, 'Usme'),
(6, 6, 'Tunjuelito'),
(7, 7, 'Bosa'),
(8, 8, 'Kennedy'),
(9, 9, 'Fontibón'),
(10, 10, 'Engativá'),
(11, 11, 'Suba'),
(12, 12, 'Barrios Unidos'),
(13, 13, 'Teusaquillo'),
(14, 14, 'Los Mártires'),
(15, 15, 'Antonio Nariño'),
(16, 16, 'Puente Aranda'),
(17, 17, 'La Candelaria'),
(18, 18, 'Rafael Uribe Uribe'),
(19, 19, 'Ciudad Bolívar'),
(20, 20, 'Sumapaz');

-- ---------------------------------------------------------------------
-- 3. TABLA: usuarios
-- Información básica de autenticación para todos los roles
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
-- 4. TABLA: perfil_reciclador
-- Información extendida para el reciclador (actor principal)
-- ---------------------------------------------------------------------
CREATE TABLE `perfil_reciclador` (
    `id_perfil` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL UNIQUE,
    `id_localidad` INT NOT NULL DEFAULT 11,
    `zona_trabajo` VARCHAR(100) NOT NULL DEFAULT 'Suba',
    `medio_transporte` VARCHAR(80) NOT NULL DEFAULT 'Bicicleta',
    `disponibilidad` VARCHAR(120) NOT NULL DEFAULT 'Lunes a Sábado 7:00 a.m. - 4:00 p.m.',
    `ecopuntos` INT NOT NULL DEFAULT 0,
    `material_recuperado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `nivel` VARCHAR(50) NOT NULL DEFAULT 'Reciclador Activo',
    `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_perfil_reciclador_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_perfil_reciclador_localidad` FOREIGN KEY (`id_localidad`) REFERENCES `localidades` (`id_localidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TABLA: perfil_ciudadano
-- Información extendida para el ciudadano solicitante
-- ---------------------------------------------------------------------
CREATE TABLE `perfil_ciudadano` (
    `id_perfil` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NOT NULL UNIQUE,
    `id_localidad` INT NOT NULL DEFAULT 11,
    `direccion` VARCHAR(150) NOT NULL,
    `barrio` VARCHAR(100) NOT NULL,
    `preferencias` TEXT NULL,
    `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_perfil_ciudadano_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_perfil_ciudadano_localidad` FOREIGN KEY (`id_localidad`) REFERENCES `localidades` (`id_localidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. TABLA: centros_acopio
-- Centros autorizados para recepción, pesaje y validación oficial
-- ---------------------------------------------------------------------
CREATE TABLE `centros_acopio` (
    `id_centro` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT NULL UNIQUE,
    `id_localidad` INT NOT NULL DEFAULT 11,
    `nombre` VARCHAR(120) NOT NULL,
    `responsable` VARCHAR(120) NOT NULL DEFAULT 'Encargado de Centro',
    `direccion` VARCHAR(180) NOT NULL,
    `telefono` VARCHAR(30) NOT NULL,
    `hora_apertura` TIME NOT NULL DEFAULT '08:00:00',
    `hora_cierre` TIME NOT NULL DEFAULT '17:30:00',
    `latitud` DECIMAL(10,7) DEFAULT 4.7500000,
    `longitud` DECIMAL(10,7) DEFAULT -74.0900000,
    `materiales_recibidos` VARCHAR(255) DEFAULT 'Cartón, Plástico, Vidrio, Metales',
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    CONSTRAINT `fk_centro_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_centro_localidad` FOREIGN KEY (`id_localidad`) REFERENCES `localidades` (`id_localidad`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. TABLA: materiales
-- Tipos de materiales reciclables y tasa de puntos por kg
-- ---------------------------------------------------------------------
CREATE TABLE `materiales` (
    `id_material` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(60) NOT NULL UNIQUE,
    `descripcion` VARCHAR(155) NOT NULL,
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
-- 8. TABLA: solicitudes
-- Solicitudes de recolección publicadas por ciudadanos
-- ---------------------------------------------------------------------
CREATE TABLE `solicitudes` (
    `id_solicitud` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ciudadano` INT NOT NULL,
    `id_material` INT NOT NULL,
    `id_localidad` INT NOT NULL DEFAULT 11,
    `cantidad_estimada` DECIMAL(10,2) NOT NULL,
    `direccion` VARCHAR(180) NOT NULL,
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
    CONSTRAINT `fk_solicitud_material` FOREIGN KEY (`id_material`) REFERENCES `materiales` (`id_material`),
    CONSTRAINT `fk_solicitud_localidad` FOREIGN KEY (`id_localidad`) REFERENCES `localidades` (`id_localidad`)
) ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_solicitudes_estado` ON `solicitudes` (`estado`);
CREATE INDEX `idx_solicitudes_localidad` ON `solicitudes` (`id_localidad`);

-- ---------------------------------------------------------------------
-- 9. TABLA: recolecciones
-- Solicitudes aceptadas y gestionadas por un reciclador
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
-- 10. TABLA: entregas
-- Material entregado por el reciclador en el centro de acopio
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
-- 11. TABLA: ecopuntos
-- Puntos acreditados ÚNICAMENTE tras una entrega validada
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
-- 12. TABLA: beneficios
-- Catálogo de beneficios canjeables con Ecopoints
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
-- 13. TABLA: canjes
-- Historial de canjes realizados por recicladores
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
-- DATOS INICIALES Y SEMILLAS DE PRUEBA
-- Contraseñas con BCRYPT verificado:
--   reciclador@ecopoint.app -> Carlos123*
--   ciudadano@ecopoint.app  -> Maria123*
--   centro@ecopoint.app     -> Centro123*
-- =====================================================================

-- 1. Usuario Reciclador: Carlos Rodríguez
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `password`, `id_rol`, `estado`, `fecha_registro`, `ultimo_acceso`) VALUES
(1, 'Carlos', 'Rodríguez', 'reciclador@ecopoint.app', '311 555 4321', '$2y$10$BVfE4Z4AmYcNbtRk7k8lOe.qkj0oOocEyNn/3Ux0hJL0CRcRRkIEK', 1, 'activo', '2025-01-10 08:00:00', NOW());

INSERT INTO `perfil_reciclador` (`id_perfil`, `id_usuario`, `id_localidad`, `zona_trabajo`, `medio_transporte`, `disponibilidad`, `ecopuntos`, `material_recuperado`, `nivel`, `fecha_actualizacion`) VALUES
(1, 1, 11, 'Suba, Bogotá', 'Bicicleta', 'Lunes a Sábado 7:00 a.m. - 4:00 p.m.', 2850, 1250.00, 'EcoReciclador', NOW());

-- 2. Usuario Ciudadana: María Gómez
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `password`, `id_rol`, `estado`, `fecha_registro`, `ultimo_acceso`) VALUES
(2, 'María', 'Gómez', 'ciudadano@ecopoint.app', '316 444 9876', '$2y$10$bLB4.DY4or62b3ANu0z.0ulYUgKb.3p/ox/zFGhuUZIMhhwNJt8eC', 2, 'activo', '2025-01-15 09:30:00', NOW());

INSERT INTO `perfil_ciudadano` (`id_perfil`, `id_usuario`, `id_localidad`, `direccion`, `barrio`, `preferencias`, `fecha_actualizacion`) VALUES
(1, 2, 11, 'Calle 145 # 92-30 Torre 2 Apto 402', 'Suba Centro', 'Material limpio, seco y separado en bolsas verdes', NOW());

-- 3. Usuario Centro de Acopio: Ana Morales
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `correo`, `telefono`, `password`, `id_rol`, `estado`, `fecha_registro`, `ultimo_acceso`) VALUES
(3, 'Ana', 'Morales', 'centro@ecopoint.app', '315 889 4410', '$2y$10$NSdx89SWDveIzhicLtCis.AAh002VoQumiPo6oh6ieZ6JHRWaMulW', 3, 'activo', '2025-01-20 10:00:00', NOW());

-- Centros de acopio físicos
INSERT INTO `centros_acopio` (`id_centro`, `id_usuario`, `id_localidad`, `nombre`, `responsable`, `direccion`, `telefono`, `hora_apertura`, `hora_cierre`, `latitud`, `longitud`, `materiales_recibidos`, `estado`) VALUES
(1, 3, 11, 'EcoCentro Suba Verde', 'Ana Morales', 'Calle 139 # 101-20', '315 889 4410', '07:30:00', '17:00:00', 4.7465000, -74.0880000, 'Cartón, Plástico, Vidrio, Metales', 'activo'),
(2, NULL, 10, 'Centro de Acopio Metropolitano', 'Luis García', 'Av. Boyacá # 72-15', '310 442 1980', '08:00:00', '18:00:00', 4.7100000, -74.0950000, 'Plástico, Papel, Cartón', 'activo'),
(3, NULL, 1, 'Punto Limpio Usaquén', 'Pedro Sanz', 'Cra. 7 # 165-40', '320 771 5530', '08:00:00', '16:30:00', 4.7550000, -74.0300000, 'Vidrio, Latas, Metales', 'activo');

-- Solicitudes de recolección de prueba
INSERT INTO `solicitudes` (`id_solicitud`, `id_ciudadano`, `id_material`, `id_localidad`, `cantidad_estimada`, `direccion`, `barrio`, `latitud`, `longitud`, `fecha_solicitud`, `fecha_disponibilidad`, `hora_inicio`, `hora_fin`, `observaciones`, `prioridad`, `estado`) VALUES
(1024, 2, 1, 11, 30.00, 'Calle 145 # 92-30 Torre 2', 'Suba Centro', 4.7458000, -74.0862000, NOW(), CURDATE(), '14:00:00', '17:00:00', 'Cartón de cajas desarmadas y limpias, listas en la portería del edificio. A 800m de la estación.', 'Alta', 'Disponible'),
(1025, 2, 3, 11, 15.00, 'Carrera 91 # 135-45', 'La Colina', 4.7380000, -74.0790000, DATE_SUB(NOW(), INTERVAL 1 HOUR), CURDATE(), '15:00:00', '18:00:00', 'Botellas plásticas compactadas y bolsas limpias.', 'Normal', 'Disponible'),
(1026, 2, 4, 1, 20.00, 'Calle 150 # 104-12', 'Tuna Alta', 4.7520000, -74.0910000, DATE_SUB(NOW(), INTERVAL 3 HOUR), CURDATE(), '09:00:00', '12:00:00', 'Botellas y frascos de vidrio limpios.', 'Normal', 'Disponible');

-- Historial previo de entrega validada para reflejar los 2.850 puntos iniciales de Carlos
INSERT INTO `solicitudes` (`id_solicitud`, `id_ciudadano`, `id_material`, `id_localidad`, `cantidad_estimada`, `direccion`, `barrio`, `latitud`, `longitud`, `fecha_solicitud`, `fecha_disponibilidad`, `hora_inicio`, `hora_fin`, `observaciones`, `prioridad`, `estado`) VALUES
(1020, 2, 1, 11, 285.00, 'Calle 140 # 90-10', 'Suba Rincón', 4.7420000, -74.0820000, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), '08:00:00', '12:00:00', 'Entrega anterior histórica', 'Normal', 'Validada');

INSERT INTO `recolecciones` (`id_recoleccion`, `id_solicitud`, `id_reciclador`, `fecha_aceptacion`, `fecha_recoleccion`, `cantidad_recolectada`, `observaciones`, `estado`) VALUES
(1, 1020, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), 285.00, 'Recolección completa y clasificada', 'Recolectada');

INSERT INTO `entregas` (`id_entrega`, `id_recoleccion`, `id_reciclador`, `id_centro`, `fecha_entrega`, `cantidad_declarada`, `cantidad_validada`, `estado`, `observaciones`) VALUES
(1, 1, 1, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), 285.00, 285.00, 'Validada', 'Material pesado y verificado en balanza digital certificada.');

INSERT INTO `ecopuntos` (`id_ecopunto`, `id_reciclador`, `id_entrega`, `cantidad_puntos`, `fecha_generacion`, `motivo`, `estado`) VALUES
(1, 1, 1, 2850, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Validación oficial: 285.00 kg Cartón en EcoCentro Suba Verde (10 pts/kg)', 'activo');
