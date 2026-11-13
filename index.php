<?php
declare(strict_types=1);

require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/home.php';

$home = home_data();
$brand = $home['brand'];
$instagram = $home['instagram_url'];
$whatsappHref = $home['whatsapp_href'];
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
    <link rel="stylesheet" href="<?= e(media_src('assets/css/app.css')) ?>">
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
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-menu">Menú</button>
        <nav class="site-nav" id="site-menu" aria-label="Secciones">
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
            <?php if ($instagram !== ''): ?>
            <a class="hero__ig" href="<?= e($instagram) ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zm5 4.6A4.4 4.4 0 1 0 16.4 12 4.4 4.4 0 0 0 12 7.6zm0 7.2A2.8 2.8 0 1 1 14.8 12 2.8 2.8 0 0 1 12 14.8zM17.4 6.2a1 1 0 1 0 1 1 1 1 0 0 0-1-1z"/></svg>
                <?= e($home['instagram_handle']) ?>
                <svg class="hero__check" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.6-4.6 1.4 1.4z"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <a class="hero__down" href="#trabajos">Trabajos</a>
    </section>

    <main id="contenido">
        <?php if ($home['featured'] !== null || $home['works'] !== []): ?>
        <section class="folio" id="trabajos" aria-labelledby="trabajos-titulo">
            <div class="folio__head">
                <h2 class="folio__title" id="trabajos-titulo">Trabajos.</h2>
                <?php if ($home['tagline'] !== ''): ?><p class="folio__lede"><?= e($home['tagline']) ?></p><?php endif; ?>
            </div>

            <?php if ($home['featured'] !== null): ?>
            <a class="feature" href="<?= e((string) $home['featured']['link_url']) ?>" target="_blank" rel="noopener noreferrer">
                <div class="feature__frame">
                    <div class="piece__pan">
                        <img src="<?= e(media_src((string) $home['featured']['file_path'])) ?>" width="<?= (int) $home['featured']['width'] ?>" height="<?= (int) $home['featured']['height'] ?>" alt="<?= e('Flyer de ' . $home['featured']['title']) ?>">
                    </div>
                </div>
                <div class="feature__copy">
                    <p class="feature__kicker">Proyecto destacado</p>
                    <p class="feature__title"><?= e((string) $home['featured']['title']) ?></p>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($home['works_reel']): ?>
            <div class="reel" data-reel>
                <button class="reel__nav reel__nav--prev" type="button" aria-label="Anterior">‹</button>
                <div class="reel__scroller">
                    <div class="reel__list">
                        <?php foreach ($home['works'] as $item): ?>
                        <a class="piece reel__card" href="<?= e((string) $item['link_url']) ?>" target="_blank" rel="noopener noreferrer">
                            <div class="piece__frame piece__frame--<?= e((string) $item['frame']) ?>">
                                <div class="piece__pan">
                                    <img src="<?= e(media_src((string) $item['file_path'])) ?>" width="<?= (int) $item['width'] ?>" height="<?= (int) $item['height'] ?>" alt="<?= e('Flyer de ' . $item['title']) ?>" loading="lazy">
                                </div>
                            </div>
                            <p class="piece__group"><?= e((string) $item['group_label']) ?></p>
                            <p class="piece__title"><?= e((string) $item['title']) ?></p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="reel__nav reel__nav--next" type="button" aria-label="Siguiente">›</button>
            </div>
            <?php else: ?>
                <?php foreach ($home['groups'] as $label => $items): ?>
                <div class="band band--<?= count($items) === 2 ? 'pair' : 'trio' ?>">
                    <p class="band__label"><?= e((string) $label) ?></p>
                    <div class="band__grid">
                        <?php foreach ($items as $item): ?>
                        <a class="piece" href="<?= e((string) $item['link_url']) ?>" target="_blank" rel="noopener noreferrer">
                            <div class="piece__frame piece__frame--<?= e((string) $item['frame']) ?>">
                                <div class="piece__pan">
                                    <img src="<?= e(media_src((string) $item['file_path'])) ?>" width="<?= (int) $item['width'] ?>" height="<?= (int) $item['height'] ?>" alt="<?= e('Flyer de ' . $item['title']) ?>" loading="lazy">
                                </div>
                            </div>
                            <p class="piece__title"><?= e((string) $item['title']) ?></p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($home['visuals'] !== []): ?>
        <section class="folio folio--visuales" id="visuales" aria-labelledby="visuales-titulo">
            <h2 class="folio__title" id="visuales-titulo">Visuales.</h2>
            <?php if ($home['visuals_reel']): ?>
            <div class="reel reel--visual" data-reel>
                <button class="reel__nav reel__nav--prev" type="button" aria-label="Anterior">‹</button>
                <div class="reel__scroller">
                    <div class="reel__list">
                        <?php foreach ($home['visuals'] as $item): ?>
                        <a class="piece piece--wide reel__card" href="<?= e((string) $item['link_url']) ?>" target="_blank" rel="noopener noreferrer">
                            <div class="piece__frame piece__frame--wide">
                                <div class="piece__pan">
                                    <video class="piece__video" poster="<?= e(media_src((string) $item['poster_path'])) ?>" width="<?= (int) $item['width'] ?>" height="<?= (int) $item['height'] ?>" muted loop playsinline preload="none" aria-label="<?= e('Visual de ' . $item['title']) ?>">
                                        <source src="<?= e(media_src((string) $item['file_path'])) ?>" type="video/mp4">
                                    </video>
                                </div>
                            </div>
                            <p class="piece__title"><?= e((string) $item['title']) ?></p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="reel__nav reel__nav--next" type="button" aria-label="Siguiente">›</button>
            </div>
            <?php else: ?>
            <div class="band band--pair">
                <div class="band__grid">
                    <?php foreach ($home['visuals'] as $item): ?>
                    <a class="piece piece--wide" href="<?= e((string) $item['link_url']) ?>" target="_blank" rel="noopener noreferrer">
                        <div class="piece__frame piece__frame--wide">
                            <div class="piece__pan">
                                <video class="piece__video" poster="<?= e(media_src((string) $item['poster_path'])) ?>" width="<?= (int) $item['width'] ?>" height="<?= (int) $item['height'] ?>" muted loop playsinline preload="none" aria-label="<?= e('Visual de ' . $item['title']) ?>">
                                    <source src="<?= e(media_src((string) $item['file_path'])) ?>" type="video/mp4">
                                </video>
                            </div>
                        </div>
                        <p class="piece__title"><?= e((string) $item['title']) ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($home['clients'] !== []): ?>
        <section class="folio" id="clientes" aria-labelledby="clientes-titulo">
            <h2 class="folio__title" id="clientes-titulo">Clientes.</h2>
            <?php if ($home['clients_reel']): ?>
            <div class="reel reel--flat" data-reel>
                <button class="reel__nav reel__nav--prev" type="button" aria-label="Anterior">‹</button>
                <div class="reel__scroller">
                    <ul class="client-list reel__list">
                        <?php foreach ($home['clients'] as $client): ?>
                        <li class="reel__card">
                            <a href="<?= e((string) $client['url']) ?>" target="_blank" rel="noopener noreferrer">
                                <span class="client-card__photo">
                                    <?php if (is_file(__DIR__ . '/' . $client['photo_path'])): ?>
                                    <img src="<?= e(media_src((string) $client['photo_path'])) ?>" alt="" width="400" height="400">
                                    <?php else: ?>
                                    <?= e(client_mark((string) $client['name'])) ?>
                                    <?php endif; ?>
                                </span>
                                <span class="client-card__copy">
                                    <span class="client-card__name"><?= e((string) $client['name']) ?></span>
                                    <span class="client-card__handle"><?= e((string) $client['handle']) ?></span>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button class="reel__nav reel__nav--next" type="button" aria-label="Siguiente">›</button>
            </div>
            <?php else: ?>
            <ul class="client-list">
                <?php foreach ($home['clients'] as $client): ?>
                <li>
                    <a href="<?= e((string) $client['url']) ?>" target="_blank" rel="noopener noreferrer">
                        <span class="client-card__photo">
                            <?php if (is_file(__DIR__ . '/' . $client['photo_path'])): ?>
                            <img src="<?= e(media_src((string) $client['photo_path'])) ?>" alt="" width="400" height="400">
                            <?php else: ?>
                            <?= e(client_mark((string) $client['name'])) ?>
                            <?php endif; ?>
                        </span>
                        <span class="client-card__copy">
                            <span class="client-card__name"><?= e((string) $client['name']) ?></span>
                            <span class="client-card__handle"><?= e((string) $client['handle']) ?></span>
                        </span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <section class="contact" id="contacto" aria-labelledby="contacto-titulo">
            <div class="works__head">
                <h2 id="contacto-titulo">Contacto</h2>
                <p>Respondo por ahí</p>
            </div>
            <?php if ($whatsappHref !== ''): ?>
            <a class="contact__link" href="<?= e($whatsappHref) ?>" target="_blank" rel="noopener noreferrer">
                WhatsApp
                <span><?= e($home['whatsapp_display']) ?></span>
            </a>
            <?php endif; ?>
            <?php if ($instagram !== ''): ?>
            <a class="contact__link" href="<?= e($instagram) ?>" target="_blank" rel="noopener noreferrer">
                Instagram
                <span><?= e($home['instagram_handle']) ?></span>
            </a>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer">
        <p><?= e($brand) ?></p>
        <p class="site-credit">Desarrollado por <a href="https://www.lavigna360.com" target="_blank" rel="noopener noreferrer">www.lavigna<span>360</span>.com</a></p>
    </footer>

    <?php if ($whatsappHref !== ''): ?>
    <a class="dock" href="<?= e($whatsappHref) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js" defer></script>
    <script src="<?= e(media_src('assets/js/intro.js')) ?>" defer></script>
    <script src="<?= e(media_src('assets/js/nav.js')) ?>" defer></script>
    <script src="<?= e(media_src('assets/js/reel.js')) ?>" defer></script>
</body>
</html>
