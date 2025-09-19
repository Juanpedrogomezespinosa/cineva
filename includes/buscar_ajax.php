<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

// Encabezado JSON
header('Content-Type: application/json; charset=utf-8');

$results = [];
$response = [
    'success' => false,
    'query' => '',
    'debug' => [],
    'results' => []
];

// Inicializar PDO correctamente
try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    echo json_encode(['error' => 'Error de conexión PDO: ' . $e->getMessage()]);
    exit;
}

// Obtener término de búsqueda
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$response['query'] = $query;

if ($query === '') {
    echo json_encode(['error' => 'No se recibió ningún término de búsqueda']);
    exit;
}

$like = "%" . $query . "%";

try {
    // Buscar usuarios
    $sqlUsuarios = "SELECT id, nombre FROM usuarios WHERE nombre LIKE :query ORDER BY nombre ASC LIMIT 5";
    $stmtUsuarios = $pdo->prepare($sqlUsuarios);
    $stmtUsuarios->execute(['query' => $like]);
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

    $response['debug']['usuarios_encontrados'] = count($usuarios);

    foreach ($usuarios as $row) {
        $response['results'][] = [
            "texto" => "👤 " . htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'),
            "url" => APP_URL . "usuarios/perfil.php?id=" . $row['id']
        ];
    }

    // Buscar películas
    $sqlPeliculas = "SELECT id, titulo FROM peliculas WHERE titulo LIKE :query ORDER BY fecha_agregado DESC LIMIT 5";
    $stmtPeliculas = $pdo->prepare($sqlPeliculas);
    $stmtPeliculas->execute(['query' => $like]);
    $peliculas = $stmtPeliculas->fetchAll(PDO::FETCH_ASSOC);

    $response['debug']['peliculas_encontradas'] = count($peliculas);

    foreach ($peliculas as $row) {
        $response['results'][] = [
            "texto" => "🎬 " . htmlspecialchars($row['titulo'], ENT_QUOTES, 'UTF-8'),
            "url" => APP_URL . "peliculas/ver.php?id=" . $row['id']
        ];
    }

    $response['success'] = true;
} catch (Exception $e) {
    $response['error'] = "Error en la búsqueda: " . $e->getMessage();
}

// Devolver resultados en JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
