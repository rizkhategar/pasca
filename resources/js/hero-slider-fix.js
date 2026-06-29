function pascaReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
        return;
    }

    callback();
}

pascaReady(() => {
    const hero = document.querySelector('body.home-page .hero');
    const titleEl = hero?.querySelector('.hero-title');
    const subtitleEl = hero?.querySelector('.hero-subtitle');
    const slides = Array.from(hero?.querySelectorAll('.hero-slide') || []);
    const dots = Array.from(hero?.querySelectorAll('.hero-dot') || []);
    const dataElement = document.getElementById('pascaHeroSlidersData');

    if (!hero || !slides.length) return;

    let items = [];

    try {
        items = JSON.parse(dataElement?.dataset?.sliders || '[]');
    } catch (error) {
        items = [];
    }

    if (!items.length) {
        items = slides.map((slide) => ({
            title: slide.dataset.title || titleEl?.textContent || '',
            subtitle: slide.dataset.subtitle || subtitleEl?.textContent || '',
            duration: Number(slide.dataset.duration || 3000),
        }));
    }

    let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('active')));
    let timer = null;

    const safeDuration = (index) => Math.min(30000, Math.max(1400, Number(items[index]?.duration || slides[index]?.dataset.duration || 3000)));

    const setText = (index) => {
        const item = items[index] || items[0] || {};
        const title = String(item.title || '').trim();
        const subtitle = String(item.subtitle || '').trim();

        if (titleEl && title) {
            titleEl.innerHTML = title.replace(/\n/g, '<br>');
        }

        if (subtitleEl) {
            subtitleEl.textContent = subtitle;
            subtitleEl.style.display = subtitle ? '' : 'none';
        }
    };

    const setActive = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('active', slideIndex === activeIndex);
        });

        dots.forEach((dot, dotIndex) => {
            dot.classList.toggle('active', dotIndex === activeIndex);
        });

        setText(activeIndex);
    };

    const scheduleNext = () => {
        clearTimeout(timer);

        if (slides.length <= 1) return;

        timer = setTimeout(() => {
            setActive(activeIndex + 1);
            scheduleNext();
        }, safeDuration(activeIndex));
    };

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            setActive(index);
            scheduleNext();
        });
    });

    setActive(activeIndex);
    scheduleNext();
});
