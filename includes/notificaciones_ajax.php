<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Obtener últimas 10 notificaciones del usuario e indicar si ya sigue al origen
    $consulta = $pdo->prepare("
        SELECT 
            n.id,
            n.tipo,
            n.leido,
            n.creado_en,
            n.relacion_id,
            n.origen_id,
            u.nombre AS origen_nombre,
            u.avatar AS origen_avatar,
            c.pelicula_id AS comentario_pelicula_id,
            (s.seguidor_id IS NOT NULL) AS ya_sigues
        FROM notificaciones n
        JOIN usuarios u ON u.id = n.origen_id
        LEFT JOIN comentarios c ON c.id = n.relacion_id AND n.tipo = 'comentario'
        LEFT JOIN seguidores s ON s.seguidor_id = ? AND s.seguido_id = n.origen_id
        WHERE n.usuario_id = ?
        ORDER BY n.creado_en DESC
        LIMIT 10
    ");

    $consulta->execute([$usuario_id, $usuario_id]);
    $notificaciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Normalizar campos
    foreach ($notificaciones as &$notif) {
        if ($notif['tipo'] === 'seguimiento') {
            $notif['comentario_pelicula_id'] = null;
            $notif['ya_sigues'] = (int) $notif['ya_sigues'];
        } else {
            $notif['ya_sigues'] = 0;
        }
    }

    echo json_encode($notificaciones);

} catch (Exception $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()]);
}
