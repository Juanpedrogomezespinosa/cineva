<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$db = new Database();
$pdo = $db->getConnection();
$usuario_id = $_SESSION['usuario_id'];

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: dashboard.php');
    exit;
}

// Comprobar que la película pertenece al usuario
$stmt = $pdo->prepare("SELECT portada FROM peliculas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $usuario_id]);
$pelicula = $stmt->fetch();

if (!$pelicula) {
    header('Location: dashboard.php');
    exit;
}

// Si confirma eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Borrar portada si existe
    if ($pelicula['portada'] && file_exists('img/portadas/' . $pelicula['portada'])) {
        unlink('img/portadas/' . $pelicula['portada']);
    }

    // Borrar registro
    $stmt = $pdo->prepare("DELETE FROM peliculas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);

    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Eliminar película | Cineva</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<h1>Eliminar película</h1>
<p>¿Seguro que quieres eliminar esta película?</p>

<form method="POST" action="eliminar.php?id=<?php echo $id; ?>">
    <button type="submit">Sí, eliminar</button>
    <a href="dashboard.php">Cancelar</a>
</form>
</body>
</html>
