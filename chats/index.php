<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los usuarios excepto el actual
$currentUser = $_SESSION['user_id'] ?? null;

if (!$currentUser) {
    header('Location: ../usuarios/login.php');
    exit;
}

$stmt = $db->prepare("SELECT id, nombre, avatar FROM usuarios WHERE id != :id");
$stmt->execute([':id' => $currentUser]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../templates/header.php';
include '../templates/navbar.php';
?>

<h2>Contactos</h2>
<ul>
    <?php foreach ($usuarios as $usuario): ?>
        <li>
            <a href="chat.php?usuario=<?= $usuario['id'] ?>">
                <img src="../img/avatars/<?= $usuario['avatar'] ?>" alt="<?= $usuario['nombre'] ?>" width="40">
                <?= htmlspecialchars($usuario['nombre']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php include '../templates/footer.php'; ?>
