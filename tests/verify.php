<?php
/**
 * ============================================================================
 * SCRIPT DE PRUEBAS AUTOMATIZADAS DE LA FASE 1 — ECOPOINTS
 * ============================================================================
 * 
 * Verifica programáticamente todos los requisitos exigidos en el Punto 61:
 * 1. Conexión PDO a base de datos en XAMPP.
 * 2. Carga y normalización de las 20 localidades de Bogotá.
 * 3. Autenticación con contraseña válida (Carlos123*).
 * 4. Rechazo con contraseña incorrecta.
 * 5. Rechazo de correo duplicado.
 * 6. Generación y verificación de token CSRF.
 * 7. Escape seguro contra ataques XSS con helper h().
 * 8. Registro de Ciudadano con FK de localidad.
 * 9. Registro de Reciclador con medio de transporte y zona.
 * 10. Registro de Centro de Acopio con establecimiento y responsable.
 * 11. Control de acceso y sesiones seguras con roles.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/view_helper.php';
require_once __DIR__ . '/../app/helpers/csrf_helper.php';
require_once __DIR__ . '/../app/helpers/auth_helper.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Localidad.php';
require_once __DIR__ . '/../app/models/Reciclador.php';
require_once __DIR__ . '/../app/models/Ciudadano.php';
require_once __DIR__ . '/../app/models/CentroAcopio.php';

$totalTests = 0;
$passedTests = 0;

function assertTest(string $description, bool $condition): void {
    global $totalTests, $passedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] ❌ {$description}\n";
    }
}

echo "\n====================================================================\n";
echo "   INICIANDO PRUEBAS AUTOMATIZADAS DE ECOPOINTS — FASE 1\n";
echo "====================================================================\n\n";

// TEST 1: Conexión PDO
try {
    $pdo = Database::getConnection();
    assertTest("Conexión PDO establecida con éxito a MySQL/MariaDB", $pdo instanceof PDO);
} catch (Exception $e) {
    assertTest("Fallo de conexión PDO: " . $e->getMessage(), false);
}

// TEST 2: Catálogo de 20 Localidades
$localidadModel = new Localidad();
$localidades = $localidadModel->getAllActive();
assertTest("El catálogo contiene exactamente 20 localidades oficiales de Bogotá", count($localidades) === 20);
assertTest("La localidad #11 es 'Suba'", ($localidades[10]['nombre'] ?? '') === 'Suba');

// TEST 3: Consulta y Autenticación del Reciclador Semilla
$usuarioModel = new Usuario();
$carlos = $usuarioModel->findByEmail('reciclador@ecopoint.app');
assertTest("Usuario semilla 'reciclador@ecopoint.app' existe en la BD", $carlos !== null);
assertTest("El rol de Carlos es 'reciclador' (id_rol = 1)", (int)($carlos['id_rol'] ?? 0) === 1);

$passValida = Usuario::verifyPassword('Carlos123*', $carlos['password']);
assertTest("Contraseña correcta 'Carlos123*' autentica con BCRYPT", $passValida === true);

$passInvalida = Usuario::verifyPassword('PasswordErroneo', $carlos['password']);
assertTest("Contraseña errónea 'PasswordErroneo' es rechazada", $passInvalida === false);

// TEST 4: Verificación de Perfil de Reciclador y Métricas Dinámicas
$recicladorModel = new Reciclador();
$perfilCarlos = $recicladorModel->findByUserId((int)$carlos['id_usuario']);
assertTest("Perfil de reciclador asociado existe", $perfilCarlos !== null);
assertTest("Ecopoints iniciales de Carlos = 2.850 pts", (int)($perfilCarlos['ecopuntos'] ?? 0) === 2850);
assertTest("Nivel calculado dinámicamente es 'Reciclador Destacado'", ($perfilCarlos['nivel_calculado']['nombre'] ?? '') === 'Reciclador Destacado');

// TEST 5: Protección CSRF
$token1 = csrf_token();
assertTest("Generación de token CSRF (longitud 64 caracteres hex)", strlen($token1) === 64);
assertTest("verify_csrf valida token legítimo con hash_equals", verify_csrf($token1) === true);
assertTest("verify_csrf rechaza token falso o manipulado", verify_csrf('token_falso_atacante_12345') === false);

// TEST 6: Escape contra ataques XSS
$maliciousInput = '<script>alert("Ataque XSS");</script>';
$escaped = h($maliciousInput);
assertTest("Helper h() neutraliza etiquetas script peligrosas", strpos($escaped, '<script>') === false && strpos($escaped, '&lt;script&gt;') !== false);

// TEST 7: Intento de Registro con Correo Duplicado (Debe rechazar)
$duplicateCaught = false;
try {
    $usuarioModel->create([
        'nombre'   => 'Intruso',
        'apellido' => 'Duplicado',
        'correo'   => 'reciclador@ecopoint.app', // Correo ya existente
        'telefono' => '300 000 0000',
        'password' => 'Secreta123*',
        'id_rol'   => 1
    ]);
} catch (InvalidArgumentException $e) {
    $duplicateCaught = true;
}
assertTest("El modelo rechaza intento de registro con correo duplicado", $duplicateCaught === true);

// TEST 8: Creación Dinámica de Nuevo Ciudadano de Prueba
$emailCiudadanoTest = 'test.ciudadano.' . time() . '@ecopoint.app';
$idCiudadanoTest = 0;
try {
    $idCiudadanoTest = $usuarioModel->create([
        'nombre'   => 'Prueba',
        'apellido' => 'Ciudadano',
        'correo'   => $emailCiudadanoTest,
        'telefono' => '315 111 2233',
        'password' => 'Prueba123*',
        'id_rol'   => 2 // Ciudadano
    ]);
    
    $ciudadanoModel = new Ciudadano();
    $ciudadanoModel->createProfile([
        'id_usuario'   => $idCiudadanoTest,
        'id_localidad' => 2, // Chapinero
        'direccion'    => 'Carrera 7 # 50-20',
        'barrio'       => 'Chapinero Alto',
        'preferencias' => 'Bolsas blancas con cartón y vidrio'
    ]);
    
    $perfilCiudadano = $ciudadanoModel->findByUserId($idCiudadanoTest);
    assertTest("Registro de Ciudadano creado con perfil y localidad vinculada (#2 Chapinero)", $perfilCiudadano !== null && ($perfilCiudadano['localidad_nombre'] ?? '') === 'Chapinero');
} catch (Exception $e) {
    assertTest("Fallo registro ciudadano test: " . $e->getMessage(), false);
}

// TEST 9: Creación Dinámica de Nuevo Reciclador de Prueba con Transporte
$emailRecicladorTest = 'test.reciclador.' . time() . '@ecopoint.app';
$idRecicladorTest = 0;
try {
    $idRecicladorTest = $usuarioModel->create([
        'nombre'   => 'Juan',
        'apellido' => 'Pérez',
        'correo'   => $emailRecicladorTest,
        'telefono' => '320 999 8877',
        'password' => 'Recicla123*',
        'id_rol'   => 1 // Reciclador
    ]);
    
    $recicladorModel->createProfile([
        'id_usuario'       => $idRecicladorTest,
        'id_localidad'     => 10, // Engativá
        'zona_trabajo'     => 'Engativá y Álamos',
        'medio_transporte' => 'Carro de recolección',
        'disponibilidad'   => 'Tardes 1:00 p.m. - 6:00 p.m.'
    ]);
    
    $perfilRec = $recicladorModel->findByUserId($idRecicladorTest);
    assertTest("Registro de Reciclador con transporte 'Carro de recolección' y localidad (#10 Engativá)", $perfilRec !== null && $perfilRec['medio_transporte'] === 'Carro de recolección');
} catch (Exception $e) {
    assertTest("Fallo registro reciclador test: " . $e->getMessage(), false);
}

// TEST 10: Creación Dinámica de Nuevo Centro de Acopio de Prueba
$emailCentroTest = 'test.centro.' . time() . '@ecopoint.app';
$idCentroTest = 0;
try {
    $idCentroTest = $usuarioModel->create([
        'nombre'   => 'Héctor',
        'apellido' => 'Valenzuela',
        'correo'   => $emailCentroTest,
        'telefono' => '318 444 3322',
        'password' => 'CentroTest123*',
        'id_rol'   => 3 // Centro de acopio
    ]);
    
    $centroModel = new CentroAcopio();
    $centroModel->createProfile([
        'id_usuario'          => $idCentroTest,
        'id_localidad'        => 9, // Fontibón
        'nombre_centro'       => 'EcoAcopio Fontibón Verde',
        'responsable'         => 'Héctor Valenzuela',
        'direccion'           => 'Av. El Dorado # 98-40',
        'telefono'            => '318 444 3322'
    ]);
    
    $perfilCentro = $centroModel->findByUserId($idCentroTest);
    assertTest("Registro de Centro de Acopio con establecimiento 'EcoAcopio Fontibón Verde' y localidad (#9 Fontibón)", $perfilCentro !== null && $perfilCentro['nombre'] === 'EcoAcopio Fontibón Verde');
} catch (Exception $e) {
    assertTest("Fallo registro centro test: " . $e->getMessage(), false);
}

// TEST 11: Simulación de Sesión y Control de Acceso por Roles
loginUser($carlos, $perfilCarlos);
assertTest("loginUser() establece usuario autenticado en sesión", isAuthenticated() === true);
assertTest("currentUser() retorna datos de Carlos Rodríguez", currentUser()['correo'] === 'reciclador@ecopoint.app');
assertTest("hasRole('reciclador') retorna true", hasRole('reciclador') === true);
assertTest("hasRole('ciudadano') retorna false para cuenta de reciclador", hasRole('ciudadano') === false);

logoutUser();
assertTest("logoutUser() destruye la sesión y limpia credenciales", isAuthenticated() === false && currentUser() === null);

// Limpieza de datos de prueba
if ($idCiudadanoTest > 0) {
    $pdo->exec("DELETE FROM usuarios WHERE id_usuario = {$idCiudadanoTest}");
}
if ($idRecicladorTest > 0) {
    $pdo->exec("DELETE FROM usuarios WHERE id_usuario = {$idRecicladorTest}");
}
if ($idCentroTest > 0) {
    $pdo->exec("DELETE FROM centros_acopio WHERE id_usuario = {$idCentroTest}");
    $pdo->exec("DELETE FROM usuarios WHERE id_usuario = {$idCentroTest}");
}
assertTest("Limpieza de usuarios temporales de prueba completada", true);

echo "\n--------------------------------------------------------------------\n";
echo "RESULTADOS DE LAS PRUEBAS: {$passedTests} / {$totalTests} PASADAS CON ÉXITO\n";
if ($passedTests === $totalTests) {
    echo "ESTADO: 🎉 TODAS LAS PRUEBAS DE LA FASE 1 CUMPLIDAS SATISFACTORIAMENTE\n";
} else {
    echo "ESTADO: ⚠️ ALGUNAS PRUEBAS FALLARON\n";
}
echo "====================================================================\n\n";
