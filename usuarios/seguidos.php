<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/follows.php';

$id = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null);

if (!$id) {
    header('Location: ../index.php');
    exit;
}

$follows = new Follows();
$listaSeguidos = $follows->obtenerSeguidos($id);

include __DIR__ . '/../templates/header.php';
?>

<section class="seguidos">
    <h1>Usuarios que sigue</h1>
    
    <?php if (count($listaSeguidos) === 0): ?>
        <p>Este usuario no sigue a nadie todavía.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($listaSeguidos as $seguido): ?>
                    <li data-usuario="<?= $seguido['id'] ?>">
                        <div class="user-info">
                            <img src="<?= APP_URL ?>img/avatars/<?= htmlspecialchars($seguido['avatar'], ENT_QUOTES, 'UTF-8') ?>" 
                            alt="Avatar de <?= htmlspecialchars($seguido['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                            <a href="<?= APP_URL ?>usuarios/perfil.php?id=<?= $seguido['id'] ?>">
                                <?= htmlspecialchars($seguido['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                        
                        <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] !== $seguido['id']): ?>
                            <button class="<?= $follows->esSeguidor($_SESSION['usuario_id'], $seguido['id']) ? 'btn-unfollow' : 'btn-follow'; ?>" 
                            data-accion="<?= $follows->esSeguidor($_SESSION['usuario_id'], $seguido['id']) ? 'dejar' : 'seguir'; ?>">
                            <?= $follows->esSeguidor($_SESSION['usuario_id'], $seguido['id']) ? 'Dejar de seguir' : 'Seguir'; ?>
                        </button>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>
            <a class="volver-perfil" href="<?= APP_URL ?>usuarios/perfil.php?id=<?= $id ?>">Volver al perfil</a>
            
            <script>
                document.querySelectorAll('.seguidos button').forEach(button => {
                    button.addEventListener('click', () => {
        const li = button.closest('li');
        const usuario_id = li.dataset.usuario;
        const accion = button.dataset.accion;

        fetch('<?= APP_URL ?>usuarios/accion_follow.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `usuario_id=${usuario_id}&accion=${accion}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                if(accion === 'seguir'){
                    button.textContent = 'Dejar de seguir';
                    button.classList.remove('btn-follow');
                    button.classList.add('btn-unfollow');
                    button.dataset.accion = 'dejar';
                } else {
                    button.textContent = 'Seguir';
                    button.classList.remove('btn-unfollow');
                    button.classList.add('btn-follow');
                    button.dataset.accion = 'seguir';
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('Error en la solicitud AJAX'));
    });
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
