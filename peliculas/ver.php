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
    let comentarioAEliminar = null;
    let comentarioIdAEliminar = null;

    // -------------------
    // Comentarios
    // -------------------
    const formularioComentario = document.getElementById('form-comentario');
    if (formularioComentario) {
        formularioComentario.addEventListener('submit', function(evento) {
            evento.preventDefault();
            const campoTexto = formularioComentario.querySelector('textarea[name="comentario"]');
            const textoComentario = campoTexto.value.trim();
            if (!textoComentario) return;

            const datos = new FormData();
            datos.append('comentario', textoComentario);
            datos.append('ajax', 1);

            fetch('ver.php?id=<?= $pelicula['id']; ?>', {
                method: 'POST',
                body: datos
            })
            .then(respuesta => respuesta.json())
            .then(comentario => {
                const nuevoDiv = document.createElement('div');
                nuevoDiv.classList.add('comentario');
                nuevoDiv.id = 'comentario_' + comentario.id;
                nuevoDiv.innerHTML = `
                    <div class="comentario-contenido">
                        <div>
                            <div class="usuario-comentario">
                                <img src="<?= APP_URL ?>img/avatars/${comentario.avatar}" alt="${comentario.usuario_nombre}" class="avatar">
                                <strong><a href="<?= APP_URL ?>usuarios/perfil.php?id=${comentario.usuario_id}" class="link-social">${comentario.usuario_nombre}</a></strong>
                                <span class="fecha">${comentario.fecha_comentario}</span>
                            </div>
                            <p id="texto_${comentario.id}">${comentario.comentario.replace(/\n/g, '<br>')}</p>
                        </div>
                        <div class="acciones-comentario">
                            <img src="<?= APP_URL ?>img/icons/editar.svg" alt="Editar" class="icono-accion btn-editar" data-id="${comentario.id}">
                            <img src="<?= APP_URL ?>img/icons/delete.svg" alt="Eliminar" class="icono-accion btn-eliminar" data-id="${comentario.id}">
                        </div>
                    </div>
                `;
                document.getElementById('lista-comentarios').appendChild(nuevoDiv);
                campoTexto.value = '';
            })
            .catch(error => console.error(error));
        });
    }

    // -------------------
    // Editar y eliminar comentarios
    // -------------------
    document.addEventListener('click', function(evento) {
        // Editar comentario
        if (evento.target.classList.contains('btn-editar')) {
            const id = evento.target.dataset.id;
            const parrafo = document.getElementById('texto_' + id);
            const textoOriginal = parrafo.innerText;

            const campoEdicion = document.createElement('textarea');
            campoEdicion.value = textoOriginal;
            campoEdicion.classList.add('textarea-editar');

            const botonGuardar = document.createElement('button');
            botonGuardar.textContent = 'Guardar';
            botonGuardar.classList.add('btn-guardar');

            const botonCancelar = document.createElement('button');
            botonCancelar.textContent = 'Cancelar';
            botonCancelar.classList.add('btn-cancelar');

            parrafo.replaceWith(campoEdicion);
            campoEdicion.insertAdjacentElement('afterend', botonGuardar);
            botonGuardar.insertAdjacentElement('afterend', botonCancelar);

            botonGuardar.addEventListener('click', function() {
                const nuevoTexto = campoEdicion.value.trim();
                if (nuevoTexto === "") return;

                const datos = new FormData();
                datos.append('id', id);
                datos.append('comentario', nuevoTexto);

                fetch('editar_comentario.php', {
                    method: 'POST',
                    body: datos
                })
                .then(respuesta => respuesta.json())
                .then(respuesta => {
                    if (respuesta.success) {
                        const nuevoParrafo = document.createElement('p');
                        nuevoParrafo.id = 'texto_' + id;
                        nuevoParrafo.innerHTML = respuesta.comentario;
                        campoEdicion.replaceWith(nuevoParrafo);
                        botonGuardar.remove();
                        botonCancelar.remove();
                    } else {
                        alert("No se pudo editar.");
                    }
                });
            });

            botonCancelar.addEventListener('click', function() {
                campoEdicion.replaceWith(parrafo);
                botonGuardar.remove();
                botonCancelar.remove();
            });
        }

        // Eliminar comentario
        if (evento.target.classList.contains('btn-eliminar')) {
            const id = evento.target.dataset.id;
            const comentarioDiv = document.getElementById('comentario_' + id);

            if (window.innerWidth <= 600) {
                comentarioAEliminar = comentarioDiv;
                comentarioIdAEliminar = id;
                document.getElementById('modal-eliminar').style.display = 'flex';
            } else {
                const botonConfirmar = document.createElement('button');
                botonConfirmar.textContent = 'Confirmar eliminación';
                botonConfirmar.classList.add('btn-confirmar');

                const botonCancelar = document.createElement('button');
                botonCancelar.textContent = 'Cancelar';
                botonCancelar.classList.add('btn-cancelar');

                evento.target.style.display = 'none';
                comentarioDiv.querySelector('.acciones-comentario').appendChild(botonConfirmar);
                comentarioDiv.querySelector('.acciones-comentario').appendChild(botonCancelar);

                botonConfirmar.addEventListener('click', function() {
                    const datos = new FormData();
                    datos.append('id', id);

                    fetch('eliminar_comentario.php', {
                        method: 'POST',
                        body: datos
                    })
                    .then(respuesta => respuesta.json())
                    .then(respuesta => {
                        if (respuesta.success) {
                            comentarioDiv.remove();
                        } else {
                            alert("No se pudo eliminar.");
                        }
                    });
                });

                botonCancelar.addEventListener('click', function() {
                    botonConfirmar.remove();
                    botonCancelar.remove();
                    evento.target.style.display = 'inline';
                });
            }
        }
    });

    // Confirmar desde el modal
    const botonModalConfirmar = document.getElementById('btn-confirmar-modal');
    const botonModalCancelar = document.getElementById('btn-cancelar-modal');

    if (botonModalConfirmar && botonModalCancelar) {
        botonModalConfirmar.addEventListener('click', function() {
            const datos = new FormData();
            datos.append('id', comentarioIdAEliminar);

            fetch('eliminar_comentario.php', {
                method: 'POST',
                body: datos
            })
            .then(respuesta => respuesta.json())
            .then(respuesta => {
                if (respuesta.success && comentarioAEliminar) {
                    comentarioAEliminar.remove();
                } else {
                    alert("No se pudo eliminar.");
                }
                document.getElementById('modal-eliminar').style.display = 'none';
            });
        });

        botonModalCancelar.addEventListener('click', function() {
            document.getElementById('modal-eliminar').style.display = 'none';
        });
    }

    // -------------------
    // Votación con estrellas
    // -------------------
    const contenedorEstrellas = document.getElementById('estrellas-votacion');
    if (contenedorEstrellas) {
        const estrellas = contenedorEstrellas.querySelectorAll('.estrella');
        const peliculaId = parseInt(contenedorEstrellas.dataset.pelicula);
        let votoUsuario = parseInt(contenedorEstrellas.dataset.voto);

        function pintarEstrellas(valor) {
            estrellas.forEach((estrella, indice) => {
                estrella.classList.toggle('activa', indice < valor);
            });
        }

        estrellas.forEach((estrella, indice) => {
            estrella.addEventListener('mouseenter', () => pintarEstrellas(indice + 1));
            estrella.addEventListener('mouseleave', () => pintarEstrellas(votoUsuario));
            estrella.addEventListener('click', () => {
                const valor = indice + 1;

                fetch('../includes/votos_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        pelicula_id: peliculaId,
                        estrellas: valor
                    })
                })
                .then(respuesta => respuesta.json())
                .then(respuesta => {
                    if (respuesta.success) {
                        votoUsuario = valor;
                        pintarEstrellas(valor);

                        const tuVoto = document.getElementById('tu-voto');
                        if (tuVoto) {
                            tuVoto.innerText = `Tu voto: ${valor}/5`;
                        }

                        const mediaComunidad = document.getElementById('media-comunidad');
                        if (mediaComunidad) {
                            mediaComunidad.innerHTML = `Media: <strong>${respuesta.media}</strong>/5 (${respuesta.total} votos)`;
                        }

                        setTimeout(() => {
                            pintarEstrellas(Math.round(respuesta.media));
                        }, 2000);
                    } else {
                        alert(respuesta.error || 'Error al votar');
                    }
                });
            });
        });

        pintarEstrellas(Math.round(<?= $media; ?>));
    }
});
</script>
<div id="modal-eliminar" class="modal">
  <div class="modal-contenido">
    <p>¿Estás seguro de que quieres eliminar el comentario?</p>
    <div class="modal-botones">
      <button id="btn-confirmar-modal" class="btn-confirmar">Confirmar</button>
      <button id="btn-cancelar-modal" class="btn-cancelar">Cancelar</button>
    </div>
  </div>
</div>



<?php include __DIR__ . '/../templates/footer.php'; ?>
