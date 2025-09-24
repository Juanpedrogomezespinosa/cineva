<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/**
 * Obtiene todos los chats del usuario, con último mensaje y mensajes no leídos
 */
function obtenerChats(PDO $db, int $usuarioId): array
{
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.nombre,
            u.avatar,
            (
                SELECT m1.mensaje
                FROM mensajes m1
                WHERE (m1.emisor_id = u.id AND m1.receptor_id = :uid1)
                   OR (m1.emisor_id = :uid2 AND m1.receptor_id = u.id)
                ORDER BY m1.creado_en DESC
                LIMIT 1
            ) AS ultimo_mensaje,
            (
                SELECT COUNT(*)
                FROM mensajes m2
                WHERE m2.emisor_id = u.id AND m2.receptor_id = :uid3 AND m2.leido = 0
            ) AS no_leidos
        FROM usuarios u
        WHERE u.id != :uid4
          AND EXISTS (
            SELECT 1
            FROM mensajes m3
            WHERE (m3.emisor_id = u.id AND m3.receptor_id = :uid5)
               OR (m3.emisor_id = :uid6 AND m3.receptor_id = u.id)
          )
        ORDER BY (
            SELECT MAX(m4.creado_en)
            FROM mensajes m4
            WHERE (m4.emisor_id = u.id AND m4.receptor_id = :uid7)
               OR (m4.emisor_id = :uid8 AND m4.receptor_id = u.id)
        ) DESC
    ");

    $stmt->execute([
        ':uid1' => $usuarioId,
        ':uid2' => $usuarioId,
        ':uid3' => $usuarioId,
        ':uid4' => $usuarioId,
        ':uid5' => $usuarioId,
        ':uid6' => $usuarioId,
        ':uid7' => $usuarioId,
        ':uid8' => $usuarioId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene mensajes entre dos usuarios
 */
function obtenerMensajes(PDO $db, int $usuario1, int $usuario2): array
{
    $stmt = $db->prepare("
        SELECT m.id, m.mensaje, m.creado_en, m.emisor_id, m.receptor_id,
               u.nombre, u.avatar
        FROM mensajes m
        JOIN usuarios u ON m.emisor_id = u.id
        WHERE (m.emisor_id = :usuario1 AND m.receptor_id = :usuario2)
           OR (m.emisor_id = :usuario2 AND m.receptor_id = :usuario1)
        ORDER BY m.creado_en ASC
    ");

    $stmt->execute([
        ':usuario1' => $usuario1,
        ':usuario2' => $usuario2
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Envía un mensaje
 */
function enviarMensaje(PDO $db, int $emisor, int $receptor, string $mensaje): bool
{
    $stmt = $db->prepare("
        INSERT INTO mensajes (emisor_id, receptor_id, mensaje, leido) 
        VALUES (:emisor, :receptor, :mensaje, 0)
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
function marcarMensajesComoLeidos(PDO $db, int $emisor, int $receptor): void
{
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
