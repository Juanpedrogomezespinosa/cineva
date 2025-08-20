<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/follows.php';

$id = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null);

if (!$id) {
    header('Location: ../index.php');
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// Obtener datos del usuario
$stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtUser->execute([$id]);
$usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: ../index.php');
    exit;
}

$esPerfilPropio = isset($_SESSION['usuario_id']) && ($_SESSION['usuario_id'] == $id);
$usuarioLogueado = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

$follows = new Follows();

// Procesar seguir/dejar de seguir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuarioLogueado && !$esPerfilPropio) {
    if (isset($_POST['seguir'])) {
        $follows->seguirUsuario($usuarioLogueado, $id);
    } elseif (isset($_POST['dejar_seguir'])) {
        $follows->dejarDeSeguirUsuario($usuarioLogueado, $id);
    }
    header("Location: perfil.php?id=$id");
    exit;
}

// Contadores
$seguidoresCount = $follows->contarSeguidores($id);
$seguidosCount = $follows->contarSeguidos($id);

// Número de publicaciones del usuario
$stmtPublicaciones = $pdo->prepare("SELECT COUNT(*) FROM peliculas WHERE usuario_id = ?");
$stmtPublicaciones->execute([$id]);
$totalPublicaciones = $stmtPublicaciones->fetchColumn();

// Comprobar si el usuario logueado ya sigue a este perfil
$yaSigue = $usuarioLogueado ? $follows->esSeguidor($usuarioLogueado, $id) : false;

// Obtener películas del usuario
$stmtPeliculas = $pdo->prepare("
    SELECT p.*, u.nombre AS usuario_nombre
    FROM peliculas p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.usuario_id = ?
    ORDER BY p.fecha_agregado DESC
");
$stmtPeliculas->execute([$id]);
$lista_peliculas = $stmtPeliculas->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
?>

<section class="perfil">
    <h1>
        <?php echo $esPerfilPropio ? "Bienvenido, " . htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') . "!" : "Perfil de " . htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>
    </h1>

    <div class="avatar-perfil">
        <?php
        $avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : 'default.png';
        ?>
        <img src="<?php echo APP_URL . 'img/avatars/' . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>" 
             alt="Avatar de <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
             width="150" style="border-radius:50%; border:2px solid #f4bf2c;">
    </div>

    <!-- Seguidores, Seguidos y Publicaciones -->
    <div class="perfil-social">
        <p>
            Seguidores: <strong><?php echo $seguidoresCount; ?></strong> | 
            Seguidos: <strong><?php echo $seguidosCount; ?></strong> | 
            Publicaciones: <strong><?php echo $totalPublicaciones; ?></strong>
        </p>

        <?php if (!$esPerfilPropio && $usuarioLogueado): ?>
            <form method="post" action="">
                <?php if ($yaSigue): ?>
                    <button type="submit" name="dejar_seguir">Dejar de seguir</button>
                <?php else: ?>
                    <button type="submit" name="seguir">Seguir</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($esPerfilPropio): ?>
        <p><a href="editar-perfil.php" class="btn-editar">Editar perfil</a></p>
        <a href="<?php echo APP_URL; ?>peliculas/agregar.php" class="btn-agregar">Agregar nueva película</a>
    <?php endif; ?>

    <h2><?php echo $esPerfilPropio ? 'Tus publicaciones' : 'Publicaciones de ' . htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>

    <?php if (count($lista_peliculas) === 0): ?>
        <p>No hay películas añadidas todavía.</p>
    <?php else: ?>
        <div class="cards-container">
            <?php foreach ($lista_peliculas as $pelicula): ?>
                <div class="card-pelicula">
                    <?php if (!empty($pelicula['portada'])): ?>
                        <a href="<?php echo APP_URL; ?>peliculas/ver.php?id=<?php echo $pelicula['id']; ?>">
                            <img src="<?php echo APP_URL . 'img/portadas/' . htmlspecialchars($pelicula['portada'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="Portada de <?php echo htmlspecialchars($pelicula['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
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

                        <?php if ($esPerfilPropio): ?>
                            <div class="acciones-card">
                                <a href="<?php echo APP_URL; ?>peliculas/editar.php?id=<?php echo $pelicula['id']; ?>">Editar</a>
                                <a href="<?php echo APP_URL; ?>peliculas/eliminar.php?id=<?php echo $pelicula['id']; ?>" onclick="return confirm('¿Seguro que quieres eliminar esta película?');">Eliminar</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
