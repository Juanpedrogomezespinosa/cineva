<?php
declare(strict_types=1);

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/mensajes.php';

$usuarioActual = $_SESSION['usuario_id'] ?? null;
$usuarioChat   = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;

if (!$usuarioActual || $usuarioChat <= 0) {
    header('Location: ' . APP_URL . 'chats/index.php');
    exit;
}

$db = (new Database())->getConnection();

// Verificar que el usuario con el que se quiere chatear exista
$stmt = $db->prepare("SELECT id, nombre, avatar FROM usuarios WHERE id = ?");
$stmt->execute([$usuarioChat]);
$usuarioReceptor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuarioReceptor) {
    echo "El usuario no existe.";
    exit;
}

// Marcar como leídos los mensajes que existan
marcarMensajesComoLeidos($db, $usuarioReceptor['id'], $usuarioActual);

include '../templates/header.php';
?>

<div class="chat-container">

    <!-- Header: nombre usuario -->
    <header class="chat-header">
        <h2><?php echo htmlspecialchars($usuarioReceptor['nombre'], ENT_QUOTES); ?></h2>
    </header>

    <!-- Sección de mensajes -->
    <section id="chat-box" class="chat-box">
        <!-- Mensajes se cargarán vía AJAX -->
    </section>

    <!-- Footer: input + botón -->
    <footer class="chat-footer">
        <form id="chat-form" class="formulario-chat">
            <input
                type="hidden"
                name="receptor_id"
                value="<?php echo (int)$usuarioReceptor['id']; ?>"
                data-current-user-id="<?php echo (int)$usuarioActual; ?>"
            >
            <input
                type="text"
                name="mensaje"
                placeholder="Escribe tu mensaje..."
                required
                autocomplete="off"
            >
            <button type="submit">Enviar</button>
        </form>
    </footer>

</div>

<link rel="stylesheet" href="<?php echo APP_URL; ?>css/chat.css?v=<?php echo filemtime(__DIR__ . '/../css/chat.css'); ?>">

<script>
window.MENSAJES_ENDPOINT = "<?php echo APP_URL; ?>includes/mensajes_ajax.php";
</script>
<script src="<?php echo APP_URL; ?>scripts/chat.js"></script>

<?php include '../templates/footer.php'; ?>
