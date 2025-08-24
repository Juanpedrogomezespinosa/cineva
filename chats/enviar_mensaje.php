<?php
require_once 'auth.php';

/**
 * Devuelve todos los mensajes entre dos usuarios.
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
    $stmt->execute([
        ':u1' => $usuario1,
        ':u2' => $usuario2
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Inserta un nuevo mensaje en la base de datos.
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
