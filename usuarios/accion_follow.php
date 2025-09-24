<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/follows.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

// Verificar parámetros
if (!isset($_POST['usuario_id'], $_POST['accion'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$usuario_actual = (int) $_SESSION['usuario_id'];
$usuario_objetivo = (int) $_POST['usuario_id'];
$accion = trim($_POST['accion']);

$follows = new Follows();
$db = new Database();
$pdo = $db->getConnection();

try {
    if ($accion === 'seguir') {
        if ($usuario_actual === $usuario_objetivo) {
            echo json_encode([
                'success' => false,
                'message' => 'No puedes seguirte a ti mismo'
            ]);
            exit;
        }

        $ok = $follows->seguirUsuario($usuario_actual, $usuario_objetivo);

        if ($ok) {
            // Comprobar si ya existe notificación de seguimiento
            $check = $pdo->prepare("
                SELECT COUNT(*) 
                FROM notificaciones 
                WHERE usuario_id = ? 
                  AND tipo = 'seguimiento' 
                  AND origen_id = ?
            ");
            $check->execute([$usuario_objetivo, $usuario_actual]);
            $existe = (int) $check->fetchColumn();

            if ($existe === 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO notificaciones (usuario_id, tipo, origen_id, relacion_id, creado_en, leido) 
                    VALUES (?, 'seguimiento', ?, NULL, NOW(), 0)
                ");
                $stmt->execute([$usuario_objetivo, $usuario_actual]);
            }
        }

        echo json_encode(['success' => $ok]);

    } elseif ($accion === 'dejar') {
        $ok = $follows->dejarDeSeguirUsuario($usuario_actual, $usuario_objetivo);
        echo json_encode(['success' => $ok]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no reconocida'
        ]);
    }

} catch (Exception $error) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la operación: ' . $error->getMessage()
    ]);
}
