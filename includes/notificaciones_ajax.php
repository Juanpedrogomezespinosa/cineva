<?php
// includes/notificaciones_ajax.php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Si no hay sesión válida devolvemos un array vacío
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Consulta robusta: obtenemos las últimas 10 notificaciones del usuario destinatario.
    // Calculamos si "yo" (usuario autenticado) ya sigo al origen usando EXISTS.
    // Obtenemos el posible comentario relacionado (si existe) mediante LEFT JOIN,
    // pero devolvemos el campo de pelicula condicionando por tipo.
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
            CASE WHEN n.tipo = 'comentario' THEN c.pelicula_id ELSE NULL END AS comentario_pelicula_id,
            EXISTS (
                SELECT 1
                FROM seguidores s
                WHERE s.seguidor_id = :me AND s.seguido_id = n.origen_id
            ) AS ya_sigues
        FROM notificaciones n
        JOIN usuarios u ON u.id = n.origen_id
        LEFT JOIN comentarios c ON c.id = n.relacion_id
        WHERE n.usuario_id = :destinatario
        ORDER BY n.creado_en DESC
        LIMIT 10
    ");

    $consulta->bindValue(':me', $usuario_id, PDO::PARAM_INT);
    $consulta->bindValue(':destinatario', $usuario_id, PDO::PARAM_INT);
    $consulta->execute();

    $notificaciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Normalizar tipos y campos esperados por el front-end
    foreach ($notificaciones as &$notificacion) {
        // Asegurarnos de que los campos existen
        $notificacion['id'] = isset($notificacion['id']) ? (int)$notificacion['id'] : null;
        $notificacion['tipo'] = $notificacion['tipo'] ?? '';
        $notificacion['leido'] = isset($notificacion['leido']) ? (int)$notificacion['leido'] : 0;
        $notificacion['creado_en'] = $notificacion['creado_en'] ?? null;
        $notificacion['relacion_id'] = isset($notificacion['relacion_id']) ? (int)$notificacion['relacion_id'] : null;
        $notificacion['origen_id'] = isset($notificacion['origen_id']) ? (int)$notificacion['origen_id'] : null;
        $notificacion['origen_nombre'] = $notificacion['origen_nombre'] ?? '';
        $notificacion['origen_avatar'] = $notificacion['origen_avatar'] ?? '';
        $notificacion['comentario_pelicula_id'] = isset($notificacion['comentario_pelicula_id']) && $notificacion['comentario_pelicula_id'] !== null
            ? (int)$notificacion['comentario_pelicula_id']
            : null;
        // ya_sigues viene como 0/1 por el EXISTS; forzamos int 0/1
        $notificacion['ya_sigues'] = isset($notificacion['ya_sigues']) && $notificacion['ya_sigues'] ? 1 : 0;
    }
    unset($notificacion);

    echo json_encode($notificaciones);
    exit;

} catch (Exception $error) {
    http_response_code(500);
    // Para no filtrar detalles sensibles en producción, puedes cambiar el mensaje
    echo json_encode(['error' => 'Error al obtener notificaciones: ' . $error->getMessage()]);
    exit;
}
