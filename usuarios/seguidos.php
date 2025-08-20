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

$follows = new Follows();
$listaSeguidos = $follows->obtenerSeguidos($id);

include __DIR__ . '/../templates/header.php';
?>

<section class="seguidos">
    <h1>Usuarios que sigue</h1>
    <?php if (count($listaSeguidos) === 0): ?>
        <p>Este usuario no sigue a nadie todavía.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($listaSeguidos as $seguido): ?>
                <li>
                    <a href="<?php echo APP_URL; ?>usuarios/perfil.php?id=<?php echo $seguido['id']; ?>">
                        <img src="<?php echo APP_URL . 'img/avatars/' . htmlspecialchars($seguido['avatar'], ENT_QUOTES, 'UTF-8'); ?>" 
                             alt="Avatar de <?php echo htmlspecialchars($seguido['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
                             width="40" style="border-radius:50%;">
                        <?php echo htmlspecialchars($seguido['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
