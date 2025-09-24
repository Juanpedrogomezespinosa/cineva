<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mensajes.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$usuarioActual = $_SESSION['usuario_id'] ?? null;
if (!$usuarioActual) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$db = (new Database())->getConnection();

// Avatar del usuario actual
$stmtAvatarActual = $db->prepare("SELECT avatar FROM usuarios WHERE id = :uid");
$stmtAvatarActual->execute([':uid' => $usuarioActual]);
$avatarUsuarioActual = $stmtAvatarActual->fetchColumn() ?: 'default.png';

// ------------------------
// CONSULTA DE MENSAJES SIN LEER
// ------------------------
if (isset($_GET['check_no_leidos'])) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) AS no_leidos FROM mensajes WHERE receptor_id = :uid AND leido = 0");
        $stmt->execute([':uid' => $usuarioActual]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'no_leidos' => (int) $resultado['no_leidos']
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'no_leidos' => 0, 'error' => $e->getMessage()]);
    }
    exit;
}

// ------------------------
// ENVÍO DE MENSAJE (POST)
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receptor = isset($_POST['receptor_id']) ? (int)$_POST['receptor_id'] : null;
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (!$receptor || $mensaje === '') {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    try {
        $resultado = enviarMensaje($db, $usuarioActual, $receptor, $mensaje);

        if ($resultado) {
            $ultimoId = (int)$db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM mensajes WHERE id = :id");
            $stmt->execute([':id' => $ultimoId]);
            $mensajeInsertado = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'mensaje' => $mensajeInsertado['mensaje'],
                'nombre' => 'Tú',
                'creado_en' => $mensajeInsertado['creado_en'],
                'id' => (int)$mensajeInsertado['id'],
                'avatar_usuario_actual' => $avatarUsuarioActual
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al enviar mensaje']);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Excepción al enviar mensaje',
            'detalle' => $e->getMessage()
        ]);
    }

    exit;
}

// ------------------------
// OBTENER MENSAJES (GET normal)
// ------------------------
$receptor = isset($_GET['receptor_id']) ? (int)$_GET['receptor_id'] : null;
$ultimoId = isset($_GET['ultimo_id']) ? (int)$_GET['ultimo_id'] : 0;

if (!$receptor) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT m.id, m.mensaje, m.creado_en, m.emisor_id, u.nombre, u.avatar
        FROM mensajes m
        JOIN usuarios u ON m.emisor_id = u.id
        WHERE ((m.emisor_id = :emisor1 AND m.receptor_id = :receptor1)
            OR (m.emisor_id = :emisor2 AND m.receptor_id = :receptor2))
          AND m.id > :ultimoId
        ORDER BY m.id ASC
    ");

    $stmt->execute([
        ':emisor1' => $usuarioActual,
        ':receptor1' => $receptor,
        ':emisor2' => $receptor,
        ':receptor2' => $usuarioActual,
        ':ultimoId' => $ultimoId
    ]);

    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marcar mensajes como leídos
    marcarMensajesComoLeidos($db, $receptor, $usuarioActual);

    // Agregar avatar_usuario_actual para tus propios mensajes
    foreach ($mensajes as &$mensaje) {
        if ((int)$mensaje['emisor_id'] === $usuarioActual) {
            $mensaje['avatar_usuario_actual'] = $avatarUsuarioActual;
        }
    }

    echo json_encode($mensajes ?: []);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener mensajes',
        'detalle' => $e->getMessage()
    ]);
}
