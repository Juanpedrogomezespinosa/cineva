<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

// Recoger datos del formulario
$token = trim((string)($_POST['token'] ?? ''));
$password = trim((string)($_POST['password'] ?? ''));
$password2 = trim((string)($_POST['password2'] ?? ''));

// Validaciones
if ($token === '' || $password === '' || $password2 === '') {
    die("Todos los campos son obligatorios.");
}

if ($password !== $password2) {
    die("Las contraseñas no coinciden.");
}

if (strlen($password) < 6) {
    die("La contraseña debe tener al menos 6 caracteres.");
}

// Conexión a la base de datos
$db = new Database();
$pdo = $db->getConnection();

// Buscar usuario por token y verificar expiración
$stmt = $pdo->prepare("SELECT id, token_expira FROM usuarios WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Token inválido o expirado.");
}

$expira = strtotime((string)$usuario['token_expira']);
if ($expira === false || $expira < time()) {
    die("El enlace ha expirado o es inválido.");
}

// Hashear nueva contraseña
$hashPassword = password_hash($password, PASSWORD_DEFAULT);

// Actualizar contraseña y limpiar token
$update = $pdo->prepare("UPDATE usuarios SET password = ?, token = NULL, token_expira = NULL WHERE id = ?");
$update->execute([$hashPassword, $usuario['id']]);

// Mensaje de éxito
$_SESSION['mensaje'] = "Tu contraseña ha sido restablecida correctamente. Ahora puedes iniciar sesión.";
header("Location: " . APP_URL . "usuarios/login.php");
exit;
