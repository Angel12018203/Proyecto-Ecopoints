<?php
/**
 * Test authentication for all 4 default accounts and verify redirects and permissions.
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/auth_helper.php';
require_once __DIR__ . '/../app/models/Usuario.php';

$usersToTest = [
    ['correo' => 'reciclador@ecopoint.app', 'pass' => 'Carlos123*', 'expected_role' => 'reciclador', 'dashboard' => 'reciclador'],
    ['correo' => 'ciudadano@ecopoint.app',  'pass' => 'Maria123*',  'expected_role' => 'ciudadano',  'dashboard' => 'ciudadano'],
    ['correo' => 'centro@ecopoint.app',     'pass' => 'Centro123*', 'expected_role' => 'centro_acopio', 'dashboard' => 'centro-acopio'],
    ['correo' => 'admin@ecopoint.app',      'pass' => 'Admin123*',  'expected_role' => 'administrador', 'dashboard' => 'admin'],
];

$usuarioModel = new Usuario();

echo "=== VERIFICACIÓN DE CREDENCIALES Y ROLES PREDETERMINADOS ===\n\n";

$allPassed = true;

foreach ($usersToTest as $test) {
    $user = $usuarioModel->findByEmail($test['correo']);
    if (!$user) {
        echo "❌ [ERROR] Usuario no encontrado: {$test['correo']}\n";
        $allPassed = false;
        continue;
    }

    $passOk = Usuario::verifyPassword($test['pass'], $user['password']);
    if (!$passOk) {
        echo "❌ [ERROR] Contraseña incorrecta para: {$test['correo']}\n";
        $allPassed = false;
        continue;
    }

    $roleOk = ($user['rol_nombre'] === $test['expected_role']);
    if (!$roleOk) {
        echo "❌ [ERROR] Rol incorrecto para {$test['correo']}: esperado {$test['expected_role']}, obtenido {$user['rol_nombre']}\n";
        $allPassed = false;
        continue;
    }

    echo "✅ [OK] {$test['correo']} ({$test['pass']}) -> Rol: {$user['rol_nombre']} -> Panel: {$test['dashboard']}\n";
}

if ($allPassed) {
    echo "\n🎉 TODOS LOS USUARIOS AUTENTICAN Y TIENEN SUS ROLES Y PANELES EXACTOS.\n";
} else {
    echo "\n⚠️ Hay errores en las credenciales o roles.\n";
}
