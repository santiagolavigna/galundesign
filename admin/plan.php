<?php
declare(strict_types=1);

require __DIR__ . '/_auth.php';
require __DIR__ . '/../includes/admin_page.php';
require __DIR__ . '/../includes/catalog.php';

$plan = monthly_plan();
$error = '';
$price = (string) $plan['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $parsed = money_parse((string) ($_POST['price'] ?? ''));
    if ($parsed === null) {
        $error = 'El precio tiene que ser mayor a cero, en pesos.';
        $price = (string) ($_POST['price'] ?? '');
    } else {
        save_plan_price($parsed);
        flash('Se guardó el precio de la membresía.');
        header('Location: plan.php');
        exit;
    }
}

admin_head('Membresía');
?>
<?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<p>Este es el débito mensual. Quien lo paga puede descargar los flyers publicados. Si cambiás el número, las membresías ya cobradas siguen con el precio anterior.</p>
<form class="stack" method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Precio en pesos <input type="text" name="price" inputmode="decimal" required value="<?= e($price) ?>"></label>
    <button type="submit">Guardar</button>
</form>
<?php admin_foot();
