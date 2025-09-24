<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];
$notificacion_id = (int) $_GET['id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $consulta = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_id = ?");
    $consulta->execute([$notificacion_id, $usuario_id]);

    // Redirigir al destino original si viene en la URL
    if (isset($_GET['url'])) {
        $url_relativa = urldecode($_GET['url']);

        // Validar que sea una ruta interna permitida
        if (preg_match('/^(peliculas\/|usuarios\/)/', $url_relativa)) {
            $redireccion = APP_URL . $url_relativa;
            header("Location: " . $redireccion);
            exit;
        }
    }

    // Si no hay destino válido, vuelve al dashboard
    header("Location: ../dashboard.php");
    exit;

} catch (Exception $error) {
    header("Location: ../dashboard.php");
    exit;
}
