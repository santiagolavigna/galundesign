<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/catalog.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $id = posted_int('id', 1, 1000000000);
    $flyer = find_flyer($id);
    if ($flyer === null) {
        flash('No se pudo borrar el flyer.');
    } else {
        $used = db()->prepare('SELECT id FROM order_items WHERE flyer_id = ? LIMIT 1');
        $used->execute([$id]);
        if ($used->fetch() !== false) {
            flash('No se puede borrar: ya tiene una venta. Ocultalo.');
        } else {
            remove_stored_file((string) $flyer['preview_path']);
            remove_stored_file((string) $flyer['source_path']);
            db()->prepare('DELETE FROM flyers WHERE id = ?')->execute([$id]);
            flash('Flyer borrado.');
        }
    }
    header('Location: flyers.php');
    exit;
}

admin_head('Flyers');
?>
<p><a class="button" href="flyer-edit.php">Nuevo flyer</a> <a class="button quiet" href="plan.php">Precio de la membresía</a> <a class="button quiet" href="sales.php">Cobros</a></p>
<div class="rows">
<?php foreach (all_flyers() as $row): ?>
    <article class="row">
        <img src="../<?= e(media_src((string) $row['preview_path'])) ?>" alt="">
        <div>
            <strong><?= e((string) $row['title']) ?></strong><br>
            <small><?= e(money_label((string) $row['price'])) ?><?= (int) $row['is_published'] === 1 ? '' : ' · oculto' ?></small>
        </div>
        <div class="actions">
            <a class="button quiet" href="flyer-edit.php?id=<?= (int) $row['id'] ?>">Editar</a>
            <form method="post" onsubmit="return confirm('¿Borrar este flyer? Esta acción no se puede deshacer.')">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button class="quiet" type="submit">Borrar</button>
            </form>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php admin_foot();
