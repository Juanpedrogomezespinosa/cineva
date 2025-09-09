<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
