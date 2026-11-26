<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/public_page.php';

if (buyer_id() > 0) {
    header('Location: ' . site_url('cuenta/compras'));
    exit;
}

$error = '';
$next = safe_next((string) ($_GET['next'] ?? $_POST['next'] ?? 'cuenta/compras'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $password = (string) ($_POST['password'] ?? '');
    if ($password !== (string) ($_POST['password_again'] ?? '')) {
        $error = 'Las claves no coinciden.';
    } else {
        $error = register_buyer(
            trim((string) ($_POST['name'] ?? '')),
            trim((string) ($_POST['email'] ?? '')),
            $password
        );
    }
    if ($error === '') {
        header('Location: ' . site_url($next));
        exit;
    }
}

public_head('Crear cuenta', 1);
if ($error !== '') {
    public_note($error, true);
}
?>
<form class="stack account" method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Nombre <input type="text" name="name" required value="<?= e((string) ($_POST['name'] ?? '')) ?>"></label>
    <label>Email <input type="email" name="email" required autocomplete="username" value="<?= e((string) ($_POST['email'] ?? '')) ?>"></label>
    <label>Clave <input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
    <label>Repetir clave <input type="password" name="password_again" required minlength="8" autocomplete="new-password"></label>
    <button class="btn" type="submit">Crear cuenta</button>
</form>
<?php public_foot();
