<?php
/**
 * ============================================================================
 * MODELO: LOCALIDAD (Catálogo Maestro de Bogotá D.C.)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE MODELO?
 * Administra el catálogo oficial de las 20 localidades de Bogotá D.C.
 * 
 * ¿POR QUÉ ES OBLIGATORIO SEGÚN LOS REQUISITOS?
 * No se debe almacenar la localidad como texto libre en los usuarios, para evitar
 * inconsistencias como "Santa Fe" vs "Santafé" o "Suba" vs "suba".
 * Este modelo provee el catálogo normalizado que alimenta los selects de
 * registro, filtros de búsqueda, centros de acopio y solicitudes de recolección.
 */

require_once __DIR__ . '/Model.php';

class Localidad extends Model {

    /**
     * Retorna la lista completa de localidades activas ordenadas por número oficial.
     * 
     * @return array Arreglo de localidades con id_localidad, numero y nombre
     */
    public function getAllActive(): array {
        $sql = "SELECT id_localidad, numero, nombre
                FROM localidades
                WHERE activo = 1
                ORDER BY numero ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Busca una localidad por su ID de catálogo.
     * 
     * @param int $idLocalidad ID numérico de la localidad
     * @return array|null Datos de la localidad o null si no existe
     */
    public function findById(int $idLocalidad): ?array {
        $sql = "SELECT id_localidad, numero, nombre, activo
                FROM localidades
                WHERE id_localidad = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idLocalidad, PDO::PARAM_INT);
        $stmt->execute();

        $loc = $stmt->fetch();
        return $loc ?: null;
    }
}
