@extends('layouts.app')

@section('title', 'Detail Berita - Pascasarjana UNW')
@section('body_class', 'news-detail-page')

@section('content')
    <section class="news-hero">
        <div class="container">
            <div class="hero-inner">
                <a href="{{ route('news.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Berita</span>
                </a>

                <div class="news-category-pill" id="newsCategory">
                    <i class="fas fa-tag"></i>
                    <span>Berita</span>
                </div>

                <h1 class="news-title-page" id="newsTitle">Detail Berita</h1>
                <div class="news-meta" id="newsMeta"></div>
            </div>
        </div>
    </section>

    <main class="news-content-section">
        <div class="news-detail-shell">
            <article class="news-card-detail" id="newsCard">
                <div class="loading-news">
                    <div class="detail-loader"></div>
                    <span>Memuat detail berita...</span>
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

                for (let page = 1; page <= 12; page++) {
                    const payload = await tryGet(API_NEWS + '?paginate=100&page=' + page);
                    if (!payload) continue;
                    const item = toArray(payload).find(function (news) { return news?.slug === slug; });
                    if (item) return item;
                    const lastPage = Number(payload?.meta?.last_page || 0);
                    if (lastPage && page >= lastPage) break;
                }

                return null;
            }

            function renderNews(news) {
                const title = news?.title || 'Detail Berita';
                const category = (news?.category?.name || 'Berita').trim();
                const image = imageUrl(news?.image || news?.image_thumbnail || '');
                const excerpt = strip(news?.excerpt || '');
                const body = news?.content || news?.body || news?.description || '';
                const date = news?.publishedAt || news?.published_at || news?.createdAt || news?.created_at || '';
                const author = news?.author?.name || '';
                const views = news?.views;

                document.title = title + ' - Pascasarjana UNW';
                newsCategory.innerHTML = `<i class="fas fa-tag"></i><span>${esc(category)}</span>`;
                newsTitle.textContent = title;

                const meta = [];
                if (date) meta.push(`<span><i class="fas fa-calendar-alt"></i>${esc(formatDate(date))}</span>`);
                if (author) meta.push(`<span><i class="fas fa-user"></i>Oleh: ${esc(author)}</span>`);
                if (views !== undefined && views !== null) meta.push(`<span><i class="fas fa-eye"></i>${esc(views)} dibaca</span>`);
                meta.push(`<span><i class="fas fa-university"></i>Pascasarjana UNW</span>`);
                newsMeta.innerHTML = meta.join('');

                const imageHtml = image
                    ? `<div class="news-cover-wrap"><img src="${esc(image)}" alt="${esc(title)}" class="news-cover"></div>`
                    : `<div class="news-cover-wrap news-no-cover"><i class="fas fa-newspaper"></i></div>`;
                const excerptHtml = excerpt ? `<p class="news-excerpt-detail">${esc(excerpt)}</p>` : '';
                const bodyHtml = body ? body : `<p>${esc(excerpt || 'Isi lengkap berita belum tersedia.')}</p>`;

                newsCard.innerHTML = `
                    ${imageHtml}
                    <div class="news-topbar">
                        <div class="news-topbar-info">
                            <div class="news-topbar-icon"><i class="fas fa-file-lines"></i></div>
                            <div><h2>Detail Berita</h2><p>Informasi resmi Pascasarjana Universitas Ngudi Waluyo</p></div>
                        </div>
                        <button class="news-action-btn" id="copyLinkButton" type="button"><i class="fas fa-link"></i><span>Salin Link</span></button>
                    </div>
                    <div class="news-body">
                        ${excerptHtml}
                        <div class="news-content-html">${bodyHtml}</div>
                    </div>`;

                const copyButton = document.getElementById('copyLinkButton');
                copyButton?.addEventListener('click', async function () {
                    try {
                        await navigator.clipboard.writeText(window.location.href);
                        copyButton.innerHTML = '<i class="fas fa-check"></i><span>Link Tersalin</span>';
                        setTimeout(function () { copyButton.innerHTML = '<i class="fas fa-link"></i><span>Salin Link</span>'; }, 1600);
                    } catch (error) {
                        copyButton.innerHTML = '<i class="fas fa-triangle-exclamation"></i><span>Gagal</span>';
                        setTimeout(function () { copyButton.innerHTML = '<i class="fas fa-link"></i><span>Salin Link</span>'; }, 1600);
                    }
                });
            }

            function renderError() {
                newsTitle.textContent = 'Berita tidak ditemukan';
                newsCategory.innerHTML = `<i class="fas fa-circle-exclamation"></i><span>Tidak Ditemukan</span>`;
                newsMeta.innerHTML = `<span><i class="fas fa-university"></i>Pascasarjana UNW</span>`;
                newsCard.innerHTML = `<div class="empty-news"><i class="fas fa-triangle-exclamation"></i><strong>Berita belum dapat dimuat</strong><span>Berita belum dapat dimuat dari API berdasarkan slug: <strong>${esc(slug)}</strong>.</span></div>`;
            }

            findNewsBySlug().then(function (news) {
                news ? renderNews(news) : renderError();
            }).catch(renderError);
        })();
    </script>
@endpush
