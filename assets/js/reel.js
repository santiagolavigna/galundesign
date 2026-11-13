(() => {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('[data-reel]').forEach((reel) => {
        const scroller = reel.querySelector('.reel__scroller');
        const list = reel.querySelector('.reel__list');
        if (!scroller || !list) {
            return;
        }
        const originals = [...list.children];
        if (originals.length < 2) {
            return;
        }

        const cloneSet = () => {
            const fragment = document.createDocumentFragment();
            originals.forEach((node) => {
                const clone = node.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.querySelectorAll('a, button, video').forEach((el) => {
                    el.setAttribute('tabindex', '-1');
                });
                fragment.appendChild(clone);
            });
            return fragment;
        };

        list.append(cloneSet());
        list.prepend(cloneSet());

        const setWidth = () => {
            const start = list.children[originals.length];
            const after = list.children[originals.length * 2];
            if (!start || !after) {
                return 0;
            }
            return after.offsetLeft - start.offsetLeft;
        };

        const markCenter = () => {
            if (reel.classList.contains('reel--flat')) {
                return;
            }
            const mid = scroller.getBoundingClientRect().left + scroller.clientWidth / 2;
            let best = null;
            let bestDist = Infinity;
            [...list.children].forEach((card) => {
                card.classList.remove('is-center');
                const rect = card.getBoundingClientRect();
                const dist = Math.abs(rect.left + rect.width / 2 - mid);
                if (dist < bestDist) {
                    bestDist = dist;
                    best = card;
                }
            });
            if (best) {
                best.classList.add('is-center');
            }
        };

        const normalize = () => {
            const width = setWidth();
            if (width <= 0) {
                return;
            }
            if (scroller.scrollLeft < width * 0.5) {
                scroller.scrollLeft += width;
            } else if (scroller.scrollLeft >= width * 1.5) {
                scroller.scrollLeft -= width;
            }
        };

        const centerOn = (index) => {
            const card = list.children[originals.length + index];
            if (!card) {
                return;
            }
            const delta = card.offsetLeft - (scroller.clientWidth - card.offsetWidth) / 2;
            scroller.scrollTo({ left: delta, behavior: reduce ? 'auto' : 'smooth' });
        };

        scroller.scrollLeft = setWidth();
        markCenter();

        let moved = false;
        scroller.addEventListener('scroll', () => {
            normalize();
            markCenter();
        }, { passive: true });

        reel.querySelector('.reel__nav--prev')?.addEventListener('click', () => {
            const card = list.children[originals.length];
            const step = card ? card.offsetWidth + 16 : 240;
            scroller.scrollBy({ left: -step, behavior: reduce ? 'auto' : 'smooth' });
        });
        reel.querySelector('.reel__nav--next')?.addEventListener('click', () => {
            const card = list.children[originals.length];
            const step = card ? card.offsetWidth + 16 : 240;
            scroller.scrollBy({ left: step, behavior: reduce ? 'auto' : 'smooth' });
        });

        let drag = null;
        scroller.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }
            drag = { id: event.pointerId, x: event.clientX, left: scroller.scrollLeft };
            moved = false;
            scroller.setPointerCapture(event.pointerId);
        });
        scroller.addEventListener('pointermove', (event) => {
            if (!drag || drag.id !== event.pointerId) {
                return;
            }
            const delta = event.clientX - drag.x;
            if (Math.abs(delta) > 6) {
                moved = true;
            }
            scroller.scrollLeft = drag.left - delta;
        });
        const endDrag = () => {
            drag = null;
        };
        scroller.addEventListener('pointerup', endDrag);
        scroller.addEventListener('pointercancel', endDrag);
        scroller.addEventListener('click', (event) => {
            if (moved) {
                event.preventDefault();
                event.stopPropagation();
                moved = false;
            }
        }, true);

        centerOn(0);

        if (!reduce) {
            const watch = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    const video = entry.target;
                    if (!(video instanceof HTMLVideoElement)) {
                        return;
                    }
                    if (entry.isIntersecting) {
                        video.play().catch(() => {});
                    } else {
                        video.pause();
                    }
                });
            }, { threshold: 0.45 });
            scroller.querySelectorAll('video').forEach((video) => watch.observe(video));
        }
    });
})();