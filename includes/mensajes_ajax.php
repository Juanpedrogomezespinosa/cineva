<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configuración y dependencias usando rutas absolutas
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$usuarioActual = $_SESSION['usuario_id'] ?? null;
if (!$usuarioActual) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// POST: enviar mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receptor = isset($_POST['receptor_id']) ? (int)$_POST['receptor_id'] : null;
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (!$receptor || $mensaje === '') {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO mensajes (emisor_id, receptor_id, mensaje) VALUES (:emisor, :receptor, :mensaje)");
    $resultado = $stmt->execute([
        ':emisor'   => $usuarioActual,
        ':receptor' => $receptor,
        ':mensaje'  => $mensaje
    ]);

    if ($resultado) {
        $ultimoId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT m.*, u.nombre FROM mensajes m JOIN usuarios u ON m.emisor_id = u.id WHERE m.id = :id");
        $stmt->execute([':id' => $ultimoId]);
        $mensajeInsertado = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success'    => true,
            'mensaje'    => $mensajeInsertado['mensaje'],
            'nombre'     => $mensajeInsertado['nombre'],
            'creado_en'  => $mensajeInsertado['creado_en']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al enviar mensaje']);
    }
    exit;
}

// GET: cargar mensajes
$receptor = isset($_GET['receptor_id']) ? (int)$_GET['receptor_id'] : null;
if (!$receptor) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("
    SELECT m.*, u.nombre 
    FROM mensajes m
    JOIN usuarios u ON m.emisor_id = u.id
    WHERE (emisor_id = :u1 AND receptor_id = :u2)
       OR (emisor_id = :u2 AND receptor_id = :u1)
    ORDER BY creado_en ASC
");
$stmt->execute([
    ':u1' => $usuarioActual,
    ':u2' => $receptor
]);

$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($mensajes);
