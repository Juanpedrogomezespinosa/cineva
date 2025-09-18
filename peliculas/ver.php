<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;

$db = new Database();
$pdo = $db->getConnection();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}

// Obtener película
$stmt = $pdo->prepare("SELECT p.*, u.nombre AS usuario_nombre FROM peliculas p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$pelicula = $stmt->fetch();

if (!$pelicula) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}

// Obtener comentarios
$stmtComentarios = $pdo->prepare("
    SELECT c.*, u.nombre AS usuario_nombre, u.avatar, u.id AS usuario_id
    FROM comentarios c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.pelicula_id = ?
    ORDER BY c.fecha_comentario ASC
");
$stmtComentarios->execute([$id]);
$comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);

// Procesar comentario AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario_id && isset($_POST['ajax']) && $_POST['ajax'] == 1) {
    $texto = trim($_POST['comentario']);
    if ($texto !== '') {
        $stmtInsert = $pdo->prepare("INSERT INTO comentarios (usuario_id, pelicula_id, comentario) VALUES (?, ?, ?)");
        $stmtInsert->execute([$usuario_id, $pelicula['id'], $texto]);

        $nuevo_id = $pdo->lastInsertId();

        // Crear notificación solo si el autor del comentario no es el dueño de la película
        if ($pelicula['usuario_id'] != $usuario_id) {
            $stmtNotif = $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, origen_id, relacion_id) 
                                        VALUES (?, 'comentario', ?, ?)");
            $stmtNotif->execute([$pelicula['usuario_id'], $usuario_id, $nuevo_id]);
        }

        $stmtNuevo = $pdo->prepare("
            SELECT c.*, u.nombre AS usuario_nombre, u.avatar, u.id AS usuario_id
            FROM comentarios c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.id = ?
        ");
        $stmtNuevo->execute([$nuevo_id]);
        $nuevoComentario = $stmtNuevo->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($nuevoComentario);
        exit;
    }
}

include __DIR__ . '/../templates/header.php';
?>

<section class="pelicula-detalle">
    <div class="portada-horizontal">
        <?php if ($pelicula['portada']): ?>
            <img src="<?= APP_URL ?>img/portadas/<?= htmlspecialchars($pelicula['portada']); ?>" alt="Banner <?= htmlspecialchars($pelicula['titulo']); ?>">
            <div class="titulo-banner"><?= htmlspecialchars($pelicula['titulo']); ?></div>
        <?php endif; ?>
    </div>

    <div class="datos-pelicula">
        <p><strong>Género:</strong> <?= htmlspecialchars($pelicula['genero']); ?></p>
        <p><strong>Plataforma:</strong> <?= htmlspecialchars($pelicula['plataforma']); ?></p>
        <p><strong>Visto:</strong> <?= $pelicula['visto'] ? 'Sí' : 'No'; ?></p>
        <p><strong>Favorito:</strong> <?= $pelicula['favorito'] ? 'Sí' : 'No'; ?></p>
        <p><strong>Valoración:</strong> <?= (int)$pelicula['valoracion']; ?> / 5</p>
        <p><strong>Reseña:</strong> <?= nl2br(htmlspecialchars($pelicula['resena'])); ?></p>
        <p><strong>Agregada por:</strong>
            <a href="<?= APP_URL ?>usuarios/perfil.php?id=<?= $pelicula['usuario_id']; ?>">
                <?= htmlspecialchars($pelicula['usuario_nombre']); ?>
            </a>
        </p>
        <p><strong>Fecha de agregado:</strong> <?= $pelicula['fecha_agregado']; ?></p>
    </div>

    <section class="comentarios">
        <h2>Comentarios</h2>

        <?php if ($usuario_id): ?>
            <form id="form-comentario" class="form-comentario">
                <textarea name="comentario" placeholder="Escribe tu comentario..." required></textarea>
                <button type="submit">Comentar</button>
            </form>
        <?php else: ?>
            <p><a href="<?= APP_URL ?>usuarios/login.php">Inicia sesión</a> para comentar.</p>
        <?php endif; ?>

        <div id="lista-comentarios" class="lista-comentarios">
            <?php if (count($comentarios) === 0): ?>
                <p>No hay comentarios aún.</p>
            <?php else: ?>
                <?php foreach ($comentarios as $com): ?>
                    <div class="comentario" id="comentario_<?= $com['id']; ?>">
                        <div class="usuario-comentario">
                            <img src="<?= APP_URL ?>img/avatars/<?= htmlspecialchars($com['avatar']); ?>" alt="<?= htmlspecialchars($com['usuario_nombre']); ?>" class="avatar">
                            <strong>
                                <a href="<?= APP_URL ?>usuarios/perfil.php?id=<?= $com['usuario_id']; ?>" class="link-social">
                                    <?= htmlspecialchars($com['usuario_nombre']); ?>
                                </a>
                            </strong>
                            <span class="fecha"><?= $com['fecha_comentario']; ?></span>
                        </div>
                        <p id="texto_<?= $com['id']; ?>"><?= nl2br(htmlspecialchars($com['comentario'])); ?></p>

                        <?php if ($usuario_id && $usuario_id == $com['usuario_id']): ?>
                            <div class="acciones-comentario">
                                <img src="<?= APP_URL ?>img/icons/editar.svg" alt="Editar" class="icono-accion btn-editar" data-id="<?= $com['id']; ?>">
                                <img src="<?= APP_URL ?>img/icons/delete.svg" alt="Eliminar" class="icono-accion btn-eliminar" data-id="<?= $com['id']; ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <a href="<?= APP_URL ?>dashboard.php">Volver al dashboard</a>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-comentario');
    if (form) {
        form.addEventListener('submit', function(evento) {
            evento.preventDefault();
            const textarea = form.querySelector('textarea[name="comentario"]');
            const comentario = textarea.value.trim();
            if (!comentario) return;

            const data = new FormData();
            data.append('comentario', comentario);
            data.append('ajax', 1);

            fetch('ver.php?id=<?= $pelicula['id']; ?>', {
                method: 'POST',
                body: data
            })
            .then(respuesta => respuesta.json())
            .then(com => {
                const div = document.createElement('div');
                div.classList.add('comentario');
                div.id = 'comentario_' + com.id;
                div.innerHTML = `
                    <div class="usuario-comentario">
                        <img src="<?= APP_URL ?>img/avatars/${com.avatar}" alt="${com.usuario_nombre}" class="avatar">
                        <strong><a href="<?= APP_URL ?>usuarios/perfil.php?id=${com.usuario_id}" class="link-social">${com.usuario_nombre}</a></strong>
                        <span class="fecha">${com.fecha_comentario}</span>
                    </div>
                    <p id="texto_${com.id}">${com.comentario.replace(/\n/g, '<br>')}</p>
                    <div class="acciones-comentario">
                        <img src="<?= APP_URL ?>img/icons/editar.svg" alt="Editar" class="icono-accion btn-editar" data-id="${com.id}">
                        <img src="<?= APP_URL ?>img/icons/delete.svg" alt="Eliminar" class="icono-accion btn-eliminar" data-id="${com.id}">
                    </div>
                `;
                document.getElementById('lista-comentarios').appendChild(div);
                textarea.value = '';
            })
            .catch(error => console.error(error));
        });
    }

    // Eventos para editar y eliminar
    document.addEventListener('click', function(e) {
        // Editar
        if (e.target.classList.contains('btn-editar')) {
            const id = e.target.dataset.id;
            const p = document.getElementById('texto_' + id);
            const textoActual = p.innerText;

            const nuevoTexto = prompt("Editar comentario:", textoActual);
            if (nuevoTexto && nuevoTexto.trim() !== "") {
                const data = new FormData();
                data.append('id', id);
                data.append('comentario', nuevoTexto);

                fetch('editar_comentario.php', {
                    method: 'POST',
                    body: data
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        p.innerHTML = res.comentario;
                    } else {
                        alert("No se pudo editar.");
                    }
                });
            }
        }

        // Eliminar
        if (e.target.classList.contains('btn-eliminar')) {
            const id = e.target.dataset.id;
            if (confirm("¿Seguro que quieres eliminar este comentario?")) {
                const data = new FormData();
                data.append('id', id);

                fetch('eliminar_comentario.php', {
                    method: 'POST',
                    body: data
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('comentario_' + id).remove();
                    } else {
                        alert("No se pudo eliminar.");
                    }
                });
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
