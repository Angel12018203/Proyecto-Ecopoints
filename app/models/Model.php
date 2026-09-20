<?php
/**
 * ============================================================================
 * CLASE BASE MODELO (MODEL)
 * ============================================================================
 * 
 * ¿QUÉ ES UN MODELO EN MVC?
 * El Modelo representa la capa de datos y la lógica de negocio. Es el único
 * componente del sistema que tiene autorización para comunicarse directamente
 * con la base de datos MySQL mediante PDO.
 * 
 * ¿QUÉ HACE ESTA CLASE BASE?
 * Proporciona a todos los modelos hijos (`Usuario`, `Localidad`, etc.):
 * 1. Acceso compartido a la conexión PDO Singleton (`$this->db`).
 * 2. Soporte nativo para transacciones atómicas (`beginTransaction`, `commit`, `rollBack`).
 *    Esto es crítico cuando registramos un usuario: debemos insertar en `usuarios`
 *    y luego en `perfil_reciclador`. Si la segunda falla, la transacción revierte
 *    todo para no dejar datos huérfanos o inconsistentes.
 */

require_once __DIR__ . '/../config/database.php';

abstract class Model {
    /**
     * Instancia de la conexión PDO
     */
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Inicia una transacción de base de datos.
     */
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma definitivamente los cambios de la transacción actual.
     */
    public function commit(): bool {
        return $this->db->commit();
    }

    /**
     * Revierte y cancela todos los cambios ejecutados en la transacción si ocurrió un error.
     */
    public function rollBack(): bool {
        if ($this->db->inTransaction()) {
            return $this->db->rollBack();
        }
        return false;
    }

    /**
     * Retorna el último ID autoincremental generado por un INSERT.
     */
    public function lastInsertId(): int {
        return (int)$this->db->lastInsertId();
    }
}
