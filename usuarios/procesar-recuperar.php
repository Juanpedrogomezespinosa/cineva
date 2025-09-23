<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email'])) {
    echo "Solicitud inválida.";
    exit;
}

$email = trim($_POST['email']);

// Crear conexión PDO
$db = new Database();
$conn = $db->getConnection();

// Verificar si el usuario existe
$stmt = $conn->prepare("SELECT id, email FROM usuarios WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo "No existe ningún usuario con ese correo.";
    exit;
}

// Generar un token único
$token = bin2hex(random_bytes(16));
$fechaExpiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Guardar token en la base de datos
$stmtUpdate = $conn->prepare("UPDATE usuarios SET token = ?, token_expira = ? WHERE id = ?");
$stmtUpdate->execute([$token, $fechaExpiracion, $usuario['id']]);

// Preparar el enlace de restablecimiento
$enlace = APP_URL . "usuarios/restablecer.php?token=" . urlencode($token);

// Configuración de PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'cinevapp@gmail.com';
    $mail->Password   = 'dhto jxml abwv gyqt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('cinevapp@gmail.com', 'Cineva - Recuperación');
    $mail->addAddress($usuario['email']);

    $mail->isHTML(true);
    $mail->Subject = 'Recupera tu contraseña en Cineva';
    $mail->Body    = "
        <p>Hola,</p>
        <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>
        <p><a href='$enlace'>$enlace</a></p>
        <p>Este enlace expirará en 1 hora.</p>
        <p>Si no solicitaste este cambio, ignora este correo.</p>
    ";
    $mail->AltBody = "Has solicitado restablecer tu contraseña. Copia y pega este enlace en tu navegador: $enlace";

    $mail->send();

    // Redirigir automáticamente al login con mensaje de éxito
    $_SESSION['mensaje'] = "Hemos enviado un enlace de recuperación a tu correo electrónico.";
    header('Location: ' . APP_URL . 'usuarios/login.php');
    exit;

} catch (Exception $e) {
    echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
}
