<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Debes iniciar sesión para votar.']);
    exit;
}

$pelicula_id = (int)($_POST['pelicula_id'] ?? 0);
$estrellas = (int)($_POST['estrellas'] ?? 0);

if ($pelicula_id <= 0 || $estrellas < 1 || $estrellas > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos.']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("
    INSERT INTO votos (usuario_id, pelicula_id, estrellas)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE estrellas = VALUES(estrellas), fecha = CURRENT_TIMESTAMP
");
$stmt->execute([$usuario_id, $pelicula_id, $estrellas]);

$stmtMedia = $pdo->prepare("SELECT AVG(estrellas) AS media, COUNT(*) AS total FROM votos WHERE pelicula_id = ?");
$stmtMedia->execute([$pelicula_id]);
$stats = $stmtMedia->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'media' => round($stats['media'], 2),
    'total' => (int)$stats['total'],
    'usuario_id' => $usuario_id,
    'pelicula_id' => $pelicula_id,
    'estrellas' => $estrellas
]);
