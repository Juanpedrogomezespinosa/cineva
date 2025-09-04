<?php
/**
 * Navbar con icono de mensajes dinámico y notificaciones en naranja.
 */

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$usuarioActualId = $_SESSION['usuario_id'] ?? null;
$usuarioActualNombre = $_SESSION['usuario_nombre'] ?? '';
?>

<nav class="navbar">
    <!-- IZQUIERDA -->
    <div class="nav-left">
        <a href="<?php echo APP_URL; ?>peliculas/agregar.php" class="btn" >+ Publicar</a>
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
                    <?php echo htmlspecialchars((string)$usuarioActualNombre, ENT_QUOTES); ?>
                </a>
            </span>

            <!-- Icono de mensajes dinámico -->
            <a href="<?php echo APP_URL; ?>chats/index.php" class="icon-mensajes" id="icon-mensajes" title="Mensajes">
                <img
                    src="<?php echo APP_URL; ?>img/icons/chat.svg"
                    alt="Mensajes"
                    width="24"
                    height="24"
                    style="filter: invert(100%);" 
                    id="img-mensajes"
                >
            </a>

            <a href="<?php echo APP_URL; ?>usuarios/logout.php" class="btn-logout">Cerrar sesión</a>
        <?php else: ?>
            <a href="<?php echo APP_URL; ?>usuarios/login.php" class="btn">Iniciar sesión</a>
            <a href="<?php echo APP_URL; ?>usuarios/register.php" class="btn">Registrarse</a>
        <?php endif; ?>
    </div>
</nav>

<?php if ($usuarioActualId): ?>
<script>
async function actualizarIconoMensajes() {
    try {
        const res = await fetch('<?php echo APP_URL; ?>includes/mensajes_ajax.php?check_no_leidos=1');
        if (!res.ok) return;
        const data = await res.json();
        const img = document.getElementById('img-mensajes');

        if (data.no_leidos > 0) {
            img.src = '<?php echo APP_URL; ?>img/icons/chat-sin-leer.svg';
            // Filtro para naranja vibrante (#f4bf2c aproximado)
            img.style.filter = 'invert(67%) sepia(85%) saturate(750%) hue-rotate(10deg) brightness(98%) contrast(101%)';
        } else {
            img.src = '<?php echo APP_URL; ?>img/icons/chat.svg';
            img.style.filter = 'invert(100%)'; // blanco normal
        }
    } catch (e) {
        console.error('Error al actualizar icono de mensajes:', e);
    }
}

// Actualizar cada 3 segundos
actualizarIconoMensajes();
setInterval(actualizarIconoMensajes, 3000);
</script>
<?php endif; ?>
