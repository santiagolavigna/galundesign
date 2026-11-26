<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/catalog.php';

$sum = db()->query(
    "SELECT COALESCE(SUM(amount), 0) FROM payments
     WHERE status = 'approved' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
)->fetchColumn();
$buyers = db()->query(
    'SELECT b.id, b.name, b.email, b.created_at, s.status AS sub_status, s.current_period_end
     FROM buyers b
     LEFT JOIN subscriptions s ON s.id = (
        SELECT s2.id FROM subscriptions s2 WHERE s2.buyer_id = b.id ORDER BY s2.id DESC LIMIT 1
     )
     ORDER BY b.id DESC'
)->fetchAll();
$payments = db()->query(
    'SELECT p.created_at, p.amount, p.status, p.subscription_id,
            COALESCE(bo.name, bs.name) AS buyer_name,
            COALESCE(bo.email, bs.email) AS buyer_email,
            oi.title AS item_title
     FROM payments p
     LEFT JOIN orders o ON o.id = p.order_id
     LEFT JOIN buyers bo ON bo.id = o.buyer_id
     LEFT JOIN order_items oi ON oi.order_id = o.id
     LEFT JOIN subscriptions s ON s.id = p.subscription_id
     LEFT JOIN buyers bs ON bs.id = s.buyer_id
     ORDER BY p.id DESC
     LIMIT 200'
)->fetchAll();

$labels = [
    'approved' => 'acreditado',
    'pending' => 'pendiente',
    'rejected' => 'rechazado',
    'refunded' => 'devuelto',
    'active' => 'al día',
    'cancelled' => 'cancelada',
    'paused' => 'pausada',
];

admin_head('Cobros');
?>
<p>Acreditado este mes: <strong><?= e(money_label((string) $sum)) ?></strong>. El porcentaje del estudio no se calcula acá.</p>
<h2>Personas</h2>
<div class="rows">
<?php foreach ($buyers as $buyer): ?>
    <article class="row">
        <div>
            <strong><?= e((string) $buyer['name']) ?></strong><br>
            <small><?= e((string) $buyer['email']) ?> · alta <?= e((string) $buyer['created_at']) ?>
                <?php if ($buyer['sub_status'] !== null): ?>
                    · membresía <?= e($labels[(string) $buyer['sub_status']] ?? (string) $buyer['sub_status']) ?>
                    <?php if ($buyer['current_period_end'] !== null): ?>hasta <?= e((string) $buyer['current_period_end']) ?><?php endif; ?>
                <?php endif; ?>
            </small>
        </div>
    </article>
<?php endforeach; ?>
<?php if ($buyers === []): ?><p>Todavía no hay cuentas.</p><?php endif; ?>
</div>
<h2>Cobros</h2>
<div class="rows">
<?php foreach ($payments as $payment): ?>
    <article class="row">
        <div>
            <strong><?= e((string) ($payment['item_title'] ?? ($payment['subscription_id'] !== null ? 'Membresía' : 'Cobro'))) ?></strong>
            · <?= e(money_label((string) $payment['amount'])) ?><br>
            <small><?= e((string) ($payment['buyer_name'] ?? '')) ?> · <?= e((string) $payment['created_at']) ?> · <?= e($labels[(string) $payment['status']] ?? (string) $payment['status']) ?></small>
        </div>
    </article>
<?php endforeach; ?>
<?php if ($payments === []): ?><p>Todavía no hay cobros.</p><?php endif; ?>
</div>
<?php admin_foot();
