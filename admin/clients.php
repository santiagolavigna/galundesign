<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/content.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $id = posted_int('id', 1, 1000000000);
    if (!delete_row('clients', $id)) {
        flash('No se pudo borrar el cliente.');
    } else {
        flash('Cliente borrado.');
    }
    header('Location: clients.php');
    exit;
}

admin_head('Clientes');
?>
<p><a class="button" href="client-edit.php">Nuevo cliente</a></p>
<div class="rows">
<?php foreach (all_rows('clients') as $row): ?>
    <article class="row">
        <img src="../<?= e(media_src((string) $row['photo_path'])) ?>" alt="">
        <div>
            <strong><?= e((string) $row['name']) ?></strong><br>
            <small><?= e((string) $row['handle']) ?><?= (int) $row['is_published'] === 1 ? '' : ' · oculto' ?></small>
        </div>
        <div class="actions">
            <a class="button quiet" href="client-edit.php?id=<?= (int) $row['id'] ?>">Editar</a>
            <form method="post" onsubmit="return confirm('¿Borrar este cliente? Esta acción no se puede deshacer.')">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button class="quiet" type="submit">Borrar</button>
            </form>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php admin_foot();