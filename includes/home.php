<?php
declare(strict_types=1);

require_once __DIR__ . '/content.php';

function home_data(): array
{
    $empty = [
        'brand' => 'Galun Design',
        'tagline' => '',
        'whatsapp_href' => '',
        'whatsapp_display' => '',
        'instagram_url' => '',
        'instagram_handle' => '',
        'featured' => null,
        'works' => [],
        'groups' => [],
        'works_reel' => false,
        'visuals' => [],
        'visuals_reel' => false,
        'clients' => [],
        'clients_reel' => false,
    ];

    try {
        $settings = settings_row();
        $works = published_rows('works');
        $visuals = published_rows('visuals');
        $clients = published_rows('clients');
    } catch (Throwable $e) {
        error_log($e->getMessage());
        return $empty;
    }

    $featured = null;
    $rest = [];
    foreach ($works as $work) {
        if ($featured === null && (int) $work['is_featured'] === 1) {
            $featured = $work;
            continue;
        }
        $rest[] = $work;
    }

    $groups = [];
    foreach ($rest as $work) {
        $groups[(string) $work['group_label']][] = $work;
    }

    $digits = preg_replace('/\D+/', '', (string) ($settings['whatsapp_e164'] ?? '')) ?? '';
    $message = (string) ($settings['whatsapp_message'] ?? '');
    $instagram = (string) ($settings['instagram_url'] ?? '');
    $path = trim((string) parse_url($instagram, PHP_URL_PATH), '/');

    return [
        'brand' => (string) ($settings['brand_name'] ?? 'Galun Design'),
        'tagline' => (string) ($settings['tagline'] ?? ''),
        'whatsapp_href' => $digits !== ''
            ? 'https://wa.me/' . $digits . ($message !== '' ? '?text=' . rawurlencode($message) : '')
            : '',
        'whatsapp_display' => $digits === '5492346571207' ? '+54 9 2346 57-1207' : ($digits !== '' ? '+' . $digits : ''),
        'instagram_url' => $instagram,
        'instagram_handle' => $path !== '' ? '@' . $path : '',
        'featured' => $featured,
        'works' => $rest,
        'groups' => $groups,
        'works_reel' => count($rest) >= 7,
        'visuals' => $visuals,
        'visuals_reel' => count($visuals) >= 5,
        'clients' => $clients,
        'clients_reel' => count($clients) >= 8,
    ];
}

function client_mark(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = function_exists('mb_substr') ? mb_substr((string) ($parts[0] ?? ''), 0, 1) : substr((string) ($parts[0] ?? ''), 0, 1);
    $second = function_exists('mb_substr') ? mb_substr((string) ($parts[1] ?? ''), 0, 1) : substr((string) ($parts[1] ?? ''), 0, 1);

    return strtoupper($first . $second);
}
