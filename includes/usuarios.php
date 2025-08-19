<?php
require_once __DIR__ . '/db.php';

class Usuarios {

    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrar($nombre, $email, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$nombre, $email, $hash]);
    }
}
?>
