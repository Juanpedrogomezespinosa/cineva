<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];
$notificacion_id = (int) $_GET['id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$notificacion_id, $usuario_id]);

    // Redirigir al destino original
    if (isset($_GET['url'])) {
        $url_relativa = urldecode($_GET['url']);
$redireccion = strpos($url_relativa, '/') === 0
    ? APP_URL . ltrim($url_relativa, '/')
    : APP_URL . $url_relativa;

header("Location: " . $redireccion);

    } else {
        header("Location: ../dashboard.php");
    }
    exit;
} catch (Exception $e) {
    header("Location: ../dashboard.php");
    exit;
}
