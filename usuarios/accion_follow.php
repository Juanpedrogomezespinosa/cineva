<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/follows.php';

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

$follows = new Follows();

try {
    if ($accion === 'seguir') {
        $ok = $follows->seguirUsuario($_SESSION['usuario_id'], $usuario_id);
        echo json_encode(['success' => $ok]);
    } elseif ($accion === 'dejar') {
        $ok = $follows->dejarDeSeguirUsuario($_SESSION['usuario_id'], $usuario_id);
        echo json_encode(['success' => $ok]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción desconocida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
