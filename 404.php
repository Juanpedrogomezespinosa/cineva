<?php
// 404.php
// Enviar cabecera HTTP 404 para que los navegadores y buscadores lo detecten correctamente
http_response_code(404);

include 'templates/header.php';

?>

<main class="error-404">
    <div class="container">
        <img src="img/assets/404.png" alt="Error 404" class="img-404">
        <h1>¡Ups! Página no encontrada</h1>
        <p>La página que buscas no existe o fue movida.</p>
        <a href="index.php" class="btn-volver">Volver al inicio</a>
    </div>
</main>

<?php include 'templates/footer.php'; ?>
