<?php
session_start();
require_once __DIR__ . '/../includes/amistad.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../usuarios/login.php');
    exit;
}

$amistad = new Amistad();
$seguidores = $amistad->obtenerSeguidores($_SESSION['usuario_id']);
$seguidos = $amistad->obtenerSeguidos($_SESSION['usuario_id']);

include __DIR__ . '/../templates/header.php';
?>

<h2>Amigos</h2>

<h3>Mis seguidores</h3>
<ul>
    <?php foreach ($seguidores as $s): ?>
        <li><?php echo htmlspecialchars($s['nombre']); ?></li>
    <?php endforeach; ?>
</ul>

<h3>Usuarios que sigo</h3>
<ul>
    <?php foreach ($seguidos as $s): ?>
        <li><?php echo htmlspecialchars($s['nombre']); ?></li>
    <?php endforeach; ?>
</ul>

<?php include __DIR__ . '/../templates/footer.php'; ?>
