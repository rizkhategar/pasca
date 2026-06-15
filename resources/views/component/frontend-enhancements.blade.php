<style>
    .hero {
        background: #031a38 !important;
        overflow: hidden !important;
    }

    .hero .container,
    .hero-content,
    .hero-text {
        position: relative !important;
        z-index: 50 !important;
    }

    .hero-title,
    .hero-subtitle,
    .hero .btn-primary {
        position: relative !important;
        z-index: 60 !important;
    }

    .hero .btn-primary {
        pointer-events: auto !important;
    }

    .hero .hero-slider-track {
        position: absolute !important;
        inset: 0 !important;
        display: flex !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 1 !important;
        transform: translateX(-100%);
        transition: transform .78s cubic-bezier(.76, 0, .24, 1) !important;
        will-change: transform;
    }

    .hero .hero-slider-track.no-transition {
        transition: none !important;
    }

    .hero .hero-slider-track .hero-slide {
        position: relative !important;
        inset: auto !important;
        flex: 0 0 100% !important;
        width: 100% !important;
        height: 100% !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        filter: brightness(.86) !important;
    }

    .hero .hero-slider-track .hero-slide::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(3, 20, 46, .82), rgba(7, 43, 87, .62), rgba(3, 20, 46, .86)) !important;
        pointer-events: none;
    }

    .program-section .program-card,
    .program-section .program-detail {
        cursor: pointer;
    }

    .program-grid {
        display: grid;
    }

    .program-grid .program-card:nth-child(4) { order: 1; }
    .program-grid .program-card:nth-child(3) { order: 2; }
    .program-grid .program-card:nth-child(2) { order: 3; }
    .program-grid .program-card:nth-child(1) { order: 4; }

    .news-area { min-width: 0; }
    .section-title i,
    .news-filter-btn i,
    .news-category i,
    .news-date i { color: var(--yellow); }
    .section-title i { margin-right: 9px; }
    .news-more-wrap { display: flex; justify-content: flex-end; margin-top: 18px; }
    .news-more-link { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 9px; background: var(--primary); color: #fff !important; text-decoration: none; font-size: 12px; font-weight: 900; text-transform: uppercase; white-space: nowrap; box-shadow: 0 10px 22px rgba(7, 43, 87, .16); }
    .news-more-link:hover { background: var(--yellow); }
    .news-filter-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0 0 18px; padding: 12px; background: #fff; border: 1px solid rgba(7, 43, 87, .1); border-radius: 14px; box-shadow: 0 10px 28px rgba(15, 23, 42, .05); }
    .news-filter-btn { border: 1px solid rgba(7, 43, 87, .16); background: #f8fafc; color: var(--primary); border-radius: 8px; padding: 8px 13px; font-size: 12px; font-weight: 800; cursor: pointer; transition: .2s ease; white-space: nowrap; display: inline-flex; align-items: center; gap: 7px; }
    .news-filter-btn:hover,
    .news-filter-btn.active { background: var(--yellow); border-color: var(--yellow); color: #fff; box-shadow: 0 8px 18px rgba(247, 181, 0, .25); }
    .news-filter-btn:hover i,
    .news-filter-btn.active i { color: #fff; }
    .news-state { padding: 22px; border-radius: 14px; background: #fff; border: 1px solid rgba(7, 43, 87, .12); font-size: 14px; line-height: 1.6; color: #64748b; }
    .news-loading { min-height: 180px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid rgba(7, 43, 87, .1); border-radius: 18px; }
    .news-loader { width: 42px; height: 42px; border: 4px solid #e5e7eb; border-top-color: var(--yellow); border-radius: 50%; animation: newsSpin .8s linear infinite; }
    @keyframes newsSpin { to { transform: rotate(360deg); } }
    .news-list { display: grid; gap: 14px; }
    .news-item { border: 0 !important; padding: 0 !important; width: 100%; overflow: hidden; }
    .news-item-link { display: grid !important; grid-template-columns: 150px minmax(0, 1fr) !important; align-items: stretch; gap: 16px; width: 100%; max-width: 100%; color: inherit; text-decoration: none; padding: 14px; background: #fff; border: 1px solid rgba(7, 43, 87, .1); border-radius: 16px; box-shadow: 0 12px 32px rgba(15, 23, 42, .06); transition: .24s ease; overflow: hidden; }
    .news-item-link:hover { transform: translateY(-2px); box-shadow: 0 18px 40px rgba(15, 23, 42, .1); border-color: rgba(247, 181, 0, .5); }
    .news-item-link:hover .news-title { color: var(--yellow); }
    .news-thumb { width: 150px !important; min-width: 150px !important; max-width: 150px !important; height: 122px !important; overflow: hidden; background: #eef2f7; border-radius: 12px; display: flex; align-items: center; justify-content: center; grid-column: 1; }
    .news-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; border-radius: inherit; transition: .35s ease; }
    .news-item-link:hover .news-thumb img { transform: scale(1.05); }
    .news-content { grid-column: 2; min-width: 0 !important; max-width: 100%; overflow: hidden; display: flex; flex-direction: column; justify-content: center; }
    .news-category { min-width: 0; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-flex; align-items: center; gap: 6px; }
    .news-title { display: -webkit-box !important; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: normal; overflow-wrap: anywhere; margin: 7px 0 0; line-height: 1.35; max-width: 100%; }
    .news-excerpt { display: -webkit-box !important; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 8px; font-size: 13px; line-height: 1.55; color: #64748b; word-break: normal; overflow-wrap: anywhere; max-width: 100%; }
    .news-date { margin-top: 10px; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-flex; align-items: center; gap: 7px; }
    .pagination#apiNewsPagination { display: none !important; }

    @media(max-width: 992px) {
        .news-item-link { grid-template-columns: 145px minmax(0, 1fr) !important; }
        .news-thumb { width: 145px !important; min-width: 145px !important; max-width: 145px !important; height: 118px !important; }
    }

    @media(max-width: 768px) {
        .hero { min-height: 360px !important; }
        .hero .hero-content { min-height: 360px !important; align-items: center !important; padding-top: 24px !important; padding-bottom: 44px !important; }
        .hero .hero-text { margin-left: 0 !important; padding: 42px 44px 58px !important; max-width: 100% !important; }
        .hero .hero-title { font-size: 24px !important; line-height: 1.35 !important; margin-bottom: 14px !important; letter-spacing: .2px !important; }
        .hero .hero-subtitle { font-size: 15px !important; line-height: 1.65 !important; margin-bottom: 24px !important; }
        .hero .btn-primary { margin-top: 4px !important; }
        .program-section { padding: 24px 0 !important; }
        .program-grid { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px !important; overflow: visible !important; }
        .program-card { min-width: 0 !important; min-height: 118px !important; padding: 14px 8px !important; }
        .program-title { font-size: 12px !important; line-height: 1.18 !important; }
        .program-detail { font-size: 11px !important; }
        .program-icon { height: 34px !important; margin-bottom: 9px !important; }
        .program-icon svg { width: 38px !important; height: 38px !important; }
        .info-section { padding: 22px 0 42px !important; }
        .info-layout { grid-template-columns: 1fr !important; gap: 28px !important; }
        .news-area { padding-right: 0 !important; border-right: 0 !important; width: 100% !important; max-width: 100% !important; overflow: hidden !important; }
        .section-header:has(#apiNewsPagination) { display: block !important; padding-bottom: 14px !important; }
        .section-title { font-size: 18px !important; }
        .news-more-link { height: 36px; padding: 0 13px; font-size: 11px; }
        .news-filter-bar { overflow-x: auto; flex-wrap: nowrap; scrollbar-width: none; }
        .news-filter-bar::-webkit-scrollbar { display: none; }
        .news-filter-btn { flex: 0 0 auto; }
        .news-item-link { grid-template-columns: 112px minmax(0, 1fr) !important; gap: 10px !important; padding: 10px !important; }
        .news-thumb { width: 112px !important; min-width: 112px !important; max-width: 112px !important; height: 92px !important; }
        .news-title { font-size: 13px !important; -webkit-line-clamp: 2 !important; }
        .news-excerpt { display: none !important; }
        .news-category,
        .news-date { font-size: 10px !important; }
    }

    @media(max-width: 420px) {
        .hero { min-height: 385px !important; }
        .hero .hero-content { min-height: 385px !important; padding-top: 30px !important; padding-bottom: 58px !important; }
        .hero .hero-text { padding: 46px 38px 68px !important; }
        .hero .hero-title { font-size: 22px !important; }
        .hero .hero-subtitle { font-size: 14px !important; margin-bottom: 24px !important; }
        .program-grid { gap: 10px !important; }
        .program-card { min-height: 112px !important; }
        .section-title { font-size: 16px !important; }
    }
</style>

@if (isset($sliders) && $sliders->count() > 0)
    @php
        $sliderItems = $sliders
            ->map(fn($slider) => [
                'title' => $slider->title,
                'subtitle' => $slider->subtitle,
                'image' => route('sliders.image', $slider) . '?v=' . optional($slider->updated_at)->timestamp,
                'duration' => (int) ($slider->duration_ms ?? 3000),
            ])
            ->values();
    @endphp
    <script>
        (function() {
            const items = @json($sliderItems);
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
            hero.querySelectorAll('.hero-slide, .hero-slider-track').forEach(element => element.remove());
            dotsWrapper.innerHTML = '';

            const track = document.createElement('div');
            track.className = 'hero-slider-track no-transition';

            function createSlide(item) {
                const slide = document.createElement('div');
                slide.className = 'hero-slide';
                slide.style.backgroundImage = `url("${item.image}")`;
                return slide;
            }

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

            function applyTransform() {
                track.style.transform = `translateX(-${trackIndex * 100}%)`;
            }

            function setDots(index) {
                dots.forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === index));
            }

            function setText(index) {
                const data = items[index] || items[0];
                if (titleEl) titleEl.innerHTML = String(data.title || '').replace(/\n/g, '<br>');
                if (subtitleEl) subtitleEl.textContent = data.subtitle || '';
            }

            function safeDuration(index) {
                return Math.min(30000, Math.max(1400, Number(items[index]?.duration || 3000)));
            }

            function normalizePositionAfterClone() {
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
            }

            function scheduleNext() {
                clearTimeout(timer);
                if (items.length <= 1) return;
                timer = setTimeout(() => move(1), safeDuration(realIndex));
            }

            function move(direction, targetIndex = null) {
                if (isMoving || items.length <= 1) return;
                isMoving = true;
                clearTimeout(timer);

                pendingIndex = targetIndex === null
                    ? (realIndex + direction + items.length) % items.length
                    : targetIndex;
                trackIndex = targetIndex === null ? trackIndex + direction : targetIndex + 1;
                applyTransform();
            }

            track.addEventListener('transitionend', function(event) {
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

            prev.addEventListener('click', function(event) {
                event.preventDefault();
                move(-1);
            });

            next.addEventListener('click', function(event) {
                event.preventDefault();
                move(1);
            });

            dots.forEach((dot, index) => dot.addEventListener('click', function() {
                if (isMoving || index === realIndex) return;
                const direction = index > realIndex ? 1 : -1;
                move(direction, index);
            }));

            setText(0);
            setDots(0);
            applyTransform();
            requestAnimationFrame(() => track.classList.remove('no-transition'));
            scheduleNext();
        })();
    </script>
@endif

<script>
    (function() {
        const API_ORIGIN = 'https://panel-web.unw.ac.id';
        const API = { news: API_ORIGIN + '/api/news', category: API_ORIGIN + '/api/category' };
        const newsIndexUrl = @json(route('news.index'));

        function esc(value) { return String(value ?? '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char])); }
        function stripHtml(value) { return String(value ?? '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim(); }
        function toArray(payload) { if (Array.isArray(payload)) return payload; if (Array.isArray(payload?.data)) return payload.data; if (Array.isArray(payload?.data?.data)) return payload.data.data; return []; }
        function absUrl(url) { if (!url) return ''; const value = String(url); if (/^https?:\/\//i.test(value)) return value; if (value.startsWith('//')) return 'https:' + value; if (value.startsWith('/')) return API_ORIGIN + value; return API_ORIGIN + '/' + value.replace(/^\/+/, ''); }
        function formatDate(value) { if (!value) return ''; const date = new Date(value); if (Number.isNaN(date.getTime())) return String(value); return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }); }
        async function getJson(url) { const response = await fetch(url, { headers: { Accept: 'application/json' } }); if (!response.ok) throw new Error('API gagal dimuat'); return response.json(); }
        function normalizeCategory(item) { return { id: String(item?.id ?? ''), slug: String(item?.slug ?? ''), name: String(item?.name ?? '').trim() }; }
        function normalizeNews(item) { const category = item?.category || {}; return { title: String(item?.title ?? 'Tanpa Judul'), slug: String(item?.slug ?? ''), image: String(item?.image_thumbnail || item?.image || ''), excerpt: String(item?.excerpt ?? ''), date: String(item?.publishedAt || item?.published_at || item?.createdAt || item?.created_at || ''), categoryId: String(category?.id ?? item?.category_id ?? ''), categorySlug: String(category?.slug ?? ''), categoryName: String(category?.name ?? 'Artikel').trim() }; }
        function newsDetailUrl(news) { return news.slug ? '/berita/' + encodeURIComponent(news.slug) : '#'; }
        function buildNewsApiUrl(category) { const params = new URLSearchParams(); params.set('paginate', '3'); params.set('page', '1'); if (category && category.id !== 'all') { params.set('category_id', category.id); params.set('category', category.slug || category.id); } return API.news + '?' + params.toString(); }
        function renderFilters(section, state) { const target = section.querySelector('#apiCategoryFilter'); if (!target) return; let html = '<button class="news-filter-btn active" type="button" data-id="all" data-slug="all"><i class="fas fa-tag"></i>Semua</button>'; html += state.categories.map(category => '<button class="news-filter-btn" type="button" data-id="' + esc(category.id) + '" data-slug="' + esc(category.slug) + '"><i class="fas fa-tag"></i>' + esc(category.name) + '</button>').join(''); target.innerHTML = html; target.querySelectorAll('.news-filter-btn').forEach(button => button.addEventListener('click', function() { target.querySelectorAll('.news-filter-btn').forEach(item => item.classList.remove('active')); button.classList.add('active'); state.category = { id: button.dataset.id, slug: button.dataset.slug }; loadNews(section, state); })); }
        function renderNews(section, items) { const target = section.querySelector('#apiNewsList'); if (!target) return; if (!items.length) { target.innerHTML = '<div class="news-state">Belum ada berita/artikel pada kategori ini.</div>'; return; } target.innerHTML = items.map(news => { const image = news.image ? '<img src="' + esc(absUrl(news.image)) + '" alt="' + esc(news.title) + '" loading="lazy">' : '<svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2ZM8.5 11.5l2.5 3.01L14.5 10l4.5 6H5l3.5-4.5Z"/></svg>'; const excerpt = stripHtml(news.excerpt); return '<article class="news-item"><a class="news-item-link" href="' + esc(newsDetailUrl(news)) + '"><div class="news-thumb">' + image + '</div><div class="news-content"><div class="news-category"><i class="fas fa-tag"></i>' + esc(news.categoryName) + '</div><h3 class="news-title">' + esc(news.title) + '</h3>' + (excerpt ? '<p class="news-excerpt">' + esc(excerpt.slice(0, 155)) + '</p>' : '') + '<div class="news-date"><i class="fas fa-calendar-alt"></i>' + esc(formatDate(news.date)) + '</div></div></a></article>'; }).join(''); }
        async function loadNews(section, state) { const target = section.querySelector('#apiNewsList'); if (target) target.innerHTML = '<div class="news-loading"><div class="news-loader"></div></div>'; try { const category = state.category?.id === 'all' ? null : state.category; const payload = await getJson(buildNewsApiUrl(category)); renderNews(section, toArray(payload).map(normalizeNews)); } catch (error) { if (target) target.innerHTML = '<div class="news-state">Berita belum dapat dimuat dari API. Periksa koneksi internet atau izin CORS API.</div>'; } }
        async function initInfoSectionApi() { const section = document.getElementById('layanan-mahasiswa'); if (!section) return; const newsArea = section.querySelector('.news-area'); if (!newsArea) return; newsArea.innerHTML = '<div class="section-header"><h2 class="section-title"><i class="fas fa-calendar-alt"></i>Berita Terkini & Agenda</h2><div class="pagination" id="apiNewsPagination"></div></div><div class="news-filter-bar" id="apiCategoryFilter"></div><div class="news-list" id="apiNewsList"><div class="news-loading"><div class="news-loader"></div></div></div><div class="news-more-wrap"><a href="' + newsIndexUrl + '" class="news-more-link"><span>Selengkapnya</span><i class="fas fa-arrow-right"></i></a></div>'; const state = { category: { id: 'all', slug: 'all' }, categories: [] }; try { const categoryPayload = await getJson(API.category); state.categories = toArray(categoryPayload).map(normalizeCategory).filter(category => category.id && category.name); renderFilters(section, state); } catch (error) { renderFilters(section, state); } loadNews(section, state); }
        function bindProgramDetailLinks() { const routes = ["{{ route('akademik.show', 's2-hukum') }}", "{{ route('akademik.show', 's2-manajemen-pendidikan') }}", "{{ route('akademik.show', 's2-kesehatan-masyarakat') }}", "{{ route('akademik.show', 's2-keperawatan') }}"]; document.querySelectorAll('.program-section .program-card').forEach((card, index) => { if (routes[index]) card.setAttribute('href', routes[index]); const detailText = card.querySelector('.program-detail'); if (detailText && routes[index]) { detailText.dataset.href = routes[index]; detailText.setAttribute('role', 'link'); detailText.setAttribute('tabindex', '0'); } }); }
        function init() { bindProgramDetailLinks(); initInfoSectionApi(); }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    })();
</script>
