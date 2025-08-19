<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}

$mensaje = '';
$mensaje_registro = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        $mensaje = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $mensaje = 'Por favor, completa todos los campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'Email no válido.';
        } else {
            $db = new Database();
            $pdo = $db->getConnection();

            $stmt = $pdo->prepare('SELECT id, nombre, password FROM usuarios WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int)$usuario['id'];
                $_SESSION['usuario_nombre'] = (string)$usuario['nombre'];
                $_SESSION['login_time'] = time();

                header('Location: ' . APP_URL . 'dashboard.php');
                exit;
            } else {
                $mensaje = 'Credenciales incorrectas.';
            }
        }
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];
}
?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<section class="form-container">
    <h2>Iniciar sesión</h2>

    <?php if ($mensaje_registro): ?>
        <p class="mensaje mensaje-exito"><?php echo htmlspecialchars($mensaje_registro, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <p class="mensaje mensaje-error"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo APP_URL; ?>usuarios/login.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="email" required />

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required />

        <button type="submit">Entrar</button>
    </form>

    <p class="registro-link">
        ¿No tienes cuenta?
        <a href="<?php echo APP_URL; ?>usuarios/register.php">Regístrate aquí</a>
    </p>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
