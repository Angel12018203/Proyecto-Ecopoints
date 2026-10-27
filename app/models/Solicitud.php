<?php
/**
 * ============================================================================
 * MODELO: SOLICITUD (Gestión de Solicitudes de Recolección — Fase 2)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE MODELO?
 * Administra todas las operaciones sobre la tabla `solicitudes` en MySQL:
 * 1. Inserción segura de nuevas solicitudes creadas por ciudadanos autenticados.
 * 2. Consulta de solicitudes pertenecientes a un ciudadano con control estricto de propiedad.
 * 3. Consulta de detalle de una solicitud con verificación de autorización.
 * 4. Métricas y conteo de estados para el panel del ciudadano.
 * 
 * MEDIDAS DE SEGURIDAD:
 * - Consultas preparadas PDO con vinculación tipada (:id, :cantidad, etc.).
 * - Estado inicial obligatorio 'Pendiente'.
 * - Verificación de propiedad (id_ciudadano) en todas las consultas individuales.
 */

require_once __DIR__ . '/Model.php';

class Solicitud extends Model {

    /**
     * Registra una nueva solicitud de recolección en la base de datos.
     * 
     * @param array $data Datos validados de la solicitud
     * @return int ID de la solicitud recién creada
     * @throws InvalidArgumentException En caso de datos inválidos
     */
    public function create(array $data): int {
        // Validación de cantidad positiva
        $cantidad = (float)($data['cantidad_estimada'] ?? 0);
        if ($cantidad <= 0) {
            throw new InvalidArgumentException("La cantidad estimada debe ser mayor a cero.");
        }

        $sql = "INSERT INTO solicitudes (
                    id_ciudadano, id_material, id_localidad, cantidad_estimada,
                    direccion, barrio, latitud, longitud, fecha_solicitud,
                    fecha_disponibilidad, hora_inicio, hora_fin, observaciones,
                    foto_url, prioridad, estado
                ) VALUES (
                    :id_ciudadano, :id_material, :id_localidad, :cantidad_estimada,
                    :direccion, :barrio, :latitud, :longitud, NOW(),
                    :fecha_disponibilidad, :hora_inicio, :hora_fin, :observaciones,
                    :foto_url, :prioridad, :estado
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_ciudadano', (int)$data['id_ciudadano'], PDO::PARAM_INT);
        $stmt->bindValue(':id_material', (int)$data['id_material'], PDO::PARAM_INT);
        $stmt->bindValue(':id_localidad', (int)$data['id_localidad'], PDO::PARAM_INT);
        $stmt->bindValue(':cantidad_estimada', $cantidad);
        $stmt->bindValue(':direccion', trim($data['direccion']), PDO::PARAM_STR);
        $stmt->bindValue(':barrio', trim($data['barrio'] ?? 'Bogotá D.C.'), PDO::PARAM_STR);
        $stmt->bindValue(':latitud', (float)($data['latitud'] ?? 4.7450000));
        $stmt->bindValue(':longitud', (float)($data['longitud'] ?? -74.0850000));
        $stmt->bindValue(':fecha_disponibilidad', $data['fecha_disponibilidad'], PDO::PARAM_STR);
        $stmt->bindValue(':hora_inicio', $data['hora_inicio'] ?? '08:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':hora_fin', $data['hora_fin'] ?? '14:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':observaciones', trim($data['observaciones'] ?? ''), PDO::PARAM_STR);
        $stmt->bindValue(':foto_url', $data['foto_url'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':prioridad', $data['prioridad'] ?? 'Normal', PDO::PARAM_STR);
        $stmt->bindValue(':estado', 'Pendiente', PDO::PARAM_STR);

        $stmt->execute();
        return $this->lastInsertId();
    }

    /**
     * Consulta todas las solicitudes pertenecientes a un ciudadano específico.
     * Incluye datos del material y de la localidad mediante JOINs.
     * 
     * @param int $idCiudadano ID del usuario ciudadano autenticado
     * @return array Lista de solicitudes
     */
    public function getByCiudadano(int $idCiudadano): array {
        $sql = "SELECT s.*, 
                       m.nombre AS material_nombre, 
                       m.icono AS material_icono, 
                       m.puntos_por_kg,
                       l.nombre AS localidad_nombre, 
                       l.numero AS localidad_numero
                FROM solicitudes s
                INNER JOIN materiales m ON s.id_material = m.id_material
                INNER JOIN localidades l ON s.id_localidad = l.id_localidad
                WHERE s.id_ciudadano = :id_ciudadano
                ORDER BY s.fecha_solicitud DESC, s.id_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_ciudadano', $idCiudadano, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Consulta una solicitud por su ID asegurando que pertenezca al ciudadano.
     * Protege contra ataques IDOR (Insecure Direct Object Reference).
     * 
     * @param int $idSolicitud
     * @param int $idCiudadano
     * @return array|null
     */
    public function findByIdAndCiudadano(int $idSolicitud, int $idCiudadano): ?array {
        $sql = "SELECT s.*, 
                       m.nombre AS material_nombre, 
                       m.icono AS material_icono, 
                       m.puntos_por_kg,
                       l.nombre AS localidad_nombre, 
                       l.numero AS localidad_numero
                FROM solicitudes s
                INNER JOIN materiales m ON s.id_material = m.id_material
                INNER JOIN localidades l ON s.id_localidad = l.id_localidad
                WHERE s.id_solicitud = :id_solicitud AND s.id_ciudadano = :id_ciudadano
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_solicitud', $idSolicitud, PDO::PARAM_INT);
        $stmt->bindValue(':id_ciudadano', $idCiudadano, PDO::PARAM_INT);
        $stmt->execute();

        $solicitud = $stmt->fetch();
        return $solicitud ?: null;
    }

    /**
     * Consulta una solicitud por ID general (para recicladores o vista de detalle autorizada).
     * 
     * @param int $idSolicitud
     * @return array|null
     */
    public function findById(int $idSolicitud): ?array {
        $sql = "SELECT s.*, 
                       m.nombre AS material_nombre, 
                       m.icono AS material_icono, 
                       l.nombre AS localidad_nombre
                FROM solicitudes s
                INNER JOIN materiales m ON s.id_material = m.id_material
                INNER JOIN localidades l ON s.id_localidad = l.id_localidad
                WHERE s.id_solicitud = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idSolicitud, PDO::PARAM_INT);
        $stmt->execute();

        $solicitud = $stmt->fetch();
        return $solicitud ?: null;
    }

    /**
     * Retorna métricas resumidas de las solicitudes de un ciudadano.
     * 
     * @param int $idCiudadano
     * @return array [total, pendientes, aceptadas, completadas]
     */
    public function getMetricasCiudadano(int $idCiudadano): array {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado IN ('Pendiente', 'Disponible') THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN estado IN ('Aceptada', 'En proceso') THEN 1 ELSE 0 END) AS en_proceso,
                    SUM(CASE WHEN estado IN ('Recolectada', 'Recogida', 'Entregada', 'Validada', 'Completada') THEN 1 ELSE 0 END) AS completadas,
                    COALESCE(SUM(cantidad_estimada), 0) AS total_kg_estimados
                FROM solicitudes
                WHERE id_ciudadano = :id_ciudadano";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_ciudadano', $idCiudadano, PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetch();
        return [
            'total'              => (int)($res['total'] ?? 0),
            'pendientes'         => (int)($res['pendientes'] ?? 0),
            'en_proceso'         => (int)($res['en_proceso'] ?? 0),
            'completadas'        => (int)($res['completadas'] ?? 0),
            'total_kg_estimados' => (float)($res['total_kg_estimados'] ?? 0)
        ];
    }
}
