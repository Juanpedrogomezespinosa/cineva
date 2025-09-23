<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token inválido.");
}

// Crear conexión PDO
$db = new Database();
$pdo = $db->getConnection();

// Buscar usuario por token
$stmt = $pdo->prepare("SELECT id, token_expira FROM usuarios WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("El enlace es inválido.");
}

// Comprobar expiración del token
$expira = strtotime((string)$usuario['token_expira']);
if ($expira !== false && $expira < time()) {
    die("El enlace ha expirado o es inválido.");
}

// Mensajes temporales (opcional)
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);
?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<section class="form-container">
    <h2>Restablecer contraseña</h2>

    <?php if ($mensaje): ?>
        <p class="mensaje mensaje-error"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo APP_URL; ?>usuarios/procesar-restablecer.php" novalidate>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

        <label for="password">Nueva contraseña</label>
        <div class="password-wrapper">
            <input type="password" id="password" name="password" required minlength="6" />
            <button type="button" id="togglePassword" aria-label="Mostrar u ocultar contraseña" aria-pressed="false">
                <img src="<?= APP_URL ?>img/icons/ver.svg" alt="Mostrar contraseña" id="toggleIcon">
            </button>
        </div>

        <label for="password2">Repite la contraseña</label>
        <div class="password-wrapper">
            <input type="password" id="password2" name="password2" required minlength="6" />
            <button type="button" id="togglePassword2" aria-label="Mostrar u ocultar contraseña" aria-pressed="false">
                <img src="<?= APP_URL ?>img/icons/ver.svg" alt="Mostrar contraseña" id="toggleIcon2">
            </button>
        </div>

        <button type="submit">Cambiar contraseña</button>
    </form>

    <p class="registro-link">
        <a href="<?php echo APP_URL; ?>usuarios/login.php">Volver al login</a>
    </p>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    function togglePassword(inputId, iconId) {
        const toggleBtn = document.getElementById(inputId + 'Btn');
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;

        icon.parentElement.addEventListener('click', () => {
            const typePassword = input.type === 'password';
            input.type = typePassword ? 'text' : 'password';
            icon.src = typePassword ? "<?= APP_URL ?>img/icons/bloquear-ver.svg" : "<?= APP_URL ?>img/icons/ver.svg";
            icon.alt = typePassword ? "Ocultar contraseña" : "Mostrar contraseña";
        });
    }

    // Inicializar toggles
    togglePassword('password', 'toggleIcon');
    togglePassword('password2', 'toggleIcon2');
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
