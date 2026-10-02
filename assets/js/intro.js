(() => {
    const hero = document.getElementById('intro');
    const header = document.querySelector('.site-header');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let done = false;

    const finish = () => {
        if (done) {
            return;
        }
        done = true;
        if (window.gsap) {
            gsap.killTweensOf('.intro__half, .intro__slash, .intro__media');
        }
        document.documentElement.classList.remove('is-intro');
        hero.classList.add('is-open');
        bindScroll();
    };

    const onScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    onScroll();
    document.addEventListener('scroll', onScroll, { passive: true });

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
        if (reduce || !window.gsap || !window.ScrollTrigger) {
            return;
        }

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
            .to('.hero__down', { autoAlpha: 0, y: 16, ease: 'none' }, 0);

        gsap.from('.statement h2', {
            rotateX: 58,
            y: 72,
            transformOrigin: '50% 100%',
            ease: 'none',
            scrollTrigger: {
                trigger: '.statement',
                start: 'top bottom',
                end: 'center 38%',
                scrub: 0.7,
            },
        });

        gsap.from('.lede', {
            y: 40,
            autoAlpha: 0,
            ease: 'none',
            scrollTrigger: {
                trigger: '.lede',
                start: 'top bottom',
                end: 'center 46%',
                scrub: 0.7,
            },
        });

        document.querySelectorAll('.work').forEach((work, index) => {
            const frame = work.querySelector('.work__frame');
            const pan = work.querySelector('.work__pan');
            const tilt = index % 2 === 0 ? -5 : 5;

            gsap.fromTo(frame, { rotate: tilt }, {
                rotate: 0,
                ease: 'none',
                scrollTrigger: {
                    trigger: work,
                    start: 'top bottom',
                    end: 'center 32%',
                    scrub: 0.7,
                },
            });

            gsap.fromTo(pan, { yPercent: -10 }, {
                yPercent: 10,
                ease: 'none',
                scrollTrigger: {
                    trigger: work,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: true,
                },
            });
        });

        document.querySelectorAll('.contact__link').forEach((link, index) => {
            gsap.from(link, {
                x: index % 2 === 0 ? -64 : 64,
                ease: 'none',
                scrollTrigger: {
                    trigger: link,
                    start: 'top bottom',
                    end: 'center 42%',
                    scrub: 0.7,
                },
            });
        });

        ScrollTrigger.refresh();
    };

    if (reduce || !window.gsap) {
        finish();
        return;
    }

    const tl = gsap.timeline({ onComplete: finish });
    tl.fromTo('.intro__media', { scale: 1.12 }, { scale: 1, duration: 1.05, ease: 'power2.out' })
        .fromTo('.intro__slash', { scaleY: 0 }, { scaleY: 1, duration: 0.42, ease: 'power2.in' }, '-=0.28')
        .to('.intro__half--left', { xPercent: -105, duration: 1.15, ease: 'power4.inOut' })
        .to('.intro__half--right', { xPercent: 105, duration: 1.15, ease: 'power4.inOut' }, '<')
        .to('.intro__slash', { autoAlpha: 0, duration: 0.25 }, '-=0.35')
        .to({}, { duration: 0.45 });
})();
