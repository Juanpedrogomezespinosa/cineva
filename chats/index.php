<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/mensajes.php';

// Usuario actual
$usuarioActual = $_SESSION['usuario_id'] ?? null;
if (!$usuarioActual) {
    header('Location: ../usuarios/login.php');
    exit;
}

// Conexión a la base de datos
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Throwable $e) {
    echo "<h2>Error de conexión a la base de datos</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

// Obtener todos los chats del usuario actual
try {
    $chats = obtenerChats($db, $usuarioActual);
} catch (Throwable $e) {
    echo "<h2>Error al obtener las conversaciones</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

// Incluir templates
include '../templates/header.php';
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
                            src="../img/avatars/<?php echo htmlspecialchars($chat['avatar'], ENT_QUOTES); ?>" 
                            alt="Avatar de <?php echo htmlspecialchars($chat['nombre'], ENT_QUOTES); ?>" 
                            width="40"
                            height="40"
                        >
                        <div class="info-chat">
                            <strong><?php echo htmlspecialchars($chat['nombre'], ENT_QUOTES); ?></strong><br>
                            <small><?php echo htmlspecialchars($chat['ultimo_mensaje'], ENT_QUOTES); ?></small>
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
