<?php
// Configuración general del proyecto

// Rutas importantes
define('BASE_PATH', __DIR__ . '/../');
define('IMG_PATH', BASE_PATH . 'img/');
define('PORTADAS_PATH', IMG_PATH . 'portadas/');
define('AVATARS_PATH', IMG_PATH . 'avatars/');

// Configuración de la aplicación
define('APP_NAME', 'Cineva');
define('APP_URL', 'http://localhost/proyectos/cineva/'); // cambia según tu entorno

// Opciones de seguridad
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
?>
