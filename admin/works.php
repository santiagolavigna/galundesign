<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/content.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $id = posted_int('id', 1, 1000000000);
    if (!delete_row('works', $id)) {
        flash('No se pudo borrar el trabajo.');
    } else {
        flash('Trabajo borrado.');
    }
    header('Location: works.php');
    exit;
}

admin_head('Trabajos');
?>
<p><a class="button" href="work-edit.php">Nuevo trabajo</a></p>
<div class="rows">
<?php foreach (all_rows('works') as $row): ?>
    <article class="row">
        <img src="../<?= e(media_src((string) $row['file_path'])) ?>" alt="">
        <div>
            <strong><?= e((string) $row['title']) ?></strong><br>
            <small><?= e((string) $row['group_label']) ?> · <?= e((string) $row['frame']) ?><?= (int) $row['is_featured'] === 1 ? ' · destacado' : '' ?><?= (int) $row['is_published'] === 1 ? '' : ' · oculto' ?></small>
        </div>
        <div class="actions">
            <a class="button quiet" href="work-edit.php?id=<?= (int) $row['id'] ?>">Editar</a>
            <form method="post" onsubmit="return confirm('¿Borrar este trabajo? Esta acción no se puede deshacer.')">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button class="quiet" type="submit">Borrar</button>
            </form>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php admin_foot();