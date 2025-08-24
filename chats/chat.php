<?php
declare(strict_types=1);

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';       // aquí sí, porque esta vista es privada
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

// Marcar como leídos los mensajes que ya existan
marcarMensajesComoLeidos($db, $usuarioChat, (int)$usuarioActual);

include '../templates/header.php';
?>
<main class="contenedor-chat">
    <h2>Chat con <?php echo htmlspecialchars($usuarioReceptor['nombre']); ?></h2>

    <div id="chat-box" class="chat-box">
        <!-- Los mensajes se cargarán automáticamente con AJAX -->
    </div>

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
</main>

<link rel="stylesheet" href="<?php echo APP_URL; ?>css/chat.css">

<script>
// Endpoint absoluto para evitar problemas de rutas
window.MENSAJES_ENDPOINT = "<?php echo APP_URL; ?>includes/mensajes_ajax.php";
</script>
<script src="<?php echo APP_URL; ?>scripts/chat.js"></script>

<?php include '../templates/footer.php'; ?>
