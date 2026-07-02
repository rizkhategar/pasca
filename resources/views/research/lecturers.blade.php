@php
    $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();
@endphp

@extends('layouts.app')

@section('title', 'Daftar Dosen & Riset - Pascasarjana UNW')
@section('body_class', 'research-list-page news-page')

@push('styles')
    <style>
        .research-list-page #dosenGrid.lecturer-list {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 1.15rem !important;
            align-items: stretch !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card {
            position: relative !important;
            display: grid !important;
            grid-template-columns: 162px minmax(0, 1fr) !important;
            grid-template-areas: "photo info" !important;
            align-items: center !important;
            gap: 0 !important;
            width: 100% !important;
            min-height: 235px !important;
            overflow: hidden !important;
            text-decoration: none !important;
            flex-direction: unset !important;
            border: 1px solid rgba(148, 163, 184, .24) !important;
            border-radius: 1.25rem !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 55%, #f1f7ff 100%) !important;
            box-shadow: 0 16px 38px rgba(15, 23, 42, .08) !important;
            transition: opacity .55s ease, transform .55s ease, box-shadow .22s ease, border-color .22s ease, background .22s ease !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover {
            transform: translateY(-5px) !important;
            border-color: rgba(247, 181, 0, .55) !important;
            box-shadow: 0 26px 58px rgba(247, 181, 0, .32), 0 10px 28px rgba(15, 23, 42, .12) !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card.lecturer-reveal-card:not(.is-card-loading):not(.is-visible) {
            opacity: 0;
            transform: translateY(28px) !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card.lecturer-reveal-card.is-visible:not(:hover) {
            opacity: 1;
            transform: translateY(0) !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card.lecturer-reveal-card.is-visible:hover {
            opacity: 1;
            transform: translateY(-5px) !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, #1d4ed8, #60a5fa, #93c5fd);
            opacity: .9;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover::before {
            background: linear-gradient(180deg, #f7b500, #ffd86b, #fff3b0);
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo {
            grid-area: photo !important;
            position: relative !important;
            width: 128px !important;
            max-width: 128px !important;
            height: 193px !important;
            min-height: 193px !important;
            margin: 0 0 0 1.55rem !important;
            border-radius: 1rem !important;
            overflow: hidden !important;
            background: #e5e7eb !important;
            border: 4px solid #ffffff !important;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .20) !important;
            flex: 0 0 128px !important;
            align-self: center !important;
            cursor: zoom-in;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover > .lecturer-card-photo {
            box-shadow: 0 18px 34px rgba(247, 181, 0, .28), 0 10px 24px rgba(15, 23, 42, .18) !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo img {
            width: 100% !important;
            height: 100% !important;
            min-height: 0 !important;
            object-fit: cover !important;
            object-position: center center !important;
            display: block !important;
            transition: transform .28s ease, filter .28s ease !important;
        }

        .research-list-page .lecturer-card-photo:hover img,
        .research-list-page .lecturer-card-photo:focus-visible img {
            transform: scale(1.05);
            filter: brightness(.78);
        }

        .research-list-page .lecturer-photo-zoom {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.7rem;
            opacity: 0;
            transform: scale(.86);
            background: rgba(7, 43, 87, .24);
            transition: opacity .24s ease, transform .24s ease;
            pointer-events: none;
        }

        .research-list-page .lecturer-card-photo:hover .lecturer-photo-zoom,
        .research-list-page .lecturer-card-photo:focus-visible .lecturer-photo-zoom {
            opacity: 1;
            transform: scale(1);
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-info {
            grid-area: info !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            width: auto !important;
            min-width: 0 !important;
            padding: 1.05rem 1.1rem 1rem .9rem !important;
        }

        .research-list-page .lecturer-card-main-info {
            min-width: 0;
        }

        .research-list-page .lecturer-card-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .65rem;
            margin-bottom: .45rem;
        }

        .research-list-page .lecturer-card-title-row .news-page-title {
            margin: 0;
            color: #0f172a;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.28;
            letter-spacing: -.01em;
        }

        .research-list-page .lecturer-sinta-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            flex: 0 0 auto;
            border-radius: 999px;
            padding: .32rem .58rem;
            background: rgba(37, 99, 235, .08);
            color: #1d4ed8;
            border: 1px solid rgba(37, 99, 235, .14);
            font-size: .7rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover .lecturer-sinta-chip {
            background: rgba(255, 248, 214, .65);
            color: #072b57;
            border-color: rgba(247, 181, 0, .30);
        }

        .research-list-page .lecturer-card-info .news-page-excerpt {
            margin-bottom: .65rem;
            color: #475569;
            font-size: .84rem;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover .news-page-excerpt {
            color: #334155;
        }

        .research-list-page .lecturer-data-label {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            gap: .4rem;
            margin-bottom: .55rem;
            border-radius: 999px;
            padding: .36rem .68rem;
            background: linear-gradient(135deg, rgba(14, 165, 233, .12), rgba(37, 99, 235, .10));
            color: #075985;
            border: 1px solid rgba(14, 165, 233, .18);
            font-size: .72rem;
            font-weight: 800;
            line-height: 1;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover .lecturer-data-label {
            background: rgba(255, 248, 214, .65);
            color: #072b57;
            border-color: rgba(247, 181, 0, .30);
        }

        .research-list-page .lecturer-data-label i {
            color: #2563eb;
        }

        .research-list-page .lecturer-card-info .lecturer-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: .48rem !important;
            margin-top: .25rem;
        }

        .research-list-page .lecturer-card-info .lecturer-stats .stat-box {
            position: relative;
            overflow: hidden;
            padding: .58rem .42rem !important;
            min-height: 62px !important;
            border-radius: .85rem !important;
            background: rgba(255, 255, 255, .9) !important;
            border: 1px solid rgba(148, 163, 184, .18) !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .05) !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover .stat-box {
            background: rgba(255, 255, 255, .92) !important;
            border-color: rgba(247, 181, 0, .26) !important;
            box-shadow: 0 10px 20px rgba(247, 181, 0, .12) !important;
        }

        .research-list-page .lecturer-card-info .lecturer-stats .stat-box::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 3px;
            background: linear-gradient(90deg, #1d4ed8, #38bdf8);
            opacity: .75;
        }

        .research-list-page .lecturer-card-info .lecturer-stats .stat-number {
            color: #0f172a;
            font-size: .9rem !important;
            font-weight: 850 !important;
            line-height: 1.12 !important;
        }

        .research-list-page .lecturer-card-info .lecturer-stats .stat-desc {
            margin-top: .18rem;
            color: #64748b;
            font-size: .66rem !important;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .research-list-page .lecturer-card-info .news-page-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: .7rem;
            padding-top: .65rem;
            border-top: 1px solid rgba(15, 23, 42, .08);
        }

        .research-list-page .lecturer-card-info .read-more {
            display: inline-flex;
            align-items: center;
            gap: .42rem;
            color: #1d4ed8;
            font-size: .78rem;
            font-weight: 800;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card:hover .read-more {
            color: #072b57;
        }

        .research-list-page .lecturer-list-card.is-card-loading {
            pointer-events: none;
            opacity: 1 !important;
            transform: translateY(0) !important;
        }

        .research-list-page .lecturer-list-card.is-card-loading > :not(.lecturer-card-skeleton) {
            opacity: 0;
        }

        .research-list-page .lecturer-card-skeleton {
            display: none;
        }

        .research-list-page .lecturer-list-card.is-card-loading .lecturer-card-skeleton {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: grid;
            grid-template-columns: 162px minmax(0, 1fr);
            align-items: center;
            min-height: 235px;
            padding: 1.25rem 1.1rem 1.2rem 1.55rem;
            gap: 1rem;
            background: linear-gradient(135deg, #f1f5f9 0%, #e5e7eb 55%, #f8fafc 100%);
        }

        .research-list-page .skeleton-block {
            position: relative;
            overflow: hidden;
            border-radius: .9rem;
            background: #dbe3ec;
        }

        .research-list-page .skeleton-block::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent 0%, rgba(148, 163, 184, .34) 42%, rgba(71, 85, 105, .22) 52%, transparent 100%);
            animation: lecturerSkeletonShimmer 1.05s ease-in-out 2;
        }

        .research-list-page .skeleton-photo {
            width: 128px;
            height: 193px;
            border-radius: 1rem;
            border: 4px solid rgba(255, 255, 255, .86);
        }

        .research-list-page .skeleton-info {
            display: flex;
            flex-direction: column;
            gap: .78rem;
            min-width: 0;
        }

        .research-list-page .skeleton-line-lg {
            width: 78%;
            height: 18px;
        }

        .research-list-page .skeleton-line-md {
            width: 58%;
            height: 14px;
        }

        .research-list-page .skeleton-line-sm {
            width: 34%;
            height: 13px;
            border-radius: 999px;
        }

        .research-list-page .skeleton-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .48rem;
            margin-top: .15rem;
        }

        .research-list-page .skeleton-stat {
            height: 62px;
            border-radius: .85rem;
        }

        @keyframes lecturerSkeletonShimmer {
            100% {
                transform: translateX(100%);
            }
        }

        body.photo-preview-open {
            overflow: hidden;
        }

        .lecturer-photo-modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .22s ease, visibility .22s ease;
        }

        .lecturer-photo-modal.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .lecturer-photo-modal-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(3, 31, 66, .72);
            backdrop-filter: blur(7px);
            cursor: zoom-out;
        }

        .lecturer-photo-dialog {
            position: relative;
            z-index: 1;
            width: min(420px, 92vw);
            border-radius: 1.35rem;
            background: #ffffff;
            box-shadow: 0 32px 80px rgba(3, 31, 66, .38);
            overflow: hidden;
            transform: translateY(18px) scale(.96);
            transition: transform .22s ease;
        }

        .lecturer-photo-modal.is-open .lecturer-photo-dialog {
            transform: translateY(0) scale(1);
        }

        .lecturer-photo-dialog img {
            display: block;
            width: 100%;
            max-height: 72vh;
            object-fit: contain;
            background: #e5e7eb;
        }

        .lecturer-photo-caption {
            padding: .95rem 1.1rem;
            color: #072b57;
            font-size: .95rem;
            font-weight: 850;
            text-align: center;
            border-top: 1px solid rgba(148, 163, 184, .22);
        }

        .lecturer-photo-close {
            position: absolute;
            top: .75rem;
            right: .75rem;
            z-index: 2;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(7, 43, 87, .86);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .22);
        }

        @media (max-width: 1200px) {
            .research-list-page #dosenGrid.lecturer-list {
                grid-template-columns: 1fr !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card {
                grid-template-columns: 162px minmax(0, 1fr) !important;
                min-height: 235px !important;
            }
        }

        @media (max-width: 640px) {
            .research-list-page #dosenGrid.lecturer-list {
                grid-template-columns: 1fr !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card {
                grid-template-columns: 1fr !important;
                grid-template-areas:
                    "photo"
                    "info" !important;
                min-height: auto !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card::before {
                inset: 0 0 auto 0;
                width: 100%;
                height: 4px;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo {
                width: 128px !important;
                max-width: 128px !important;
                height: 193px !important;
                min-height: 193px !important;
                margin: 1.1rem auto .15rem auto !important;
                flex-basis: 128px !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-info {
                padding: .9rem 1rem 1rem 1rem !important;
            }

            .research-list-page .lecturer-card-title-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .research-list-page .lecturer-sinta-chip {
                white-space: normal;
            }

            .research-list-page .lecturer-card-info .lecturer-stats,
            .research-list-page .skeleton-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .research-list-page .lecturer-list-card.is-card-loading .lecturer-card-skeleton {
                grid-template-columns: 1fr;
                justify-items: center;
                padding: 1.1rem;
            }

            .research-list-page .skeleton-info {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <section class="rd-hero">
        <div class="rd-hero-dots"></div>
        @include('components.hero-spotlight')

        <div class="rd-container">
            <div class="rd-hero-inner">
                <div class="rd-kicker">
                    <i class="fas fa-flask"></i>
                    <span>Riset Dosen</span>
                </div>

                <h1 class="rd-title">Daftar Riset Dosen</h1>

                <p class="rd-desc">
                    Temukan profil dosen, program studi, dan capaian riset berdasarkan data SINTA Pascasarjana
                    Universitas Ngudi Waluyo.
                </p>

                <div class="rd-hero-meta">
                    <span><i class="fas fa-user-graduate"></i>Profil Dosen</span>
                    <span><i class="fas fa-chart-line"></i>Data SINTA</span>
                    <span><i class="fas fa-university"></i>Pascasarjana UNW</span>
                </div>
            </div>
        </div>

        <div class="rd-hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <main class="news-section">
        <div class="rd-container">
            <section class="news-panel">
                <div class="profile-content-heading">
                    <div class="profile-heading-title">
                        <div class="profile-heading-icon">
                            <i class="fas fa-filter"></i>
                        </div>

                        <div>
                            <h2>Filter Data Dosen</h2>
                            <p>Gunakan pencarian atau pilih jurusan untuk menemukan dosen secara lebih cepat.</p>
                        </div>
                    </div>

                    <div class="tab-badge">
                        <i class="fas fa-database"></i>
                        <span>{{ $dosens->total() }} Data</span>
                    </div>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="news-toolbar" id="filterForm">
                    <div class="news-search-wrap">
                        <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Cari nama dosen atau ID SINTA..." class="search-box" autocomplete="off">
                        <button type="submit" class="search-icon-btn" aria-label="Cari dosen">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div class="filter-wrap">
                        <select name="jurusan" id="jurusanSelect" class="search-box">
                            <option value="">Semua Jurusan</option>
                            @foreach ($academicProgramsNav as $jurusan)
                                <option value="{{ $jurusan['id'] }}" {{ request('jurusan') == $jurusan['id'] ? 'selected' : '' }}>
                                    {{ $jurusan['display_name'] }}
                                </option>
                            @endforeach
                        </select>

                        @if (request('search') || request('jurusan'))
                            <a href="{{ url()->current() }}" class="news-more-link">
                                <i class="fas fa-rotate-left"></i>
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                <div class="profile-content-heading">
                    <div class="profile-heading-title">
                        <div class="profile-heading-icon">
                            <i class="fas fa-users"></i>
                        </div>

                        <div>
                            <h2>Daftar Dosen</h2>
                            <p>Silakan pilih salah satu dosen untuk melihat detail riset dan publikasinya.</p>
                        </div>
                    </div>
                </div>

                <section class="lecturer-list" id="dosenGrid">
                    @forelse($dosens as $dosen)
                        @php
                            $safeSintaId = collect(str_split((string) $dosen->sinta_id))
                                ->filter(fn ($char) => ctype_alnum($char) || in_array($char, ['_', '-'], true))
                                ->implode('');
                            $customPhotoPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";
                            $scrapedPhotoPath = "sinta-lecturers/{$safeSintaId}.jpg";
                            $photoUrl = $dosen->profile_photo ?: asset('assets/images/default-user.png');

                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($customPhotoPath)) {
                                $photoUrl = url('storage/' . $customPhotoPath);
                            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($scrapedPhotoPath)) {
                                $photoUrl = url('storage/' . $scrapedPhotoPath);
                            }
                        @endphp

                        <a href="{{ route('riset.detail', $dosen->sinta_id) }}" class="news-page-card lecturer-card lecturer-list-card is-card-loading">
                            <div
                                class="news-page-thumb lecturer-thumb lecturer-card-photo"
                                role="button"
                                tabindex="0"
                                aria-label="Lihat foto {{ $dosen->nama }}"
                                data-photo-preview
                                data-photo-url="{{ $photoUrl }}"
                                data-photo-name="{{ $dosen->nama }}"
                            >
                                <img src="{{ $photoUrl }}" alt="{{ $dosen->nama }}">
                                <span class="lecturer-photo-zoom" aria-hidden="true">
                                    <i class="fas fa-magnifying-glass-plus"></i>
                                </span>
                            </div>

                            <div class="news-page-body lecturer-card-info">
                                <div class="lecturer-card-main-info">
                                    <div class="lecturer-card-title-row">
                                        <h3 class="news-page-title">{{ $dosen->nama }}</h3>
                                        <span class="lecturer-sinta-chip">
                                            <i class="fas fa-id-badge"></i>
                                            SINTA {{ $dosen->sinta_id }}
                                        </span>
                                    </div>

                                    <p class="news-page-excerpt">{{ $dosen->program_studi }}</p>

                                    <div class="lecturer-data-label">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Data Riset SINTA</span>
                                    </div>

                                    <div class="stats-grid lecturer-stats">
                                        <div class="stat-box">
                                            <div class="stat-number">{{ number_format($dosen->sinta_score_overall ?? 0) }}</div>
                                            <div class="stat-desc">Total</div>
                                        </div>

                                        <div class="stat-box">
                                            <div class="stat-number">{{ number_format($dosen->sinta_score_3yr ?? 0) }}</div>
                                            <div class="stat-desc">3 Year</div>
                                        </div>

                                        <div class="stat-box">
                                            <div class="stat-number">{{ number_format($dosen->affil_score ?? 0) }}</div>
                                            <div class="stat-desc">Affil</div>
                                        </div>

                                        <div class="stat-box">
                                            <div class="stat-number">{{ number_format($dosen->affil_score_3yr ?? 0) }}</div>
                                            <div class="stat-desc">Affil 3Y</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="news-page-footer">
                                    <span class="read-more">Detail<i class="fas fa-arrow-right"></i></span>
                                </div>
                            </div>

                            <div class="lecturer-card-skeleton" aria-hidden="true">
                                <div class="skeleton-block skeleton-photo"></div>
                                <div class="skeleton-info">
                                    <div class="skeleton-block skeleton-line-lg"></div>
                                    <div class="skeleton-block skeleton-line-md"></div>
                                    <div class="skeleton-block skeleton-line-sm"></div>
                                    <div class="skeleton-stat-grid">
                                        <div class="skeleton-block skeleton-stat"></div>
                                        <div class="skeleton-block skeleton-stat"></div>
                                        <div class="skeleton-block skeleton-stat"></div>
                                        <div class="skeleton-block skeleton-stat"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty">
                            <i class="fas fa-folder-open"></i>
                            <strong>Data Tidak Ditemukan</strong>
                            <span>Tidak ada data dosen yang sesuai dengan kriteria pencarian Anda.</span>
                        </div>
                    @endforelse
                </section>

                <div class="pagination">
                    {{ $dosens->withQueryString()->links() }}
                </div>
            </section>
        </div>
    </main>

    <div class="lecturer-photo-modal" id="lecturerPhotoModal" role="dialog" aria-modal="true" aria-hidden="true" aria-label="Preview foto dosen">
        <button type="button" class="lecturer-photo-modal-backdrop" data-photo-close aria-label="Tutup preview foto"></button>
        <div class="lecturer-photo-dialog">
            <button type="button" class="lecturer-photo-close" data-photo-close aria-label="Tutup preview foto">
                <i class="fas fa-times"></i>
            </button>
            <img src="" alt="" id="lecturerPhotoModalImage">
            <div class="lecturer-photo-caption" id="lecturerPhotoModalCaption"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const jurusanSelect = document.getElementById('jurusanSelect');
            const lecturerCards = Array.from(document.querySelectorAll('.lecturer-list-card'));
            const photoModal = document.getElementById('lecturerPhotoModal');
            const photoModalImage = document.getElementById('lecturerPhotoModalImage');
            const photoModalCaption = document.getElementById('lecturerPhotoModalCaption');
            const photoPreviewButtons = document.querySelectorAll('[data-photo-preview]');
            const photoCloseButtons = document.querySelectorAll('[data-photo-close]');

            jurusanSelect?.addEventListener('change', function () {
                form.submit();
            });

            let debounceTimer;
            searchInput?.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    form.submit();
                }, 800);
            });

            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return rect.top < window.innerHeight * 0.92 && rect.bottom > 0;
            }

            function revealCard(card, observer = null) {
                if (!card || card.dataset.revealed === 'true') {
                    return;
                }

                card.dataset.revealed = 'true';
                card.classList.add('is-visible');
                observer?.unobserve(card);
            }

            function setupLecturerCards() {
                if (!lecturerCards.length) {
                    return;
                }

                lecturerCards.forEach(function (card) {
                    card.classList.add('lecturer-reveal-card');
                });

                const observer = 'IntersectionObserver' in window
                    ? new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                revealCard(entry.target, observer);
                            }
                        });
                    }, { threshold: 0.16, rootMargin: '0px 0px -8% 0px' })
                    : null;

                const finishLoading = function () {
                    lecturerCards.forEach(function (card) {
                        if (isInViewport(card) || !observer) {
                            revealCard(card, observer);
                        }
                    });

                    lecturerCards.forEach(function (card) {
                        card.classList.remove('is-card-loading');
                    });

                    if (observer) {
                        lecturerCards.forEach(function (card) {
                            if (card.dataset.revealed !== 'true') {
                                observer.observe(card);
                            }
                        });
                    }
                };

                window.setTimeout(finishLoading, 850);
            }

            function openPhotoPreview(trigger) {
                if (!photoModal || !photoModalImage || !photoModalCaption) {
                    return;
                }

                const imageUrl = trigger.dataset.photoUrl;
                const photoName = trigger.dataset.photoName || 'Foto Dosen';

                if (!imageUrl) {
                    return;
                }

                photoModalImage.src = imageUrl;
                photoModalImage.alt = photoName;
                photoModalCaption.textContent = photoName;
                photoModal.classList.add('is-open');
                photoModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('photo-preview-open');
            }

            function closePhotoPreview() {
                if (!photoModal || !photoModalImage) {
                    return;
                }

                photoModal.classList.remove('is-open');
                photoModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('photo-preview-open');
                window.setTimeout(function () {
                    if (!photoModal.classList.contains('is-open')) {
                        photoModalImage.src = '';
                        photoModalImage.alt = '';
                    }
                }, 220);
            }

            photoPreviewButtons.forEach(function (trigger) {
                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openPhotoPreview(trigger);
                });

                trigger.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        event.stopPropagation();
                        openPhotoPreview(trigger);
                    }
                });
            });

            photoCloseButtons.forEach(function (button) {
                button.addEventListener('click', closePhotoPreview);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closePhotoPreview();
                }
            });

            setupLecturerCards();
        });
    </script>
@endpush
