<?php
// Este archivo asume que config.php ya se ha incluido antes,
// y que la sesión ya está iniciada allí (no usar session_start() aquí)

// Tiempo máximo de sesión: 6 horas
define('SESSION_EXPIRATION_SECONDS', 6 * 60 * 60);

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Redirigir a login si no está autenticado
    header('Location: ' . APP_URL . 'index.php');
    exit;
}

// Verificar si la sesión ha expirado
if (isset($_SESSION['login_time'])) {
    $tiempoTranscurrido = time() - $_SESSION['login_time'];

    if ($tiempoTranscurrido > SESSION_EXPIRATION_SECONDS) {
        // Si ha expirado, destruir la sesión y redirigir
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . 'index.php?mensaje=sesion_expirada');
        exit;
    }
}

// Renovar tiempo de sesión
$_SESSION['login_time'] = time();
