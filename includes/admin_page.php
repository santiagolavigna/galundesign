<?php
declare(strict_types=1);

function admin_head(string $title): void
{
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' — Galun</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600&family=Syne:wght@700&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="../assets/css/admin.css">';
    echo '</head><body>';
    echo '<header class="admin-bar"><strong>Galun</strong><nav>';
    echo '<a href="index.php">Inicio</a>';
    echo '<a href="works.php">Trabajos</a>';
    echo '<a href="visuals.php">Visuales</a>';
    echo '<a href="clients.php">Clientes</a>';
    echo '<a href="settings.php">Contacto</a>';
    echo '<a href="logout.php">Salir</a>';
    echo '</nav></header><main class="wrap">';
    $flash = take_flash();
    if ($flash !== '') {
        echo '<p class="flash">' . e($flash) . '</p>';
    }
    echo '<h1>' . e($title) . '</h1>';
}

function admin_foot(): void
{
    echo '</main></body></html>';
}

function clean_text(string $value, int $max): string
{
    $value = trim($value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }

    return substr($value, 0, $max);
}

function posted_int(string $key, int $min, int $max): int
{
    $value = filter_var($_POST[$key] ?? 0, FILTER_VALIDATE_INT);
    if ($value === false) {
        return $min;
    }

    return max($min, min($max, $value));
}

function posted_flag(string $key): int
{
    return isset($_POST[$key]) ? 1 : 0;
}
