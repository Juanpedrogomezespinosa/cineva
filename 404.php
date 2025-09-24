<?php
// Archivo 404.php
// Enviar cabecera HTTP 404 para que los navegadores y buscadores lo detecten correctamente
http_response_code(404);

require_once __DIR__ . '/includes/config.php';
include 'templates/header.php';
?>

<main class="seccion-error-404">
    <div class="contenedor-error-404">
        <div class="contenedor-gif">
            <img src="<?= APP_URL ?>img/assets/404.gif" alt="Error 404" class="imagen-error-404">
        </div>
        <div class="contenedor-texto-404">
            <h1 class="titulo-error">404</h1>
            <h2 class="subtitulo-error">Page not found</h2>
            <p class="mensaje-error-404">La página que buscas no existe o fue movida.</p>
            <a href="<?= APP_URL ?>index.php" class="boton-volver">Volver al inicio</a>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>
