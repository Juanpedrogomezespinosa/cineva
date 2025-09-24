<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/mensajes.php';

session_start();
$currentUser = $_SESSION['usuario_id'] ?? null;
$chatUser = $_GET['usuario'] ?? null;

if (!$currentUser || !$chatUser) {
    echo json_encode([]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$mensajes = obtenerMensajes($db, $currentUser, $chatUser);
echo json_encode($mensajes);
