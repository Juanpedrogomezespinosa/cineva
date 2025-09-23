<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/follows.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null);

if (!$id) {
    header('Location: ../index.php');
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// Obtener datos del usuario
$stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtUser->execute([$id]);
$usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: ../index.php');
    exit;
}

$esPerfilPropio = isset($_SESSION['usuario_id']) && ($_SESSION['usuario_id'] == $id);
$usuarioLogueado = $_SESSION['usuario_id'] ?? null;

$follows = new Follows();

// Procesar seguir/dejar de seguir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuarioLogueado && !$esPerfilPropio) {
    if (isset($_POST['seguir'])) {
        $follows->seguirUsuario($usuarioLogueado, $id);
    } elseif (isset($_POST['dejar_seguir'])) {
        $follows->dejarDeSeguirUsuario($usuarioLogueado, $id);
    }
    header("Location: perfil.php?id=" . $id);
    exit;
}

// Contadores
$seguidoresCount = $follows->contarSeguidores($id);
$seguidosCount = $follows->contarSeguidos($id);

// Número de publicaciones
$stmtPublicaciones = $pdo->prepare("SELECT COUNT(*) FROM peliculas WHERE usuario_id = ?");
$stmtPublicaciones->execute([$id]);
$totalPublicaciones = $stmtPublicaciones->fetchColumn();

// Comprobar si el usuario logueado ya sigue a este perfil
$yaSigue = $usuarioLogueado ? $follows->esSeguidor($usuarioLogueado, $id) : false;

// Obtener películas del usuario
$stmtPeliculas = $pdo->prepare("
    SELECT p.*, u.nombre AS usuario_nombre
    FROM peliculas p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.usuario_id = ?
    ORDER BY p.fecha_agregado DESC
");
$stmtPeliculas->execute([$id]);
$lista_peliculas = $stmtPeliculas->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
?>

<section class="perfil">
    <h1>
        <?php if ($esPerfilPropio): ?>
            Bienvenido, <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>!
        <?php else: ?>
            Perfil de <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>
        <?php endif; ?>
    </h1>

    <div class="perfil-info">
        <div class="avatar-perfil">
            <?php $avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : 'default.png'; ?>
            <img src="<?php echo APP_URL . 'img/avatars/' . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="Avatar de <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                 width="150" style="border-radius:50%; border:2px solid #f4bf2c;">
        </div>

        <div class="perfil-detalles">
            <div class="perfil-nombre">
                <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($esPerfilPropio): ?>
                    <a href="<?php echo APP_URL; ?>usuarios/editar-perfil.php" class="editar-perfil" title="Editar perfil">
                        <img src="<?php echo APP_URL; ?>img/icons/editar.svg" alt="Editar perfil" width="20">
                    </a>
                <?php endif; ?>
            </div>

            <div class="perfil-biografia">
                <?php
                echo !empty($usuario['biografia'])
                    ? htmlspecialchars($usuario['biografia'], ENT_QUOTES, 'UTF-8')
                    : 'Esta persona no ha agregado biografía.';
                ?>
            </div>

            <div class="perfil-social">
                <p>
                    <a class="link-social" href="seguidores.php?id=<?php echo $id; ?>" style="color:#f4bf2c;">
                        Seguidores: <strong><?php echo (int)$seguidoresCount; ?></strong>
                    </a> |
                    <a class="link-social" href="seguidos.php?id=<?php echo $id; ?>" style="color:#f4bf2c;">
                        Seguidos: <strong><?php echo (int)$seguidosCount; ?></strong>
                    </a> |
                    Publicaciones: <strong><?php echo (int)$totalPublicaciones; ?></strong>
                </p>

                <?php if (!$esPerfilPropio && $usuarioLogueado): ?>
                    <div class="perfil-acciones-botones">
                        <form method="post" action="" style="margin:0;">
                            <?php if ($yaSigue): ?>
                                <button type="submit" name="dejar_seguir" class="btn-seguir">Dejar de seguir</button>
                            <?php else: ?>
                                <button type="submit" name="seguir" class="btn-seguir">Seguir</button>
                            <?php endif; ?>
                        </form>

                        <a href="<?php echo APP_URL; ?>chats/chat.php?usuario=<?php echo $usuario['id']; ?>"
                           title="Enviar mensaje" class="btn-mensaje">
                            <img src="<?php echo APP_URL; ?>img/icons/enviar-mensaje.svg" alt="Enviar mensaje" width="24">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h2>
        <?php echo $esPerfilPropio ? 'Tus publicaciones' : 'Publicaciones de ' . htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>
    </h2>

    <?php if (count($lista_peliculas) === 0): ?>
        <p>No hay películas añadidas todavía.</p>
    <?php else: ?>
        <div class="cards-container">
            <?php foreach ($lista_peliculas as $pelicula): ?>
                <div class="card-pelicula">
                    <?php if (!empty($pelicula['portada'])): ?>
                        <a href="<?php echo APP_URL; ?>peliculas/ver.php?id=<?php echo (int)$pelicula['id']; ?>">
                            <img src="<?php echo APP_URL . 'img/portadas/' . htmlspecialchars($pelicula['portada'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="Portada de <?php echo htmlspecialchars($pelicula['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                        </a>
                    <?php else: ?>
                        <div class="sin-portada">
                            <a href="<?php echo APP_URL; ?>peliculas/ver.php?id=<?php echo (int)$pelicula['id']; ?>" style="color: inherit; text-decoration: none;">
                                Sin portada
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="card-contenido">
                        <h3>
                            <a href="<?php echo APP_URL; ?>peliculas/ver.php?id=<?php echo (int)$pelicula['id']; ?>" style="color: inherit; text-decoration: none;">
                                <?php echo htmlspecialchars($pelicula['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </h3>

                        <div class="valoracion">
                            <?php
                            $valor = (float)$pelicula['valoracion'];
                            for ($i = 1; $i <= 5; $i++) {
                                if ($valor >= $i) {
                                    echo '<img src="' . APP_URL . 'img/icons/estrella.svg" class="icono-valoracion" alt="Estrella">';
                                } elseif ($valor > $i - 1) {
                                    echo '<img src="' . APP_URL . 'img/icons/media-estrella.svg" class="icono-valoracion" alt="Media estrella">';
                                } else {
                                    echo '<img src="' . APP_URL . 'img/icons/estrella.svg" class="icono-valoracion icono-vacio" alt="Estrella vacía">';
                                }
                            }
                            ?>
                            <span class="valor-num">(<?php echo floor($valor); ?>/5)</span>
                        </div>

                        <div class="usuario-publico">
                            Agregada por:
                            <a href="<?php echo APP_URL; ?>usuarios/perfil.php?id=<?php echo (int)$pelicula['usuario_id']; ?>" style="color: #f4bf2c; font-weight: bold; text-decoration: none;">
                                <?php echo htmlspecialchars($pelicula['usuario_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>

                        <?php if ($esPerfilPropio): ?>
                            <div class="acciones-card">
                                <a href="<?php echo APP_URL; ?>peliculas/editar.php?id=<?php echo (int)$pelicula['id']; ?>" title="Editar">
                                    <img src="<?php echo APP_URL; ?>img/icons/editar.svg" alt="Editar">
                                </a>

                                <a href="#"
                                   class="btn-eliminar-pelicula"
                                   data-id="<?php echo (int)$pelicula['id']; ?>"
                                   title="Eliminar">
                                    <img src="<?php echo APP_URL; ?>img/icons/delete.svg" alt="Eliminar">
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<!-- Modal de confirmación -->
<div id="modalEliminar" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-contenido" role="document">
    <p style="margin:0 0 1rem 0;">¿Seguro que quieres eliminar esta película?</p>
    <div class="modal-botones" style="display:flex; gap:8px; justify-content:center;">
      <button id="modalConfirmar" class="btn-guardar" type="button">Aceptar</button>
      <button id="modalCancelar" class="btn-cancelar" type="button">Cancelar</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalEliminar');
    const modalConfirmar = document.getElementById('modalConfirmar');
    const modalCancelar = document.getElementById('modalCancelar');
    let peliculaId = null;

    // función reutilizable para cerrar modal
    function cerrarModal() {
        // si el foco está dentro del modal, lo sacamos
        if (document.activeElement && modal.contains(document.activeElement)) {
            document.activeElement.blur();
        }
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        peliculaId = null;
    }

    // Abrir modal
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-eliminar-pelicula');
        if (!btn) return;

        e.preventDefault();
        peliculaId = btn.dataset.id;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');

        // dar foco al botón aceptar para accesibilidad
        if (modalConfirmar) modalConfirmar.focus();
    });

    // Confirmar eliminación
    modalConfirmar.addEventListener('click', () => {
        if (!peliculaId) return;

        const data = new FormData();
        data.append('id', peliculaId);

        fetch('<?php echo APP_URL; ?>peliculas/eliminar.php', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                const card = document.querySelector(`.card-pelicula .btn-eliminar-pelicula[data-id="${peliculaId}"]`).closest('.card-pelicula');
                if (card) card.remove();
            } else {
                alert(json.error || 'No se pudo eliminar la película.');
            }
        })
        .catch(() => alert('Error de conexión'))
        .finally(() => {
            cerrarModal();
        });
    });

    // Cancelar
    modalCancelar.addEventListener('click', () => {
        cerrarModal();
    });

    // Cerrar al hacer clic fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            cerrarModal();
        }
    });

    // Cerrar con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            cerrarModal();
        }
    });
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
