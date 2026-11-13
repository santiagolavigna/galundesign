<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $ip = client_ip();
    $count = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > (NOW() - INTERVAL 15 MINUTE)'
    );
    $count->execute([$ip]);
    if ((int) $count->fetchColumn() >= 5) {
        $error = 'Demasiados intentos. Probá de nuevo en unos minutos.';
    } else {
        $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user !== false && password_verify($password, (string) $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $user['id'];
            db()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
            header('Location: index.php');
            exit;
        }
        db()->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, NOW())')->execute([$ip]);
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — Galun</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600&family=Syne:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <main class="login">
        <h1>Entrar</h1>
        <?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <form class="stack" method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label>Email <input type="email" name="email" required autocomplete="username"></label>
            <label>Contraseña <input type="password" name="password" required autocomplete="current-password"></label>
            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
