<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario_id) {
    $comentario_id = (int)($_POST['id'] ?? 0);

    if ($comentario_id) {
        $db = new Database();
        $pdo = $db->getConnection();

        // Validar que el comentario pertenece al usuario
        $stmt = $pdo->prepare("SELECT usuario_id FROM comentarios WHERE id = ?");
        $stmt->execute([$comentario_id]);
        $com = $stmt->fetch();

        if ($com && $com['usuario_id'] == $usuario_id) {
            $stmtDel = $pdo->prepare("DELETE FROM comentarios WHERE id = ?");
            $stmtDel->execute([$comentario_id]);

            echo json_encode(['success' => true]);
            exit;
        }
    }
}

echo json_encode(['success' => false]);
