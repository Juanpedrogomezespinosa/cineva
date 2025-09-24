<?php
// Configuración general del proyecto

// Mostrar errores en desarrollo (solo en local)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Opciones de seguridad de sesión (deben ir antes de iniciar la sesión)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Rutas importantes
define('BASE_PATH', __DIR__ . '/../');
define('IMG_PATH', BASE_PATH . 'img/');
define('PORTADAS_PATH', IMG_PATH . 'portadas/');
define('AVATARS_PATH', IMG_PATH . 'avatars/');

// Configuración de la aplicación
define('APP_NAME', 'Cineva');
define('APP_URL', 'http://localhost:80/proyectos/cineva/'); // Cambiar según entorno
