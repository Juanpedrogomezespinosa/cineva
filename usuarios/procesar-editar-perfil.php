<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . 'usuarios/login.php');
    exit;
}

$id = $_SESSION['usuario_id'];
$nombre = $_POST['nombre'] ?? '';
$biografia = $_POST['biografia'] ?? '';

$db = new Database();
$pdo = $db->getConnection();

// --- Procesar imagen de perfil ---
$avatar = null;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $directorio = __DIR__ . '/../img/avatars/';

    // Crear carpeta si no existe
    if (!is_dir($directorio)) {
        if (!mkdir($directorio, 0777, true)) {
            die('Error: no se pudo crear la carpeta avatars.');
        }
    }

    // Comprobar permisos de escritura
    if (!is_writable($directorio)) {
        die('Error: la carpeta avatars no es escribible. Ajusta los permisos.');
    }

    $nombreArchivoOriginal = $_FILES['avatar']['name'];
    $tmpArchivo = $_FILES['avatar']['tmp_name'];
    $extension = strtolower(pathinfo($nombreArchivoOriginal, PATHINFO_EXTENSION));

    // Solo permitir extensiones de imagen comunes
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $extensionesPermitidas)) {
        die('Error: tipo de archivo no permitido. Solo se permiten imágenes.');
    }

    $nuevoNombre = 'avatar_' . $id . '_' . time() . '.' . $extension;
    $rutaDestino = $directorio . $nuevoNombre;

    // Verificar que sea imagen
    $check = getimagesize($tmpArchivo);
    if ($check === false) {
        die('Error: el archivo subido no es una imagen válida.');
    }

    // Mover archivo al directorio final
    if (move_uploaded_file($tmpArchivo, $rutaDestino)) {
        $avatar = $nuevoNombre;
    } else {
        die('Error: no se pudo mover la imagen al directorio avatars. Comprueba permisos.');
    }
}

// --- Actualizar datos del usuario ---
try {
    if ($avatar) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, biografia = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$nombre, $biografia, $avatar, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, biografia = ? WHERE id = ?");
        $stmt->execute([$nombre, $biografia, $id]);
    }
} catch (PDOException $e) {
    die('Error al actualizar usuario: ' . $e->getMessage());
}

// Redirigir al perfil del usuario
header('Location: perfil.php?id=' . $id);
exit;
