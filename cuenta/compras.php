<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/public_page.php';

$buyer = current_buyer();
if ($buyer === null) {
    header('Location: login');
    exit;
}

$items = db()->prepare(
    'SELECT f.id, f.title, oi.unit_price, o.created_at
     FROM order_items oi
     INNER JOIN orders o ON o.id = oi.order_id
     INNER JOIN flyers f ON f.id = oi.flyer_id
     WHERE o.buyer_id = ? AND o.status = \'paid\'
     ORDER BY o.id DESC'
);
$items->execute([(int) $buyer['id']]);
$owned = $items->fetchAll();
$sub = db()->prepare(
    'SELECT status, price, current_period_end FROM subscriptions
     WHERE buyer_id = ? ORDER BY id DESC LIMIT 1'
);
$sub->execute([(int) $buyer['id']]);
$membership = $sub->fetch();
$open = membership_open((int) $buyer['id']);

public_head('Mis compras', 1);
?>
<p>Hola, <?= e((string) $buyer['name']) ?>.</p>
<?php if ($open && is_array($membership)): ?>
    <p class="catalog__note">Membresía al día hasta <?= e((string) $membership['current_period_end']) ?>. Podés descargar los flyers publicados.</p>
<?php elseif (is_array($membership) && $membership['current_period_end'] !== null): ?>
    <p class="catalog__note catalog__note--bad">La membresía no está al día. El acceso nuevo queda apagado.</p>
<?php endif; ?>
<h2>Flyers comprados</h2>
<?php if ($owned === []): ?>
    <p>Todavía no hay compras acreditadas.</p>
<?php else: ?>
    <div class="catalog__list">
    <?php foreach ($owned as $item): ?>
        <p>
            <?= e((string) $item['title']) ?> · <?= e(money_label((string) $item['unit_price'])) ?>
            · <a href="../descarga?id=<?= (int) $item['id'] ?>">Descargar PSD</a>
        </p>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($open): ?>
    <p><a class="btn" href="../catalogo">Ver el catálogo</a></p>
<?php endif; ?>
<?php public_foot();
