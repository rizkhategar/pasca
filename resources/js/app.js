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
        if (!filters || !categories.length) {
            filters?.querySelectorAll('.cat-pill').forEach((pill, index) => {
                pill.dataset.categoryId = index === 0 ? 'all' : (pill.dataset.categoryId || pill.textContent.trim());
                if (!pill.querySelector('i')) pill.insertAdjacentHTML('afterbegin', '<i class="fas fa-tag"></i> ');
            });
            attachFilterEvents();
            return;
        }

        const options = [
            { id: 'all', label: 'Semua' },
            ...categories
                .map(normalizeCategory)
                .filter((category) => category.id && category.label)
                .filter((category, index, source) => source.findIndex((item) => item.id === category.id) === index),
        ];

        filters.innerHTML = options.map((category, index) => `
            <button class="cat-pill ${index === 0 ? 'active' : ''}" type="button" data-category-id="${escapeHtml(category.id)}">
                <i class="fas fa-tag"></i>
                <span>${escapeHtml(category.label)}</span>
            </button>
        `).join('');

        attachFilterEvents();
    };

    fetch(categoryApiUrl, { headers: { Accept: 'application/json' } })
        .then((response) => response.ok ? response.json() : Promise.reject())
        .then((payload) => renderCategories(toArray(payload)))
        .catch(() => renderCategories([]));

    if (pagination) pagination.style.display = 'none';
    loadNews();
}

function setupStudentServiceCards() {
    const serviceCards = Array.from(document.querySelectorAll('.service-grid .service-card'));
    let learningIndex = 0;

    serviceCards.forEach((card) => {
        const labelElement = card.querySelector('span');
        const rawLabel = (labelElement?.innerText || card.innerText || '').replace(/\s+/g, ' ').trim().toLowerCase();
        let url = null;
        let label = null;

        if (rawLabel.includes('e-learning') || rawLabel.includes('siakad') || rawLabel.includes('sipolin')) {
            learningIndex += 1;
            if (rawLabel.includes('siakad') || learningIndex === 1) {
                label = 'SIAKAD';
                url = 'https://siakad.unw.ac.id';
            } else {
                label = 'SIPOLIN';
                url = 'https://sipolin.unw.ac.id';
            }
        }

        if (rawLabel.includes('perpustakaan')) {
            label = 'PERPUSTAKAAN<br>DIGITAL';
            url = 'https://play.google.com/store/apps/details?id=id.kubuku.kbk50635ea&hl=id&pli=1';
        }

        if (!url) return;
        if (labelElement && label) labelElement.innerHTML = label;

        card.setAttribute('role', 'link');
        card.setAttribute('tabindex', '0');
        card.setAttribute('aria-label', `Buka ${labelElement?.innerText || label || rawLabel}`);
        card.addEventListener('click', (event) => {
            event.preventDefault();
            openExternal(url);
        });
        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            openExternal(url);
        });
    });
}

function setupFooterAboutLinks() {
    const quickLinks = document.querySelector('.footer .footer-links');
    if (!quickLinks) return;

    const aboutUrl = document.querySelector('meta[name="pasca-about-url"]')?.getAttribute('content') || '/tentang-pascasarjana';
    const visionUrl = document.querySelector('meta[name="pasca-vision-mission-url"]')?.getAttribute('content') || '/visi-misi';
    const footerHeading = quickLinks.closest('.footer-column')?.querySelector('h3');

    if (footerHeading) footerHeading.textContent = 'TENTANG UNW';

    quickLinks.innerHTML = `
        <li><a href="${aboutUrl}">Pascasarjana</a></li>
        <li><a href="${visionUrl}">Visi dan Misi</a></li>
        <li><a href="https://www.youtube.com/@UNWTV" target="_blank" rel="noopener noreferrer">Video Profil</a></li>
    `;
}

function setupWhatsAppAdminModal() {
    const modal = document.getElementById('waAdminModal');
    if (!modal) return;

    const primaryAdminJson = modal.querySelector('[data-primary-admin-json]')?.textContent;
    let primaryAdmin = null;

    try {
        primaryAdmin = primaryAdminJson ? JSON.parse(primaryAdminJson) : null;
    } catch (error) {
        primaryAdmin = null;
    }

    const openModal = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('wa-modal-open');
        modal.querySelector('.wa-admin-modal__close')?.focus();
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('wa-modal-open');
    };

    document.querySelectorAll('[data-wa-modal-close]').forEach((button) => button.addEventListener('click', closeModal));

    document.querySelectorAll('.contact-card').forEach((card) => {
        if (!card.querySelector('.fa-whatsapp')) return;

        card.classList.add('whatsapp-contact-card');
        card.setAttribute('role', 'button');
        card.setAttribute('tabindex', '0');
        card.setAttribute('aria-label', 'Pilih Admin WhatsApp');

        const title = card.querySelector('.contact-info h2');
        const number = card.querySelector('.contact-info a, .contact-info p');

        if (title) title.textContent = 'Admin WhatsApp';
        if (number && primaryAdmin) {
            number.innerHTML = `${primaryAdmin.number} <i class="fas fa-chevron-right"></i>`;
            number.setAttribute('href', '#waAdminModal');
        }

        card.addEventListener('click', (event) => {
            event.preventDefault();
            openModal();
        });
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openModal();
            }
        });
    });

    const floatingButton = document.querySelector('.wa-floating');
    if (floatingButton) {
        floatingButton.setAttribute('href', '#waAdminModal');
        floatingButton.addEventListener('click', (event) => {
            event.preventDefault();
            openModal();
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
}

function setupHeroSlider() {
    const sliderDataElement = document.getElementById('pascaHeroSlidersData');
    if (!sliderDataElement) return;

    let items = [];
    try {
        items = JSON.parse(sliderDataElement.dataset.sliders || '[]');
    } catch (error) {
        items = [];
    }

    if (!items.length) return;

    const hero = document.querySelector('.hero');
    const oldPrev = document.getElementById('prevSlide');
    const oldNext = document.getElementById('nextSlide');
    const dotsWrapper = document.getElementById('heroDots');
    const titleEl = document.querySelector('.hero-title');
    const subtitleEl = document.querySelector('.hero-subtitle');

    if (!hero || !dotsWrapper || !oldPrev || !oldNext) return;

    const prev = oldPrev.cloneNode(true);
    const next = oldNext.cloneNode(true);

    oldPrev.replaceWith(prev);
    oldNext.replaceWith(next);
    hero.querySelectorAll('.hero-slide, .hero-slider-track').forEach((element) => element.remove());
    dotsWrapper.innerHTML = '';

    const track = document.createElement('div');
    track.className = 'hero-slider-track no-transition';

    const createSlide = (item) => {
        const slide = document.createElement('div');
        slide.className = 'hero-slide';
        slide.style.backgroundImage = `url("${item.image}")`;
        return slide;
    };

    track.appendChild(createSlide(items[items.length - 1]));
    items.forEach((item, index) => {
        track.appendChild(createSlide(item));
        const dot = document.createElement('button');
        dot.className = index === 0 ? 'hero-dot active' : 'hero-dot';
        dot.type = 'button';
        dot.setAttribute('aria-label', `Slider ${index + 1}`);
        dotsWrapper.appendChild(dot);
    });
    track.appendChild(createSlide(items[0]));
    hero.insertBefore(track, prev);

    const dots = Array.from(dotsWrapper.querySelectorAll('.hero-dot'));
    let trackIndex = 1;
    let realIndex = 0;
    let pendingIndex = null;
    let isMoving = false;
    let timer = null;

    const applyTransform = () => {
        track.style.transform = `translateX(-${trackIndex * 100}%)`;
    };
    const setDots = (index) => dots.forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === index));
    const setText = (index) => {
        const data = items[index] || items[0];
        if (titleEl) titleEl.innerHTML = String(data.title || '').replace(/\n/g, '<br>');
        if (subtitleEl) subtitleEl.textContent = data.subtitle || '';
    };
    const safeDuration = (index) => Math.min(30000, Math.max(1400, Number(items[index]?.duration || 3000)));

    const normalizePositionAfterClone = () => {
        if (trackIndex === 0) {
            track.classList.add('no-transition');
            trackIndex = items.length;
            applyTransform();
            track.offsetHeight;
            track.classList.remove('no-transition');
        }

        if (trackIndex === items.length + 1) {
            track.classList.add('no-transition');
            trackIndex = 1;
            applyTransform();
            track.offsetHeight;
            track.classList.remove('no-transition');
        }
    };

    const scheduleNext = () => {
        clearTimeout(timer);
        if (items.length <= 1) return;
        timer = setTimeout(() => move(1), safeDuration(realIndex));
    };

    function move(direction, targetIndex = null) {
        if (isMoving || items.length <= 1) return;
        isMoving = true;
        clearTimeout(timer);
        pendingIndex = targetIndex === null ? (realIndex + direction + items.length) % items.length : targetIndex;
        trackIndex = targetIndex === null ? trackIndex + direction : targetIndex + 1;
        applyTransform();
    }

    track.addEventListener('transitionend', (event) => {
        if (event.propertyName !== 'transform') return;
        if (pendingIndex !== null) {
            realIndex = pendingIndex;
            pendingIndex = null;
            setText(realIndex);
            setDots(realIndex);
        }
        normalizePositionAfterClone();
        isMoving = false;
        scheduleNext();
    });

    prev.addEventListener('click', (event) => {
        event.preventDefault();
        move(-1);
    });
    next.addEventListener('click', (event) => {
        event.preventDefault();
        move(1);
    });
    dots.forEach((dot, index) => dot.addEventListener('click', () => {
        if (isMoving || index === realIndex) return;
        const direction = index > realIndex ? 1 : -1;
        move(direction, index);
    }));

    setText(0);
    setDots(0);
    applyTransform();
    requestAnimationFrame(() => track.classList.remove('no-transition'));
    scheduleNext();
}
