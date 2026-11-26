<?php
declare(strict_types=1);

require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/public_page.php';

public_head('Catálogo');
$flyers = published_flyers();
$plan = monthly_plan();
?>
<?php if (buyer_id() === 0): ?>
<dialog class="gate">
    <form method="dialog">
        <h2>Iniciar sesión</h2>
        <div class="gate__actions">
            <a class="btn" href="cuenta/login.php">Iniciar sesión</a>
            <a class="btn btn--quiet" href="cuenta/registro.php">Crear cuenta</a>
        </div>
        <button class="gate__close" type="submit">Ver el catálogo</button>
    </form>
</dialog>
<script>
    const gate = document.querySelector('.gate');
    if (gate && typeof gate.showModal === 'function') {
        gate.showModal();
    }
</script>
<?php endif; ?>
<?php if ((string) $plan['price'] !== '0.00'): ?>
    <p class="catalog__lead">Membresía <?= e(money_label((string) $plan['price'])) ?> por mes: descargás los que estén publicados, también los que se sumen después.</p>
<?php endif; ?>
<?php if ($flyers === []): ?>
    <p>Todavía no hay flyers publicados.</p>
<?php else: ?>
    <div class="catalog__grid">
    <?php foreach ($flyers as $flyer): ?>
        <a class="catalog__card" href="flyer.php?id=<?= (int) $flyer['id'] ?>">
            <img src="<?= e(media_src((string) $flyer['preview_path'])) ?>" alt="">
            <span><?= e((string) $flyer['title']) ?></span>
            <strong><?= e(money_label((string) $flyer['price'])) ?></strong>
        </a>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php public_foot();
