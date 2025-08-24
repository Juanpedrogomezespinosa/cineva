<?php
require_once __DIR__ . '/auth.php';

/**
 * Obtiene todos los chats del usuario
 */
function obtenerChats(PDO $db, int $usuarioId): array {
    $stmt = $db->prepare("
        SELECT 
            u.id, u.nombre, u.avatar,
            (SELECT mensaje FROM mensajes 
             WHERE (emisor_id = u.id AND receptor_id = :uid) 
                OR (emisor_id = :uid AND receptor_id = u.id)
             ORDER BY creado_en DESC LIMIT 1) AS ultimo_mensaje,
            (SELECT COUNT(*) FROM mensajes 
             WHERE emisor_id = u.id AND receptor_id = :uid AND leido = 0) AS no_leidos
        FROM usuarios u
        WHERE u.id != :uid AND EXISTS (
            SELECT 1 FROM mensajes 
            WHERE (emisor_id = u.id AND receptor_id = :uid)
               OR (emisor_id = :uid AND receptor_id = u.id)
        )
        ORDER BY 
            (SELECT MAX(creado_en) FROM mensajes 
             WHERE (emisor_id = u.id AND receptor_id = :uid)
                OR (emisor_id = :uid AND receptor_id = u.id)
            ) DESC
    ");
    $stmt->execute([':uid' => $usuarioId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene mensajes entre dos usuarios
 */
function obtenerMensajes(PDO $db, int $usuario1, int $usuario2): array {
    $stmt = $db->prepare("
        SELECT m.*, u.nombre, u.avatar 
        FROM mensajes m
        JOIN usuarios u ON m.emisor_id = u.id
        WHERE (emisor_id = :u1 AND receptor_id = :u2) 
           OR (emisor_id = :u2 AND receptor_id = :u1)
        ORDER BY creado_en ASC
    ");
    $stmt->execute([':u1' => $usuario1, ':u2' => $usuario2]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Envía un mensaje
 */
function enviarMensaje(PDO $db, int $emisor, int $receptor, string $mensaje): bool {
    $stmt = $db->prepare("
        INSERT INTO mensajes (emisor_id, receptor_id, mensaje) 
        VALUES (:emisor, :receptor, :mensaje)
    ");
    return $stmt->execute([
        ':emisor'   => $emisor,
        ':receptor' => $receptor,
        ':mensaje'  => $mensaje
    ]);
}

/**
 * Marca mensajes como leídos
 */
function marcarMensajesComoLeidos(PDO $db, int $emisor, int $receptor): void {
    $stmt = $db->prepare("
        UPDATE mensajes 
        SET leido = 1 
        WHERE emisor_id = :emisor AND receptor_id = :receptor AND leido = 0
    ");
    $stmt->execute([
        ':emisor'   => $emisor,
        ':receptor' => $receptor
    ]);
}
