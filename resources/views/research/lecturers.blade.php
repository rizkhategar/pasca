@php
    $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();
@endphp

@extends('layouts.app')

@section('title', 'Daftar Dosen & Riset - Pascasarjana UNW')
@section('body_class', 'research-list-page news-page')

@push('styles')
    <style>
        .research-list-page #dosenGrid.lecturer-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 1.25rem !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card {
            display: grid !important;
            grid-template-columns: 240px minmax(0, 1fr) !important;
            grid-template-areas: "photo info" !important;
            align-items: stretch !important;
            gap: 0 !important;
            width: 100% !important;
            min-height: 220px !important;
            overflow: hidden !important;
            text-decoration: none !important;
            flex-direction: unset !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo {
            grid-area: photo !important;
            position: relative !important;
            width: 240px !important;
            max-width: 240px !important;
            height: 100% !important;
            min-height: 220px !important;
            margin: 0 !important;
            border-radius: 0 !important;
            flex: 0 0 240px !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo img {
            width: 100% !important;
            height: 100% !important;
            min-height: 220px !important;
            object-fit: cover !important;
            object-position: center top !important;
            display: block !important;
        }

        .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-info {
            grid-area: info !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            width: auto !important;
            min-width: 0 !important;
            padding: 1.35rem 1.5rem !important;
        }

        .research-list-page .lecturer-card-main-info {
            min-width: 0;
        }

        .research-list-page .lecturer-card-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .65rem;
        }

        .research-list-page .lecturer-card-title-row .news-page-title {
            margin: 0;
        }

        .research-list-page .lecturer-sinta-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            flex: 0 0 auto;
            border-radius: 999px;
            padding: .4rem .7rem;
            background: rgba(37, 99, 235, .08);
            color: #1d4ed8;
            font-size: .82rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .research-list-page .lecturer-card-info .news-page-excerpt {
            margin-bottom: 1rem;
        }

        .research-list-page .lecturer-card-info .lecturer-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            margin-top: .75rem;
        }

        .research-list-page .lecturer-card-info .news-page-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(15, 23, 42, .08);
        }

        @media (max-width: 900px) {
            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card {
                grid-template-columns: 190px minmax(0, 1fr) !important;
                min-height: 200px !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo {
                width: 190px !important;
                max-width: 190px !important;
                min-height: 200px !important;
                flex-basis: 190px !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo img {
                min-height: 200px !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-info {
                padding: 1.1rem !important;
            }

            .research-list-page .lecturer-card-info .lecturer-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 640px) {
            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card {
                grid-template-columns: 1fr !important;
                grid-template-areas:
                    "photo"
                    "info" !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo {
                width: 100% !important;
                max-width: 100% !important;
                height: 260px !important;
                min-height: 260px !important;
                flex-basis: auto !important;
            }

            .research-list-page #dosenGrid.lecturer-list > .lecturer-list-card > .lecturer-card-photo img {
                min-height: 260px !important;
            }

            .research-list-page .lecturer-card-title-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .research-list-page .lecturer-sinta-chip {
                white-space: normal;
            }
        }
    </style>
@endpush

@section('content')
    <section class="rd-hero">
        <div class="rd-hero-dots"></div>
        <div class="rd-hero-line"></div>

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
                            $safeSintaId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $dosen->sinta_id);
                            $customPhotoPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";
                            $scrapedPhotoPath = "sinta-lecturers/{$safeSintaId}.jpg";
                            $photoUrl = $dosen->profile_photo ?: asset('assets/images/default-user.png');

                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($customPhotoPath)) {
                                $photoUrl = url('storage/' . $customPhotoPath);
                            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($scrapedPhotoPath)) {
                                $photoUrl = url('storage/' . $scrapedPhotoPath);
                            }
                        @endphp

                        <a href="{{ route('riset.detail', $dosen->sinta_id) }}" class="news-page-card lecturer-card lecturer-list-card">
                            <div class="news-page-thumb lecturer-thumb lecturer-card-photo">
                                <img src="{{ $photoUrl }}" alt="{{ $dosen->nama }}">
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

                                    <div class="stats-grid lecturer-stats">
                                        <div class="stat-box">
                                            <div class="stat-number">{{ number_format($dosen->sinta_score_overall ?? 0) }}</div>
                                            <div class="stat-desc">Overall</div>
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
                                            <div class="stat-desc">Affil 3Yr</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="news-page-footer">
                                    <span class="news-page-date">
                                        <i class="fas fa-chart-line"></i>
                                        Data Riset SINTA
                                    </span>

                                    <span class="read-more">
                                        Detail
                                        <i class="fas fa-arrow-right"></i>
                                    </span>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const jurusanSelect = document.getElementById('jurusanSelect');

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
        });
    </script>
@endpush
