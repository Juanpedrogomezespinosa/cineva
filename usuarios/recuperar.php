<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<section class="form-container">
    <h2>Recuperar contraseña</h2>

    <form method="POST" action="<?php echo APP_URL; ?>usuarios/procesar-recuperar.php">
        <label for="email">Introduce tu email</label>
        <input type="email" id="email" name="email" required placeholder="tuemail@ejemplo.com" />

        <button type="submit">Enviar enlace</button>
    </form>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
