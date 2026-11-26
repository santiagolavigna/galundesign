<?php
declare(strict_types=1);

require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/public_page.php';
require __DIR__ . '/includes/mercadopago.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$flyer = is_int($id) ? find_flyer($id) : null;
if ($flyer === null || (int) $flyer['is_published'] !== 1) {
    header('Location: catalogo');
    exit;
}

$error = '';
$buyer = current_buyer();
$plan = monthly_plan();
$canDownload = $buyer !== null && buyer_can_download((int) $buyer['id'], $flyer);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    if ($buyer === null) {
        header('Location: cuenta/login?next=' . rawurlencode('flyer?id=' . (int) $flyer['id']));
        exit;
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'buy') {
        $order = create_flyer_order((int) $buyer['id'], (int) $flyer['id']);
        if (is_string($order)) {
            $error = $order;
        } elseif (!mp_ready()) {
            $error = 'El cobro todavía no está conectado a Mercado Pago.';
        } else {
            $url = mp_preference((int) $order['id'], (string) $order['title'], (string) $order['price']);
            if ($url === '') {
                $error = 'No se pudo abrir Mercado Pago.';
            } else {
                header('Location: ' . $url);
                exit;
            }
        }
    } elseif ($action === 'join') {
        $sub = create_subscription_row((int) $buyer['id']);
        if (is_string($sub)) {
            $error = $sub;
        } elseif (!mp_ready()) {
            $error = 'El cobro todavía no está conectado a Mercado Pago.';
        } else {
            $url = mp_subscribe((int) $sub['id'], $sub['plan'], (string) $sub['email']);
            if ($url === '') {
                $error = 'No se pudo abrir Mercado Pago.';
            } else {
                header('Location: ' . $url);
                exit;
            }
        }
    }
}

public_head((string) $flyer['title']);
if ($error !== '') {
    public_note($error, true);
}
?>
<article class="flyer">
    <img src="<?= e(media_src((string) $flyer['preview_path'])) ?>" alt="">
    <div>
        <h1><?= e((string) $flyer['title']) ?></h1>
        <p class="flyer__price"><?= e(money_label((string) $flyer['price'])) ?></p>
        <p>La muestra tiene marca de agua. El PSD se usa en tus fiestas y no se revende como plantilla.</p>
        <?php if ($canDownload): ?>
            <p><a class="btn" href="descarga?id=<?= (int) $flyer['id'] ?>">Descargar PSD</a></p>
        <?php else: ?>
            <form method="post" class="stack">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button class="btn" type="submit" name="action" value="buy">Comprar este flyer</button>
                <?php if ((string) $plan['price'] !== '0.00'): ?>
                    <button class="btn btn--quiet" type="submit" name="action" value="join">Membresía <?= e(money_label((string) $plan['price'])) ?> por mes</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</article>
<?php public_foot();
