<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/follows.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if (!isset($_POST['usuario_id'], $_POST['accion'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetros incorrectos']);
    exit;
}

$usuario_id = (int) $_POST['usuario_id'];
$accion = $_POST['accion'];
$usuario_actual = (int) $_SESSION['usuario_id'];

$follows = new Follows();
$db = new Database();
$pdo = $db->getConnection();

try {
    if ($accion === 'seguir') {
        $ok = $follows->seguirUsuario($usuario_actual, $usuario_id);

        if ($ok) {
            // Crear notificación de seguimiento
            $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, origen_id, relacion_id) 
                                   VALUES (?, 'seguimiento', ?, NULL)");
            $stmt->execute([$usuario_id, $usuario_actual]);
        }

        echo json_encode(['success' => $ok]);
    } elseif ($accion === 'dejar') {
        $ok = $follows->dejarDeSeguirUsuario($usuario_actual, $usuario_id);
        echo json_encode(['success' => $ok]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción desconocida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
