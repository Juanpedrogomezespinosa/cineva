<?php
/**
 * Navbar dinámico con control de sesión.
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
        <?php if ($usuarioActualId): ?>
            <a href="<?php echo APP_URL; ?>peliculas/agregar.php" class="btn">
                <img src="<?php echo APP_URL; ?>img/icons/crear.svg" alt="Crear"> 
                <span>+ Publicar</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- CENTRO -->
    <div class="nav-center">
        <a href="<?php echo APP_URL; ?>dashboard.php" class="site-title">
            🎬 <?php echo APP_NAME; ?>
        </a>
    </div>

    <!-- DERECHA (solo escritorio) -->
    <div class="nav-right">
        <?php if ($usuarioActualId): ?>
            <span class="welcome">
                Bienvenido,
                <a href="<?php echo APP_URL; ?>usuarios/perfil.php" class="usuario-link">
                    <?php echo htmlspecialchars((string)$usuarioActualNombre, ENT_QUOTES); ?>
                </a>
            </span>

            <a href="<?php echo APP_URL; ?>chats/index.php" class="icon-mensajes" id="icon-mensajes" title="Mensajes">
                <img
                    src="<?php echo APP_URL; ?>img/icons/chat.svg"
                    alt="Mensajes"
                    width="24"
                    height="24"
                    id="img-mensajes"
                >
            </a>

            <a href="<?php echo APP_URL; ?>usuarios/logout.php" class="icon-logout" title="Cerrar sesión">
                <img
                    src="<?php echo APP_URL; ?>img/icons/logout.svg"
                    alt="Cerrar sesión"
                    width="24"
                    height="24"
                >
            </a>
        <?php endif; ?>
    </div>

    <!-- BOTÓN HAMBURGUESA (solo móvil) -->
    <?php if ($usuarioActualId): ?>
    <div class="hamburger">
        <img src="<?php echo APP_URL; ?>img/icons/menu.svg" alt="Menú" width="28" height="28" id="hamburger-toggle">
    </div>
    <?php endif; ?>
</nav>

<!-- MENÚ MÓVIL DESPLEGABLE -->
<?php if ($usuarioActualId): ?>
<div class="mobile-menu" id="mobile-menu">
    <a href="<?php echo APP_URL; ?>usuarios/perfil.php">
        <img src="<?php echo APP_URL; ?>img/icons/perfil.svg" alt="Perfil" width="22" height="22"> Perfil
    </a>
    <a href="<?php echo APP_URL; ?>chats/index.php">
        <img src="<?php echo APP_URL; ?>img/icons/chat.svg" alt="Chats" width="22" height="22"> Chats
    </a>
    <a href="<?php echo APP_URL; ?>usuarios/logout.php">
        <img src="<?php echo APP_URL; ?>img/icons/logout.svg" alt="Cerrar sesión" width="22" height="22"> Cerrar sesión
    </a>
</div>
<?php endif; ?>

<?php if ($usuarioActualId): ?>
<script>
const hamburger = document.getElementById('hamburger-toggle');
const mobileMenu = document.getElementById('mobile-menu');

if (hamburger) {
    hamburger.addEventListener('click', () => {
        mobileMenu.style.display = mobileMenu.style.display === 'flex' ? 'none' : 'flex';
    });
}

// Actualización dinámica del icono de mensajes (solo escritorio)
async function actualizarIconoMensajes() {
    try {
        const res = await fetch('<?php echo APP_URL; ?>includes/mensajes_ajax.php?check_no_leidos=1');
        if (!res.ok) return;
        const data = await res.json();
        const img = document.getElementById('img-mensajes');

        if (img) {
            img.src = data.no_leidos > 0
                ? '<?php echo APP_URL; ?>img/icons/chat-sin-leer.svg'
                : '<?php echo APP_URL; ?>img/icons/chat.svg';
        }
    } catch (e) {
        console.error('Error al actualizar icono de mensajes:', e);
    }
}
actualizarIconoMensajes();
setInterval(actualizarIconoMensajes, 3000);
</script>
<?php endif; ?>
