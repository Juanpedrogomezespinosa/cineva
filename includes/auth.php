<?php
session_start();

define('SESSION_EXPIRATION_SECONDS', 6 * 60 * 60); // 6 horas

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // No logueado, redirigir a login
    header('Location: index.php');
    exit;
}

// Verificar expiración de sesión
if (isset($_SESSION['login_time'])) {
    $tiempo_transcurrido = time() - $_SESSION['login_time'];
    if ($tiempo_transcurrido > SESSION_EXPIRATION_SECONDS) {
        // Sesión expirada
        session_unset();
        session_destroy();
        header('Location: index.php?mensaje=sesion_expirada');
        exit;
    } else {
        // Renovar tiempo de sesión
        $_SESSION['login_time'] = time();
    }
}
