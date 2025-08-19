<nav class="navbar">
  <!-- IZQUIERDA -->
  <div class="nav-left">
    <a href="<?php echo APP_URL; ?>peliculas/agregar.php" class="btn">+ Publicar</a>
  </div>

  <!-- CENTRO -->
  <div class="nav-center">
    <a href="<?php echo APP_URL; ?>dashboard.php" class="site-title">
      🎬 <?php echo APP_NAME; ?>
    </a>
  </div>

  <!-- DERECHA -->
  <div class="nav-right">
    <?php if (isset($_SESSION['usuario_id'])): ?>
      <span class="welcome">
        Bienvenido, 
        <a href="<?php echo APP_URL; ?>usuarios/perfil.php" class="usuario-link">
          <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
        </a>
      </span>
      <a href="<?php echo APP_URL; ?>usuarios/logout.php" class="btn-logout">Cerrar sesión</a>
    <?php else: ?>
      <a href="<?php echo APP_URL; ?>usuarios/login.php" class="btn">Iniciar sesión</a>
      <a href="<?php echo APP_URL; ?>usuarios/register.php" class="btn">Registrarse</a>
    <?php endif; ?>
  </div>
</nav>
