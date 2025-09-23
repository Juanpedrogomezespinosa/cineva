<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$baseDeDatos = new Database();
$conexion = $baseDeDatos->getConnection();
$usuarioId = $_SESSION['usuario_id'];
$mensaje = '';

$peliculaId = (int)($_GET['id'] ?? 0);
if (!$peliculaId) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}

$consulta = $conexion->prepare("SELECT * FROM peliculas WHERE id = ? AND usuario_id = ?");
$consulta->execute([$peliculaId, $usuarioId]);
$pelicula = $consulta->fetch();
if (!$pelicula) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $plataforma = trim($_POST['plataforma'] ?? '');
    $visto = isset($_POST['visto']) ? 1 : 0;
    $favorito = isset($_POST['favorito']) ? 1 : 0;
    $valoracion = (int)($_POST['valoracion'] ?? 0);
    $resena = trim($_POST['resena'] ?? '');

    if (!$titulo || !$genero || !$plataforma) {
        $mensaje = 'Título, género y plataforma son obligatorios.';
    } else {
        $portada = $pelicula['portada'];
        if (!empty($_FILES['portada']['name'])) {
            $archivo = $_FILES['portada'];
            $formatosPermitidos = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

            if ($archivo['error'] === UPLOAD_ERR_OK) {
                if (in_array($extension, $formatosPermitidos) && $archivo['size'] <= 2 * 1024 * 1024) {
                    $nombreNuevo = uniqid('portada_') . '.' . $extension;
                    $directorio = __DIR__ . '/../img/portadas';

                    if (!is_dir($directorio)) {
                        mkdir($directorio, 0755, true);
                    }

                    if (is_writable($directorio)) {
                        $rutaDestino = $directorio . '/' . $nombreNuevo;
                        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                            $portadaAnterior = $pelicula['portada'];
                            $rutaAnterior = $directorio . '/' . $portadaAnterior;
                            if ($portadaAnterior && file_exists($rutaAnterior)) {
                                unlink($rutaAnterior);
                            }
                            $portada = $nombreNuevo;
                        } else {
                            $mensaje = 'Error al mover el archivo subido.';
                        }
                    } else {
                        $mensaje = 'La carpeta de destino no tiene permisos de escritura.';
                    }
                } else {
                    $mensaje = 'Formato o tamaño de imagen no válido.';
                }
            } else {
                $mensaje = 'Error al subir la imagen (código: ' . $archivo['error'] . ')';
            }
        }

        if (!$mensaje) {
            $actualizacion = $conexion->prepare("UPDATE peliculas SET titulo=?, genero=?, plataforma=?, visto=?, favorito=?, portada=?, valoracion=?, resena=? WHERE id=? AND usuario_id=?");
            $resultado = $actualizacion->execute([
                $titulo,
                $genero,
                $plataforma,
                $visto,
                $favorito,
                $portada,
                $valoracion,
                $resena,
                $peliculaId,
                $usuarioId
            ]);

            if ($resultado) {
                header('Location: ' . APP_URL . 'dashboard.php');
                exit;
            } else {
                $mensaje = 'Error al actualizar la película.';
            }
        }
    }
}

include __DIR__ . '/../templates/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar película | Cineva</title>
<link rel="stylesheet" href="<?= APP_URL ?>css/editar.css" />

</head>
<body>
    <div class="editar-contenedor">
        <h1>Editar película</h1>

        <?php if ($mensaje): ?>
            <p style="color:red;"><?= htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="<?= APP_URL ?>peliculas/editar.php?id=<?= $peliculaId; ?>">
            <div class="editar-grid">
                <!-- Columna izquierda: portada actual -->
                <div class="portada-actual">
                    <?php if ($pelicula['portada']): ?>
                        <label>Portada actual</label>
                        <img src="<?= APP_URL ?>img/portadas/<?= htmlspecialchars($pelicula['portada']); ?>" alt="Portada" />
                    <?php endif; ?>
                </div>

                <!-- Columna central: campos -->
                <div class="campo-formulario">
                    <div>
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($pelicula['titulo']); ?>" required />
                    </div>

                    <div>
                        <label for="genero">Género</label>
                        <input type="text" id="genero" name="genero" value="<?= htmlspecialchars($pelicula['genero']); ?>" required />
                    </div>

                    <div>
                        <label for="plataforma">Plataforma</label>
                        <input type="text" id="plataforma" name="plataforma" value="<?= htmlspecialchars($pelicula['plataforma']); ?>" required />
                    </div>

                    <div class="visto-favorito">
                        <label class="switch">
                            <input type="checkbox" name="visto" <?= $pelicula['visto'] ? 'checked' : ''; ?> />
                            <span class="slider"></span>
                            <span class="label-text">Visto</span>
                        </label>

                        <label class="switch">
                            <input type="checkbox" name="favorito" <?= $pelicula['favorito'] ? 'checked' : ''; ?> />
                            <span class="slider"></span>
                            <span class="label-text">Favorito</span>
                        </label>
                    </div>

                    <div class="valoracion-editar">
                        <label>Valoración</label>
                        <div class="estrellas-editar" id="estrellas">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <img src="<?= APP_URL ?>img/icons/estrella.svg" 
                                     data-value="<?= $i ?>" 
                                     alt="estrella <?= $i ?>" 
                                     class="estrella-editar <?= ($i <= $pelicula['valoracion']) ? 'activo' : '' ?>" />
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="valoracion" name="valoracion" value="<?= (int)$pelicula['valoracion']; ?>" />
                    </div>
                </div>

                <!-- Columna derecha: cambiar portada y reseña -->
                <div class="columna-derecha">
                    <div>
                        <label for="portada">Cambiar portada</label>
                        <input type="file" id="portada" name="portada" accept=".jpg,.jpeg,.png,.gif" />
                    </div>

                    <div>
                        <label for="resena">Reseña</label>
                        <textarea id="resena" name="resena" rows="6"><?= htmlspecialchars($pelicula['resena']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Actualizar película</button>
            </div>
        </form>

    </div>
<div class="volver-dashboard">
    <a href="dashboard.php">Volver al dashboard</a>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    // seleccionar las estrellas de editar (coincide con el HTML)
    const estrellas = document.querySelectorAll('#estrellas .estrella-editar');
    const campoValoracion = document.getElementById('valoracion');
    let valorSeleccionado = parseInt(campoValoracion.value) || 0;

    estrellas.forEach((estrella, indice) => {
        estrella.addEventListener('mouseover', () => {
            // ilumina hasta la estrella sobre la que pasamos
            estrellas.forEach((e, i) => {
                e.classList.toggle('activo', i <= indice);
            });
        });

        estrella.addEventListener('mouseout', () => {
            // vuelve al valor seleccionado
            estrellas.forEach((e, i) => {
                e.classList.toggle('activo', i < valorSeleccionado);
            });
        });

        estrella.addEventListener('click', () => {
            valorSeleccionado = indice + 1;
            campoValoracion.value = valorSeleccionado;

            estrellas.forEach((e, i) => {
                e.classList.toggle('activo', i < valorSeleccionado);
            });
        });
    });
});
</script>


    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
