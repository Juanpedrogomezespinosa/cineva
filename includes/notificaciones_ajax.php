<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = (int) $_SESSION['usuario_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("
        SELECT n.id, n.tipo, n.leido, n.creado_en, u.nombre AS origen_nombre
        FROM notificaciones n
        JOIN usuarios u ON u.id = n.origen_id
        WHERE n.usuario_id = ?
        ORDER BY n.creado_en DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
