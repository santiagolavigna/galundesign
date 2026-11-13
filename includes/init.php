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
    $sent = $_POST['csrf'] ?? '';
    $known = $_SESSION['csrf'] ?? '';
    if (!is_string($sent) || !is_string($known) || !hash_equals($known, $sent)) {
        http_response_code(403);
        exit('La sesión expiró. Volvé a cargar la página.');
    }
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
