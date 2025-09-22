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

<div class="datos-pelicula-tarjeta">
    <!-- Título grande -->


    <!-- Valoración con estrellas -->
<div class="valoracion">
    <?php
    $valor = (float)$pelicula['valoracion']; // valor puede ser decimal
    $maxEstrellas = 5;

    for ($i = 1; $i <= $maxEstrellas; $i++) {
        if ($valor >= $i) {
            // Estrella completa
            echo '<img src="' . APP_URL . 'img/icons/estrella.svg" alt="Estrella" class="icono-valoracion">';
        } elseif ($valor >= $i - 0.5) {
            // Media estrella
            echo '<img src="' . APP_URL . 'img/icons/media-estrella.svg" alt="Media estrella" class="icono-valoracion">';
        } else {
            // Estrella vacía: usamos la misma estrella pero con opacidad reducida
            echo '<img src="' . APP_URL . 'img/icons/estrella.svg" alt="" class="icono-valoracion icono-vacio">';
        }
    }
    ?>
    <span class="valor-num">(<?= htmlspecialchars($pelicula['valoracion']); ?>/5)</span>
</div>


    <!-- Reseña destacada -->
    <?php if (!empty($pelicula['resena'])): ?>
        <blockquote class="reseña">
            “<?= nl2br(htmlspecialchars($pelicula['resena'])); ?>”
        </blockquote>
    <?php endif; ?>

    <!-- Información adicional en filas con emojis -->
    <div class="info-adicional">
        <p>🎭 <strong>Género:</strong> <?= htmlspecialchars($pelicula['genero']); ?></p>
        <p>💻 <strong>Plataforma:</strong> <?= htmlspecialchars($pelicula['plataforma']); ?></p>
        <p>👁️ <strong>Visto:</strong> <?= $pelicula['visto'] ? 'Sí' : 'No'; ?></p>
        <p>❤️ <strong>Favorito:</strong> <?= $pelicula['favorito'] ? 'Sí' : 'No'; ?></p>
    </div>

    <!-- Autor y fecha -->
    <div class="autor-fecha">
        📝 Agregada por: 
        <a href="<?= APP_URL ?>usuarios/perfil.php?id=<?= $pelicula['usuario_id']; ?>">
            <?= htmlspecialchars($pelicula['usuario_nombre']); ?>
        </a>
        | 📅 <?= $pelicula['fecha_agregado']; ?>
    </div>
</div>

    <!-- Nota de la comunidad -->
    <section class="nota-comunidad">
        <h3>⭐ Nota de la comunidad</h3>

        <?php
        // Obtener nota actual de la comunidad
        $stmtMedia = $pdo->prepare("SELECT AVG(estrellas) AS media, COUNT(*) AS total FROM votos WHERE pelicula_id = ?");
        $stmtMedia->execute([$pelicula['id']]);
        $stats = $stmtMedia->fetch(PDO::FETCH_ASSOC);
        $media = $stats['media'] ? round($stats['media'], 2) : 0;
        $totalVotos = (int)$stats['total'];

        // Saber si este usuario ya votó
        $usuarioVoto = null;
        if ($usuario_id) {
            $stmtVoto = $pdo->prepare("SELECT estrellas FROM votos WHERE pelicula_id = ? AND usuario_id = ?");
            $stmtVoto->execute([$pelicula['id'], $usuario_id]);
            $usuarioVoto = $stmtVoto->fetchColumn();
        }
        ?>

<?php $mediaRedondeada = round($media); ?>
<div id="estrellas-votacion" class="estrellas-votacion" data-pelicula="<?= $pelicula['id']; ?>" data-voto="<?= $usuarioVoto ?? 0; ?>">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <span class="estrella <?= ($i <= $mediaRedondeada) ? 'activa' : ''; ?>" data-valor="<?= $i; ?>">★</span>
    <?php endfor; ?>
</div>
<p id="media-comunidad">
    Media: <strong><?= $media; ?></strong>/5 (<?= $totalVotos; ?> votos)
</p>
<?php if ($usuario_id): ?>
    <p id="tu-voto">
        <?= $usuarioVoto ? "Tu voto: {$usuarioVoto}/5" : "Aún no has votado"; ?>
    </p>
<?php endif; ?>

    </section>



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
                        <div class="comentario-contenido">
                            <div>
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
                            </div>

                            <?php if ($usuario_id && $usuario_id == $com['usuario_id']): ?>
                                <div class="acciones-comentario">
                                    <img src="<?= APP_URL ?>img/icons/editar.svg" alt="Editar" class="icono-accion btn-editar" data-id="<?= $com['id']; ?>">
                                    <img src="<?= APP_URL ?>img/icons/delete.svg" alt="Eliminar" class="icono-accion btn-eliminar" data-id="<?= $com['id']; ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

   <div class="volver-dashboard">
    <a href="<?= APP_URL ?>dashboard.php">Volver al dashboard</a>
</div>

</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const usuarioId = <?= $usuario_id ? $usuario_id : 'null'; ?>;

    // -------------------
    // Comentarios
    // -------------------
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
                    <div class="comentario-contenido">
                        <div>
                            <div class="usuario-comentario">
                                <img src="<?= APP_URL ?>img/avatars/${com.avatar}" alt="${com.usuario_nombre}" class="avatar">
                                <strong><a href="<?= APP_URL ?>usuarios/perfil.php?id=${com.usuario_id}" class="link-social">${com.usuario_nombre}</a></strong>
                                <span class="fecha">${com.fecha_comentario}</span>
                            </div>
                            <p id="texto_${com.id}">${com.comentario.replace(/\n/g, '<br>')}</p>
                        </div>
                        <div class="acciones-comentario">
                            <img src="<?= APP_URL ?>img/icons/editar.svg" alt="Editar" class="icono-accion btn-editar" data-id="${com.id}">
                            <img src="<?= APP_URL ?>img/icons/delete.svg" alt="Eliminar" class="icono-accion btn-eliminar" data-id="${com.id}">
                        </div>
                    </div>
                `;
                document.getElementById('lista-comentarios').appendChild(div);
                textarea.value = '';
            })
            .catch(error => console.error(error));
        });
    }

    // -------------------
    // Editar y eliminar comentarios
    // -------------------
    document.addEventListener('click', function(e) {
        // Editar comentario
        if (e.target.classList.contains('btn-editar')) {
            const id = e.target.dataset.id;
            const p = document.getElementById('texto_' + id);
            const textoActual = p.innerText;

            const textarea = document.createElement('textarea');
            textarea.value = textoActual;
            textarea.classList.add('textarea-editar');

            const btnGuardar = document.createElement('button');
            btnGuardar.textContent = 'Guardar';
            btnGuardar.classList.add('btn-guardar');

            const btnCancelar = document.createElement('button');
            btnCancelar.textContent = 'Cancelar';
            btnCancelar.classList.add('btn-cancelar');

            p.replaceWith(textarea);
            textarea.insertAdjacentElement('afterend', btnGuardar);
            btnGuardar.insertAdjacentElement('afterend', btnCancelar);

            btnGuardar.addEventListener('click', function() {
                const nuevoTexto = textarea.value.trim();
                if (nuevoTexto === "") return;

                const data = new FormData();
                data.append('id', id);
                data.append('comentario', nuevoTexto);

                fetch('editar_comentario.php', {
                    method: 'POST',
                    body: data
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const nuevoP = document.createElement('p');
                        nuevoP.id = 'texto_' + id;
                        nuevoP.innerHTML = res.comentario;
                        textarea.replaceWith(nuevoP);
                        btnGuardar.remove();
                        btnCancelar.remove();
                    } else {
                        alert("No se pudo editar.");
                    }
                });
            });

            btnCancelar.addEventListener('click', function() {
                textarea.replaceWith(p);
                btnGuardar.remove();
                btnCancelar.remove();
            });
        }

        // Eliminar comentario
        if (e.target.classList.contains('btn-eliminar')) {
            const id = e.target.dataset.id;
            const comentarioDiv = document.getElementById('comentario_' + id);

            const btnConfirmar = document.createElement('button');
            btnConfirmar.textContent = 'Confirmar eliminación';
            btnConfirmar.classList.add('btn-confirmar');

            const btnCancelar = document.createElement('button');
            btnCancelar.textContent = 'Cancelar';
            btnCancelar.classList.add('btn-cancelar');

            e.target.style.display = 'none';
            comentarioDiv.querySelector('.acciones-comentario').appendChild(btnConfirmar);
            comentarioDiv.querySelector('.acciones-comentario').appendChild(btnCancelar);

            btnConfirmar.addEventListener('click', function() {
                const data = new FormData();
                data.append('id', id);

                fetch('eliminar_comentario.php', {
                    method: 'POST',
                    body: data
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        comentarioDiv.remove();
                    } else {
                        alert("No se pudo eliminar.");
                    }
                });
            });

            btnCancelar.addEventListener('click', function() {
                btnConfirmar.remove();
                btnCancelar.remove();
                e.target.style.display = 'inline';
            });
        }
    });

    // -------------------
    // Votación con estrellas
    // -------------------
    const estrellasCont = document.getElementById('estrellas-votacion');
    if (estrellasCont) {
        const estrellas = estrellasCont.querySelectorAll('.estrella');
        const peliculaId = parseInt(estrellasCont.dataset.pelicula);
        let votoUsuario = parseInt(estrellasCont.dataset.voto);

        function pintarEstrellas(valor) {
            estrellas.forEach((estrella, idx) => {
                estrella.classList.toggle('activa', idx < valor);
            });
        }

        // Hover
        estrellas.forEach((estrella, idx) => {
            estrella.addEventListener('mouseenter', () => pintarEstrellas(idx + 1));
            estrella.addEventListener('mouseleave', () => pintarEstrellas(votoUsuario));
        });

        // Click
        estrellas.forEach((estrella, idx) => {
            estrella.addEventListener('click', () => {
                const valor = idx + 1;

                fetch('../includes/votos_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        pelicula_id: peliculaId,
                        estrellas: valor
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        votoUsuario = valor;

                        // Mostrar primero el voto del usuario
                        pintarEstrellas(valor);
                        if (usuarioId) {
                            const tuVotoSpan = document.getElementById('tu-voto');
                            if (tuVotoSpan) {
                                tuVotoSpan.innerText = `Tu voto: ${valor}/5`;
                            }
                        }

                        // Actualizar la media en texto
                        const mediaSpan = document.getElementById('media-comunidad');
                        if (mediaSpan) {
                            mediaSpan.innerHTML = `Media: <strong>${res.media}</strong>/5 (${res.total} votos)`;
                        }

                        // Pasados 2 segundos, volver a pintar la media redondeada
                        setTimeout(() => {
                            pintarEstrellas(Math.round(res.media));
                        }, 2000);

                    } else {
                        alert(res.error || 'Error al votar');
                    }
                });
            });
        });

        // Inicial → pintar la media redondeada
        pintarEstrellas(Math.round(<?= $media; ?>));
    }

});
</script>


<?php include __DIR__ . '/../templates/footer.php'; ?>
