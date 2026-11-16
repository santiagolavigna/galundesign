<?php
declare(strict_types=1);

$uri = (string) ($_SERVER['REDIRECT_URL'] ?? $_SERVER['REDIRECT_REQUEST_URI'] ?? '');
$failedSave = str_contains($uri, '/admin/');
$status = (int) ($_SERVER['REDIRECT_STATUS'] ?? 404);
if ($status < 400 || $status > 599) {
    $status = 404;
}
http_response_code($status);
header('Content-Type: text/html; charset=UTF-8');

$title = $failedSave ? 'No se pudo guardar' : 'Esta dirección no está disponible.';
$text = $failedSave
    ? 'La pieza no se guardó. Volvé al panel y probá de nuevo.'
    : 'Volvé al inicio para seguir viendo los trabajos.';
$href = $failedSave ? '/admin/works.php' : '/';
$label = $failedSave ? 'Volver al panel' : 'Volver al inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Galun Design</title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600&family=Syne:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #070708;
            color: #f3efe6;
            font-family: Manrope, sans-serif;
            text-align: center;
        }
        main { width: min(28rem, calc(100% - 40px)); }
        h1 {
            margin: 0 0 12px;
            font-family: Syne, sans-serif;
            font-size: clamp(2rem, 8vw, 3.4rem);
            letter-spacing: -0.05em;
            text-transform: uppercase;
        }
        p { margin: 0 0 28px; color: #9a958c; }
        a {
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            padding: 0 18px;
            background: #d6ff3f;
            color: #14160a;
            font-family: Syne, sans-serif;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main>
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
    </main>
</body>
</html>
