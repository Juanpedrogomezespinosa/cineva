<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario_id) {
    $comentario_id = (int)($_POST['id'] ?? 0);
    $nuevo_texto   = trim($_POST['comentario'] ?? '');

    if ($comentario_id && $nuevo_texto !== '') {
        $db = new Database();
        $pdo = $db->getConnection();

        // Validar que el comentario pertenece al usuario
        $stmt = $pdo->prepare("SELECT usuario_id FROM comentarios WHERE id = ?");
        $stmt->execute([$comentario_id]);
        $com = $stmt->fetch();

        if ($com && $com['usuario_id'] == $usuario_id) {
            $stmtUpd = $pdo->prepare("UPDATE comentarios SET comentario = ? WHERE id = ?");
            $stmtUpd->execute([$nuevo_texto, $comentario_id]);

            echo json_encode(['success' => true, 'comentario' => nl2br(htmlspecialchars($nuevo_texto))]);
            exit;
        }
    }
}

echo json_encode(['success' => false]);
