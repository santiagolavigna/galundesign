<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/public_page.php';

if (buyer_id() > 0) {
    header('Location: ' . site_url(safe_next((string) ($_GET['next'] ?? 'cuenta/compras'))));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $error = login_buyer(trim((string) ($_POST['email'] ?? '')), (string) ($_POST['password'] ?? ''));
    if ($error === '') {
        header('Location: ' . site_url(safe_next((string) ($_POST['next'] ?? 'cuenta/compras'))));
        exit;
    }
}

$next = safe_next((string) ($_GET['next'] ?? $_POST['next'] ?? 'cuenta/compras'));
public_head('Iniciar sesión', 1);
if ($error !== '') {
    public_note($error, true);
}
?>
<form class="stack account" method="post">
    <h1 class="account__title">Iniciar sesión</h1>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Email <input type="email" name="email" required autocomplete="username"></label>
    <label>Clave <input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn" type="submit">Iniciar sesión</button>
</form>
<p><a href="registro?next=<?= e(rawurlencode($next)) ?>">Crear cuenta</a></p>
<?php public_foot();
