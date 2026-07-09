@extends('layouts.app')

@section('title', 'Berita - Pascasarjana UNW')
@section('body_class', 'news-detail-page')

@section('content')
    <section class="news-hero">
        <div class="hero-dots"></div>
        @include('components.hero-spotlight')

        <div class="container">
            <div class="hero-inner">
                <a href="{{ route('news.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Berita</span>
                </a>

                <div class="news-category-pill" id="newsCategory">
                    <i class="fas fa-tag"></i>
                    <span></span>
                </div>

                <h1 class="news-title-page" id="newsTitle" aria-live="polite"></h1>
                <div class="news-meta" id="newsMeta"></div>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <main class="news-content-section">
        <div class="news-detail-shell">
            <article class="news-card-detail" id="newsCard">
                <div class="loading-news">
                    <div class="detail-loader"></div>
                    <span>Memuat berita...</span>
                </div>
            </article>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            const slug = @json($slug);
            const API_ORIGIN = 'https://panel-web.unw.ac.id';
            const API_NEWS = API_ORIGIN + '/api/news';

            const newsCard = document.getElementById('newsCard');
            const newsTitle = document.getElementById('newsTitle');
            const newsCategory = document.getElementById('newsCategory');
            const newsMeta = document.getElementById('newsMeta');

            function esc(value) {
                return String(value ?? '').replace(/[&<>'"]/g, function (char) {
                    return {
                        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
                    }[char];
                });
            }

            function strip(value) {
                return String(value ?? '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
            }

            function toArray(payload) {
                if (Array.isArray(payload)) return payload;
                if (Array.isArray(payload?.data)) return payload.data;
                if (Array.isArray(payload?.data?.data)) return payload.data.data;
                return [];
            }

            function extractItem(payload) {
                if (payload?.slug || payload?.id) return payload;
                if (payload?.data?.slug || payload?.data?.id) return payload.data;
                const list = toArray(payload);
                return list.find(function (item) { return item?.slug === slug; }) || list[0] || null;
            }

            function formatDate(value) {
                if (!value) return '';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return String(value);
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
            }

            function imageUrl(url) {
                if (!url) return '';
                url = String(url);
                if (/^https?:\/\//i.test(url)) return url;
                if (url.startsWith('/')) return API_ORIGIN + url;
                return API_ORIGIN + '/' + url.replace(/^\/+/, '');
            }

            async function getJson(url) {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('API gagal dimuat');
                return response.json();
            }

            async function tryGet(url) {
                try { return await getJson(url); } catch (error) { return null; }
            }

            async function findNewsBySlug() {
                const encodedSlug = encodeURIComponent(slug);
                const direct = await tryGet(API_NEWS + '/' + encodedSlug);
                const directItem = direct ? extractItem(direct) : null;
                if (directItem && (!directItem.slug || directItem.slug === slug)) return directItem;

                const byQuery = await tryGet(API_NEWS + '?slug=' + encodedSlug);
                const queryList = toArray(byQuery);
                const queryItem = queryList.find(function (item) { return item?.slug === slug; }) || extractItem(byQuery);
                if (queryItem && (!queryItem.slug || queryItem.slug === slug)) return queryItem;

                const allNews = await tryGet(API_NEWS + '?paginate=100&page=1');
                const allList = toArray(allNews);
                return allList.find(function (item) { return item?.slug === slug; }) || null;
            }

            function renderEmpty() {
                newsCard.innerHTML = `
                    <div class="empty-news">
                        <i class="fas fa-folder-open"></i>
                        <strong>Berita tidak ditemukan</strong>
                        <span>Konten berita belum tersedia atau gagal dimuat.</span>
                    </div>
                `;
            }

            function renderNews(item) {
                if (!item) {
                    renderEmpty();
                    return;
                }

                const title = item.title || 'Berita';
                const categoryName = item.category?.name || item.category_name || 'Berita';
                const date = formatDate(item.publishedAt || item.published_at || item.createdAt || item.created_at);
                const author = item.author?.name || item.user?.name || item.created_by || '';
                const cover = imageUrl(item.image || item.thumbnail || item.image_thumbnail || item.cover || '');
                const excerpt = strip(item.excerpt || item.description || '');
                const content = item.body || item.content || item.description || '<p>Konten belum tersedia.</p>';

                document.title = title + ' - Pascasarjana UNW';
                newsTitle.textContent = title;
                newsCategory.querySelector('span').textContent = categoryName;

                newsMeta.innerHTML = `
                    ${date ? `<span><i class="fas fa-calendar-alt"></i>${esc(date)}</span>` : ''}
                    ${author ? `<span><i class="fas fa-user"></i>${esc(author)}</span>` : ''}
                    <span><i class="fas fa-university"></i>Pascasarjana UNW</span>
                `;

                newsCard.innerHTML = `
                    ${cover ? `<div class="news-cover-wrap"><img class="news-cover" src="${esc(cover)}" alt="${esc(title)}" onerror="this.closest('.news-cover-wrap').remove()"></div>` : '<div class="news-no-cover"><i class="fas fa-newspaper"></i></div>'}
                    <div class="news-topbar">
                        <div class="news-topbar-info">
                            <div class="news-topbar-icon"><i class="fas fa-newspaper"></i></div>
                            <div>
                                <h2>${esc(categoryName)}</h2>
                                <p>${date ? esc(date) : 'Tanggal belum tersedia'}</p>
                            </div>
                        </div>
                        <button type="button" class="news-action-btn" onclick="navigator.share ? navigator.share({title: document.title, url: location.href}) : navigator.clipboard.writeText(location.href)">
                            <i class="fas fa-share-nodes"></i>
                            Bagikan
                        </button>
                    </div>
                    <div class="news-body">
                        ${excerpt ? `<div class="news-excerpt-detail">${esc(excerpt)}</div>` : ''}
                        <div class="news-content-html">${content}</div>
                    </div>
                `;
            }

            findNewsBySlug().then(renderNews).catch(renderEmpty);
        })();
    </script>
@endpush
