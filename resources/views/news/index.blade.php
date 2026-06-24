@extends('layouts.app')

@section('title', 'Berita - Pascasarjana UNW')
@section('body_class', 'news-page')

@section('content')
    <section class="news-hero">
        <div class="hero-dots"></div>
        <div class="hero-line"></div>

        <div class="container">
            <div class="hero-inner">
                <div class="hero-kicker">
                    <i class="fas fa-newspaper"></i>
                    <span>Berita Pascasarjana</span>
                </div>

                <h1 class="page-title">Berita Terkini & Agenda</h1>

                <p class="page-desc">
                    Kumpulan berita, agenda, pengumuman, dan informasi terbaru Pascasarjana Universitas Ngudi Waluyo.
                </p>

                <div class="hero-meta">
                    <span><i class="fas fa-bullhorn"></i>Informasi Resmi</span>
                    <span><i class="fas fa-university"></i>Universitas Ngudi Waluyo</span>
                </div>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <main class="news-section">
        <div class="container">
            <section class="news-panel">
                <div class="news-toolbar">
                    <div class="filter-wrap">
                        <div class="compact-dropdown category-filter" id="categoryDropdown">
                            <button class="dropdown-trigger" type="button" id="categoryDropdownButton" aria-label="Pilih Kategori" aria-expanded="false">
                                <i class="fas fa-layer-group left-icon"></i>
                                <span class="selected-text" id="categorySelectedText">Memuat...</span>
                                <i class="fas fa-chevron-down chevron-icon"></i>
                            </button>
                            <div class="dropdown-menu" id="categoryDropdownMenu"></div>
                        </div>

                        <div class="compact-dropdown sort-filter" id="sortDropdown">
                            <button class="dropdown-trigger" type="button" id="sortDropdownButton" aria-label="Urutkan Berita" aria-expanded="false">
                                <i class="fas fa-arrow-down-wide-short left-icon"></i>
                                <span class="selected-text" id="sortSelectedText">Terbaru</span>
                                <i class="fas fa-chevron-down chevron-icon"></i>
                            </button>
                            <div class="dropdown-menu" id="sortDropdownMenu">
                                <button type="button" class="dropdown-option active" data-value="desc" data-label="Terbaru"><i class="fas fa-check"></i><span>Terbaru</span></button>
                                <button type="button" class="dropdown-option" data-value="asc" data-label="Terlama"><i class="fas fa-check"></i><span>Terlama</span></button>
                            </div>
                        </div>
                    </div>

                    <div class="news-search-wrap">
                        <input class="search-box" id="newsSearch" type="search" placeholder="Cari berita...">
                        <button class="search-icon-btn" id="newsSearchButton" type="button" title="Cari Berita" aria-label="Cari berita">
                            <i class="fas fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>

                <div class="news-content">
                    <div class="news-page-grid" id="newsGrid">
                        <div class="loading"><div class="loader"></div><span>Memuat berita...</span></div>
                    </div>
                    <div class="pagination" id="newsPagination"></div>
                </div>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        (function() {
            const API_ORIGIN = 'https://panel-web.unw.ac.id';
            const API = { search: @json(route('news.search')), category: API_ORIGIN + '/api/category' };
            const PAGE_SIZE = 9;
            const state = { page: 1, lastPage: 1, category: 'all', sort: 'desc', q: '' };
            let searchTimer = null;
            let activeRequestId = 0;

            const grid = document.getElementById('newsGrid');
            const pagination = document.getElementById('newsPagination');
            const search = document.getElementById('newsSearch');
            const searchButton = document.getElementById('newsSearchButton');
            const categoryDropdown = document.getElementById('categoryDropdown');
            const categoryButton = document.getElementById('categoryDropdownButton');
            const categoryMenu = document.getElementById('categoryDropdownMenu');
            const categoryText = document.getElementById('categorySelectedText');
            const sortDropdown = document.getElementById('sortDropdown');
            const sortButton = document.getElementById('sortDropdownButton');
            const sortMenu = document.getElementById('sortDropdownMenu');
            const sortText = document.getElementById('sortSelectedText');

            function esc(value) {
                return String(value ?? '').replace(/[&<>'"]/g, function(char) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char];
                });
            }

            function strip(value) { return String(value ?? '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim(); }
            function arr(payload) { if (Array.isArray(payload)) return payload; if (Array.isArray(payload?.data)) return payload.data; if (Array.isArray(payload?.data?.data)) return payload.data.data; return []; }
            function date(value) { if (!value) return ''; const d = new Date(value); if (Number.isNaN(d.getTime())) return String(value); return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }); }
            function img(url) { if (!url) return ''; url = String(url); if (/^https?:\/\//i.test(url)) return url; if (url.startsWith('/')) return API_ORIGIN + url; return API_ORIGIN + '/' + url.replace(/^\/+/, ''); }

            async function get(url) {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('failed');
                return response.json();
            }

            function closeDropdowns(except = null) {
                [categoryDropdown, sortDropdown].forEach(function(dropdown) {
                    if (!dropdown) return;
                    if (dropdown !== except) {
                        dropdown.classList.remove('open');
                        dropdown.querySelector('.dropdown-trigger')?.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            function openDropdown(dropdown) {
                if (!dropdown) return;
                closeDropdowns(dropdown);
                dropdown.classList.add('open');
                dropdown.querySelector('.dropdown-trigger')?.setAttribute('aria-expanded', 'true');
            }

            function closeDropdown(dropdown) {
                if (!dropdown) return;
                dropdown.classList.remove('open');
                dropdown.querySelector('.dropdown-trigger')?.setAttribute('aria-expanded', 'false');
            }

            function toggleDropdown(dropdown) { dropdown?.classList.contains('open') ? closeDropdown(dropdown) : openDropdown(dropdown); }
            function setActiveOption(menu, value) { menu?.querySelectorAll('.dropdown-option').forEach(function(option) { option.classList.toggle('active', String(option.dataset.value) === String(value)); }); }

            function setupDropdownButton(button, dropdown) {
                if (!button || !dropdown) return;
                button.addEventListener('pointerdown', function(event) { event.preventDefault(); event.stopPropagation(); toggleDropdown(dropdown); });
                button.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleDropdown(dropdown); }
                    if (event.key === 'Escape') closeDropdown(dropdown);
                });
            }

            function setupDropdownArea(dropdown) {
                if (!dropdown) return;
                dropdown.addEventListener('pointerdown', function(event) { event.stopPropagation(); });
                dropdown.addEventListener('click', function(event) { event.stopPropagation(); });
            }

            setupDropdownButton(categoryButton, categoryDropdown);
            setupDropdownButton(sortButton, sortDropdown);
            setupDropdownArea(categoryDropdown);
            setupDropdownArea(sortDropdown);
            document.addEventListener('pointerdown', function(event) { if (!event.target.closest('.compact-dropdown')) closeDropdowns(); });
            document.addEventListener('keydown', function(event) { if (event.key === 'Escape') closeDropdowns(); });

            categoryMenu?.addEventListener('pointerdown', function(event) {
                const option = event.target.closest('.dropdown-option');
                if (!option) return;
                event.preventDefault(); event.stopPropagation();
                state.category = option.dataset.value || 'all'; state.page = 1;
                categoryText.textContent = option.dataset.label || option.textContent.trim() || 'Semua';
                setActiveOption(categoryMenu, state.category); closeDropdown(categoryDropdown); load();
            });

            sortMenu?.addEventListener('pointerdown', function(event) {
                const option = event.target.closest('.dropdown-option');
                if (!option) return;
                event.preventDefault(); event.stopPropagation();
                state.sort = option.dataset.value || 'desc'; state.page = 1;
                sortText.textContent = option.dataset.label || option.textContent.trim() || 'Terbaru';
                setActiveOption(sortMenu, state.sort); closeDropdown(sortDropdown); load();
            });

            function normalize(item) {
                const category = item?.category || {};
                return { title: String(item?.title ?? 'Tanpa Judul'), slug: String(item?.slug ?? ''), image: String(item?.image_thumbnail || item?.image || ''), excerpt: String(item?.excerpt ?? item?.body ?? ''), date: String(item?.publishedAt || item?.published_at || item?.createdAt || item?.created_at || ''), categoryId: String(category?.id ?? item?.category_id ?? ''), categorySlug: String(category?.slug ?? ''), categoryName: String(category?.name ?? 'Artikel') };
            }

            function buildUrl(page) {
                const params = new URLSearchParams({ paginate: String(PAGE_SIZE), page: String(page), sort: state.sort });
                if (state.q.trim() !== '') params.set('q', state.q.trim());
                if (state.category !== 'all') params.set('category_id', state.category);
                return API.search + '?' + params.toString();
            }

            function render(items) {
                if (!items.length) { grid.innerHTML = `<div class="empty"><i class="fas fa-magnifying-glass"></i><strong>Berita tidak ditemukan</strong><span>Belum ada berita yang sesuai dengan pencarian Anda.</span></div>`; return; }
                grid.innerHTML = items.map(function(news) {
                    const title = esc(news.title);
                    const url = '/berita/' + encodeURIComponent(news.slug);
                    const imageUrl = img(news.image);
                    const excerpt = esc(strip(news.excerpt));
                    const categoryName = esc(news.categoryName);
                    const newsDate = esc(date(news.date));
                    const imageHtml = imageUrl ? `<div class="news-page-thumb"><img src="${esc(imageUrl)}" alt="${title}"><div class="news-page-category"><i class="fas fa-tag"></i>${categoryName}</div></div>` : `<div class="news-page-thumb no-image"><i class="fas fa-newspaper"></i><div class="news-page-category"><i class="fas fa-tag"></i>${categoryName}</div></div>`;
                    return `<a class="news-page-card" href="${url}">${imageHtml}<div class="news-page-body"><h2 class="news-page-title">${title}</h2><p class="news-page-excerpt">${excerpt}</p><div class="news-page-footer"><div class="news-page-date"><i class="fas fa-calendar-alt"></i>${newsDate || 'Tanggal belum tersedia'}</div><div class="read-more">Baca <i class="fas fa-arrow-right"></i></div></div></div></a>`;
                }).join('');
            }

            function renderPages() {
                const last = Math.max(1, state.lastPage);
                const current = Math.min(state.page, last);
                if (last <= 1) { pagination.innerHTML = ''; return; }
                const visiblePages = window.innerWidth <= 480 ? 3 : 5;
                let start = Math.max(1, current - Math.floor(visiblePages / 2));
                let end = Math.min(last, start + visiblePages - 1);
                if (end - start < visiblePages - 1) start = Math.max(1, end - visiblePages + 1);
                let html = `<button class="page-btn" data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''}>‹</button>`;
                for (let page = start; page <= end; page++) html += `<button class="page-btn ${page === current ? 'active' : ''}" data-page="${page}">${page}</button>`;
                if (end < last) html += `<span class="page-dots">...</span><button class="page-btn" data-page="${last}">${last}</button>`;
                html += `<button class="page-btn" data-page="${current + 1}" ${current >= last ? 'disabled' : ''}>›</button><div class="page-jump"><input type="number" min="1" max="${last}" value="${current}" aria-label="Pilih halaman"><button type="button" aria-label="Cari halaman"><i class="fas fa-magnifying-glass"></i></button></div>`;
                pagination.innerHTML = html;
                pagination.querySelectorAll('[data-page]').forEach(function(button) { button.onclick = function() { const page = Number(button.dataset.page); if (page >= 1 && page <= last && page !== current) { state.page = page; load(); } }; });
                const input = pagination.querySelector('.page-jump input');
                const button = pagination.querySelector('.page-jump button');
                function jump() { const page = Number(input.value); if (page >= 1 && page <= last && page !== current) { state.page = page; load(); } }
                button?.addEventListener('click', jump);
                input?.addEventListener('keydown', function(event) { if (event.key === 'Enter') jump(); });
            }

            async function load() {
                const requestId = ++activeRequestId;
                grid.innerHTML = `<div class="loading"><div class="loader"></div><span>Memuat berita...</span></div>`;
                try {
                    const payload = await get(buildUrl(state.page));
                    if (requestId !== activeRequestId) return;
                    state.lastPage = Number(payload?.meta?.last_page || 1);
                    state.page = Number(payload?.meta?.current_page || state.page);
                    render(arr(payload).map(normalize)); renderPages();
                } catch (error) {
                    if (requestId === activeRequestId) { grid.innerHTML = `<div class="empty"><i class="fas fa-triangle-exclamation"></i><strong>Berita belum dapat dimuat</strong><span>Silakan coba muat ulang halaman atau periksa koneksi internet Anda.</span></div>`; pagination.innerHTML = ''; }
                }
            }

            async function loadFilters() {
                try {
                    const payload = await get(API.category);
                    const categoryOptions = [`<button type="button" class="dropdown-option active" data-value="all" data-label="Semua"><i class="fas fa-check"></i><span>Semua</span></button>`];
                    arr(payload).forEach(function(category) { categoryOptions.push(`<button type="button" class="dropdown-option" data-value="${esc(category.id)}" data-label="${esc(category.name)}" title="${esc(category.name)}"><i class="fas fa-check"></i><span>${esc(category.name)}</span></button>`); });
                    categoryMenu.innerHTML = categoryOptions.join(''); categoryText.textContent = 'Semua'; setActiveOption(categoryMenu, state.category);
                } catch (error) {
                    categoryMenu.innerHTML = `<button type="button" class="dropdown-option active" data-value="all" data-label="Semua"><i class="fas fa-check"></i><span>Semua</span></button>`;
                    categoryText.textContent = 'Semua'; setActiveOption(categoryMenu, 'all');
                }
            }

            search.addEventListener('input', function() { clearTimeout(searchTimer); state.q = search.value; state.page = 1; searchTimer = setTimeout(load, 400); });
            searchButton?.addEventListener('click', function() { state.q = search.value; state.page = 1; search.focus(); load(); });
            loadFilters(); load();
            window.addEventListener('resize', function() { clearTimeout(window.resizeTimer); window.resizeTimer = setTimeout(function() { closeDropdowns(); renderPages(); }, 200); });
        })();
    </script>
@endpush
