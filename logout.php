<?php
session_start();

// Borrar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Redirigir a la página de login
header('Location: index.php');
exit;
