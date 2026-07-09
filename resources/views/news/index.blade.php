@extends('layouts.app')

@section('title', 'Berita - Pascasarjana UNW')
@section('body_class', 'news-page')

@section('content')
    <section class="news-hero">
        <div class="hero-dots"></div>
        @include('components.hero-spotlight')

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

                        <div class="compact-dropdown program-filter" id="programDropdown">
                            <button class="dropdown-trigger" type="button" id="programDropdownButton" aria-label="Pilih Program Studi" aria-expanded="false">
                                <i class="fas fa-graduation-cap left-icon"></i>
                                <span class="selected-text" id="programSelectedText">Memuat...</span>
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
                        <input class="search-box" id="newsSearch" type="search" placeholder="Cari berita...">
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

@push('styles')
    <style>
        .news-page .news-page-date,
        .news-page .read-more,
        .news-page .news-page-date *,
        .news-page .read-more * {
            font-weight: 600 !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            const API_ORIGIN = 'https://panel-web.unw.ac.id';
            const API = {
                search: @json(route('news.search')),
                category: API_ORIGIN + '/api/category',
                programs: API_ORIGIN + '/api/unw-fakultas',
            };
            const PAGE_SIZE = 9;
            const state = {
                page: 1,
                lastPage: 1,
                category: 'all',
                program: 'all',
                programName: '',
                programId: '',
                sort: 'desc',
                q: '',
            };
            const PROGRAM_ORDER = ['s2-keperawatan', 's2-kesehatan-masyarakat', 's2-manajemen-pendidikan', 's2-hukum'];
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
            function slug(value) { return String(value ?? '').trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''); }

            async function get(url) {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('failed');
                return response.json();
            }

            function closeDropdowns(except = null) {
                [categoryDropdown, programDropdown, sortDropdown].forEach(function(dropdown) {
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
            setupDropdownButton(programButton, programDropdown);
            setupDropdownButton(sortButton, sortDropdown);
            setupDropdownArea(categoryDropdown);
            setupDropdownArea(programDropdown);
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

            programMenu?.addEventListener('pointerdown', function(event) {
                const option = event.target.closest('.dropdown-option');
                if (!option) return;
                event.preventDefault(); event.stopPropagation();
                state.program = option.dataset.value || 'all';
                state.programName = option.dataset.name || '';
                state.programId = option.dataset.id || '';
                state.page = 1;
                programText.textContent = option.dataset.label || option.textContent.trim() || 'Semua Program Studi';
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
                    categoryName: String(category?.name ?? item?.category_name ?? 'Artikel')
                };
            }

            function buildUrl(page) {
                const params = new URLSearchParams({ paginate: String(PAGE_SIZE), page: String(page), sort: state.sort });
                if (state.q.trim() !== '') params.set('q', state.q.trim());
                if (state.category !== 'all') params.set('category_id', state.category);
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
                    return `<article class="news-page-card animate-pulse" aria-hidden="true"><div class="news-page-thumb bg-slate-200"></div><div class="news-page-body"><div class="h-5 w-4/5 rounded-full bg-slate-200 mb-3"></div><div class="h-4 w-full rounded-full bg-slate-200 mb-2"></div><div class="h-4 w-5/6 rounded-full bg-slate-200 mb-2"></div><div class="h-4 w-2/3 rounded-full bg-slate-200 mb-5"></div><div class="news-page-footer"><div class="h-4 w-28 rounded-full bg-slate-200"></div><div class="h-4 w-16 rounded-full bg-slate-200"></div></div></div></article>`;
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

                const isMobile = window.innerWidth <= 560;

                if (isMobile) {
                    pagination.innerHTML = `
                        <div class="flex w-full flex-wrap items-center justify-center gap-2">
                            <button class="page-btn !h-8 !min-w-8 !rounded-lg !px-2 !text-xs" data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''}>‹</button>
                            <span class="inline-flex h-8 items-center rounded-lg border border-slate-200 bg-white px-3 text-[11px] font-black text-[#062e62] shadow-sm">${current} / ${last}</span>
                            <button class="page-btn !h-8 !min-w-8 !rounded-lg !px-2 !text-xs" data-page="${current + 1}" ${current >= last ? 'disabled' : ''}>›</button>
                            <div class="page-jump !mt-0 !w-auto !gap-1">
                                <input class="!h-8 !w-14 !rounded-lg !text-xs" type="number" min="1" max="${last}" value="${current}" aria-label="Pilih halaman">
                                <button class="!h-8 !min-w-8 !rounded-lg !px-2 !text-xs" type="button" aria-label="Cari halaman"><i class="fas fa-magnifying-glass"></i></button>
                            </div>
                        </div>
                    `;
                } else {
                    const visiblePages = 5;
                    let start = Math.max(1, current - Math.floor(visiblePages / 2));
                    let end = Math.min(last, start + visiblePages - 1);
                    if (end - start < visiblePages - 1) start = Math.max(1, end - visiblePages + 1);
                    let html = `<button class="page-btn" data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''}>‹</button>`;
                    for (let page = start; page <= end; page++) html += `<button class="page-btn ${page === current ? 'active' : ''}" data-page="${page}">${page}</button>`;
                    if (end < last) html += `<span class="page-dots">...</span><button class="page-btn" data-page="${last}">${last}</button>`;
                    html += `<button class="page-btn" data-page="${current + 1}" ${current >= last ? 'disabled' : ''}>›</button><div class="page-jump"><input type="number" min="1" max="${last}" value="${current}" aria-label="Pilih halaman"><button type="button" aria-label="Cari halaman"><i class="fas fa-magnifying-glass"></i></button></div>`;
                    pagination.innerHTML = html;
                }

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
                    { id: '23', nama: 'Keperawatan', page_slug: 's2-keperawatan', jenjang_nama_singkat: 'S2' },
                    { id: '24', nama: 'Kesehatan Masyarakat', page_slug: 's2-kesehatan-masyarakat', jenjang_nama_singkat: 'S2' },
                    { id: '21', nama: 'Manajemen Pendidikan', page_slug: 's2-manajemen-pendidikan', jenjang_nama_singkat: 'S2' },
                    { id: '22', nama: 'Hukum', page_slug: 's2-hukum', jenjang_nama_singkat: 'S2' },
                ];
            }

            function extractS2Programs(payload) {
                const faculties = arr(payload);
                const pascasarjana = faculties.find(function(faculty) {
                    const name = String(faculty?.nama || faculty?.unwFakultas?.nama || '').trim().toLowerCase();
                    const slugValue = String(faculty?.slug || faculty?.page_slug || '').trim().toLowerCase();
                    return slugValue === 'pascasarjana' || name === 'pascasarjana';
                });
                const programs = Array.isArray(pascasarjana?.unwProgramStudi) ? pascasarjana.unwProgramStudi : [];
                return programs.filter(function(program) {
                    const degree = String(program?.jenjang_nama_singkat || program?.jenjang || '').trim().toLowerCase();
                    const pageSlug = String(program?.page_slug || '').trim().toLowerCase();
                    return degree === 's2' || degree === 'magister' || pageSlug.startsWith('s2-');
                }).sort(function(a, b) {
                    const firstOrder = PROGRAM_ORDER.indexOf(slug(a?.page_slug || a?.slug || a?.nama));
                    const secondOrder = PROGRAM_ORDER.indexOf(slug(b?.page_slug || b?.slug || b?.nama));
                    return (firstOrder === -1 ? 99 : firstOrder) - (secondOrder === -1 ? 99 : secondOrder);
                });
            }

            function renderCategoryOptions(categories) {
                const options = [`<button type="button" class="dropdown-option active" data-value="all" data-label="Semua"><i class="fas fa-check"></i><span>Semua</span></button>`];
                categories.forEach(function(category) { options.push(`<button type="button" class="dropdown-option" data-value="${esc(category.id)}" data-label="${esc(category.name)}" title="${esc(category.name)}"><i class="fas fa-check"></i><span>${esc(category.name)}</span></button>`); });
                categoryMenu.innerHTML = options.join(''); categoryText.textContent = 'Semua'; setActiveOption(categoryMenu, state.category);
            }

            function renderProgramOptions(programs) {
                const options = [`<button type="button" class="dropdown-option active" data-value="all" data-label="Semua Program Studi" data-name="" data-id=""><i class="fas fa-check"></i><span>Semua Program Studi</span></button>`];
                programs.forEach(function(program) {
                    const label = `${program.jenjang_nama_singkat || 'S2'} ${program.nama || 'Program Studi'}`.trim();
                    options.push(`<button type="button" class="dropdown-option" data-value="${esc(program.page_slug || program.slug || program.id)}" data-label="${esc(label)}" data-name="${esc(program.nama || '')}" data-id="${esc(program.id || '')}" title="${esc(label)}"><i class="fas fa-check"></i><span>${esc(label)}</span></button>`);
                });
                programMenu.innerHTML = options.join(''); programText.textContent = 'Semua Program Studi'; setActiveOption(programMenu, state.program);
            }

            async function loadFilters() {
                try {
                    const payload = await get(API.category);
                    renderCategoryOptions(arr(payload));
                } catch (error) {
                    renderCategoryOptions([]);
                }

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
