<?php
/**
 * Navbar dinámico con control de sesión y animación hamburguesa → X.
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

            <!-- ICONO DE MENSAJES -->
            <a href="<?php echo APP_URL; ?>chats/index.php" class="icon-mensajes" id="icon-mensajes" title="Mensajes">
                <img
                    src="<?php echo APP_URL; ?>img/icons/chat.svg"
                    alt="Mensajes"
                    width="24"
                    height="24"
                    id="img-mensajes"
                >
            </a>

            <!-- ICONO DE NOTIFICACIONES -->
            <a href="javascript:void(0);" class="icon-notificaciones" id="icono-notificaciones" title="Notificaciones">
                <img
                    src="<?php echo APP_URL; ?>img/icons/notificacion.svg"
                    alt="Notificaciones"
                    width="24"
                    height="24"
                >
                <span id="contador-notificaciones" class="badge"></span>
            </a>
            <div id="lista-notificaciones" class="dropdown"></div>

            <!-- CERRAR SESIÓN -->
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
    <div class="hamburger" id="hamburger-toggle">
        <span></span>
        <span></span>
        <span></span>
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
    <a href="javascript:void(0);" class="icon-notificaciones" id="icono-notificaciones-movil" title="Notificaciones">
        <img
            src="<?php echo APP_URL; ?>img/icons/notificacion.svg"
            alt="Notificaciones"
            width="22"
            height="22"
        >
        <span id="contador-notificaciones-movil" class="badge"></span>
        <span class="texto-notificaciones">Notificaciones</span>
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
        hamburger.classList.toggle('active');
    });
}

// ====================
// Actualización dinámica de mensajes
// ====================
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

// ====================
// Sistema de notificaciones
// ====================
async function cargarNotificaciones() {
    try {
        const res = await fetch('<?php echo APP_URL; ?>includes/notificaciones_ajax.php');
        if (!res.ok) return;
        const data = await res.json();

        const lista = document.getElementById('lista-notificaciones');
        const contador = document.getElementById('contador-notificaciones');
        const contadorMovil = document.getElementById('contador-notificaciones-movil');
        lista.innerHTML = '';
        let noLeidas = 0;

        data.forEach(n => {
            if (n.leido == 0) noLeidas++;
            const item = document.createElement('div');
            item.classList.add('notificacion-item');
            item.textContent = n.tipo === 'seguimiento'
                ? `${n.origen_nombre} te ha seguido`
                : `${n.origen_nombre} comentó en tu película`;
            lista.appendChild(item);
        });

        // Mostrar solo número, sin fondo
        if (contador) {
            if (noLeidas > 0) {
                contador.textContent = noLeidas;
                contador.style.display = "inline-block";
            } else {
                contador.textContent = "";
                contador.style.display = "none";
            }
        }

        if (contadorMovil) {
            if (noLeidas > 0) {
                contadorMovil.textContent = noLeidas;
                contadorMovil.style.display = "inline-block";
            } else {
                contadorMovil.textContent = "";
                contadorMovil.style.display = "none";
            }
        }
    } catch (e) {
        console.error('Error al cargar notificaciones:', e);
    }
}

const iconoNotificaciones = document.getElementById('icono-notificaciones');
if (iconoNotificaciones) {
    iconoNotificaciones.addEventListener('click', async () => {
        document.getElementById('lista-notificaciones').classList.toggle('show');
        await fetch('<?php echo APP_URL; ?>includes/marcar_notificaciones.php', { method: 'POST' });

        const contador = document.getElementById('contador-notificaciones');
        const contadorMovil = document.getElementById('contador-notificaciones-movil');

        if (contador) contador.style.display = 'none';
        if (contadorMovil) contadorMovil.style.display = 'none';
    });
}

cargarNotificaciones();
setInterval(cargarNotificaciones, 30000);
</script>
<?php endif; ?>
