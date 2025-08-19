<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$mensaje = '';
$nombre = '';
$email = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        $mensaje = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password2'] ?? '');

        if ($nombre === '' || $email === '' || $password === '' || $password2 === '') {
            $mensaje = 'Todos los campos son obligatorios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'El email no es válido.';
        } elseif ($password !== $password2) {
            $mensaje = 'Las contraseñas no coinciden.';
        } else {
            $db = new Database();
            $pdo = $db->getConnection();

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $mensaje = 'El email ya está registrado.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                $resultado = $stmt->execute([$nombre, $email, $hash]);

                if ($resultado) {
                    $_SESSION['mensaje'] = 'Registro correcto. Ya puedes iniciar sesión.';
                    header('Location: ' . APP_URL . 'usuarios/login.php');
                    exit;
                } else {
                    $mensaje = 'Error al registrar usuario.';
                }
            }
        }
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];
}
?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<section class="form-container">
    <h2>Registro de usuario</h2>

    <?php if ($mensaje): ?>
        <p class="mensaje mensaje-error"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo APP_URL; ?>usuarios/register.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" required />

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required />

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required />

        <label for="password2">Repetir Contraseña:</label>
        <input type="password" id="password2" name="password2" required />

        <button type="submit">Registrarse</button>
    </form>

    <p class="registro-link">¿Ya tienes cuenta? 
        <a href="<?php echo APP_URL; ?>usuarios/login.php">Inicia sesión aquí</a>
    </p>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
