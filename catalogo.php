<?php
declare(strict_types=1);

require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/public_page.php';

public_head('Catálogo');
$flyers = published_flyers();
$plan = monthly_plan();
?>
<p class="catalog__lead">Flyers editables en PSD. La imagen es una muestra. El archivo se descarga después de pagarlo, o con la membresía al día.</p>
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
