(() => {
    const hero = document.getElementById('intro');
    const header = document.querySelector('.site-header');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let scrollBound = false;
    let introTl = null;
    let done = false;

    const onScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    onScroll();
    document.addEventListener('scroll', onScroll, { passive: true });

    document.querySelectorAll('.piece__video').forEach((video) => {
        if (reduce) {
            return;
        }
        const watch = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            });
        }, { threshold: 0.45 });
        watch.observe(video);
    });

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const id = link.getAttribute('href');
            const target = id ? document.querySelector(id) : null;
            if (!target) {
                return;
            }
            event.preventDefault();
            target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' });
        });
    });

    const bindScroll = () => {
        if (scrollBound || reduce || !window.gsap || !window.ScrollTrigger) {
            return;
        }
        scrollBound = true;
        gsap.registerPlugin(ScrollTrigger);

        gsap.timeline({
            scrollTrigger: {
                trigger: '#intro',
                start: 'top top',
                end: '+=85%',
                pin: true,
                scrub: 0.7,
            },
        })
            .to('.hero__line--a', { rotate: -22, y: -28, ease: 'none' }, 0)
            .to('.hero__line--b', { rotate: 18, y: 22, ease: 'none' }, 0)
            .to('.hero__ig', { autoAlpha: 0, y: 16, ease: 'none' }, 0)
            .to('.hero__down', { autoAlpha: 0, y: 16, ease: 'none' }, 0);

        document.querySelectorAll('.folio__title').forEach((title) => {
            gsap.from(title, {
                rotateX: 58,
                y: 72,
                transformOrigin: '50% 100%',
                ease: 'none',
                scrollTrigger: {
                    trigger: title,
                    start: 'top bottom',
                    end: 'center 42%',
                    scrub: 0.7,
                },
            });
        });

        gsap.from('.folio__lede', {
            y: 40,
            autoAlpha: 0,
            ease: 'none',
            scrollTrigger: {
                trigger: '.folio__lede',
                start: 'top bottom',
                end: 'center 46%',
                scrub: 0.7,
            },
        });

        document.querySelectorAll('.client-list a').forEach((link) => {
            gsap.from(link, {
                y: 48,
                ease: 'none',
                scrollTrigger: {
                    trigger: link,
                    start: 'top bottom',
                    end: 'center 60%',
                    scrub: 0.7,
                },
            });
        });

        document.querySelectorAll('.feature, .piece').forEach((card) => {
            gsap.from(card, {
                y: 64,
                ease: 'none',
                scrollTrigger: {
                    trigger: card,
                    start: 'top bottom',
                    end: 'center 55%',
                    scrub: 0.7,
                },
            });
        });

        const contactLinks = document.querySelectorAll('.contact__link');
        const contactScroll = () => ({
            trigger: '.contact',
            start: 'top 92%',
            end: 'top 58%',
            scrub: 0.65,
        });
        if (contactLinks[0]) {
            gsap.from(contactLinks[0], { x: -160, ease: 'none', scrollTrigger: contactScroll() });
        }
        if (contactLinks[1]) {
            gsap.from(contactLinks[1], { x: 160, ease: 'none', scrollTrigger: contactScroll() });
        }

        ScrollTrigger.refresh();
        window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
    };

    const finish = () => {
        if (done) {
            return;
        }
        done = true;
        if (introTl) {
            introTl.kill();
            introTl = null;
        }
        if (window.gsap) {
            gsap.set('.hero__char span', { yPercent: 0 });
            gsap.set('.hero__ig, .hero__down', { autoAlpha: 1, y: 0 });
        }
        document.documentElement.classList.remove('is-reveal', 'is-playing');
        hero.classList.add('is-open');
        bindScroll();
    };

    const splitChars = (el) => {
        const text = el.textContent || '';
        el.setAttribute('aria-hidden', 'true');
        el.textContent = '';
        [...text].forEach((ch) => {
            const outer = document.createElement('span');
            outer.className = 'hero__char';
            const inner = document.createElement('span');
            inner.textContent = ch === ' ' ? '\u00a0' : ch;
            outer.append(inner);
            el.append(outer);
        });
        return [...el.querySelectorAll(':scope > .hero__char > span')];
    };

    const fromCenter = (nodes) => {
        const mid = (nodes.length - 1) / 2;
        return nodes
            .map((node, index) => ({ node, index }))
            .sort((a, b) => Math.abs(a.index - mid) - Math.abs(b.index - mid))
            .map((item) => item.node);
    };

    if (reduce || !window.gsap) {
        finish();
        return;
    }

    const lineA = document.querySelector('.hero__line--a');
    const lineB = document.querySelector('.hero__line--b');
    const small = fromCenter(splitChars(lineA));
    const big = fromCenter(splitChars(lineB));
    const openX = window.innerWidth < 800 ? 100 : 105;

    gsap.set([...small, ...big], { yPercent: 110 });
    gsap.set('.hero__ig, .hero__down', { autoAlpha: 0, y: 12 });
    document.documentElement.classList.add('is-playing');

    introTl = gsap.timeline({
        onComplete: () => {
            introTl = null;
            finish();
        },
    });
    introTl
        .fromTo('.intro__media', { scale: 1.08 }, { scale: 1, duration: 0.85, ease: 'power2.out' })
        .fromTo('.intro__slash', { scaleY: 0 }, { scaleY: 1, duration: 0.35, ease: 'power2.in' }, '-=0.18')
        .to('.intro__half--left', { xPercent: -openX, duration: 1.05, ease: 'power4.inOut' })
        .to('.intro__half--right', { xPercent: openX, duration: 1.05, ease: 'power4.inOut' }, '<')
        .to('.intro__slash', { autoAlpha: 0, duration: 0.2 }, '-=0.4')
        .to(small, { yPercent: 0, duration: 0.55, stagger: 0.028, ease: 'power3.out' }, '-=0.82')
        .to(big, { yPercent: 0, duration: 0.72, stagger: 0.04, ease: 'power3.out' }, '-=0.42')
        .to('.hero__ig, .hero__down', { autoAlpha: 1, y: 0, duration: 0.4, stagger: 0.06, ease: 'power2.out' }, '-=0.3');

    window.setTimeout(finish, 3800);
})();
