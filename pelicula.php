<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
require_once "templates/header.php";
require_once "templates/navbar.php";

// Obtener ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM peliculas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $pelicula = $stmt->get_result()->fetch_assoc();
}
?>

<main class="container">
    <?php if (!empty($pelicula)): ?>
        <h2><?= htmlspecialchars($pelicula['titulo']) ?></h2>
        <p><strong>Género:</strong> <?= htmlspecialchars($pelicula['genero']) ?></p>
        <p><strong>Plataforma:</strong> <?= htmlspecialchars($pelicula['plataforma']) ?></p>
        <p><strong>Valoración:</strong> <?= $pelicula['valoracion'] ?>/5</p>
        <p><strong>Reseña:</strong> <?= nl2br(htmlspecialchars($pelicula['reseña'])) ?></p>
        <?php if (!empty($pelicula['portada'])): ?>
            <img src="img/portadas/<?= htmlspecialchars($pelicula['portada']) ?>" alt="Portada">
        <?php endif; ?>
    <?php else: ?>
        <p>Película no encontrada.</p>
    <?php endif; ?>
</main>

<?php require_once "templates/footer.php"; ?>
