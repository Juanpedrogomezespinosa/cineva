<?php
require_once __DIR__ . '/db.php';

class Follows {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function seguirUsuario($followerId, $followedId) {
        if ($followerId == $followedId) return false;
        if ($this->esSeguidor($followerId, $followedId)) return false;

        $stmt = $this->pdo->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
        return $stmt->execute([$followerId, $followedId]);
    }

    public function dejarDeSeguirUsuario($followerId, $followedId) {
        $stmt = $this->pdo->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        return $stmt->execute([$followerId, $followedId]);
    }

    public function esSeguidor($followerId, $followedId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $stmt->execute([$followerId, $followedId]);
        return $stmt->fetchColumn() > 0;
    }

    public function contarSeguidores($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguido_id = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchColumn();
    }

    public function contarSeguidos($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchColumn();
    }

    public function obtenerSeguidores($usuarioId) {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.nombre, u.avatar
            FROM seguidores s
            JOIN usuarios u ON s.seguidor_id = u.id
            WHERE s.seguido_id = ?
            ORDER BY u.nombre ASC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeguidos($usuarioId) {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.nombre, u.avatar
            FROM seguidores s
            JOIN usuarios u ON s.seguido_id = u.id
            WHERE s.seguidor_id = ?
            ORDER BY u.nombre ASC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
