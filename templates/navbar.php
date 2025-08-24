<?php
/**
 * Navbar con icono de mensajes. No dependas de auth.php aquí.
 * Emplea sesión directa para no romper vistas públicas.
 */

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$usuarioActualId = $_SESSION['usuario_id'] ?? null;
$usuarioActualNombre = $_SESSION['usuario_nombre'] ?? '';

$tieneMensajesNoLeidos = false;

if ($usuarioActualId) {
    try {
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM mensajes 
            WHERE receptor_id = :uid AND leido = 0
        ");
        $stmt->execute([':uid' => (int)$usuarioActualId]);
        $tieneMensajesNoLeidos = ((int)$stmt->fetchColumn() > 0);
    } catch (Throwable $e) {
        $tieneMensajesNoLeidos = false;
    }
}
?>
<nav class="navbar">
  <!-- IZQUIERDA -->
  <div class="nav-left">
    <a href="<?php echo APP_URL; ?>peliculas/agregar.php" class="btn">+ Publicar</a>
  </div>

  <!-- CENTRO -->
  <div class="nav-center">
    <a href="<?php echo APP_URL; ?>dashboard.php" class="site-title">
      🎬 <?php echo APP_NAME; ?>
    </a>
  </div>

  <!-- DERECHA -->
  <div class="nav-right">
    <?php if ($usuarioActualId): ?>
      <span class="welcome">
        Bienvenido,
        <a href="<?php echo APP_URL; ?>usuarios/perfil.php" class="usuario-link">
          <?php echo htmlspecialchars((string)$usuarioActualNombre); ?>
        </a>
      </span>

      <a href="<?php echo APP_URL; ?>chats/index.php" class="icon-mensajes" title="Mensajes">
        <img
          src="<?php echo APP_URL; ?>img/icons/<?php echo $tieneMensajesNoLeidos ? 'chat-sin-leer.svg' : 'chat.svg'; ?>"
          alt="Mensajes"
          width="24"
          height="24"
        >
      </a>

      <a href="<?php echo APP_URL; ?>usuarios/logout.php" class="btn-logout">Cerrar sesión</a>
    <?php else: ?>
      <a href="<?php echo APP_URL; ?>usuarios/login.php" class="btn">Iniciar sesión</a>
      <a href="<?php echo APP_URL; ?>usuarios/register.php" class="btn">Registrarse</a>
    <?php endif; ?>
  </div>
</nav>
