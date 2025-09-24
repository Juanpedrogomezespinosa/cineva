<?php
require_once "includes/db.php";
require_once "includes/funciones.php";
require_once "templates/header.php";
require_once "templates/navbar.php";

// Obtener término de búsqueda
$termino = isset($_GET['q']) ? limpiarCadena($_GET['q']) : "";

$resultados = [];
if (!empty($termino)) {
    $stmt = $conn->prepare("SELECT * FROM peliculas WHERE titulo LIKE ? ORDER BY fecha_agregado DESC");
    $like = "%" . $termino . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $resultados = $stmt->get_result();
}
?>

<main class="container">
    <h2>Resultados de búsqueda</h2>
    <?php if (!empty($termino)): ?>
        <p>Mostrando resultados para: <strong><?= $termino ?></strong></p>
        <?php if ($resultados->num_rows > 0): ?>
            <ul>
                <?php while ($row = $resultados->fetch_assoc()): ?>
                    <li>
                        <a href="pelicula.php?id=<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['titulo']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No se encontraron resultados.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Introduce un término de búsqueda.</p>
    <?php endif; ?>
</main>

<?php require_once "templates/footer.php"; ?>
