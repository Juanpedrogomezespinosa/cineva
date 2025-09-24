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

// Obtener datos del usuario incluyendo biografía
$stmt = $pdo->prepare("SELECT nombre, avatar, biografia FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : 'default.png';
$biografia = $usuario['biografia'] ?? '';

include __DIR__ . '/../templates/header.php';
?>

<section class="editar-perfil containter-principal">
    <div class="container-agregar">
        <h1>Editar perfil</h1>

        <form action="procesar-editar-perfil.php" method="POST" enctype="multipart/form-data" class="form-horizontal">
            
            <!-- Imagen actual -->
            <div class="columna izquierda">
                <label>Imagen de perfil actual:</label>
                <img src="<?php echo APP_URL . 'img/avatars/' . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>" 
                     alt="Avatar de <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
                     class="avatar-actual">
            </div>

            <!-- Nombre + Biografía -->
            <div class="columna centro">
                <div class="form-group">
                    <label for="nombre">Nombre de usuario:</label>
                    <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="biografia">Biografía:</label>
                    <textarea name="biografia" id="biografia" maxlength="300" placeholder="Escribe algo sobre ti..."><?php echo htmlspecialchars($biografia, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <!-- Nueva imagen -->
            <div class="columna derecha">
                <div class="form-group">
                    <label for="avatar">Nueva imagen de perfil:</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*">
                </div>
            </div>

            <!-- Botón -->
            <div class="form-actions">
                <button type="submit">Guardar cambios</button>
            </div>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
