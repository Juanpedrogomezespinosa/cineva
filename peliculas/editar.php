<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$db = new Database();
$pdo = $db->getConnection();
$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';

// Obtener id de la película a editar
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}

// Comprobar que la película pertenece al usuario
$stmt = $pdo->prepare("SELECT * FROM peliculas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $usuario_id]);
$pelicula = $stmt->fetch();
if (!$pelicula) {
    header('Location: ' . APP_URL . 'dashboard.php');
    exit;
}

// Si se envía formulario actualizar
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
            $permitidos = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if ($archivo['error'] === UPLOAD_ERR_OK) {
                if (in_array($extension, $permitidos) && $archivo['size'] <= 2 * 1024 * 1024) {
                    $nuevoNombre = uniqid('portada_') . '.' . $extension;

                    $directorioPortadas = __DIR__ . '/../img/portadas';

                    if (!is_dir($directorioPortadas)) {
                        mkdir($directorioPortadas, 0755, true);
                    }

                    if (!is_writable($directorioPortadas)) {
                        $mensaje = 'La carpeta de destino no tiene permisos de escritura.';
                    } else {
                        $rutaAbsoluta = $directorioPortadas . '/' . $nuevoNombre;

                        if (move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
                            // Borrar imagen anterior si existe
                            $portadaAnterior = $pelicula['portada'];
                            $rutaAnterior = $directorioPortadas . '/' . $portadaAnterior;
                            if ($portadaAnterior && file_exists($rutaAnterior)) {
                                unlink($rutaAnterior);
                            }
                            $portada = $nuevoNombre;
                        } else {
                            $mensaje = 'Error al mover el archivo subido.';
                        }
                    }
                } else {
                    $mensaje = 'Formato o tamaño de imagen no válido.';
                }
            } else {
                $mensaje = 'Error al subir la imagen (código: ' . $archivo['error'] . ')';
            }
        }

        if (!$mensaje) {
            $sql = "UPDATE peliculas SET titulo=?, genero=?, plataforma=?, visto=?, favorito=?, portada=?, valoracion=?, resena=? WHERE id=? AND usuario_id=?";
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                $titulo,
                $genero,
                $plataforma,
                $visto,
                $favorito,
                $portada,
                $valoracion,
                $resena,
                $id,
                $usuario_id
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Editar película | Cineva</title>
<link rel="stylesheet" href="<?= APP_URL ?>css/styles.css" />
</head>
<body>
<h1>Editar película</h1>

<?php if ($mensaje): ?>
    <p style="color:red;"><?= htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="<?= APP_URL ?>peliculas/editar.php?id=<?= $id; ?>">
    <label for="titulo">Título:</label><br />
    <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($pelicula['titulo']); ?>" required /><br /><br />

    <label for="genero">Género:</label><br />
    <input type="text" id="genero" name="genero" value="<?= htmlspecialchars($pelicula['genero']); ?>" required /><br /><br />

    <label for="plataforma">Plataforma:</label><br />
    <input type="text" id="plataforma" name="plataforma" value="<?= htmlspecialchars($pelicula['plataforma']); ?>" required /><br /><br />

    <label>
        <input type="checkbox" name="visto" <?= $pelicula['visto'] ? 'checked' : ''; ?> />
        Visto
    </label><br />

    <label>
        <input type="checkbox" name="favorito" <?= $pelicula['favorito'] ? 'checked' : ''; ?> />
        Favorito
    </label><br /><br />

    <?php if ($pelicula['portada']): ?>
        <img src="<?= APP_URL ?>img/portadas/<?= htmlspecialchars($pelicula['portada']); ?>" alt="Portada" style="max-width:150px;" /><br />
    <?php endif; ?>

    <label for="portada">Cambiar portada (imagen jpg/png/gif, máx 2MB):</label><br />
    <input type="file" id="portada" name="portada" accept=".jpg,.jpeg,.png,.gif" /><br /><br />

    <label for="valoracion">Valoración (1-5):</label><br />
    <input type="number" id="valoracion" name="valoracion" min="1" max="5" value="<?= (int)$pelicula['valoracion']; ?>" /><br /><br />

    <label for="resena">Reseña:</label><br />
    <textarea id="resena" name="resena" rows="4" cols="50"><?= htmlspecialchars($pelicula['resena']); ?></textarea><br /><br />

    <button type="submit">Actualizar película</button>
</form>

<a href="<?= APP_URL ?>dashboard.php">Volver al dashboard</a>
</body>
</html>
