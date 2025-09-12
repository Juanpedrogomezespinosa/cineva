<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/follows.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar parámetros
if (!isset($_POST['usuario_id'], $_POST['accion'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetros incorrectos']);
    exit;
}

$usuario_objetivo = (int) $_POST['usuario_id'];       // Usuario a seguir o dejar de seguir
$accion = $_POST['accion'];                            // Acción: 'seguir' o 'dejar'
$usuario_actual = (int) $_SESSION['usuario_id'];      // Usuario logueado

// Instancias
$follows = new Follows();
$db = new Database();
$pdo = $db->getConnection();

try {
    if ($accion === 'seguir') {
        $ok = $follows->seguirUsuario($usuario_actual, $usuario_objetivo);

        if ($ok) {
            // Crear notificación solo si no te sigues a ti mismo
            if ($usuario_objetivo !== $usuario_actual) {
                $stmt = $pdo->prepare("
                    INSERT INTO notificaciones (usuario_id, tipo, origen_id, relacion_id) 
                    VALUES (?, 'seguimiento', ?, NULL)
                ");
                $stmt->execute([$usuario_objetivo, $usuario_actual]);
            }
        }

        echo json_encode(['success' => $ok]);

    } elseif ($accion === 'dejar') {
        $ok = $follows->dejarDeSeguirUsuario($usuario_actual, $usuario_objetivo);
        echo json_encode(['success' => $ok]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Acción desconocida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
