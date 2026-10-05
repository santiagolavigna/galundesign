<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$brand = 'Galun Design';
$tagline = 'Dirección visual para artistas que están sonando ahora.';
$instagram = 'https://www.instagram.com/galun.psd/';
$whatsappDisplay = '+54 9 2346 57-1207';
$whatsappHref = 'https://wa.me/5492346571207?text=' . rawurlencode(
    'Hola Galun, vi la página y quiero consultar por un trabajo.'
);
$intro = 'assets/img/demo/intro-split.webp';

$works = [
    [
        'title' => 'Corte',
        'index' => '01',
        'image' => 'assets/img/demo/work-01.webp',
        'width' => 720,
        'height' => 1280,
        'layout' => 'reel',
        'url' => $instagram,
        'alt' => 'Pieza negra con un círculo hueso partido por una línea vertical',
    ],
    [
        'title' => 'Rojo',
        'index' => '02',
        'image' => 'assets/img/demo/work-02.webp',
        'width' => 720,
        'height' => 1280,
        'layout' => 'reel',
        'url' => $instagram,
        'alt' => 'Impresión con papel rojo rasgado, trama de puntos y una barra chartreuse',
    ],
    [
        'title' => 'Cromo',
        'index' => '03',
        'image' => 'assets/img/demo/work-03.webp',
        'width' => 720,
        'height' => 1280,
        'layout' => 'reel',
        'url' => $instagram,
        'alt' => 'Forma de metal líquido sobre negro, con reflejos chartreuse y hueso',
    ],
    [
        'title' => 'Señal',
        'index' => '04',
        'image' => 'assets/img/demo/work-06.webp',
        'width' => 1280,
        'height' => 720,
        'layout' => 'wide',
        'url' => $instagram,
        'alt' => 'Habitación oscura cortada por una línea vertical de luz chartreuse',
    ],
    [
        'title' => 'Mesa',
        'index' => '05',
        'image' => 'assets/img/demo/work-05.webp',
        'width' => 1152,
        'height' => 864,
        'layout' => 'table',
        'url' => $instagram,
        'alt' => 'Mesa de estudio con papeles, una lámina geométrica y una regla',
    ],
    [
        'title' => 'Tinta',
        'index' => '06',
        'image' => 'assets/img/demo/work-04.webp',
        'width' => 720,
        'height' => 1280,
        'layout' => 'reel',
        'url' => $instagram,
        'alt' => 'Tinta negra en agua con un punto de pigmento chartreuse',
    ],
];

$clients = [
    [
        'name' => 'Nombre del artista',
        'text' => 'Una frase sobre quién es y qué se hizo juntos.',
        'slides' => [
            ['src' => 'assets/img/demo/work-01.webp', 'width' => 720, 'height' => 1280, 'alt' => 'Pieza negra con un círculo partido por una línea'],
            ['src' => 'assets/img/demo/work-02.webp', 'width' => 720, 'height' => 1280, 'alt' => 'Impresión roja con trama de puntos'],
            ['src' => 'assets/img/demo/work-03.webp', 'width' => 720, 'height' => 1280, 'alt' => 'Forma de metal líquido sobre negro'],
        ],
    ],
    [
        'name' => 'Nombre del artista',
        'text' => 'Otra línea corta: el proyecto, el disco o la campaña.',
        'slides' => [
            ['src' => 'assets/img/demo/work-06.webp', 'width' => 1280, 'height' => 720, 'alt' => 'Línea de luz chartreuse en una habitación oscura'],
            ['src' => 'assets/img/demo/work-05.webp', 'width' => 1152, 'height' => 864, 'alt' => 'Mesa de estudio con una lámina geométrica'],
        ],
    ],
    [
        'name' => 'Nombre del artista',
        'text' => 'Se reemplaza por el texto real de cada cliente.',
        'slides' => [
            ['src' => 'assets/img/demo/work-04.webp', 'width' => 720, 'height' => 1280, 'alt' => 'Tinta negra con un punto chartreuse'],
            ['src' => 'assets/img/demo/work-01.webp', 'width' => 720, 'height' => 1280, 'alt' => 'Pieza negra con un círculo partido por una línea'],
        ],
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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="<?= e($intro) ?>">
    <link rel="stylesheet" href="assets/css/app.css">
    <script>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('is-intro');
        }
    </script>
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <header class="site-header">
        <a class="logo" href="#intro"><?= e($brand) ?></a>
        <nav class="site-nav" aria-label="Secciones">
            <a href="#trabajos">Trabajos</a>
            <a href="#clientes">Clientes</a>
            <a href="#contacto">Contacto</a>
        </nav>
    </header>

    <section class="hero" id="intro" aria-label="Introducción">
        <canvas class="hero__scene" id="hero-scene" aria-hidden="true"></canvas>
        <img class="hero__still" src="<?= e($intro) ?>" width="864" height="1152" alt="Retrato editorial de prueba para la intro de Galun Design">
        <div class="hero__mark">
            <p class="eyebrow">Estudio</p>
            <h1>
                <span class="hero__line hero__line--a">Galun</span>
                <span class="hero__line hero__line--b">Design</span>
            </h1>
        </div>
        <a class="hero__down" href="#trabajos">Trabajos</a>

        <div class="intro__half intro__half--left" aria-hidden="true">
            <img class="intro__media" src="<?= e($intro) ?>" width="864" height="1152" alt="">
        </div>
        <div class="intro__half intro__half--right" aria-hidden="true">
            <img class="intro__media" src="<?= e($intro) ?>" width="864" height="1152" alt="">
        </div>
        <div class="intro__slash" aria-hidden="true"></div>
    </section>

    <main id="contenido">
        <section class="statement">
            <p class="eyebrow">Diseño gráfico</p>
            <h2><?= e($tagline) ?></h2>
            <p class="lede">Identidad, portadas y piezas de campaña. Abajo, lo último.</p>
        </section>

        <section class="works" id="trabajos" aria-labelledby="trabajos-titulo">
            <div class="works__head">
                <h2 id="trabajos-titulo">Últimos trabajos</h2>
                <p>Selección</p>
            </div>
            <div class="works__grid">
                <?php foreach ($works as $work): ?>
                    <a class="work work--<?= e($work['layout']) ?>" href="<?= e($work['url']) ?>" target="_blank" rel="noopener noreferrer">
                        <div class="work__frame">
                            <div class="work__pan">
                                <img
                                    src="<?= e($work['image']) ?>"
                                    width="<?= (int) $work['width'] ?>"
                                    height="<?= (int) $work['height'] ?>"
                                    alt="<?= e($work['alt']) ?>"
                                    loading="lazy"
                                >
                            </div>
                        </div>
                        <p class="work__meta">
                            <span><?= e($work['title']) ?></span>
                            <span><?= e($work['index']) ?></span>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="clients" id="clientes" aria-labelledby="clientes-titulo">
            <div class="works__head">
                <h2 id="clientes-titulo">Algunos de nuestros clientes</h2>
                <p>Artistas</p>
            </div>
            <div class="clients__grid">
                <?php foreach ($clients as $client): ?>
                    <article class="client">
                        <div class="client__carousel">
                            <div class="client__track" tabindex="0">
                                <?php foreach ($client['slides'] as $slide): ?>
                                    <img
                                        src="<?= e($slide['src']) ?>"
                                        width="<?= (int) $slide['width'] ?>"
                                        height="<?= (int) $slide['height'] ?>"
                                        alt="<?= e($slide['alt']) ?>"
                                        loading="lazy"
                                    >
                                <?php endforeach; ?>
                            </div>
                            <button class="client__nav client__nav--prev" type="button" aria-label="Foto anterior">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5 L8 12 L15 19"/></svg>
                            </button>
                            <button class="client__nav client__nav--next" type="button" aria-label="Foto siguiente">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5 L16 12 L9 19"/></svg>
                            </button>
                            <p class="client__count" aria-hidden="true">01 / <?= e(str_pad((string) count($client['slides']), 2, '0', STR_PAD_LEFT)) ?></p>
                        </div>
                        <h3><?= e($client['name']) ?></h3>
                        <p><?= e($client['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
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
        <p class="site-credit">Desarrollado por <a href="https://www.lavigna360.com" target="_blank" rel="noopener noreferrer">www.lavigna360.com</a></p>
    </footer>

    <a class="dock" href="<?= e($whatsappHref) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>

    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js"
        }
    }
    </script>
    <script type="module" src="assets/js/hero-3d.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js" defer></script>
    <script src="assets/js/intro.js" defer></script>
</body>
</html>
