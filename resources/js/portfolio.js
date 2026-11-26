const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const themeToggle = document.querySelector('[data-theme-toggle]');
if (themeToggle) {
    const page = document.documentElement;
    const themeColor = document.querySelector('meta[name="theme-color"]');

    function syncTheme() {
        const isLight = page.dataset.theme === 'light';
        themeToggle.setAttribute('aria-pressed', String(isLight));
        themeToggle.title = isLight
            ? themeToggle.dataset.darkLabel
            : themeToggle.getAttribute('aria-label');
        themeColor?.setAttribute(
            'content',
            getComputedStyle(page).backgroundColor,
        );
    }

    themeToggle.addEventListener('click', () => {
        page.dataset.theme = page.dataset.theme === 'light' ? 'dark' : 'light';
        try {
            localStorage.setItem('portfolio-theme', page.dataset.theme);
        } catch {
            // The theme can still change for the current page.
        }
        syncTheme();
    });

    syncTheme();
    themeToggle.hidden = false;
}

document.querySelectorAll('[data-gallery-track]').forEach(setupGallery);

document.querySelectorAll('[data-gallery-preview]').forEach((preview) => {
    if (typeof preview.showModal !== 'function') {
        return;
    }

    const image = preview.querySelector('img');

    preview.parentElement
        .querySelectorAll('[data-gallery-expand]')
        .forEach((link) => {
            link.setAttribute('aria-haspopup', 'dialog');
            link.addEventListener('click', (event) => {
                if (
                    event.defaultPrevented ||
                    event.ctrlKey ||
                    event.metaKey ||
                    event.shiftKey ||
                    event.altKey
                ) {
                    return;
                }

                image.src = link.href;
                image.alt = link.querySelector('img').alt;
                link.focus({ preventScroll: true });
                preview.showModal();
                event.preventDefault();
            });
        });
});

function setupGallery(track) {
    const slides = [...track.querySelectorAll('[data-gallery-slide]')];
    const pauseArea = track.closest('[data-project-card]') ?? track;
    const pauseButton = track.querySelector('[data-gallery-pause]');
    const preview = track.querySelector('[data-gallery-preview]');
    const canObserve = 'IntersectionObserver' in window;
    let index = 0;
    let paused = false;
    let inView = !canObserve;
    let interval;

    function setSlideActive(slide, active) {
        slide.toggleAttribute('data-active', active);
        if (active) {
            slide.removeAttribute('aria-hidden');
        } else {
            slide.setAttribute('aria-hidden', 'true');
        }
        slide.inert = !active;
    }

    function showSlide(step) {
        setSlideActive(slides[index], false);
        index = (index + step + slides.length) % slides.length;
        setSlideActive(slides[index], true);
    }

    function syncTimer() {
        window.clearInterval(interval);
        const canRotate =
            inView &&
            !paused &&
            !preview?.open &&
            !document.hidden &&
            !reducedMotion.matches;
        if (canRotate && !pauseArea.matches(':hover, :focus-within')) {
            interval = window.setInterval(() => showSlide(1), 4500);
        }
    }

    pauseButton.addEventListener('click', () => {
        paused = !paused;
        pauseButton.setAttribute('aria-pressed', String(paused));
        syncTimer();
    });
    track.querySelectorAll('[data-gallery-step]').forEach((button) => {
        button.addEventListener('click', () =>
            showSlide(Number(button.dataset.galleryStep)),
        );
    });
    pauseArea.addEventListener('mouseenter', syncTimer);
    pauseArea.addEventListener('mouseleave', syncTimer);
    pauseArea.addEventListener('focusin', syncTimer);
    pauseArea.addEventListener('focusout', () => queueMicrotask(syncTimer));
    preview?.addEventListener('toggle', syncTimer);
    reducedMotion.addEventListener('change', syncTimer);
    document.addEventListener('visibilitychange', syncTimer);
    slides.forEach((slide, slideIndex) =>
        setSlideActive(slide, slideIndex === index),
    );
    track.setAttribute('data-gallery-ready', '');
    track.scrollLeft = 0;
    track.querySelector('[data-gallery-controls]').hidden = false;

    if (canObserve) {
        new IntersectionObserver(([entry]) => {
            inView = entry.isIntersecting;
            syncTimer();
        }).observe(track);
    } else {
        syncTimer();
    }
}

if ('IntersectionObserver' in window) {
    const reveal = new IntersectionObserver(
        (entries, observer) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) {
                    continue;
                }

                const element = entry.target;
                element.classList.add('revealed');
                if (!reducedMotion.matches) {
                    element.animate(
                        [
                            { opacity: 0, transform: 'translateY(24px)' },
                            { opacity: 1, transform: 'translateY(0)' },
                        ],
                        {
                            duration: 700,
                            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                        },
                    );
                }
                observer.unobserve(element);
            }
        },
        { rootMargin: '-10%' },
    );

    document
        .querySelectorAll('[data-reveal]')
        .forEach((element) => reveal.observe(element));
}

const grid = document.querySelector('[data-grid-field]');
if (grid instanceof HTMLCanvasElement) {
    setupGrid(grid);
}

function setupGrid(canvas) {
    const context = canvas.getContext('2d');
    if (!context) {
        return;
    }

    const styles = getComputedStyle(canvas);
    const gap = 48;
    let width = 0;
    let height = 0;
    let pointerX = -999;
    let pointerY = -999;
    let frame = 0;

    function draw() {
        frame = 0;
        context.clearRect(0, 0, width, height);
        context.fillStyle = styles.getPropertyValue('--color-sage').trim();
        for (let x = 0; x <= width + gap; x += gap) {
            for (let y = 0; y <= height + gap; y += gap) {
                const dx = x - pointerX;
                const dy = y - pointerY;
                const distance = Math.hypot(dx, dy) || 1;
                const force = Math.max(0, 1 - distance / 220);
                const size = 2 + force * 2;
                context.globalAlpha = 0.12 + force * 0.7;
                context.fillRect(
                    x + (dx / distance) * force * 18 - 1,
                    y + (dy / distance) * force * 18 - 1,
                    size,
                    size,
                );
            }
        }
    }

    function resize() {
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        width = canvas.clientWidth;
        height = canvas.clientHeight;
        canvas.width = width * ratio;
        canvas.height = height * ratio;
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        draw();
    }

    themeToggle?.addEventListener('click', draw);
    window.addEventListener('resize', resize);
    window.addEventListener(
        'pointermove',
        (event) => {
            if (reducedMotion.matches) {
                return;
            }

            const bounds = canvas.getBoundingClientRect();
            pointerX = event.clientX - bounds.left;
            pointerY = event.clientY - bounds.top;
            if (!frame) {
                frame = window.requestAnimationFrame(draw);
            }
        },
        { passive: true },
    );
    reducedMotion.addEventListener('change', () => {
        pointerX = -999;
        pointerY = -999;
        draw();
    });
    resize();
}

const clock = document.querySelector('[data-local-time]');
if (clock instanceof HTMLElement) {
    const format = new Intl.DateTimeFormat('en-GB', {
        timeZone: clock.dataset.zone,
        hour: '2-digit',
        minute: '2-digit',
    });
    const update = () => {
        clock.textContent = clock.dataset.localTime.replace(
            '__time__',
            format.format(new Date()),
        );
    };
    update();
    window.setInterval(update, 30000);
}

const copy = document.querySelector('[data-copy-email]');
if (copy instanceof HTMLButtonElement && navigator.clipboard?.writeText) {
    copy.hidden = false;
    const originalLabel = copy.textContent;
    let resetTimer;
    copy.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(copy.dataset.copyEmail);
            copy.textContent = copy.dataset.copied;
        } catch {
            copy.textContent = copy.dataset.failed;
        }
        window.clearTimeout(resetTimer);
        resetTimer = window.setTimeout(() => {
            copy.textContent = originalLabel;
        }, 2000);
    });
}
