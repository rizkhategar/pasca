@extends('layouts.app')

@section('title', 'Berita - Pascasarjana UNW')
@section('body_class', 'news-page')

@push('styles')
    <style>
        .news-page .news-section {
            position: relative;
            z-index: 6;
            margin-top: -42px;
            padding: 0 0 88px;
        }

        .news-page .news-panel {
            overflow: visible;
            border-radius: 28px;
            background: rgba(255, 255, 255, .96);
            border: 1px solid rgba(226, 232, 240, .92);
            box-shadow: 0 22px 56px rgba(15, 23, 42, .10);
            backdrop-filter: blur(12px);
        }

        .news-page .news-panel-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            padding: 24px 26px 0;
        }

        .news-page .news-panel-title h2 {
            margin: 0 0 6px;
            color: #062e62;
            font-size: clamp(22px, 2.4vw, 30px);
            line-height: 1.18;
            font-weight: 900;
            letter-spacing: -.45px;
        }

        .news-page .news-panel-title p {
            max-width: 760px;
            margin: 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.75;
            font-weight: 600;
        }

        .news-page .news-panel-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 42px;
            padding: 9px 14px;
            border-radius: 999px;
            color: #062e62;
            background: rgba(6, 46, 98, .07);
            border: 1px solid rgba(6, 46, 98, .10);
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .news-page .news-panel-badge i {
            color: #f7b500;
        }

        .news-page .news-toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);
            gap: 14px;
            align-items: center;
            padding: 22px 26px 24px;
        }

        .news-page .filter-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            min-width: 0;
        }

        .news-page .compact-dropdown {
            position: relative;
            min-width: min(100%, 250px);
        }

        .news-page .program-filter {
            min-width: min(100%, 310px);
        }

        .news-page .dropdown-trigger {
            width: 100%;
            min-height: 48px;
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr) 18px;
            gap: 10px;
            align-items: center;
            padding: 10px 14px;
            border: 1px solid #dbe7f3;
            border-radius: 16px;
            color: #062e62;
            background: #f8fbff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .85);
            font-size: 13px;
            font-weight: 850;
            cursor: pointer;
            transition: .2s ease;
        }

        .news-page .dropdown-trigger:hover,
        .news-page .compact-dropdown.open .dropdown-trigger {
            border-color: rgba(11, 95, 159, .32);
            background: #fff;
            box-shadow: 0 12px 26px rgba(15, 23, 42, .08);
        }

        .news-page .selected-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: left;
        }

        .news-page .left-icon,
        .news-page .chevron-icon {
            color: #f7b500;
        }

        .news-page .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            z-index: 40;
            display: grid;
            gap: 6px;
            max-height: 310px;
            overflow: auto;
            padding: 8px;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 20px 46px rgba(15, 23, 42, .16);
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: .18s ease;
        }

        .news-page .compact-dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .news-page .dropdown-option {
            width: 100%;
            display: grid;
            grid-template-columns: 18px minmax(0, 1fr);
            gap: 9px;
            align-items: center;
            padding: 10px 11px;
            border: 0;
            border-radius: 13px;
            color: #334155;
            background: transparent;
            font-size: 13px;
            font-weight: 750;
            text-align: left;
            cursor: pointer;
        }

        .news-page .dropdown-option i {
            opacity: 0;
            color: #f7b500;
        }

        .news-page .dropdown-option:hover,
        .news-page .dropdown-option.active {
            color: #062e62;
            background: #f1f7fd;
        }

        .news-page .dropdown-option.active i {
            opacity: 1;
        }

        .news-page .news-search-wrap {
            position: relative;
            min-width: 0;
        }

        .news-page .search-box {
            width: 100%;
            min-height: 48px;
            padding: 10px 52px 10px 16px;
            border: 1px solid #dbe7f3;
            border-radius: 16px;
            color: #062e62;
            background: #f8fbff;
            font-size: 14px;
            font-weight: 700;
            outline: none;
            transition: .2s ease;
        }

        .news-page .search-box:focus {
            border-color: rgba(11, 95, 159, .38);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(11, 95, 159, .10);
        }

        .news-page .search-icon-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 12px;
            color: #fff;
            background: #0b5f9f;
            cursor: pointer;
        }

        .news-page .news-content {
            padding: 0 26px 28px;
        }

        .news-page .news-page-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .news-page .news-page-card,
        .news-page .news-skeleton-card {
            min-width: 0;
            overflow: hidden;
            border-radius: 22px;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
        }

        .news-page .news-page-card {
            display: flex;
            flex-direction: column;
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .news-page .news-page-card:hover {
            transform: translateY(-4px);
            border-color: rgba(11, 95, 159, .22);
            box-shadow: 0 22px 48px rgba(15, 23, 42, .13);
        }

        .news-page .news-page-thumb {
            position: relative;
            height: 188px;
            overflow: hidden;
            background: linear-gradient(135deg, #eaf2fb, #f8fbff);
        }

        .news-page .news-page-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .28s ease;
        }

        .news-page .news-page-card:hover .news-page-thumb img {
            transform: scale(1.04);
        }

        .news-page .news-page-thumb.no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0b5f9f;
            font-size: 40px;
        }

        .news-page .news-page-category {
            position: absolute;
            left: 14px;
            bottom: 14px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            max-width: calc(100% - 28px);
            padding: 7px 10px;
            border-radius: 999px;
            color: #062e62;
            background: rgba(255, 255, 255, .92);
            font-size: 11px;
            font-weight: 900;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .news-page .news-page-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 18px;
        }

        .news-page .news-page-title {
            margin: 0 0 10px;
            color: #062e62;
            font-size: 17px;
            line-height: 1.38;
            font-weight: 900;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-page .news-page-excerpt {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.75;
            font-weight: 600;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-page .news-page-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: auto;
            padding-top: 18px;
        }

        .news-page .news-page-date,
        .news-page .read-more {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 850;
        }

        .news-page .news-page-date {
            color: #64748b;
        }

        .news-page .read-more {
            color: #0b5f9f;
            white-space: nowrap;
        }

        .news-page .news-skeleton-card {
            pointer-events: none;
        }

        .news-page .skeleton-block,
        .news-page .skeleton-line,
        .news-page .skeleton-pill {
            position: relative;
            overflow: hidden;
            background: #e9f1f8;
        }

        .news-page .skeleton-block::after,
        .news-page .skeleton-line::after,
        .news-page .skeleton-pill::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .72), transparent);
            animation: newsSkeleton 1.15s infinite;
        }

        .news-page .skeleton-block {
            height: 188px;
        }

        .news-page .skeleton-body {
            padding: 18px;
        }

        .news-page .skeleton-line {
            height: 13px;
            border-radius: 999px;
            margin-bottom: 10px;
        }

        .news-page .skeleton-line.title {
            height: 18px;
            width: 88%;
        }

        .news-page .skeleton-line.short {
            width: 64%;
        }

        .news-page .skeleton-pill {
            width: 132px;
            height: 28px;
            border-radius: 999px;
            margin-top: 18px;
        }

        @keyframes newsSkeleton {
            100% { transform: translateX(100%); }
        }

        .news-page .empty {
            grid-column: 1 / -1;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 34px;
            border-radius: 22px;
            color: #64748b;
            background: #f8fbff;
            border: 1px dashed #cbd5e1;
            text-align: center;
        }

        .news-page .empty i {
            color: #f7b500;
            font-size: 34px;
        }

        .news-page .empty strong {
            color: #062e62;
            font-size: 18px;
            font-weight: 900;
        }

        .news-page .pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 28px;
        }

        .news-page .page-btn,
        .news-page .page-jump button {
            min-width: 38px;
            height: 38px;
            border: 1px solid #dbe7f3;
            border-radius: 12px;
            color: #062e62;
            background: #fff;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        .news-page .page-btn.active,
        .news-page .page-btn:hover,
        .news-page .page-jump button:hover {
            color: #fff;
            background: #0b5f9f;
            border-color: #0b5f9f;
        }

        .news-page .page-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .news-page .page-jump {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .news-page .page-jump input {
            width: 70px;
            height: 38px;
            border: 1px solid #dbe7f3;
            border-radius: 12px;
            color: #062e62;
            font-weight: 800;
            text-align: center;
        }

        @media (max-width: 992px) {
            .news-page .news-panel-head,
            .news-page .news-toolbar {
                grid-template-columns: 1fr;
            }

            .news-page .news-panel-badge {
                width: fit-content;
            }

            .news-page .news-page-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .news-page .news-section {
                margin-top: -34px;
                padding-bottom: 68px;
            }

            .news-page .news-panel {
                border-radius: 22px;
            }

            .news-page .news-panel-head,
            .news-page .news-toolbar,
            .news-page .news-content {
                padding-left: 16px;
                padding-right: 16px;
            }

            .news-page .news-toolbar {
                gap: 12px;
                padding-top: 18px;
            }

            .news-page .filter-wrap,
            .news-page .compact-dropdown {
                width: 100%;
            }

            .news-page .news-page-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .news-page .news-page-thumb,
            .news-page .skeleton-block {
                height: 180px;
            }

            .news-page .news-page-footer {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }

            .news-page .pagination {
                justify-content: flex-start;
            }

            .news-page .page-jump {
                width: 100%;
                margin-top: 4px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="news-hero">
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
            <svg viewBox="0 0 1440 140" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,78 C190,118 364,38 620,62 C898,88 1074,132 1440,54 L1440,140 L0,140 Z" fill="rgba(255,255,255,.58)"></path>
                <path d="M0,94 C210,126 402,72 640,82 C914,94 1114,116 1440,72 L1440,140 L0,140 Z" fill="#f8fbff"></path>
            </svg>
        </div>
    </section>

    <main class="news-section">
        <div class="container">
            <section class="news-panel">
                <div class="news-panel-head">
                    <div class="news-panel-title">
                        <h2>Daftar Berita Pascasarjana</h2>
                        <p>Pilih program studi Magister atau gunakan pencarian untuk menemukan berita Pascasarjana yang paling relevan.</p>
                    </div>
                    <div class="news-panel-badge">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Filter S2 Pascasarjana</span>
                    </div>
                </div>

                <div class="news-toolbar">
                    <div class="filter-wrap">
                        <div class="compact-dropdown program-filter" id="programDropdown">
                            <button class="dropdown-trigger" type="button" id="programDropdownButton" aria-label="Pilih Program Studi S2" aria-expanded="false">
                                <i class="fas fa-graduation-cap left-icon"></i>
                                <span class="selected-text" id="programSelectedText">Memuat program...</span>
                                <i class="fas fa-chevron-down chevron-icon"></i>
                            </button>
                            <div class="dropdown-menu" id="programDropdownMenu"></div>
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
                        <input class="search-box" id="newsSearch" type="search" placeholder="Cari berita Pascasarjana...">
                        <button class="search-icon-btn" id="newsSearchButton" type="button" title="Cari Berita" aria-label="Cari berita">
                            <i class="fas fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>

                <div class="news-content">
                    <div class="news-page-grid" id="newsGrid" aria-live="polite"></div>
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
            const API = {
                search: @json(route('news.search')),
                programs: API_ORIGIN + '/api/unw-fakultas',
            };
            const PAGE_SIZE = 9;
            const state = { page: 1, lastPage: 1, program: 'all', programName: '', programId: '', sort: 'desc', q: '' };
            let searchTimer = null;
            let activeRequestId = 0;

            const grid = document.getElementById('newsGrid');
            const pagination = document.getElementById('newsPagination');
            const search = document.getElementById('newsSearch');
            const searchButton = document.getElementById('newsSearchButton');
            const programDropdown = document.getElementById('programDropdown');
            const programButton = document.getElementById('programDropdownButton');
            const programMenu = document.getElementById('programDropdownMenu');
            const programText = document.getElementById('programSelectedText');
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
                [programDropdown, sortDropdown].forEach(function(dropdown) {
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

            setupDropdownButton(programButton, programDropdown);
            setupDropdownButton(sortButton, sortDropdown);
            setupDropdownArea(programDropdown);
            setupDropdownArea(sortDropdown);
            document.addEventListener('pointerdown', function(event) { if (!event.target.closest('.compact-dropdown')) closeDropdowns(); });
            document.addEventListener('keydown', function(event) { if (event.key === 'Escape') closeDropdowns(); });

            programMenu?.addEventListener('pointerdown', function(event) {
                const option = event.target.closest('.dropdown-option');
                if (!option) return;
                event.preventDefault(); event.stopPropagation();
                state.program = option.dataset.value || 'all';
                state.programName = option.dataset.name || '';
                state.programId = option.dataset.id || '';
                state.page = 1;
                programText.textContent = option.dataset.label || option.textContent.trim() || 'Semua S2';
                setActiveOption(programMenu, state.program); closeDropdown(programDropdown); load();
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
                return {
                    title: String(item?.title ?? 'Tanpa Judul'),
                    slug: String(item?.slug ?? ''),
                    image: String(item?.image_thumbnail || item?.thumbnail || item?.image || item?.cover || item?.photo || ''),
                    excerpt: String(item?.excerpt ?? item?.body ?? item?.content ?? item?.description ?? ''),
                    date: String(item?.publishedAt || item?.published_at || item?.createdAt || item?.created_at || ''),
                    categoryId: String(category?.id ?? item?.category_id ?? ''),
                    categorySlug: String(category?.slug ?? ''),
                    categoryName: String(category?.name ?? item?.category_name ?? 'Pascasarjana')
                };
            }

            function buildUrl(page) {
                const params = new URLSearchParams({ paginate: String(PAGE_SIZE), page: String(page), sort: state.sort });
                if (state.q.trim() !== '') params.set('q', state.q.trim());
                if (state.program !== 'all') {
                    params.set('program_studi', state.program);
                    if (state.programName) params.set('program_name', state.programName);
                    if (state.programId) params.set('program_id', state.programId);
                }
                return API.search + '?' + params.toString();
            }

            function renderSkeleton(count = PAGE_SIZE) {
                if (!grid) return;
                grid.innerHTML = Array.from({ length: count }).map(function() {
                    return `<article class="news-skeleton-card" aria-hidden="true"><div class="skeleton-block"></div><div class="skeleton-body"><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line short"></div><div class="skeleton-pill"></div></div></article>`;
                }).join('');
            }

            function render(items) {
                if (!items.length) { grid.innerHTML = `<div class="empty"><i class="fas fa-magnifying-glass"></i><strong>Berita tidak ditemukan</strong><span>Belum ada berita yang sesuai dengan filter atau pencarian Anda.</span></div>`; return; }
                grid.innerHTML = items.map(function(news) {
                    const title = esc(news.title);
                    const url = news.slug ? '/berita/' + encodeURIComponent(news.slug) : '/berita';
                    const imageUrl = img(news.image);
                    const excerpt = esc(strip(news.excerpt)).slice(0, 190);
                    const categoryName = esc(news.categoryName);
                    const newsDate = esc(date(news.date));
                    const imageHtml = imageUrl ? `<div class="news-page-thumb"><img src="${esc(imageUrl)}" alt="${title}" loading="lazy" onerror="this.closest('.news-page-thumb').classList.add('no-image'); this.remove();"><div class="news-page-category"><i class="fas fa-tag"></i>${categoryName}</div></div>` : `<div class="news-page-thumb no-image"><i class="fas fa-newspaper"></i><div class="news-page-category"><i class="fas fa-tag"></i>${categoryName}</div></div>`;
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
                renderSkeleton();
                pagination.innerHTML = '';
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

            function fallbackPrograms() {
                return [
                    { id: '21', nama: 'Manajemen Pendidikan', page_slug: 's2-manajemen-pendidikan', jenjang_nama_singkat: 'S2' },
                    { id: '22', nama: 'Hukum', page_slug: 's2-hukum', jenjang_nama_singkat: 'S2' },
                    { id: '23', nama: 'Keperawatan', page_slug: 's2-keperawatan', jenjang_nama_singkat: 'S2' },
                    { id: '24', nama: 'Kesehatan Masyarakat', page_slug: 's2-kesehatan-masyarakat', jenjang_nama_singkat: 'S2' },
                ];
            }

            function extractS2Programs(payload) {
                const faculties = arr(payload);
                const pascasarjana = faculties.find(function(faculty) {
                    const name = String(faculty?.nama || faculty?.unwFakultas?.nama || '').trim().toLowerCase();
                    const slug = String(faculty?.slug || faculty?.page_slug || '').trim().toLowerCase();
                    return slug === 'pascasarjana' || name === 'pascasarjana';
                });
                const programs = Array.isArray(pascasarjana?.unwProgramStudi) ? pascasarjana.unwProgramStudi : [];
                return programs.filter(function(program) {
                    const degree = String(program?.jenjang_nama_singkat || program?.jenjang || '').trim().toLowerCase();
                    const slug = String(program?.page_slug || '').trim().toLowerCase();
                    return degree === 's2' || degree === 'magister' || slug.startsWith('s2-');
                });
            }

            function renderProgramOptions(programs) {
                const options = [`<button type="button" class="dropdown-option active" data-value="all" data-label="Semua S2 Pascasarjana" data-name="" data-id=""><i class="fas fa-check"></i><span>Semua S2 Pascasarjana</span></button>`];
                programs.forEach(function(program) {
                    const label = `${program.jenjang_nama_singkat || 'S2'} ${program.nama || 'Program Studi'}`.trim();
                    options.push(`<button type="button" class="dropdown-option" data-value="${esc(program.page_slug || program.slug || program.id)}" data-label="${esc(label)}" data-name="${esc(program.nama || '')}" data-id="${esc(program.id || '')}" title="${esc(label)}"><i class="fas fa-check"></i><span>${esc(label)}</span></button>`);
                });
                programMenu.innerHTML = options.join('');
                programText.textContent = 'Semua S2 Pascasarjana';
                setActiveOption(programMenu, state.program);
            }

            async function loadFilters() {
                try {
                    const payload = await get(API.programs);
                    const programs = extractS2Programs(payload);
                    renderProgramOptions(programs.length ? programs : fallbackPrograms());
                } catch (error) {
                    renderProgramOptions(fallbackPrograms());
                }
            }

            search.addEventListener('input', function() { clearTimeout(searchTimer); state.q = search.value; state.page = 1; searchTimer = setTimeout(load, 400); });
            searchButton?.addEventListener('click', function() { state.q = search.value; state.page = 1; search.focus(); load(); });
            renderSkeleton();
            loadFilters(); load();
            window.addEventListener('resize', function() { clearTimeout(window.resizeTimer); window.resizeTimer = setTimeout(function() { closeDropdowns(); renderPages(); }, 200); });
        })();
    </script>
@endpush
