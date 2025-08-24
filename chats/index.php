<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/mensajes.php';

$database = new Database();
$db = $database->getConnection();

$usuarioActual = $_SESSION['usuario_id'] ?? null;
if (!$usuarioActual) {
    header('Location: ../usuarios/login.php');
    exit;
}

// Obtener todos los chats del usuario actual
$chats = obtenerChats($db, $usuarioActual);

include '../templates/header.php';
include '../templates/navbar.php';
?>

<main class="contenedor-chats">
    <h2>Mensajes</h2>

    <?php if (empty($chats)): ?>
        <p>No tienes conversaciones todavía.</p>
    <?php else: ?>
        <ul class="lista-chats">
            <?php foreach ($chats as $chat): ?>
                <li class="item-chat">
                    <a href="chat.php?usuario=<?php echo (int)$chat['id']; ?>">
                        <img 
                            src="../img/avatars/<?php echo htmlspecialchars($chat['avatar']); ?>" 
                            alt="Avatar de <?php echo htmlspecialchars($chat['nombre']); ?>" 
                            width="40"
                            height="40"
                        >
                        <div class="info-chat">
                            <strong><?php echo htmlspecialchars($chat['nombre']); ?></strong><br>
                            <small><?php echo htmlspecialchars($chat['ultimo_mensaje']); ?></small>
                        </div>
                        <?php if ((int)$chat['no_leidos'] > 0): ?>
                            <span class="badge"><?php echo (int)$chat['no_leidos']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include '../templates/footer.php'; ?>
