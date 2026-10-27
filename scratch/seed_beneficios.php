<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
$db = Database::getConnection();

echo "Actualizando catálogo de beneficios...\n";

// Beneficios completos según requerimientos e imágenes del usuario:
// - Herramientas / Elementos de trabajo
// - Alimentos / Alimentación
// - Conectividad / Recarga
// - Limpieza / Productos de limpieza
// - Capacitación / Descuentos

$beneficios = [
    [
        'id' => 1,
        'nombre' => 'Kit de Protección y Seguridad Industrial',
        'descripcion' => 'Guantes de nitrilo reforzados, chaleco reflectivo reglamentario, gafas de protección y botas.',
        'costo' => 800,
        'stock' => 50,
        'categoria' => 'Trabajo',
        'icono' => 'fas fa-hard-hat'
    ],
    [
        'id' => 2,
        'nombre' => 'Bono de Alimentación Canasta Básica ($50.000)',
        'descripcion' => 'Bono digital redimible en supermercados D1 / Éxito por alimentos de primera necesidad.',
        'costo' => 1500,
        'stock' => 60,
        'categoria' => 'Alimentos',
        'icono' => 'fas fa-utensils'
    ],
    [
        'id' => 3,
        'nombre' => 'Accesorio Carro Recolección CarroMax',
        'descripcion' => 'Par de llantas industriales macizas reforzadas + cinta reflectiva 3M para triciclo o carreta.',
        'costo' => 3000,
        'stock' => 25,
        'categoria' => 'Trabajo',
        'icono' => 'fas fa-tools'
    ],
    [
        'id' => 4,
        'nombre' => 'Recarga de Conectividad Móvil (15 GB + Ilimitado)',
        'descripcion' => 'Paquete prepago de 15GB navegación + llamadas y WhatsApp ilimitado por 30 días.',
        'costo' => 800,
        'stock' => 120,
        'categoria' => 'Conectividad',
        'icono' => 'fas fa-mobile-alt'
    ],
    [
        'id' => 5,
        'nombre' => 'Mercado Básico Familiar MercaVerde',
        'descripcion' => 'Canasta completa de granos, arroz, aceite, legumbres y productos frescos de la sabana.',
        'costo' => 2000,
        'stock' => 40,
        'categoria' => 'Alimentos',
        'icono' => 'fas fa-shopping-basket'
    ],
    [
        'id' => 6,
        'nombre' => 'Kit de Productos de Limpieza y Desinfección',
        'descripcion' => 'Galón de desinfectante biodegradable, jabón industrial antibacterial, guantes y paños microfibra.',
        'costo' => 1100,
        'stock' => 45,
        'categoria' => 'Limpieza',
        'icono' => 'fas fa-pump-soap'
    ],
    [
        'id' => 7,
        'nombre' => 'Capacitación en Reciclaje y Seguridad AcademiaPro',
        'descripcion' => 'Curso certificado sobre manejo técnico de materiales reciclables y economía circular.',
        'costo' => 1200,
        'stock' => 100,
        'categoria' => 'Descuentos',
        'icono' => 'fas fa-graduation-cap'
    ],
    [
        'id' => 8,
        'nombre' => 'Báscula Digital Portátil de Gancho (50 kg)',
        'descripcion' => 'Báscula de alta precisión con pantalla LCD retroiluminada para pesaje en ruta.',
        'costo' => 2500,
        'stock' => 30,
        'categoria' => 'Trabajo',
        'icono' => 'fas fa-weight-hanging'
    ]
];

$sql = "INSERT INTO beneficios (id_beneficio, nombre, descripcion, costo_ecopuntos, stock, categoria, icono, estado)
        VALUES (:id, :nombre, :descripcion, :costo, :stock, :categoria, :icono, 'activo')
        ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre),
            descripcion = VALUES(descripcion),
            costo_ecopuntos = VALUES(costo_ecopuntos),
            stock = VALUES(stock),
            categoria = VALUES(categoria),
            icono = VALUES(icono),
            estado = 'activo'";

$stmt = $db->prepare($sql);
foreach ($beneficios as $b) {
    $stmt->execute([
        ':id' => $b['id'],
        ':nombre' => $b['nombre'],
        ':descripcion' => $b['descripcion'],
        ':costo' => $b['costo'],
        ':stock' => $b['stock'],
        ':categoria' => $b['categoria'],
        ':icono' => $b['icono']
    ]);
}

echo "Catálogo de beneficios actualizado con 8 ítems completos en las categorías requeridas.\n";
