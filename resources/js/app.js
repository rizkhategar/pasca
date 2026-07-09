import '../css/research-lecturers.css';

function ready(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
}

function openExternal(url) {
    window.open(url, '_blank', 'noopener,noreferrer');
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;',
    }[char]));
}

function stripHtml(value) {
    return String(value ?? '')
        .replace(/<[^>]*>/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}

function formatDateId(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

ready(() => {
    setupSiteHeaderNavigation();
    setupHeroContactButton();
    setupHomeLatestNews();
    setupStudentServiceCards();
    setupFooterAboutLinks();
    setupWhatsAppAdminModal();
    setupHeroSlider();
});

function setupSiteHeaderNavigation() {
    const siteHeader = document.getElementById('siteHeader');
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const dropdownTriggers = document.querySelectorAll('#siteHeader .dropdown-trigger');
    const navLinks = document.querySelectorAll('#siteHeader .nav-link');

    if (!siteHeader) return;

    siteHeader.classList.remove('nav-collapsed');

    const isCompactMode = () => window.innerWidth <= 992;
    const clearClickActive = () => navLinks.forEach((item) => item.classList.remove('nav-click-active'));
    const isMenuOpen = () => navMenu?.classList.contains('active') || navMenu?.classList.contains('show');

    const setMenuState = (open) => {
        if (!navMenu || !hamburger) return;

        navMenu.classList.toggle('active', open);
        navMenu.classList.toggle('show', open);
        hamburger.classList.toggle('active', open);
        hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('mobile-nav-open', open);

        if (!open) {
            document.querySelectorAll('#siteHeader .nav-item.has-dropdown')
                .forEach((item) => item.classList.remove('open'));
        }
    };

    const closeNavMenu = () => setMenuState(false);

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            clearClickActive();
            if (link.dataset.externalNav === 'true') return;
            link.classList.add('nav-click-active');

            if (!isCompactMode() && link.classList.contains('dropdown-trigger')) {
                requestAnimationFrame(() => link.classList.remove('nav-click-active'));
            }
        });
    });

    window.addEventListener('pageshow', () => {
        siteHeader.classList.remove('nav-collapsed');
        clearClickActive();
        closeNavMenu();
    });

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setMenuState(!isMenuOpen());
        });

        hamburger.addEventListener('touchend', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setMenuState(!isMenuOpen());
        }, { passive: false });
    }

    dropdownTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            if (!isCompactMode()) return;

            event.preventDefault();
            event.stopPropagation();

            const currentItem = trigger.closest('.nav-item');
            document.querySelectorAll('#siteHeader .nav-item.has-dropdown').forEach((item) => {
                if (item !== currentItem) item.classList.remove('open');
            });
            currentItem?.classList.toggle('open');
        });
    });

    navMenu?.querySelectorAll('a:not(.dropdown-trigger)').forEach((link) => {
        link.addEventListener('click', () => {
            if (isCompactMode()) closeNavMenu();
        });
    });

    document.addEventListener('click', (event) => {
        if (!navMenu || !hamburger || !isCompactMode()) return;
        if (!navMenu.contains(event.target) && !hamburger.contains(event.target)) closeNavMenu();
    });

    window.addEventListener('scroll', () => siteHeader.classList.remove('nav-collapsed'));
    window.addEventListener('resize', () => {
        siteHeader.classList.remove('nav-collapsed');
        if (!isCompactMode()) closeNavMenu();
    });
}

function setupHeroContactButton() {
    const contactUrl = document.querySelector('meta[name="pasca-contact-url"]')?.getAttribute('content') || '/kontak';
    document.querySelectorAll('.hero .btn-primary').forEach((button) => button.setAttribute('href', contactUrl));
}

function setupHomeLatestNews() {
    const list = document.querySelector('.home-page .info-section .news-list');
    if (!list) return;

    const pagination = document.querySelector('.home-page .info-section .pagination');
    const filters = document.querySelector('.home-page .category-filters');
    const apiOrigin = 'https://panel-web.unw.ac.id';
    const newsApiUrl = '/berita/search';
    const categoryApiUrl = `${apiOrigin}/api/category`;
    let activeCategory = 'all';

    const normalizeImage = (url) => {
        url = String(url || '').trim();
        if (!url) return '';
        if (/^https?:\/\//i.test(url)) return url;
        return `${apiOrigin}/${url.replace(/^\/+/, '')}`;
    };

    const toArray = (payload) => {
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload?.data)) return payload.data;
        if (Array.isArray(payload?.data?.data)) return payload.data.data;
        if (Array.isArray(payload?.items)) return payload.items;
        if (Array.isArray(payload?.categories)) return payload.categories;
        return [];
    };

    const normalizeCategory = (item) => ({
        id: String(item?.id ?? item?.category_id ?? item?.value ?? item?.slug ?? '').trim(),
        label: String(item?.name ?? item?.title ?? item?.label ?? item?.category_name ?? '').trim(),
    });

    const normalizeNews = (item) => {
        const category = item?.category || {};
        return {
            title: String(item?.title || 'Tanpa Judul'),
            slug: String(item?.slug || '#'),
            image: normalizeImage(item?.image_thumbnail || item?.thumbnail || item?.image || item?.cover || item?.photo || ''),
            excerpt: stripHtml(item?.excerpt || item?.body || item?.content || ''),
            category: String(category?.name || item?.category_name || 'Umum'),
            date: String(item?.publishedAt || item?.published_at || item?.createdAt || item?.created_at || ''),
        };
    };

    const renderNews = (items) => {
        if (!items.length) {
            list.innerHTML = `
                <article class="news-item">
                    <a class="news-item-link" href="/berita">
                        <div class="news-thumb no-image"><i class="fas fa-newspaper"></i></div>
                        <div class="news-content">
                            <div class="news-category"><i class="fas fa-tag"></i>Informasi</div>
                            <h3 class="news-title">Berita belum tersedia</h3>
                            <p class="news-excerpt">Silakan buka halaman Berita untuk melihat informasi terbaru.</p>
                            <div class="news-date"><i class="fas fa-calendar-alt"></i>${escapeHtml(formatDateId(new Date()))}</div>
                        </div>
                    </a>
                </article>
            `;
            return;
        }

        list.innerHTML = items.slice(0, 3).map((item) => {
            const title = escapeHtml(item.title);
            const category = escapeHtml(item.category);
            const excerpt = escapeHtml(item.excerpt).slice(0, 170);
            const date = escapeHtml(formatDateId(item.date) || 'Tanggal belum tersedia');
            const href = item.slug && item.slug !== '#' ? `/berita/${encodeURIComponent(item.slug)}` : '/berita';
            const image = item.image
                ? `<img src="${escapeHtml(item.image)}" alt="${title}" loading="lazy" onerror="this.closest('.news-thumb').classList.add('no-image'); this.remove();">`
                : `<i class="fas fa-newspaper"></i>`;

            return `
                <article class="news-item">
                    <a class="news-item-link" href="${href}">
                        <div class="news-thumb">${image}</div>
                        <div class="news-content">
                            <div class="news-category"><i class="fas fa-tag"></i>${category}</div>
                            <h3 class="news-title">${title}</h3>
                            <p class="news-excerpt">${excerpt}</p>
                            <div class="news-date"><i class="fas fa-calendar-alt"></i>${date}</div>
                        </div>
                    </a>
                </article>
            `;
        }).join('');
    };

    const buildNewsUrl = () => {
        const params = new URLSearchParams({ paginate: '3', page: '1', sort: 'desc' });
        if (activeCategory !== 'all') params.set('category_id', activeCategory);
        return `${newsApiUrl}?${params.toString()}`;
    };

    const loadNews = () => {
        fetch(buildNewsUrl(), { headers: { Accept: 'application/json' } })
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then((payload) => renderNews(toArray(payload).map(normalizeNews)))
            .catch(() => {});
    };

    const setActiveFilter = (value) => {
        activeCategory = value;
        filters?.querySelectorAll('.cat-pill').forEach((pill) => {
            pill.classList.toggle('active', pill.dataset.categoryId === value);
        });
        loadNews();
    };

    const attachFilterEvents = () => {
        filters?.querySelectorAll('.cat-pill').forEach((pill) => {
            pill.addEventListener('click', () => setActiveFilter(pill.dataset.categoryId || 'all'));
        });
    };

    const renderCategories = (categories) => {
        const validCategories = categories
            .map(normalizeCategory)
            .filter((item) => item.id && item.label);

        if (validCategories.length) {
            filters.innerHTML = `
                <button class="cat-pill active" type="button" data-category-id="all">
                    <i class="fas fa-tag" aria-hidden="true"></i><span>Semua</span>
                </button>
                ${validCategories.map((item) => `
                    <button class="cat-pill" type="button" data-category-id="${escapeHtml(item.id)}">
                        <i class="fas fa-tag" aria-hidden="true"></i><span>${escapeHtml(item.label)}</span>
                    </button>
                `).join('')}
            `;
        }

        attachFilterEvents();
    };

    fetch(categoryApiUrl, { headers: { Accept: 'application/json' } })
        .then((response) => response.ok ? response.json() : Promise.reject())
        .then((payload) => renderCategories(toArray(payload)))
        .catch(() => attachFilterEvents())
        .finally(loadNews);
}

function setupStudentServiceCards() {
    const serviceButtons = document.querySelectorAll('.service-card');
    serviceButtons.forEach((button) => {
        const label = button.querySelector('.service-label')?.textContent?.toLowerCase() || '';
        button.addEventListener('click', () => {
            if (label.includes('siakad')) {
                openExternal('https://siakad.unw.ac.id/');
                return;
            }
            if (label.includes('perpustakaan')) {
                openExternal('https://perpus.unw.ac.id/');
                return;
            }
            if (label.includes('sipolin')) {
                openExternal('https://sipolin.unw.ac.id/');
                return;
            }
            if (label.includes('login')) {
                openExternal('https://siakad.unw.ac.id/');
            }
        });
    });
}

function setupFooterAboutLinks() {
    const aboutUrl = document.querySelector('meta[name="pasca-about-url"]')?.getAttribute('content') || '/tentang';
    const visionUrl = document.querySelector('meta[name="pasca-vision-mission-url"]')?.getAttribute('content') || '/visi-misi';

    document.querySelectorAll('a[href="#"]').forEach((link) => {
        const text = stripHtml(link.textContent).toLowerCase();
        if (text.includes('tentang pascasarjana')) link.setAttribute('href', aboutUrl);
        if (text.includes('visi') || text.includes('misi')) link.setAttribute('href', visionUrl);
    });
}

function setupWhatsAppAdminModal() {
    const modal = document.getElementById('contactWhatsappModal');
    if (!modal) return;

    const dialog = modal.querySelector('.wa-modal-dialog');
    const closeButtons = modal.querySelectorAll('[data-wa-close]');
    const openButtons = document.querySelectorAll('[data-wa-modal-trigger], .whatsapp-float');
    let previousFocus = null;

    const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function openModal(event) {
        if (event) event.preventDefault();
        previousFocus = document.activeElement;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('wa-modal-open');
        window.requestAnimationFrame(() => {
            modal.querySelector(focusableSelector)?.focus();
        });
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('wa-modal-open');
        if (previousFocus && typeof previousFocus.focus === 'function') {
            previousFocus.focus();
        }
    }

    openButtons.forEach((button) => button.addEventListener('click', openModal));
    closeButtons.forEach((button) => button.addEventListener('click', closeModal));

    modal.addEventListener('click', (event) => {
        if (event.target === modal || event.target.dataset.waClose !== undefined) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('is-open')) return;

        if (event.key === 'Escape') {
            closeModal();
            return;
        }

        if (event.key !== 'Tab' || !dialog) return;

        const focusable = Array.from(dialog.querySelectorAll(focusableSelector))
            .filter((item) => !item.hasAttribute('disabled') && item.offsetParent !== null);

        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
}

function setupHeroSlider() {
    const hero = document.querySelector('.hero');
    const slides = Array.from(document.querySelectorAll('.hero-slide'));
    const dots = Array.from(document.querySelectorAll('.hero-dot'));
    if (!hero || slides.length <= 1 || dots.length <= 1) return;

    let index = 0;
    let timer = null;

    const activate = (nextIndex) => {
        index = nextIndex;
        slides.forEach((slide, slideIndex) => slide.classList.toggle('active', slideIndex === index));
        dots.forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === index));
    };

    const next = () => activate((index + 1) % slides.length);

    const start = () => {
        stop();
        const duration = Number(slides[index]?.dataset.duration || 3000);
        timer = window.setTimeout(() => {
            next();
            start();
        }, duration);
    };

    const stop = () => {
        if (timer) window.clearTimeout(timer);
    };

    dots.forEach((dot, dotIndex) => dot.addEventListener('click', () => {
        activate(dotIndex);
        start();
    }));

    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    start();
}
