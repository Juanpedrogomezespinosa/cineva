<?php
require_once __DIR__ . '/db.php';

class Amistad {

    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    // Seguir a un usuario
    public function seguir($seguidor_id, $seguido_id) {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO amistades (seguidor_id, seguido_id) VALUES (?, ?)");
        return $stmt->execute([$seguidor_id, $seguido_id]);
    }

    // Dejar de seguir
    public function dejarDeSeguir($seguidor_id, $seguido_id) {
        $stmt = $this->pdo->prepare("DELETE FROM amistades WHERE seguidor_id = ? AND seguido_id = ?");
        return $stmt->execute([$seguidor_id, $seguido_id]);
    }

    // Obtener seguidores
    public function obtenerSeguidores($usuario_id) {
        $stmt = $this->pdo->prepare("SELECT u.id, u.nombre FROM amistades a JOIN usuarios u ON a.seguidor_id = u.id WHERE a.seguido_id = ?");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener seguidos
    public function obtenerSeguidos($usuario_id) {
        $stmt = $this->pdo->prepare("SELECT u.id, u.nombre FROM amistades a JOIN usuarios u ON a.seguido_id = u.id WHERE a.seguidor_id = ?");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
