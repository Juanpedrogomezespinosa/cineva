<?php
session_start();
require_once 'includes/db.php';

$mensaje = '';

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
            // Login correcto, iniciar sesión
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

// Mostrar mensaje de registro exitoso si viene de register.php
$mensaje_registro = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login | Cineva</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
    <h1>Iniciar sesión</h1>

    <?php if ($mensaje_registro): ?>
        <p style="color:green;"><?php echo htmlspecialchars($mensaje_registro); ?></p>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <p style="color:red;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php" novalidate>
        <label for="email">Email:</label><br />
        <input type="email" id="email" name="email" required /><br /><br />

        <label for="password">Contraseña:</label><br />
        <input type="password" id="password" name="password" required /><br /><br />

        <button type="submit">Entrar</button>
    </form>

    <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
</body>
</html>
