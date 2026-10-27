<?php
/**
 * ============================================================================
 * MODELO: MATERIAL (Catálogo de Materiales Reciclables)
 * ============================================================================
 * 
 * Gestiona el catálogo maestro de materiales reciclables admitidos por EcoPoints:
 * - Cartón, Papel, Plástico, Vidrio, Metales, Otros.
 * - Iconografía asociada y puntos referenciales por kilogramo.
 */

require_once __DIR__ . '/Model.php';

class Material extends Model {

    /**
     * Retorna todos los materiales reciclables activos.
     * 
     * @return array
     */
    public function getAllActive(): array {
        $sql = "SELECT id_material, nombre, descripcion, puntos_por_kg, icono, estado
                FROM materiales
                WHERE estado = 'activo'
                ORDER BY id_material ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Busca un material específico por su ID.
     * 
     * @param int $idMaterial
     * @return array|null
     */
    public function findById(int $idMaterial): ?array {
        $sql = "SELECT id_material, nombre, descripcion, puntos_por_kg, icono, estado
                FROM materiales
                WHERE id_material = :id AND estado = 'activo'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idMaterial, PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetch();
        return $res ?: null;
    }
}
