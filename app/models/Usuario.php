<?php
/**
 * ============================================================================
 * MODELO: USUARIO (Entidad Principal de Autenticación)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE MODELO?
 * Administra las operaciones sobre la tabla `usuarios` en MySQL:
 * - Búsqueda de usuarios por correo electrónico.
 * - Registro de nuevos usuarios con contraseña encriptada con BCRYPT.
 * - Verificación de contraseñas contra hashes seguros.
 * - Actualización de marcas de tiempo de último acceso.
 * 
 * MEDIDAS DE SEGURIDAD CLAVE:
 * 1. Uso de password_hash() con PASSWORD_BCRYPT para nunca almacenar texto plano.
 * 2. Consultas preparadas con parámetros nombrados (:correo, :password) para
 *    garantizar que ningún dato del usuario se concatene en la sentencia SQL.
 */

require_once __DIR__ . '/Model.php';

class Usuario extends Model {

    /**
     * Busca un usuario activo por su dirección de correo electrónico.
     * Incluye mediante JOIN el nombre del rol asignado.
     * 
     * @param string $correo Correo a buscar
     * @return array|null Datos del usuario o null si no existe
     */
    public function findByEmail(string $correo): ?array {
        $sql = "SELECT u.*, r.nombre AS rol_nombre, r.descripcion AS rol_descripcion
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE LOWER(TRIM(u.correo)) = LOWER(TRIM(:correo))
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Busca un usuario por su ID único.
     * 
     * @param int $idUsuario ID del usuario
     * @return array|null Datos del usuario o null
     */
    public function findById(int $idUsuario): ?array {
        $sql = "SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Inserta un nuevo usuario en la base de datos con contraseña hasheada.
     * 
     * @param array $data Arreglo con nombre, apellido, correo, telefono, password y id_rol
     * @return int ID del usuario recién creado
     * @throws Exception Si el correo ya existe o la inserción falla
     */
    public function create(array $data): int {
        // 1. Validar que el correo no se encuentre registrado previamente
        if ($this->findByEmail($data['correo'])) {
            throw new InvalidArgumentException("El correo electrónico '{$data['correo']}' ya se encuentra registrado.");
        }

        // 2. Hashear la contraseña con el algoritmo BCRYPT
        // BCRYPT incluye automáticamente un 'salt' criptográfico único por cada hash
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);

        // 3. Sentencia SQL con parámetros preparados
        $sql = "INSERT INTO usuarios (nombre, apellido, correo, telefono, password, id_rol, estado, fecha_registro)
                VALUES (:nombre, :apellido, :correo, :telefono, :password, :id_rol, 'activo', NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre', trim($data['nombre']), PDO::PARAM_STR);
        $stmt->bindValue(':apellido', trim($data['apellido']), PDO::PARAM_STR);
        $stmt->bindValue(':correo', strtolower(trim($data['correo'])), PDO::PARAM_STR);
        $stmt->bindValue(':telefono', trim($data['telefono'] ?? ''), PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindValue(':id_rol', (int)$data['id_rol'], PDO::PARAM_INT);
        
        $stmt->execute();

        return $this->lastInsertId();
    }

    /**
     * Actualiza la fecha y hora del último acceso exitoso del usuario.
     * 
     * @param int $idUsuario
     * @return bool
     */
    public function updateLastLogin(int $idUsuario): bool {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Comprueba si una contraseña en texto plano coincide con el hash almacenado.
     * 
     * @param string $passwordPlain Contraseña introducida por el usuario
     * @param string $passwordHash Hash almacenado en la base de datos
     * @return bool True si la contraseña es correcta
     */
    public static function verifyPassword(string $passwordPlain, string $passwordHash): bool {
        return password_verify($passwordPlain, $passwordHash);
    }
}
