<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $consulta = $pdo->prepare("
        SELECT 
            n.id,
            n.tipo,
            n.leido,
            n.creado_en,
            n.relacion_id,
            n.origen_id,
            u.nombre AS origen_nombre,
            c.pelicula_id AS comentario_pelicula_id
        FROM notificaciones n
        JOIN usuarios u ON u.id = n.origen_id
        LEFT JOIN comentarios c ON c.id = n.relacion_id AND n.tipo = 'comentario'
        WHERE n.usuario_id = ?
        ORDER BY n.creado_en DESC
        LIMIT 10
    ");
    $consulta->execute([$usuario_id]);

    echo json_encode($consulta->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()]);
}
