<?php
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
    <div class="nav-left">
        <?php if ($usuarioActualId): ?>
            <a href="<?php echo APP_URL; ?>peliculas/agregar.php" class="btn">
                <img src="<?php echo APP_URL; ?>img/icons/crear.svg" alt="Crear">
                <span>+ Publicar</span>
            </a>
        <?php endif; ?>
    </div>

    <div class="nav-center">
        <a href="<?php echo APP_URL; ?>dashboard.php" class="site-title">
            🎬 <?php echo APP_NAME; ?>
        </a>
    </div>

    <div class="nav-right">
        <?php if ($usuarioActualId): ?>

            <!-- 🔍 Icono e input de búsqueda -->
            <div class="navbar-search">
                <img src="<?php echo APP_URL; ?>img/icons/buscar.svg" alt="Buscar" id="search-icon" class="icon">
                <input type="text" id="search-input" placeholder="Buscar usuarios o publicaciones..." autocomplete="off">
                <div id="search-results"></div>
            </div>

            <span class="welcome">
                Bienvenido,
                <a href="<?php echo APP_URL; ?>usuarios/perfil.php" class="usuario-link">
                    <?php echo htmlspecialchars($usuarioActualNombre, ENT_QUOTES); ?>
                </a>
            </span>

            <!-- Icono de mensajes -->
            <a href="<?php echo APP_URL; ?>chats/index.php" class="icon-mensajes" id="icon-mensajes" title="Mensajes">
                <img src="<?php echo APP_URL; ?>img/icons/chat.svg" alt="Mensajes" width="24" height="24">
                <span id="contador-mensajes" class="notif-count"></span>
            </a>

            <!-- Icono de notificaciones -->
            <a href="javascript:void(0);" class="icon-notificaciones" id="icono-notificaciones" title="Notificaciones">
                <img src="<?php echo APP_URL; ?>img/icons/notificacion.svg" alt="Notificaciones" width="24" height="24">
                <span id="contador-notificaciones" class="notif-count"></span>
            </a>
            <div id="lista-notificaciones" class="dropdown"></div>

            <a href="<?php echo APP_URL; ?>usuarios/logout.php" class="icon-logout" title="Cerrar sesión">
                <img src="<?php echo APP_URL; ?>img/icons/logout.svg" alt="Cerrar sesión" width="24" height="24">
            </a>
        <?php endif; ?>
    </div>

    <?php if ($usuarioActualId): ?>
        <div class="hamburger" id="hamburger-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    <?php endif; ?>
</nav>

<?php if ($usuarioActualId): ?>
<div class="mobile-menu" id="mobile-menu">
    <a href="<?php echo APP_URL; ?>usuarios/perfil.php">
        <img src="<?php echo APP_URL; ?>img/icons/perfil.svg" alt="Perfil" width="22" height="22"> Perfil
    </a>
    <a href="<?php echo APP_URL; ?>chats/index.php">
        <img src="<?php echo APP_URL; ?>img/icons/chat.svg" alt="Chats" width="22" height="22"> Chats
        <span id="contador-mensajes-movil" class="notif-count"></span>
    </a>
    <a href="<?php echo APP_URL; ?>usuarios/logout.php">
        <img src="<?php echo APP_URL; ?>img/icons/logout.svg" alt="Cerrar sesión" width="22" height="22"> Cerrar sesión
    </a>
    <a href="javascript:void(0);" class="icon-notificaciones" id="icono-notificaciones-movil" title="Notificaciones">
        <img src="<?php echo APP_URL; ?>img/icons/notificacion.svg" alt="Notificaciones" width="22" height="22">
        <span id="contador-notificaciones-movil" class="notif-count"></span>
        <span class="texto-notificaciones">Notificaciones</span>
    </a>
</div>
<?php endif; ?>

<?php if ($usuarioActualId): ?>
<script>
const APP_URL = "<?php echo APP_URL; ?>";

const hamburger = document.getElementById('hamburger-toggle');
const mobileMenu = document.getElementById('mobile-menu');

if (hamburger) {
    hamburger.addEventListener('click', () => {
        mobileMenu.style.display = mobileMenu.style.display === 'flex' ? 'none' : 'flex';
        hamburger.classList.toggle('active');
    });
}

// =========================
// ACTUALIZAR ICONO Y CONTADOR DE MENSAJES
// =========================
async function actualizarIconoMensajes() {
    try {
        const respuesta = await fetch(`${APP_URL}includes/mensajes_ajax.php?check_no_leidos=1`);
        if (!respuesta.ok) return;
        const datos = await respuesta.json();

        const contador = document.getElementById('contador-mensajes');
        const contadorMovil = document.getElementById('contador-mensajes-movil');

        const mostrarContador = (elemento, cantidad) => {
            if (!elemento) return;
            if (cantidad > 0) {
                elemento.textContent = cantidad > 9 ? '9+' : cantidad;
                elemento.style.display = 'inline-block';
            } else {
                elemento.textContent = '';
                elemento.style.display = 'none';
            }
        };

        mostrarContador(contador, datos.no_leidos);
        mostrarContador(contadorMovil, datos.no_leidos);

    } catch (error) {
        console.error('Error al actualizar icono de mensajes:', error);
    }
}

actualizarIconoMensajes();
setInterval(actualizarIconoMensajes, 3000);

// =========================
// MARCAR NOTIFICACIÓN INDIVIDUAL
// =========================
function generarURL(notificacion) {
    let destino = "#";
    if (notificacion.tipo === "seguimiento") {
        destino = `usuarios/perfil.php?id=${notificacion.origen_id}`;
    } else if (notificacion.tipo === "comentario") {
        destino = `peliculas/ver.php?id=${notificacion.comentario_pelicula_id}#comentario_${notificacion.relacion_id}`;
    }
    return `${APP_URL}includes/marcar_notificacion_individual.php?id=${notificacion.id}&url=${encodeURIComponent(destino)}`;
}

// =========================
// SEGUIR / DEJAR DE SEGUIR DESDE NOTIFICACIÓN
// =========================
async function toggleFollow(usuarioId, boton) {
    const accion = boton.dataset.siguiendo === "1" ? "dejar" : "seguir";
    try {
        const respuesta = await fetch(`${APP_URL}usuarios/accion_follow.php`, {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: `usuario_id=${usuarioId}&accion=${accion}`,
            credentials: 'same-origin'
        });
        const datos = await respuesta.json();
        if (datos.success) {
            boton.textContent = accion === "seguir" ? "Siguiendo" : "Seguir";
            boton.dataset.siguiendo = accion === "seguir" ? "1" : "0";
            boton.disabled = accion === "seguir";
        }
    } catch (error) {
        console.error("Error al cambiar estado de seguimiento:", error);
    }
}

// =========================
// CARGAR NOTIFICACIONES
// =========================
async function cargarNotificaciones() {
    try {
        const respuesta = await fetch(`${APP_URL}includes/notificaciones_ajax.php`);
        if (!respuesta.ok) return;
        const datos = await respuesta.json();

        const lista = document.getElementById('lista-notificaciones');
        const contador = document.getElementById('contador-notificaciones');
        const contadorMovil = document.getElementById('contador-notificaciones-movil');
        lista.innerHTML = '';
        let noLeidas = 0;

        datos.forEach(n => {
            if (n.leido == 0) noLeidas++;
            const item = document.createElement('div');
            item.classList.add('notificacion-item');

            if (n.tipo === 'seguimiento') {
                const yaSigues = Number(n.ya_sigues) === 1;
                item.innerHTML = `
                    <span>${n.origen_nombre} te ha seguido</span>
                    <button class="follow-btn" data-usuario="${n.origen_id}" data-siguiendo="${yaSigues ? 1 : 0}">
                        ${yaSigues ? 'Siguiendo' : 'Seguir'}
                    </button>
                `;
                const boton = item.querySelector('.follow-btn');
                boton.disabled = yaSigues;
                boton.addEventListener('click', () => toggleFollow(n.origen_id, boton));
            } else {
                item.innerHTML = `<a href="${generarURL(n)}">${n.origen_nombre} comentó en tu película</a>`;
            }

            lista.appendChild(item);
        });

        const mostrarContador = (elemento, cantidad) => {
            if (!elemento) return;
            if (cantidad > 0) {
                elemento.textContent = cantidad > 9 ? "9+" : cantidad;
                elemento.style.display = "inline-block";
            } else {
                elemento.textContent = "";
                elemento.style.display = "none";
            }
        };

        mostrarContador(contador, noLeidas);
        mostrarContador(contadorMovil, noLeidas);

    } catch (error) {
        console.error('Error al cargar notificaciones:', error);
    }
}

// =========================
// TOGGLE DROPDOWN NOTIFICACIONES
// =========================
const iconoNotificaciones = document.getElementById('icono-notificaciones');
const iconoNotificacionesMovil = document.getElementById('icono-notificaciones-movil');

if (iconoNotificaciones) {
    iconoNotificaciones.addEventListener('click', async () => {
        const lista = document.getElementById('lista-notificaciones');
        const opening = !lista.classList.contains('show');
        lista.classList.toggle('show');

        if (opening) {
            // Marcar todas como leídas
            try {
                const res = await fetch(`${APP_URL}includes/marcar_todas_notificaciones.php`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                const datos = await res.json();
                if (datos.success) {
                    document.getElementById('contador-notificaciones').textContent = '';
                    document.getElementById('contador-notificaciones').style.display = 'none';
                    document.getElementById('contador-notificaciones-movil').textContent = '';
                    document.getElementById('contador-notificaciones-movil').style.display = 'none';
                    cargarNotificaciones();
                }
            } catch (e) {
                console.error("Error al marcar todas las notificaciones:", e);
            }
        }
    });
}

if (iconoNotificacionesMovil) {
    iconoNotificacionesMovil.addEventListener('click', async () => {
        const lista = document.getElementById('lista-notificaciones');
        const opening = !lista.classList.contains('show');
        lista.classList.toggle('show');

        if (opening) {
            try {
                const res = await fetch(`${APP_URL}includes/marcar_todas_notificaciones.php`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                const datos = await res.json();
                if (datos.success) {
                    document.getElementById('contador-notificaciones').textContent = '';
                    document.getElementById('contador-notificaciones').style.display = 'none';
                    document.getElementById('contador-notificaciones-movil').textContent = '';
                    document.getElementById('contador-notificaciones-movil').style.display = 'none';
                    cargarNotificaciones();
                }
            } catch (e) {
                console.error("Error al marcar todas las notificaciones:", e);
            }
        }
    });
}

// Cargar notificaciones al inicio y cada 30 segundos
cargarNotificaciones();
setInterval(cargarNotificaciones, 30000);
</script>

<!-- Script de búsqueda -->
<script src="<?php echo APP_URL; ?>scripts/buscar.js"></script>
<?php endif; ?>
