<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$mensaje = '';
$database = new Database();
$conexion = $database->getConnection();

$usuarioId = $_SESSION['usuario_id'] ?? null;

// =========================
// PROCESAR FORMULARIO
// =========================
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
        $portada = null;

        if (!empty($_FILES['portada']['name'])) {
            $archivo = $_FILES['portada'];
            $formatosPermitidos = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

            if ($archivo['error'] === UPLOAD_ERR_OK) {
                if (in_array($extension, $formatosPermitidos) && $archivo['size'] <= 2 * 1024 * 1024) {
                    $nuevoNombre = uniqid('portada_') . '.' . $extension;
                    $carpetaPortadas = __DIR__ . '/../img/portadas';

                    if (!is_dir($carpetaPortadas)) {
                        mkdir($carpetaPortadas, 0755, true);
                    }

                    if (is_writable($carpetaPortadas)) {
                        $rutaDestino = $carpetaPortadas . '/' . $nuevoNombre;
                        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                            $portada = $nuevoNombre;
                        } else {
                            $mensaje = 'Error al mover el archivo subido.';
                        }
                    } else {
                        $mensaje = 'La carpeta de destino no tiene permisos de escritura.';
                    }
                } else {
                    $mensaje = 'Formato de imagen no permitido o tamaño excedido.';
                }
            } else {
                $mensaje = 'Error en la subida del archivo (código: ' . $archivo['error'] . ')';
            }
        }

        if (!$mensaje) {
            $sql = "INSERT INTO peliculas 
                (usuario_id, titulo, genero, plataforma, visto, favorito, portada, valoracion, resena, fecha_agregado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $sentencia = $conexion->prepare($sql);
            $resultado = $sentencia->execute([
                $usuarioId,
                $titulo,
                $genero,
                $plataforma,
                $visto,
                $favorito,
                $portada,
                $valoracion,
                $resena
            ]);

            if ($resultado) {
                header('Location: ' . APP_URL . 'dashboard.php');
                exit;
            } else {
                $mensaje = 'Error al guardar la película en la base de datos.';
            }
        }
    }
}

// =========================
// MOSTRAR HTML
// =========================
include __DIR__ . '/../templates/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Agregar película | Cineva</title>
    <link rel="stylesheet" href="<?= APP_URL ?>css/styles.css" />
</head>
<body>
    <div class="containter-principal">
        <div class="container-agregar">

            <h1>Agregar nueva película</h1>

            <?php if ($mensaje): ?>
                <p style="color:red;"><?= htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" action="<?= APP_URL ?>peliculas/agregar.php">
                <div class="form-grid">
                    <!-- Columna izquierda -->
                    <div class="form-left">
                        <div class="form-group">
                            <label for="titulo">Título</label>
                            <input type="text" id="titulo" name="titulo" required />
                        </div>

                        <div class="form-group">
                            <label for="genero">Género</label>
                            <input type="text" id="genero" name="genero" required />
                        </div>

                        <div class="form-group">
                            <label for="plataforma">Plataforma</label>
                            <input type="text" id="plataforma" name="plataforma" required />
                        </div>

                        <div class="container-checkbox">
                            <label><input type="checkbox" name="visto" /> Visto</label>
                            <label><input type="checkbox" name="favorito" /> Favorito</label>
                        </div>

                        <div class="form-group">
                            <label for="valoracion">Valoración (1-5)</label>
                            <input type="number" id="valoracion" name="valoracion" min="1" max="5" />
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="form-right">
                        <div class="form-group">
                            <label for="portada">Portada</label>
                            <input type="file" id="portada" name="portada" accept=".jpg,.jpeg,.png,.gif" />
                        </div>

                        <div class="form-group">
                            <label for="resena">Reseña</label>
                            <textarea id="resena" name="resena" rows="6"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botón centrado -->
                <div class="form-actions">
                    <button type="submit">Guardar película</button>
                </div>
            </form>

        </div>
        <a class="url" href="<?= APP_URL ?>dashboard.php">Volver al dashboard</a>
    </div>

    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
