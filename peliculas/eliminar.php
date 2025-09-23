<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();
$usuario_id = $_SESSION['usuario_id'];

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

// Comprobar que la película pertenece al usuario
$stmt = $pdo->prepare("SELECT portada FROM peliculas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $usuario_id]);
$pelicula = $stmt->fetch();

if (!$pelicula) {
    echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar esta película']);
    exit;
}

// Borrar portada si existe
if (!empty($pelicula['portada'])) {
    $rutaPortada = __DIR__ . '/../img/portadas/' . $pelicula['portada'];
    if (file_exists($rutaPortada)) {
        unlink($rutaPortada);
    }
}

// Borrar registro
$stmt = $pdo->prepare("DELETE FROM peliculas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $usuario_id]);

echo json_encode(['success' => true, 'id' => $id]);
exit;
