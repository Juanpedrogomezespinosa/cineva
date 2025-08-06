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

// Obtenemos la película, permitiendo que se vea cualquiera
$stmt = $pdo->prepare("SELECT p.*, u.nombre as usuario_nombre FROM peliculas p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$pelicula = $stmt->fetch();

if (!$pelicula) {
    header('Location: dashboard.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Detalles de película | Cineva</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<h1><?php echo htmlspecialchars($pelicula['titulo']); ?></h1>

<p><strong>Género:</strong> <?php echo htmlspecialchars($pelicula['genero']); ?></p>
<p><strong>Plataforma:</strong> <?php echo htmlspecialchars($pelicula['plataforma']); ?></p>
<p><strong>Visto:</strong> <?php echo $pelicula['visto'] ? 'Sí' : 'No'; ?></p>
<p><strong>Favorito:</strong> <?php echo $pelicula['favorito'] ? 'Sí' : 'No'; ?></p>
<p><strong>Valoración:</strong> <?php echo (int)$pelicula['valoracion']; ?> / 5</p>
<p><strong>Reseña:</strong> <?php echo nl2br(htmlspecialchars($pelicula['resena'])); ?></p>
<p><strong>Agregada por:</strong> <?php echo htmlspecialchars($pelicula['usuario_nombre']); ?></p>
<p><strong>Fecha de agregado:</strong> <?php echo $pelicula['fecha_agregado']; ?></p>

<?php if ($pelicula['portada']): ?>
    <img src="img/portadas/<?php echo htmlspecialchars($pelicula['portada']); ?>" alt="Portada" style="max-width:200px;" />
<?php endif; ?>

<br /><br />
<a href="dashboard.php">Volver al dashboard</a>
</body>
</html>
