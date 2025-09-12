<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Usuario actual o un id pasado por GET (opcional)
$usuario_id = isset($_GET['id']) ? (int) $_GET['id'] : ($_SESSION['usuario_id'] ?? null);

if (!$usuario_id) {
    echo json_encode(['seguidores' => 0]);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("SELECT COUNT(*) AS seguidores FROM seguidores WHERE seguido_id = ?");
    $stmt->execute([$usuario_id]);
    $seguidores = (int) $stmt->fetchColumn();

    echo json_encode(['seguidores' => $seguidores]);
} catch (Exception $e) {
    echo json_encode(['seguidores' => 0, 'error' => $e->getMessage()]);
}
