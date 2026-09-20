<?php
/**
 * ============================================================================
 * MODELO: CENTRO DE ACOPIO (Receptor y Validador Oficial)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE MODELO?
 * Gestiona la información de los centros de acopio autorizados en `centros_acopio`:
 * - Vinculación del establecimiento comercial con la cuenta de usuario del responsable.
 * - Registro de nombre del centro, responsable, localidad normalizada, dirección y horarios.
 * - Consulta de centros disponibles para los recicladores en sus recorridos y rutas.
 */

require_once __DIR__ . '/Model.php';

class CentroAcopio extends Model {

    /**
     * Crea el registro del centro de acopio vinculado al usuario recién registrado.
     * 
     * @param array $data
     * @return int
     */
    public function createProfile(array $data): int {
        $sql = "INSERT INTO centros_acopio (
                    id_usuario, id_localidad, nombre, responsable, direccion,
                    telefono, hora_apertura, hora_cierre, materiales_recibidos, estado
                ) VALUES (
                    :id_usuario, :id_localidad, :nombre, :responsable, :direccion,
                    :telefono, :hora_apertura, :hora_cierre, :materiales_recibidos, 'activo'
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$data['id_usuario'], PDO::PARAM_INT);
        $stmt->bindValue(':id_localidad', (int)($data['id_localidad'] ?? 11), PDO::PARAM_INT);
        $stmt->bindValue(':nombre', trim($data['nombre_centro'] ?? $data['nombre']), PDO::PARAM_STR);
        $stmt->bindValue(':responsable', trim($data['responsable'] ?? 'Responsable'), PDO::PARAM_STR);
        $stmt->bindValue(':direccion', trim($data['direccion'] ?? 'Dirección del centro'), PDO::PARAM_STR);
        $stmt->bindValue(':telefono', trim($data['telefono'] ?? ''), PDO::PARAM_STR);
        $stmt->bindValue(':hora_apertura', $data['hora_apertura'] ?? '08:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':hora_cierre', $data['hora_cierre'] ?? '17:30:00', PDO::PARAM_STR);
        $stmt->bindValue(':materiales_recibidos', $data['materiales_recibidos'] ?? 'Cartón, Plástico, Vidrio, Metales', PDO::PARAM_STR);

        $stmt->execute();
        return $this->lastInsertId();
    }

    /**
     * Busca la ficha del centro de acopio asociado al usuario autenticado.
     * 
     * @param int $idUsuario
     * @return array|null
     */
    public function findByUserId(int $idUsuario): ?array {
        $sql = "SELECT ca.*, l.nombre AS localidad_nombre, l.numero AS localidad_numero
                FROM centros_acopio ca
                LEFT JOIN localidades l ON ca.id_localidad = l.id_localidad
                WHERE ca.id_usuario = :id_usuario
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $centro = $stmt->fetch();
        return $centro ?: null;
    }

    /**
     * Retorna todos los centros de acopio activos para la vista de los recicladores.
     * 
     * @return array
     */
    public function getAllActive(): array {
        $sql = "SELECT ca.*, l.nombre AS localidad_nombre
                FROM centros_acopio ca
                LEFT JOIN localidades l ON ca.id_localidad = l.id_localidad
                WHERE ca.estado = 'activo'
                ORDER BY ca.nombre ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
