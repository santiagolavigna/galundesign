<?php
declare(strict_types=1);

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/log.php';

register_shutdown_function(static function (): void {
    $last = error_get_last();
    if ($last === null) {
        return;
    }
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($last['type'], $fatal, true)) {
        return;
    }
    app_log('php-fatal', [
        'message' => (string) $last['message'],
        'file' => basename((string) $last['file']),
        'line' => (string) $last['line'],
    ]);
});

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_require(): void
{
    $length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($length > 0 && $_POST === [] && $_FILES === []) {
        app_log('post-vacio', ['bytes' => (string) $length, 'uri' => (string) ($_SERVER['SCRIPT_NAME'] ?? '')]);
        csrf_page(413, 'No se guardó. El archivo es demasiado pesado.');
    }

    $sent = $_POST['csrf'] ?? '';
    $known = $_SESSION['csrf'] ?? '';
    if (!is_string($sent) || !is_string($known) || $known === '' || !hash_equals($known, $sent)) {
        app_log('csrf', ['uri' => (string) ($_SERVER['SCRIPT_NAME'] ?? '')]);
        csrf_page(403, 'La sesión expiró. Volvé a entrar.');
    }
}

function csrf_page(int $status, string $message): never
{
    http_response_code($status);
    $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Galun</title>';
    echo '<link rel="stylesheet" href="../assets/css/admin.css">';
    echo '</head><body><main class="wrap">';
    echo '<p class="notice notice--bad">' . $safe . '</p>';
    echo '<p><a class="button" href="login.php">Entrar</a></p>';
    echo '</main></body></html>';
    exit;
}

function media_src(string $path): string
{
    $full = dirname(__DIR__) . '/' . $path;
    $version = is_file($full) ? (string) filemtime($full) : '1';
    $parts = explode('/', str_replace('\\', '/', $path));

    return implode('/', array_map('rawurlencode', $parts)) . '?v=' . $version;
}

function https_url(string $value): ?string
{
    $value = trim($value);
    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        return null;
    }
    $parts = parse_url($value);
    if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
        return null;
    }

    return $value;
}

function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    return substr($ip, 0, 45);
}

function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function take_flash(): string
{
    $message = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);

    return is_string($message) ? $message : '';
}
