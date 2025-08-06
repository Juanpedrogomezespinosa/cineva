<?php
session_start();
require_once 'includes/db.php';

// Variables para mensajes y valores previos
$mensaje = '';
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones básicas
    if (!$nombre || !$email || !$password || !$password2) {
        $mensaje = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El email no es válido.';
    } elseif ($password !== $password2) {
        $mensaje = 'Las contraseñas no coinciden.';
    } else {
        // Conexión BD
        $db = new Database();
        $pdo = $db->getConnection();

        // Comprobar si email existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $mensaje = 'El email ya está registrado.';
        } else {
            // Insertar nuevo usuario con password_hash
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            $resultado = $stmt->execute([$nombre, $email, $hash]);

            if ($resultado) {
                $_SESSION['mensaje'] = 'Registro correcto. Ya puedes iniciar sesión.';
                header('Location: index.php');
                exit;
            } else {
                $mensaje = 'Error al registrar usuario.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Registro | Cineva</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
    <h1>Registro de usuario</h1>

    <?php if ($mensaje): ?>
        <p style="color:red;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <label for="nombre">Nombre:</label><br />
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required /><br /><br />

        <label for="email">Email:</label><br />
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required /><br /><br />

        <label for="password">Contraseña:</label><br />
        <input type="password" id="password" name="password" required /><br /><br />

        <label for="password2">Repetir Contraseña:</label><br />
        <input type="password" id="password2" name="password2" required /><br /><br />

        <button type="submit">Registrarse</button>
    </form>

    <p>¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a></p>
</body>
</html>
