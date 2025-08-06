<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$db = new Database();
$pdo = $db->getConnection();

// Obtener id del usuario logueado
$usuario_id = $_SESSION['usuario_id'];

// Contadores de películas vistas y no vistas
$stmtVistas = $pdo->prepare("SELECT COUNT(*) FROM peliculas WHERE usuario_id = ? AND visto = 1");
$stmtVistas->execute([$usuario_id]);
$totalVistas = $stmtVistas->fetchColumn();

$stmtNoVistas = $pdo->prepare("SELECT COUNT(*) FROM peliculas WHERE usuario_id = ? AND visto = 0");
$stmtNoVistas->execute([$usuario_id]);
$totalNoVistas = $stmtNoVistas->fetchColumn();

// Obtener listado de películas del usuario
$stmtPeliculas = $pdo->prepare("SELECT id, titulo, genero, plataforma, visto, favorito FROM peliculas WHERE usuario_id = ? ORDER BY fecha_agregado DESC");
$stmtPeliculas->execute([$usuario_id]);
$peliculas = $stmtPeliculas->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard | Cineva</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
    <header style="background:#090d10; padding:1rem; color:#f4bf2c;">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h1>
        <a href="logout.php" style="color:#f4bf2c; text-decoration:none;">Cerrar sesión</a>
    </header>

    <section style="padding:1rem;">
        <h2>Resumen de tus películas</h2>
        <p>Películas vistas: <strong><?php echo $totalVistas; ?></strong></p>
        <p>Películas por ver: <strong><?php echo $totalNoVistas; ?></strong></p>

        <a href="agregar.php" style="display:inline-block; margin:1rem 0; padding:0.5rem 1rem; background:#f4bf2c; color:#06080e; text-decoration:none;">Agregar nueva película</a>

        <h2>Listado de tus películas</h2>

        <?php if (count($peliculas) === 0): ?>
            <p>No tienes películas añadidas todavía.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%;">
                <thead style="background:#f4bf2c; color:#06080e;">
                    <tr>
                        <th>Título</th>
                        <th>Género</th>
                        <th>Plataforma</th>
                        <th>Visto</th>
                        <th>Favorito</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peliculas as $pelicula): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pelicula['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($pelicula['genero']); ?></td>
                            <td><?php echo htmlspecialchars($pelicula['plataforma']); ?></td>
                            <td><?php echo $pelicula['visto'] ? '✔️' : '❌'; ?></td>
                            <td><?php echo $pelicula['favorito'] ? '⭐' : '☆'; ?></td>
                            <td>
                                <a href="ver.php?id=<?php echo $pelicula['id']; ?>">Ver</a> |
                                <a href="editar.php?id=<?php echo $pelicula['id']; ?>">Editar</a> |
                                <a href="eliminar.php?id=<?php echo $pelicula['id']; ?>" onclick="return confirm('¿Seguro que quieres eliminar esta película?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</body>
</html>
