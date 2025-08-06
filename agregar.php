<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$mensaje = '';
$db = new Database();
$pdo = $db->getConnection();

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y limpiar datos
    $titulo = trim($_POST['titulo'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $plataforma = trim($_POST['plataforma'] ?? '');
    $visto = isset($_POST['visto']) ? 1 : 0;
    $favorito = isset($_POST['favorito']) ? 1 : 0;
    $valoracion = (int)($_POST['valoracion'] ?? 0);
    $reseña = trim($_POST['resena'] ?? '');
    
    // Validar campos obligatorios (ejemplo básico)
    if (!$titulo || !$genero || !$plataforma) {
        $mensaje = 'Título, género y plataforma son obligatorios.';
    } else {
        // Subida de portada (opcional)
        $portada = null;
        if (!empty($_FILES['portada']['name'])) {
            $archivo = $_FILES['portada'];
            $permitidos = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if (in_array($extension, $permitidos) && $archivo['size'] <= 2 * 1024 * 1024) {
                $nuevoNombre = uniqid('portada_') . '.' . $extension;
                $rutaDestino = 'img/portadas/' . $nuevoNombre;
                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $portada = $nuevoNombre;
                } else {
                    $mensaje = 'Error al subir la imagen.';
                }
            } else {
                $mensaje = 'Formato o tamaño de imagen no válido.';
            }
        }

        if (!$mensaje) {
            // Insertar en BD
            $sql = "INSERT INTO peliculas 
            (usuario_id, titulo, genero, plataforma, visto, favorito, portada, valoracion, resena, fecha_agregado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                $usuario_id,
                $titulo,
                $genero,
                $plataforma,
                $visto,
                $favorito,
                $portada,
                $valoracion,
                $reseña,
            ]);

            if ($resultado) {
                header('Location: dashboard.php');
                exit;
            } else {
                $mensaje = 'Error al guardar la película.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Agregar película | Cineva</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<h1>Agregar nueva película</h1>

<?php if ($mensaje): ?>
    <p style="color:red;"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="agregar.php">
    <label for="titulo">Título:</label><br />
    <input type="text" id="titulo" name="titulo" required /><br /><br />

    <label for="genero">Género:</label><br />
    <input type="text" id="genero" name="genero" required /><br /><br />

    <label for="plataforma">Plataforma:</label><br />
    <input type="text" id="plataforma" name="plataforma" required /><br /><br />

    <label>
        <input type="checkbox" name="visto" />
        Visto
    </label><br />

    <label>
        <input type="checkbox" name="favorito" />
        Favorito
    </label><br /><br />

    <label for="portada">Portada (imagen jpg/png/gif, máx 2MB):</label><br />
    <input type="file" id="portada" name="portada" accept=".jpg,.jpeg,.png,.gif" /><br /><br />

    <label for="valoracion">Valoración (1-5):</label><br />
    <input type="number" id="valoracion" name="valoracion" min="1" max="5" /><br /><br />

    <label for="resena">Reseña:</label><br />
    <textarea id="resena" name="resena" rows="4" cols="50"></textarea><br /><br />

    <button type="submit">Guardar película</button>
</form>

<a href="dashboard.php">Volver al dashboard</a>
</body>
</html>
