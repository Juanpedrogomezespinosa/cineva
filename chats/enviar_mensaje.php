<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/mensajes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emisor = $_SESSION['usuario_id'] ?? null;
    $receptor = isset($_POST['receptor_id']) ? (int)$_POST['receptor_id'] : null;
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (!$emisor || !$receptor || $mensaje === '') {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $db = (new Database())->getConnection();
    $resultado = enviarMensaje($db, $emisor, $receptor, $mensaje);

    if ($resultado) {
        $stmt = $db->prepare("SELECT * FROM mensajes WHERE id = :id");
        $stmt->execute([':id' => $db->lastInsertId()]);
        $mensajeInsertado = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success'   => true,
            'mensaje'   => $mensajeInsertado['mensaje'],
            'nombre'    => 'Tú',
            'creado_en' => $mensajeInsertado['creado_en'],
            'id'        => (int)$mensajeInsertado['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al enviar mensaje']);
    }
}
