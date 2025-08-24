<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configuración y dependencias usando rutas absolutas
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mensajes.php';

// Usuario logueado
$currentUser = $_SESSION['usuario_id'] ?? null;
$chatUser = isset($_GET['usuario']) ? (int)$_GET['usuario'] : null;

// Redirigir si falta información
if (!$currentUser || !$chatUser) {
    header('Location: ' . APP_URL);
    exit;
}

// Inicializar conexión
$db = (new Database())->getConnection();

// Verificar que el usuario receptor exista
$stmt = $db->prepare("SELECT id, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$chatUser]);
$usuarioReceptor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuarioReceptor) {
    echo "El usuario no existe.";
    exit;
}

// Incluir templates
include __DIR__ . '/../templates/header.php';
// include __DIR__ . '/../templates/navbar.php';
?>

<h2>Chat con <?php echo htmlspecialchars($usuarioReceptor['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>

<div id="chat-box" style="border:1px solid #ccc; padding:10px; max-height:400px; overflow-y:auto;">
    <!-- Los mensajes se cargarán mediante AJAX -->
</div>

<form id="chat-form">
    <input type="hidden" 
           name="receptor_id" 
           value="<?php echo htmlspecialchars($usuarioReceptor['id'], ENT_QUOTES, 'UTF-8'); ?>" 
           data-current-user-id="<?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
    <button type="submit">Enviar</button>
</form>

<link rel="stylesheet" href="<?php echo APP_URL; ?>css/chat.css">

<script>
    const chatUser = <?php echo json_encode($usuarioReceptor['id']); ?>;
    const currentUser = <?php echo json_encode($currentUser); ?>;
</script>
<script src="<?php echo APP_URL; ?>scripts/chat.js"></script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
