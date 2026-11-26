<?php
declare(strict_types=1);

require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/catalog.php';

$buyer = current_buyer();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($buyer === null) {
    $next = is_int($id) ? 'descarga?id=' . $id : 'cuenta/compras';
    header('Location: cuenta/login?next=' . rawurlencode($next));
    exit;
}
$flyer = is_int($id) ? find_flyer($id) : null;
if ($flyer === null || !buyer_can_download((int) $buyer['id'], $flyer)) {
    http_response_code(403);
    require __DIR__ . '/includes/public_page.php';
    public_head('Descarga');
    public_note('Esta descarga no está habilitada.', true);
    public_foot();
    exit;
}

$path = dirname(__FILE__) . '/' . (string) $flyer['source_path'];
$real = realpath($path);
$root = realpath(__DIR__ . '/uploads/flyers/sources');
if ($real === false || $root === false || !str_starts_with($real, $root) || !is_file($real)) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="flyer-' . (int) $flyer['id'] . '.psd"');
header('Content-Length: ' . (string) filesize($real));
header('X-Content-Type-Options: nosniff');
readfile($real);
