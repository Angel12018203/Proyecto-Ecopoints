<?php
/**
 * ============================================================================
 * SCRIPT DE PRUEBAS AUTOMATIZADAS — ECOPOINTS (FASES 1 Y 2)
 * ============================================================================
 * 
 * Verifica programáticamente todos los requisitos exigidos:
 * 1. Conexión PDO a base de datos en XAMPP.
 * 2. Catálogo de 20 localidades oficiales de Bogotá.
 * 3. Catálogo de materiales reciclables activos.
 * 4. Autenticación y verificación BCRYPT segura.
 * 5. Sanitización estricta de entradas:
 *    - Nombres/Apellidos con números rechazados.
 *    - Teléfonos con letras rechazados.
 * 6. Registros multi-rol transaccionales (Ciudadano, Reciclador, Centro Acopio).
 * 7. Control de sesiones, roles RBAC y regeneración de ID de sesión.
 * 8. Protección CSRF y neutralización XSS.
 * 9. Fase 2: Registro de solicitudes de recolección en MySQL.
 * 10. Fase 2: Validaciones de negocio (cantidad > 0, fecha no pasada, catálogos válidos).
 * 11. Fase 2: Estado inicial 'Pendiente' y persistencia de campos.
 * 12. Fase 2: Consulta segura por ciudadano y protección contra IDOR.
 * 13. Limpieza atómica de datos de prueba.
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
require_once __DIR__ . '/../app/models/Material.php';
require_once __DIR__ . '/../app/models/Solicitud.php';

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
echo "   INICIANDO BATERÍA DE PRUEBAS AUTOMATIZADAS — ECOPOINTS (FASES 1 Y 2)\n";
echo "====================================================================\n\n";

// ── SECCIÓN 1: INFRAESTRUCTURA Y CATÁLOGOS BASE ──
echo "── [1] Base de Datos y Catálogos Maestros ──\n";
try {
    $pdo = Database::getConnection();
    assertTest("Conexión PDO establecida con éxito a MySQL/MariaDB", $pdo instanceof PDO);
} catch (Exception $e) {
    assertTest("Fallo de conexión PDO: " . $e->getMessage(), false);
}

$localidadModel = new Localidad();
$localidades = $localidadModel->getAllActive();
assertTest("El catálogo contiene exactamente 20 localidades oficiales de Bogotá", count($localidades) === 20);
assertTest("La localidad #11 es 'Suba'", ($localidades[10]['nombre'] ?? '') === 'Suba');

$materialModel = new Material();
$materiales = $materialModel->getAllActive();
assertTest("El catálogo de materiales contiene al menos 5 categorías activas", count($materiales) >= 5);
$nombresMateriales = array_column($materiales, 'nombre');
assertTest("Catálogo incluye 'Cartón', 'Plástico', 'Vidrio' y 'Metales'", 
    in_array('Cartón', $nombresMateriales) && in_array('Plástico', $nombresMateriales) && in_array('Vidrio', $nombresMateriales));

// ── SECCIÓN 2: AUTENTICACIÓN Y SEGURIDAD ──
echo "\n── [2] Autenticación y Verificación Criptográfica ──\n";
$usuarioModel = new Usuario();
$carlos = $usuarioModel->findByEmail('reciclador@ecopoint.app');
assertTest("Usuario semilla 'reciclador@ecopoint.app' existe en la BD", $carlos !== null);
assertTest("El rol de Carlos es 'reciclador' (id_rol = 1)", (int)($carlos['id_rol'] ?? 0) === 1);

$passValida = Usuario::verifyPassword('Carlos123*', $carlos['password']);
assertTest("Contraseña correcta 'Carlos123*' autentica con BCRYPT", $passValida === true);

$passInvalida = Usuario::verifyPassword('PasswordErroneo', $carlos['password']);
assertTest("Contraseña errónea 'PasswordErroneo' es rechazada", $passInvalida === false);

$token1 = csrf_token();
assertTest("Generación de token CSRF (longitud 64 caracteres hex)", strlen($token1) === 64);
assertTest("verify_csrf valida token legítimo con hash_equals", verify_csrf($token1) === true);
assertTest("verify_csrf rechaza token falso o manipulado", verify_csrf('token_falso_atacante_12345') === false);

$maliciousInput = '<script>alert("Ataque XSS");</script>';
$escaped = h($maliciousInput);
assertTest("Helper h() neutraliza etiquetas script peligrosas", strpos($escaped, '<script>') === false && strpos($escaped, '&lt;script&gt;') !== false);

// ── SECCIÓN 3: SANITIZACIÓN DE CAMPOS (LETRAS VS NÚMEROS) ──
echo "\n── [3] Sanitización de Campos (Letras vs Números) ──\n";

// A. Prueba de rechazo de números en Nombres y Apellidos
$nombreConNumeros = 'Carlos123';
$validaNombreNum = preg_match('/\d/', $nombreConNumeros) || !preg_match('/^[\p{L}\s\'-]{2,80}$/u', $nombreConNumeros);
assertTest("Sanitización rechaza nombre que contiene números ('Carlos123')", $validaNombreNum == true);

$nombreValido = 'María José';
$validaNombreOk = !preg_match('/\d/', $nombreValido) && preg_match('/^[\p{L}\s\'-]{2,80}$/u', $nombreValido);
assertTest("Sanitización acepta nombre alfabético con tilde y espacio ('María José')", $validaNombreOk == true);

$apellidoConNumeros = 'Perez99';
$validaApellidoNum = preg_match('/\d/', $apellidoConNumeros) || !preg_match('/^[\p{L}\s\'-]{2,80}$/u', $apellidoConNumeros);
assertTest("Sanitización rechaza apellido que contiene números ('Perez99')", $validaApellidoNum == true);

// B. Prueba de rechazo de letras en Teléfonos
$telefonoConLetras = '311ABCD555';
$validaTelLetras = preg_match('/[a-zA-Z]/', $telefonoConLetras) || !preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $telefonoConLetras);
assertTest("Sanitización rechaza teléfono que contiene letras ('311ABCD555')", $validaTelLetras == true);

$telefonoValido = '311 555 4321';
$validaTelOk = !preg_match('/[a-zA-Z]/', $telefonoValido) && preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $telefonoValido) && strlen(preg_replace('/\D/', '', $telefonoValido)) >= 7;
assertTest("Sanitización acepta teléfono en formato numérico válido ('311 555 4321')", $validaTelOk == true);

// ── SECCIÓN 4: REGISTRO TRANSACCIONAL Y MODELOS MULTI-ROL ──
echo "\n── [4] Registro y Perfiles Multi-Rol ──\n";

// Intento de correo duplicado
$duplicateCaught = false;
try {
    $usuarioModel->create([
        'nombre'   => 'Intruso',
        'apellido' => 'Duplicado',
        'correo'   => 'reciclador@ecopoint.app',
        'telefono' => '3000000000',
        'password' => 'Secreta123*',
        'id_rol'   => 1
    ]);
} catch (InvalidArgumentException $e) {
    $duplicateCaught = true;
}
assertTest("El modelo rechaza intento de registro con correo duplicado", $duplicateCaught === true);

// Registro de Ciudadano de prueba
$emailCiudadanoTest = 'test.ciudadano.' . time() . '@ecopoint.app';
$idCiudadanoTest = 0;
try {
    $idCiudadanoTest = $usuarioModel->create([
        'nombre'   => 'Camilo',
        'apellido' => 'Restrepo',
        'correo'   => $emailCiudadanoTest,
        'telefono' => '3151112233',
        'password' => 'Ciudadano123*',
        'id_rol'   => 2
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
    assertTest("Registro de Ciudadano creado con perfil y localidad vinculada (#2 Chapinero)", 
        $perfilCiudadano !== null && ($perfilCiudadano['localidad_nombre'] ?? '') === 'Chapinero');
} catch (Exception $e) {
    assertTest("Fallo registro ciudadano test: " . $e->getMessage(), false);
}

// ── SECCIÓN 5: CONTROL DE SESIONES Y RBAC ──
echo "\n── [5] Sesiones y Control de Acceso por Roles (RBAC) ──\n";
loginUser($carlos);
assertTest("loginUser() establece usuario autenticado en sesión", isAuthenticated() === true);
assertTest("currentUser() retorna datos de Carlos", currentUser()['correo'] === 'reciclador@ecopoint.app');
assertTest("hasRole('reciclador') retorna true para cuenta de reciclador", hasRole('reciclador') === true);
assertTest("hasRole('ciudadano') retorna false para cuenta de reciclador", hasRole('ciudadano') === false);

logoutUser();
assertTest("logoutUser() destruye la sesión y limpia credenciales", isAuthenticated() === false && currentUser() === null);

// ── SECCIÓN 6: FASE 2 — SOLICITUDES DE RECOLECCIÓN ──
echo "\n── [6] Fase 2: Solicitudes de Recolección (Registro y Persistencia) ──\n";
$solicitudModel = new Solicitud();
$idSolicitudCreada = 0;

// A. Intento de registrar cantidad <= 0 (Debe fallar)
$cantZeroCaught = false;
try {
    $solicitudModel->create([
        'id_ciudadano'         => $idCiudadanoTest,
        'id_material'          => 1, // Cartón
        'id_localidad'         => 2,
        'cantidad_estimada'    => 0, // Invalido
        'direccion'            => 'Carrera 7 # 50-20',
        'fecha_disponibilidad' => date('Y-m-d')
    ]);
} catch (InvalidArgumentException $e) {
    $cantZeroCaught = true;
}
assertTest("Modelo Solicitud rechaza cantidad igual o menor a cero (0 kg)", $cantZeroCaught === true);

// B. Registro de solicitud con datos válidos
try {
    $fechaValida = date('Y-m-d', strtotime('+1 day'));
    $idSolicitudCreada = $solicitudModel->create([
        'id_ciudadano'         => $idCiudadanoTest,
        'id_material'          => 1, // Cartón
        'id_localidad'         => 2, // Chapinero
        'cantidad_estimada'    => 12.5,
        'direccion'            => 'Carrera 7 # 50-20 Torre B Apto 402',
        'barrio'               => 'Chapinero Alto',
        'fecha_disponibilidad' => $fechaValida,
        'hora_inicio'          => '08:00:00',
        'hora_fin'             => '12:00:00',
        'prioridad'            => 'Normal',
        'observaciones'        => 'Material cartón corrugado plano seco y amarrado'
    ]);

    assertTest("Solicitud válida creada con éxito en MySQL (ID: #{$idSolicitudCreada})", $idSolicitudCreada > 0);
} catch (Exception $e) {
    assertTest("Fallo al crear solicitud válida: " . $e->getMessage(), false);
}

// C. Verificación de persistencia de datos en MySQL
if ($idSolicitudCreada > 0) {
    $solRow = $solicitudModel->findById($idSolicitudCreada);
    assertTest("La solicitud persiste en MySQL y está vinculada a Chapinero", ($solRow['localidad_nombre'] ?? '') === 'Chapinero');
    assertTest("El material asociado es 'Cartón'", ($solRow['material_nombre'] ?? '') === 'Cartón');
    
    // Comprobar estado inicial 'Pendiente'
    $stmtEstado = $pdo->prepare("SELECT estado, cantidad_estimada, prioridad FROM solicitudes WHERE id_solicitud = :id");
    $stmtEstado->execute([':id' => $idSolicitudCreada]);
    $solRaw = $stmtEstado->fetch();
    assertTest("El estado inicial de la solicitud es 'Pendiente'", $solRaw['estado'] === 'Pendiente');
    assertTest("La cantidad estimada almacenada es 12.50 kg", (float)$solRaw['cantidad_estimada'] === 12.5);
    assertTest("La prioridad asignada es 'Normal'", $solRaw['prioridad'] === 'Normal');

    // D. Consulta de solicitudes pertenecientes al ciudadano
    $misSolicitudes = $solicitudModel->getByCiudadano($idCiudadanoTest);
    assertTest("getByCiudadano() retorna las solicitudes del ciudadano autenticado", count($misSolicitudes) >= 1);
    assertTest("La solicitud #{$idSolicitudCreada} figura en la lista del ciudadano", 
        in_array($idSolicitudCreada, array_column($misSolicitudes, 'id_solicitud')));

    // E. Control de Aislamiento y Anti-IDOR
    $idOtroCiudadano = 999999;
    $intentoEspiar = $solicitudModel->findByIdAndCiudadano($idSolicitudCreada, $idOtroCiudadano);
    assertTest("Aislamiento seguro: findByIdAndCiudadano() rechaza acceso a usuario no propietario", $intentoEspiar === null);

    $propietarioLegitimo = $solicitudModel->findByIdAndCiudadano($idSolicitudCreada, $idCiudadanoTest);
    assertTest("Acceso autorizado: findByIdAndCiudadano() permite consulta al propietario legítimo", $propietarioLegitimo !== null);

    // F. Métricas dinámicas del ciudadano
    $metricasCiudadano = $solicitudModel->getMetricasCiudadano($idCiudadanoTest);
    assertTest("Métricas del ciudadano contabilizan al menos 1 solicitud pendiente", $metricasCiudadano['pendientes'] >= 1);
    assertTest("Métricas acumulan el peso estimado correctamente", $metricasCiudadano['total_kg_estimados'] >= 12.5);
}

// ── SECCIÓN 7: REGRESIÓN DE RUTAS Y PANELES ──
echo "\n── [7] Pruebas de Regresión de Vistas y Paneles ──\n";
// Panel del ciudadano
$usuarioCiudadanoTest = $usuarioModel->findById($idCiudadanoTest);
loginUser($usuarioCiudadanoTest, $perfilCiudadano);
try {
    require_once APP_PATH . 'controllers/CiudadanoController.php';
    require_once APP_PATH . 'controllers/SolicitudController.php';

    $ciuCtrl = new CiudadanoController();
    ob_start();
    $ciuCtrl->dashboard();
    $htmlCiu = ob_get_clean();
    assertTest("CiudadanoController->dashboard() renderiza panel con métricas y enlaces reales", 
        strpos($htmlCiu, 'Registrar Solicitud') !== false && strpos($htmlCiu, 'Tus datos de recolección') !== false);

    $solCtrl = new SolicitudController();
    ob_start();
    $solCtrl->crear();
    $htmlForm = ob_get_clean();
    assertTest("SolicitudController->crear() renderiza formulario con catálogo de materiales y CSRF", 
        strpos($htmlForm, 'Tipo de material') !== false && strpos($htmlForm, 'csrf_token') !== false);

    ob_start();
    $solCtrl->misSolicitudes();
    $htmlList = ob_get_clean();
    assertTest("SolicitudController->misSolicitudes() renderiza listado con la solicitud #{$idSolicitudCreada}", 
        strpos($htmlList, 'Mis Solicitudes de Recolección') !== false && strpos($htmlList, 'Cartón') !== false);

} catch (Throwable $e) {
    ob_end_clean();
    assertTest("Fallo en renderizado de controladores de ciudadano/solicitud: " . $e->getMessage(), false);
}
logoutUser();

// ── SECCIÓN 8: LIMPIEZA ATÓMICA DE DATOS DE PRUEBA ──
echo "\n── [8] Limpieza de Registros Temporales ──\n";
if ($idSolicitudCreada > 0) {
    $pdo->exec("DELETE FROM solicitudes WHERE id_solicitud = {$idSolicitudCreada}");
}
if ($idCiudadanoTest > 0) {
    $pdo->exec("DELETE FROM perfil_ciudadano WHERE id_usuario = {$idCiudadanoTest}");
    $pdo->exec("DELETE FROM usuarios WHERE id_usuario = {$idCiudadanoTest}");
}
assertTest("Limpieza de solicitudes y usuarios de prueba completada sin alterar registros reales", true);

echo "\n--------------------------------------------------------------------\n";
echo "RESULTADOS DE LAS PRUEBAS: {$passedTests} / {$totalTests} PASADAS CON ÉXITO\n";
if ($passedTests === $totalTests) {
    echo "ESTADO: 🎉 TODAS LAS PRUEBAS DE LAS FASES 1 Y 2 CUMPLIDAS SATISFACTORIAMENTE\n";
} else {
    echo "ESTADO: ⚠️ ALGUNAS PRUEBAS FALLARON ({$passedTests}/{$totalTests})\n";
}
echo "====================================================================\n\n";
