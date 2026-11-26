<?php
declare(strict_types=1);

require_once __DIR__ . '/catalog.php';

function public_head(string $title, int $depth = 0): void
{
    $up = $depth > 0 ? str_repeat('../', $depth) : '';
    $buyer = buyer_id() > 0;
    $account = $depth === 0 ? 'cuenta/' : '';
    $here = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' — ' . e(brand_name()) . '</title>';
    echo '<meta name="theme-color" content="#070708">';
    echo '<link rel="icon" href="' . e($up) . 'assets/favicon.svg" type="image/svg+xml">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="' . e($up . media_src('assets/css/app.css')) . '">';
    echo '<script src="' . e($up . media_src('assets/js/nav.js')) . '" defer></script>';
    echo '</head><body class="catalog-body">';
    echo '<header class="site-header is-scrolled"><a class="logo" href="' . e($up) . 'index.php">' . e(brand_name()) . '</a>';
    echo '<div class="site-tools">';
    if (!$buyer) {
        echo '<a class="enter-link" href="' . e($account) . 'login.php">Iniciar sesión</a>';
    }
    echo '<button class="nav-toggle" type="button" aria-expanded="false">Menú</button>';
    echo '<nav class="site-nav" aria-label="Secciones">';
    echo '<a href="' . e($up) . 'index.php">Inicio</a>';
    if ($here !== 'catalogo.php') {
        echo '<a href="' . e($up) . 'catalogo.php">Catálogo</a>';
    }
    if ($buyer) {
        echo '<a href="' . e($account) . 'compras.php">Mis compras</a>';
        echo '<a href="' . e($account) . 'logout.php">Salir</a>';
    }
    echo '</nav></div></header><main class="catalog">';
}

function public_foot(): void
{
    echo '</main></body></html>';
}

function public_note(string $message, bool $bad = false): void
{
    echo '<p class="' . ($bad ? 'catalog__note catalog__note--bad' : 'catalog__note') . '">' . e($message) . '</p>';
}
