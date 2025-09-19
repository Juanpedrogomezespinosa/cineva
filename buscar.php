<?php
declare(strict_types=1);

require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/funciones.php";

// Esto es un endpoint AJAX, devolvemos JSON
header('Content-Type: application/json; charset=utf-8');

$termino = isset($_GET['q']) ? limpiarCadena($_GET['q']) : "";
$resultados = [];

if (!empty($termino)) {
    $like = "%" . $termino . "%";

    // Buscar películas
    $stmt = $conn->prepare("SELECT id, titulo FROM peliculas WHERE titulo LIKE ? ORDER BY fecha_agregado DESC");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $resPeliculas = $stmt->get_result();
    while ($fila = $resPeliculas->fetch_assoc()) {
        $resultados[] = [
            'tipo' => 'pelicula',
            'id' => $fila['id'],
            'nombre' => $fila['titulo'],
            'url' => APP_URL . "peliculas/ver.php?id=" . $fila['id']
        ];
    }
    $stmt->close();

    // Buscar usuarios
    $stmt2 = $conn->prepare("SELECT id, nombre FROM usuarios WHERE nombre LIKE ? ORDER BY nombre ASC");
    $stmt2->bind_param("s", $like);
    $stmt2->execute();
    $resUsuarios = $stmt2->get_result();
    while ($fila = $resUsuarios->fetch_assoc()) {
        $resultados[] = [
            'tipo' => 'usuario',
            'id' => $fila['id'],
            'nombre' => $fila['nombre'],
            'url' => APP_URL . "usuarios/perfil.php?id=" . $fila['id']
        ];
    }
    $stmt2->close();
}

// Devolver resultados en JSON
echo json_encode($resultados);
