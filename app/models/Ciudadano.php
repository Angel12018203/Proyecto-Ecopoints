<?php
/**
 * ============================================================================
 * MODELO: CIUDADANO (Generador de Solicitudes)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE MODELO?
 * Administra la información específica de los ciudadanos en `perfil_ciudadano`:
 * - Vinculación obligatoria con su localidad normalizada.
 * - Dirección residencial y barrio para futuras recolecciones domiciliarias.
 * - Preferencias e instrucciones de entrega del material.
 */

require_once __DIR__ . '/Model.php';

class Ciudadano extends Model {

    /**
     * Crea el perfil de ciudadano tras el registro exitoso en `usuarios`.
     * 
     * @param array $data Contiene id_usuario, id_localidad, direccion, barrio, preferencias
     * @return int ID del perfil generado
     */
    public function createProfile(array $data): int {
        $sql = "INSERT INTO perfil_ciudadano (
                    id_usuario, id_localidad, direccion, barrio, preferencias
                ) VALUES (
                    :id_usuario, :id_localidad, :direccion, :barrio, :preferencias
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$data['id_usuario'], PDO::PARAM_INT);
        $stmt->bindValue(':id_localidad', (int)($data['id_localidad'] ?? 11), PDO::PARAM_INT);
        $stmt->bindValue(':direccion', trim($data['direccion'] ?? 'Dirección por registrar'), PDO::PARAM_STR);
        $stmt->bindValue(':barrio', trim($data['barrio'] ?? 'Barrio'), PDO::PARAM_STR);
        $stmt->bindValue(':preferencias', trim($data['preferencias'] ?? 'Material limpio y separado'), PDO::PARAM_STR);

        $stmt->execute();
        return $this->lastInsertId();
    }

    /**
     * Consulta el perfil de ciudadano por ID de usuario.
     * 
     * @param int $idUsuario
     * @return array|null
     */
    public function findByUserId(int $idUsuario): ?array {
        $sql = "SELECT pc.*, l.nombre AS localidad_nombre, l.numero AS localidad_numero
                FROM perfil_ciudadano pc
                LEFT JOIN localidades l ON pc.id_localidad = l.id_localidad
                WHERE pc.id_usuario = :id_usuario
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $profile = $stmt->fetch();
        return $profile ?: null;
    }
}
