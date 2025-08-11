<?php
session_start();
require_once 'includes/db.php';

$mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $mensaje = 'Por favor, completa todos los campos.';
    } else {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['login_time'] = time();
            header('Location: dashboard.php');
            exit;
        } else {
            $mensaje = 'Credenciales incorrectas.';
        }
    }
}

// Mensaje desde register.php
$mensaje_registro = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>

<?php include 'templates/header.php'; ?>

<section class="form-container">
    <h2>Iniciar sesión</h2>

    <?php if ($mensaje_registro): ?>
        <p class="mensaje mensaje-exito"><?php echo htmlspecialchars($mensaje_registro); ?></p>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <p class="mensaje mensaje-error"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php" novalidate>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required />

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required />

        <button type="submit">Entrar</button>
    </form>

    <p class="registro-link">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
</section>

<?php include 'templates/footer.php'; ?>
