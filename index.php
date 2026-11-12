<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    $full = __DIR__ . '/' . $path;
    $version = is_file($full) ? (string) filemtime($full) : '1';

    return $path . '?v=' . $version;
}

$brand = 'Galun Design';
$instagram = 'https://www.instagram.com/galun.psd/';
$whatsappDisplay = '+54 9 2346 57-1207';
$whatsappHref = 'https://wa.me/5492346571207?text=' . rawurlencode(
    'Hola Galun, vi la página y quiero consultar por un trabajo.'
);
$elias = 'https://www.instagram.com/eliastomok/';
$candu = 'https://www.instagram.com/candu_dominguez/';
$lali = 'https://www.instagram.com/lali__dj/';
$frenesi = 'https://www.instagram.com/frenesi_fiesta/';
$boveda = 'https://www.instagram.com/boveda____/';

$clients = [
    ['name' => 'Elias Tom', 'handle' => '@eliastomok', 'mark' => 'ET', 'photo' => 'assets/img/clients/eliastomok.jpg', 'url' => 'https://www.instagram.com/eliastomok/'],
    ['name' => 'Candu Dominguez', 'handle' => '@candu_dominguez', 'mark' => 'CD', 'photo' => 'assets/img/clients/candu_dominguez.jpg', 'url' => 'https://www.instagram.com/candu_dominguez/'],
    ['name' => 'Nicole Fernandez', 'handle' => '@__nicolefernandez', 'mark' => 'NF', 'photo' => 'assets/img/clients/__nicolefernandez.jpg', 'url' => 'https://www.instagram.com/__nicolefernandez/'],
    ['name' => 'Lali DJ', 'handle' => '@lali__dj', 'mark' => 'LD', 'photo' => 'assets/img/clients/lali__dj.jpg', 'url' => 'https://www.instagram.com/lali__dj/'],
    ['name' => 'Frenesi Fiesta', 'handle' => '@frenesi_fiesta', 'mark' => 'FF', 'photo' => 'assets/img/clients/frenesi_fiesta.jpg', 'url' => 'https://www.instagram.com/frenesi_fiesta/'],
    ['name' => 'Boveda Fiesta', 'handle' => '@boveda____', 'mark' => 'BF', 'photo' => 'assets/img/clients/boveda____.jpg', 'url' => 'https://www.instagram.com/boveda____/'],
];

$lede = 'Diseño gráfico para artistas, lanzamientos y eventos.';

$poster = static function (string $file, int $width, int $height, string $alt, string $url): array {
    return [
        'image' => 'assets/assets-web-gd/' . rawurlencode($file),
        'width' => $width,
        'height' => $height,
        'alt' => $alt,
        'url' => $url,
    ];
};

$featured = [
    'kicker' => 'Proyecto destacado',
    'title' => 'Fuego x Soho',
] + $poster('FUEGOFEED2.jpg.jpeg', 1080, 1350, 'Flyer de Fuego x Soho, sábado 7 de noviembre', $instagram);

$groups = [
    [
        'label' => 'Ellasom / Kick',
        'layout' => 'trio',
        'frame' => 'story',
        'items' => [
            ['title' => 'Amor de su cama'] + $poster('ELIASCUMBIA+DOBLEP.jpg.jpeg', 1080, 1920, 'Flyer de Amor de su cama, Eliastomok con Doble P y Cumbia Rocha', $elias),
            ['title' => 'Componemos con Lauta'] + $poster('LAUTAFTELIAS.jpg.jpeg', 1080, 1920, 'Flyer de Componemos con Lauta, Eliastomok y Lauta', $elias),
            ['title' => 'Zaina x Tomhouse'] + $poster('ZAINACOLOR2.jpg.jpeg', 1080, 1920, 'Flyer de Zaina x Tomhouse en Kick', $elias),
        ],
    ],
    [
        'label' => 'Eventos',
        'layout' => 'trio',
        'frame' => 'feed',
        'items' => [
            ['title' => 'Perro Primo'] + $poster('FEED1.jpg.jpeg', 1080, 1350, 'Flyer de Perro Primo en Frenesi, sábado 17.10', $frenesi),
            ['title' => 'Tomas Marenzana'] + $poster('FEED1xfrenesi.jpg.jpeg', 1080, 1350, 'Flyer de Tomas Marenzana en Bóveda, sábado 14 de noviembre', $boveda),
            ['title' => 'Frenesi'] + $poster('FEEd2.png', 1080, 1350, 'Flyer de Frenesi con Agustín García y Nahuel Salomone', $frenesi),
        ],
    ],
    [
        'label' => 'Historias',
        'layout' => 'pair',
        'frame' => 'story',
        'items' => [
            ['title' => 'Fuego x Soho'] + $poster('FUEGOXSOHOHISTORIA.jpg.jpeg', 1080, 1920, 'Historia de Fuego x Soho', $instagram),
            ['title' => 'Fuego x El Club'] + $poster('FUEGOXCLUB-HISTORIA.jpg.jpeg', 1080, 1920, 'Historia de Fuego x El Club, viernes 20.11', $instagram),
        ],
    ],
];

$visuales = [
    [
        'title' => 'Lali DJ',
        'video' => 'assets/media/lali-logo.mp4',
        'poster' => 'assets/media/lali-logo.jpg',
        'width' => 1280,
        'height' => 640,
        'alt' => 'Visual de Lali DJ, logo con destellos',
        'url' => $lali,
    ],
    [
        'title' => 'Lali 360',
        'video' => 'assets/media/lali-360.mp4',
        'poster' => 'assets/media/lali-360.jpg',
        'width' => 1280,
        'height' => 640,
        'alt' => 'Visual 360 de Lali DJ',
        'url' => $lali,
    ],
    [
        'title' => 'Luciana Idiart',
        'video' => 'assets/media/luciana.mp4',
        'poster' => 'assets/media/luciana.jpg',
        'width' => 1280,
        'height' => 640,
        'alt' => 'Visual de Luciana Idiart',
        'url' => $instagram,
    ],
    [
        'title' => 'Candu',
        'video' => 'assets/media/candu.mp4',
        'poster' => 'assets/media/candu.jpg',
        'width' => 1280,
        'height' => 640,
        'alt' => 'Visual de Candu Dominguez',
        'url' => $candu,
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($brand) ?> — Dirección visual</title>
    <meta name="description" content="Galun Design. Dirección visual para artistas. Últimos trabajos, Instagram y WhatsApp.">
    <meta name="theme-color" content="#070708">
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Syne:wght@600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <script>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('is-reveal');
            window.setTimeout(function () {
                document.documentElement.classList.remove('is-reveal');
                var hero = document.getElementById('intro');
                if (hero) {
                    hero.classList.add('is-open');
                }
            }, 4500);
        }
    </script>
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <header class="site-header">
        <a class="logo" href="#intro"><?= e($brand) ?></a>
        <nav class="site-nav" aria-label="Secciones">
            <a href="#trabajos">Trabajos</a>
            <a href="#visuales">Visuales</a>
            <a href="#clientes">Clientes</a>
            <a href="#contacto">Contacto</a>
        </nav>
    </header>

    <section class="hero" id="intro" aria-label="Introducción">
        <div class="hero__stage" aria-hidden="true">
            <div class="hero__wash"></div>
            <div class="hero__grain"></div>
        </div>
        <div class="hero__veil hero__veil--top" aria-hidden="true"></div>
        <div class="hero__veil hero__veil--bottom" aria-hidden="true"></div>
        <div class="intro" aria-hidden="true">
            <div class="intro__half intro__half--left">
                <div class="intro__media">
                    <div class="hero__wash"></div>
                    <div class="hero__grain"></div>
                </div>
            </div>
            <div class="intro__half intro__half--right">
                <div class="intro__media">
                    <div class="hero__wash"></div>
                    <div class="hero__grain"></div>
                </div>
            </div>
            <div class="intro__slash"></div>
        </div>
        <div class="hero__mark">
            <h1 aria-label="Bienvenido a Galundesign">
                <span class="hero__line hero__line--a">Bienvenido a</span>
                <span class="hero__line hero__line--b">Galundesign</span>
            </h1>
            <a class="hero__ig" href="<?= e($instagram) ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zm5 4.6A4.4 4.4 0 1 0 16.4 12 4.4 4.4 0 0 0 12 7.6zm0 7.2A2.8 2.8 0 1 1 14.8 12 2.8 2.8 0 0 1 12 14.8zM17.4 6.2a1 1 0 1 0 1 1 1 1 0 0 0-1-1z"/></svg>
                @galun.psd
                <svg class="hero__check" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.6-4.6 1.4 1.4z"/></svg>
            </a>
        </div>
        <a class="hero__down" href="#trabajos">Trabajos</a>
    </section>

    <main id="contenido">
        <section class="folio" id="trabajos" aria-labelledby="trabajos-titulo">
            <div class="folio__head">
                <h2 class="folio__title" id="trabajos-titulo">Trabajos.</h2>
                <p class="folio__lede"><?= e($lede) ?></p>
            </div>

            <a class="feature" href="<?= e($featured['url']) ?>" target="_blank" rel="noopener noreferrer">
                <div class="feature__frame">
                    <div class="piece__pan">
                        <img
                            src="<?= e($featured['image']) ?>"
                            width="<?= (int) $featured['width'] ?>"
                            height="<?= (int) $featured['height'] ?>"
                            alt="<?= e($featured['alt']) ?>"
                        >
                    </div>
                </div>
                <div class="feature__copy">
                    <p class="feature__kicker"><?= e($featured['kicker']) ?></p>
                    <p class="feature__title"><?= e($featured['title']) ?></p>
                </div>
            </a>

            <?php foreach ($groups as $group): ?>
                <div class="band band--<?= e($group['layout']) ?>">
                    <p class="band__label"><?= e($group['label']) ?></p>
                    <div class="band__grid">
                        <?php foreach ($group['items'] as $item): ?>
                            <a class="piece" href="<?= e($item['url']) ?>" target="_blank" rel="noopener noreferrer">
                                <div class="piece__frame piece__frame--<?= e($group['frame']) ?>">
                                    <div class="piece__pan">
                                        <img
                                            src="<?= e($item['image']) ?>"
                                            width="<?= (int) $item['width'] ?>"
                                            height="<?= (int) $item['height'] ?>"
                                            alt="<?= e($item['alt']) ?>"
                                            loading="lazy"
                                        >
                                    </div>
                                </div>
                                <p class="piece__title"><?= e($item['title']) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="folio folio--visuales" id="visuales" aria-labelledby="visuales-titulo">
            <h2 class="folio__title" id="visuales-titulo">Visuales.</h2>
            <div class="band band--pair">
                <div class="band__grid">
                    <?php foreach ($visuales as $item): ?>
                        <a class="piece piece--wide" href="<?= e($item['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <div class="piece__frame piece__frame--wide">
                                <div class="piece__pan">
                                    <video
                                        class="piece__video"
                                        poster="<?= e($item['poster']) ?>"
                                        width="<?= (int) $item['width'] ?>"
                                        height="<?= (int) $item['height'] ?>"
                                        muted
                                        loop
                                        playsinline
                                        preload="none"
                                        aria-label="<?= e($item['alt']) ?>"
                                    >
                                        <source src="<?= e($item['video']) ?>" type="video/mp4">
                                    </video>
                                </div>
                            </div>
                            <p class="piece__title"><?= e($item['title']) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="folio" id="clientes" aria-labelledby="clientes-titulo">
            <h2 class="folio__title" id="clientes-titulo">Clientes.</h2>
            <ul class="client-list">
                <?php foreach ($clients as $client): ?>
                    <li>
                        <a href="<?= e($client['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="client-card__photo">
                                <?php if (is_file(__DIR__ . '/' . $client['photo'])): ?>
                                    <img src="<?= e($client['photo']) ?>" alt="" width="150" height="150">
                                <?php else: ?>
                                    <?= e($client['mark']) ?>
                                <?php endif; ?>
                            </span>
                            <span class="client-card__copy">
                                <span class="client-card__name"><?= e($client['name']) ?></span>
                                <span class="client-card__handle"><?= e($client['handle']) ?></span>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="contact" id="contacto" aria-labelledby="contacto-titulo">
            <div class="works__head">
                <h2 id="contacto-titulo">Contacto</h2>
                <p>Respondo por ahí</p>
            </div>
            <a class="contact__link" href="<?= e($whatsappHref) ?>" target="_blank" rel="noopener noreferrer">
                WhatsApp
                <span><?= e($whatsappDisplay) ?></span>
            </a>
            <a class="contact__link" href="<?= e($instagram) ?>" target="_blank" rel="noopener noreferrer">
                Instagram
                <span>@galun.psd</span>
            </a>
        </section>
    </main>

    <footer class="site-footer">
        <p><?= e($brand) ?></p>
        <p class="site-credit">Desarrollado por <a href="https://www.lavigna360.com" target="_blank" rel="noopener noreferrer">www.lavigna<span>360</span>.com</a></p>
    </footer>

    <a class="dock" href="<?= e($whatsappHref) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>

    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js" defer></script>
    <script src="<?= e(asset('assets/js/intro.js')) ?>" defer></script>
</body>
</html>
