<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . 'usuarios/login.php');
    exit;
}

$id = $_SESSION['usuario_id'];

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT nombre, avatar FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : 'default.png';

include __DIR__ . '/../templates/header.php';
?>

<section class="editar-perfil">
    <h1>Editar perfil</h1>

    <form action="procesar-editar-perfil.php" method="POST" enctype="multipart/form-data">
        <label for="nombre">Nombre de usuario:</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label>Imagen de perfil actual:</label><br>
        <img src="<?php echo APP_URL . 'img/avatars/' . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>" 
             alt="Avatar de <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
             width="120" style="border-radius:50%; border:2px solid #f4bf2c;"><br><br>

        <label for="avatar">Nueva imagen de perfil:</label>
        <input type="file" name="avatar" id="avatar" accept="image/*">

        <button type="submit">Guardar cambios</button>
    </form>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
