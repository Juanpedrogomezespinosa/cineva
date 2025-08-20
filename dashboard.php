<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/peliculas.php';

$db = new Database();
$pdo = $db->getConnection();

// Obtener timeline: todas las películas de todos los usuarios
$stmtTimeline = $pdo->prepare("
    SELECT p.*, u.nombre AS usuario_nombre, u.id AS usuario_id
    FROM peliculas p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_agregado DESC
");
$stmtTimeline->execute();
$peliculasTimeline = $stmtTimeline->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/templates/header.php';
?>

<section class="dashboard">
    <?php if (count($peliculasTimeline) === 0): ?>
        <p>No hay publicaciones aún.</p>
    <?php else: ?>
        <div class="cards-container">
            <?php foreach ($peliculasTimeline as $pelicula): ?>
                <div class="card-pelicula">
                    <?php if (!empty($pelicula['portada'])): ?>
                        <a href="<?php echo APP_URL; ?>peliculas/ver.php?id=<?php echo $pelicula['id']; ?>">
                            <img src="<?php echo APP_URL . 'img/portadas/' . htmlspecialchars($pelicula['portada'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="<?php echo htmlspecialchars($pelicula['titulo'], ENT_QUOTES, 'UTF-8'); ?> portada">
                        </a>
                    <?php else: ?>
                        <div class="sin-portada">
                            <a href="<?php echo APP_URL; ?>peliculas/ver.php?id=<?php echo $pelicula['id']; ?>" style="color: inherit; text-decoration: none;">Sin portada</a>
                        </div>
                    <?php endif; ?>

                    <div class="card-contenido">
                        <h3>
                            <a href="<?php echo APP_URL; ?>peliculas/ver.php?id=<?php echo $pelicula['id']; ?>" style="color: inherit; text-decoration: none;">
                                <?php echo htmlspecialchars($pelicula['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </h3>
                        <div class="valoracion">
                            <?php echo str_repeat('⭐', (int)$pelicula['valoracion']); ?>
                            <?php echo str_repeat('☆', 5 - (int)$pelicula['valoracion']); ?>
                        </div>
                        <div class="usuario-publico">
                            Agregada por: 
                            <a href="<?php echo APP_URL; ?>usuarios/perfil.php?id=<?php echo $pelicula['usuario_id']; ?>" style="color: #f4bf2c; font-weight: bold; text-decoration: none;">
                                <?php echo htmlspecialchars($pelicula['usuario_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
