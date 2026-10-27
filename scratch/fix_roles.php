<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
$db = Database::getConnection();

echo "Actualizando roles de usuarios predeterminados...\n";

// 1. Corregir rol de ciudadano@ecopoint.app a 2 (ciudadano)
$stmt1 = $db->prepare("UPDATE usuarios SET id_rol = 2 WHERE correo = 'ciudadano@ecopoint.app'");
$stmt1->execute();
echo "ciudadano@ecopoint.app actualizado a id_rol = 2 (filas afectadas: {$stmt1->rowCount()})\n";

// 2. Corregir rol de centro@ecopoint.app a 3 (centro_acopio)
$stmt2 = $db->prepare("UPDATE usuarios SET id_rol = 3 WHERE correo = 'centro@ecopoint.app'");
$stmt2->execute();
echo "centro@ecopoint.app actualizado a id_rol = 3 (filas afectadas: {$stmt2->rowCount()})\n";

// 3. Confirmar reciclador y admin
$stmt3 = $db->prepare("UPDATE usuarios SET id_rol = 1 WHERE correo = 'reciclador@ecopoint.app'");
$stmt3->execute();

$stmt4 = $db->prepare("UPDATE usuarios SET id_rol = 4 WHERE correo = 'admin@ecopoint.app'");
$stmt4->execute();

// 4. Limpiar perfiles cruzados residuales en perfil_reciclador para usuarios 2 y 3
$stmt5 = $db->prepare("DELETE FROM perfil_reciclador WHERE id_usuario IN (2, 3)");
$stmt5->execute();
echo "Registros residuales en perfil_reciclador eliminados: {$stmt5->rowCount()}\n";

// 5. Verificar estado final
echo "\n--- ESTADO FINAL DE LOS USUARIOS PREDETERMINADOS ---\n";
$stmtCheck = $db->query("
    SELECT u.id_usuario, u.correo, u.nombre, u.apellido, u.id_rol, r.nombre AS rol_nombre, u.estado 
    FROM usuarios u 
    INNER JOIN roles r ON u.id_rol = r.id_rol 
    WHERE u.correo IN ('reciclador@ecopoint.app', 'ciudadano@ecopoint.app', 'centro@ecopoint.app', 'admin@ecopoint.app')
");
$users = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID: {$u['id_usuario']} | Correo: {$u['correo']} | Rol: {$u['id_rol']} - {$u['rol_nombre']} | Nombre: {$u['nombre']} {$u['apellido']}\n";
}
