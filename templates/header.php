<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo APP_NAME; ?></title>

  <!-- Cargar los CSS por componente -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>css/main.css" />
  <link rel="stylesheet" href="<?php echo APP_URL; ?>css/navbar.css" />
  <link rel="stylesheet" href="<?php echo APP_URL; ?>css/forms.css" />
  <link rel="stylesheet" href="<?php echo APP_URL; ?>css/dashboard.css" />
  <link rel="stylesheet" href="<?php echo APP_URL; ?>css/perfil.css" />
  <link rel="stylesheet" href="<?php echo APP_URL; ?>css/pelicula.css" />
  <link rel="stylesheet" href="<?php echo APP_URL; ?>css/followers.css" />
</head>
<body>
  <header class="main-header">
    <?php include __DIR__ . '/navbar.php'; ?>
  </header>

  <main>
