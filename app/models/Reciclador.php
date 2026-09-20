<?php
/**
 * ============================================================================
 * MODELO: RECICLADOR (Actor Principal del Sistema)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE MODELO?
 * Gestiona la información profesional, operativa y métricas de recompensa
 * del reciclador en la tabla `perfil_reciclador`:
 * - Vinculación con la localidad y zona habitual de trabajo.
 * - Medio de transporte utilizado (A pie, Bicicleta, Carro de recolección, etc.).
 * - Consulta y cálculo dinámico de Ecopoints acumulados y material recuperado.
 * - Determinación del nivel oficial según los rangos establecidos del negocio.
 * 
 * REGLA DE NEGOCIO SOBRE NIVELES (Punto 38 de requisitos):
 * - Reciclador Activo: 0 – 500 Ecopoints
 * - EcoReciclador: 501 – 1.500 Ecopoints
 * - Reciclador Destacado: 1.501 – 3.000 Ecopoints
 * - Embajador de Economía Circular: Más de 3.000 Ecopoints
 */

require_once __DIR__ . '/Model.php';

class Reciclador extends Model {

    /**
     * Crea el perfil inicial del reciclador tras su registro en el sistema.
     * 
     * @param array $data Contiene id_usuario, id_localidad, zona_trabajo, medio_transporte
     * @return int ID del perfil generado
     */
    public function createProfile(array $data): int {
        $sql = "INSERT INTO perfil_reciclador (
                    id_usuario, id_localidad, zona_trabajo, medio_transporte,
                    disponibilidad, ecopuntos, material_recuperado, nivel
                ) VALUES (
                    :id_usuario, :id_localidad, :zona_trabajo, :medio_transporte,
                    :disponibilidad, 0, 0.00, 'Reciclador Activo'
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$data['id_usuario'], PDO::PARAM_INT);
        $stmt->bindValue(':id_localidad', (int)($data['id_localidad'] ?? 11), PDO::PARAM_INT);
        $stmt->bindValue(':zona_trabajo', trim($data['zona_trabajo'] ?? 'Suba'), PDO::PARAM_STR);
        $stmt->bindValue(':medio_transporte', trim($data['medio_transporte'] ?? 'Bicicleta'), PDO::PARAM_STR);
        $stmt->bindValue(':disponibilidad', trim($data['disponibilidad'] ?? 'Lunes a Sábado 7:00 a.m. - 4:00 p.m.'), PDO::PARAM_STR);

        $stmt->execute();
        return $this->lastInsertId();
    }

    /**
     * Obtiene el perfil completo del reciclador por su ID de usuario,
     * incluyendo los datos de su localidad asignada.
     * 
     * @param int $idUsuario ID en la tabla usuarios
     * @return array|null Perfil o null si no existe
     */
    public function findByUserId(int $idUsuario): ?array {
        $sql = "SELECT pr.*, l.nombre AS localidad_nombre, l.numero AS localidad_numero
                FROM perfil_reciclador pr
                LEFT JOIN localidades l ON pr.id_localidad = l.id_localidad
                WHERE pr.id_usuario = :id_usuario
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $profile = $stmt->fetch();
        if ($profile) {
            // Recalcular nivel dinámicamente según puntos reales
            $profile['nivel_calculado'] = self::calcularNivel((int)$profile['ecopuntos']);
        }
        return $profile ?: null;
    }

    /**
     * Calcula dinámicamente el nivel oficial a partir del saldo de Ecopoints.
     * 
     * @param int $puntos Cantidad de Ecopoints actuales
     * @return array Arreglo con nombre del nivel, puntos meta, progreso y siguiente nivel
     */
    public static function calcularNivel(int $puntos): array {
        if ($puntos <= 500) {
            return [
                'nombre'     => 'Reciclador Activo',
                'meta'       => 500,
                'min'        => 0,
                'siguiente'  => 'EcoReciclador',
                'pct'        => round(($puntos / 500) * 100),
                'puntos_faltantes' => max(0, 501 - $puntos)
            ];
        } elseif ($puntos <= 1500) {
            return [
                'nombre'     => 'EcoReciclador',
                'meta'       => 1500,
                'min'        => 501,
                'siguiente'  => 'Reciclador Destacado',
                'pct'        => round((($puntos - 500) / 1000) * 100),
                'puntos_faltantes' => max(0, 1501 - $puntos)
            ];
        } elseif ($puntos <= 3000) {
            return [
                'nombre'     => 'Reciclador Destacado',
                'meta'       => 3000,
                'min'        => 1501,
                'siguiente'  => 'Embajador de Economía Circular',
                'pct'        => round((($puntos - 1500) / 1500) * 100),
                'puntos_faltantes' => max(0, 3001 - $puntos)
            ];
        } else {
            return [
                'nombre'     => 'Embajador de Economía Circular',
                'meta'       => $puntos,
                'min'        => 3000,
                'siguiente'  => 'Nivel Máximo Alcanzado',
                'pct'        => 100,
                'puntos_faltantes' => 0
            ];
        }
    }

    /**
     * Obtiene métricas agregadas para el dashboard del reciclador.
     * 
     * @param int $idUsuario
     * @return array
     */
    public function getDashboardMetrics(int $idUsuario): array {
        $perfil = $this->findByUserId($idUsuario);
        $ecopuntos = (int)($perfil['ecopuntos'] ?? 0);
        $materialKg = (float)($perfil['material_recuperado'] ?? 0);

        // Conteo de solicitudes disponibles en el sistema
        $sqlSol = "SELECT COUNT(*) AS total FROM solicitudes WHERE estado = 'Disponible'";
        $stmtSol = $this->db->query($sqlSol);
        $totalSolicitudes = (int)($stmtSol->fetchColumn() ?: 0);

        // Conteo de recolecciones activas del reciclador
        $sqlRec = "SELECT COUNT(*) AS total FROM recolecciones WHERE id_reciclador = :id AND estado IN ('Aceptada', 'En camino')";
        $stmtRec = $this->db->prepare($sqlRec);
        $stmtRec->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmtRec->execute();
        $totalRecolecciones = (int)($stmtRec->fetchColumn() ?: 0);

        return [
            'ecopuntos'          => $ecopuntos,
            'material_kg'        => $materialKg,
            'solicitudes_activas'=> $totalSolicitudes,
            'recolecciones_activas' => $totalRecolecciones,
            'nivel'              => self::calcularNivel($ecopuntos)
        ];
    }
}
