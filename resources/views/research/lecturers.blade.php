@php
    $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();
@endphp

@extends('layouts.app')

@section('title', 'Daftar Dosen & Riset - Pascasarjana UNW')
@section('body_class', 'research-list-page news-page')

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
