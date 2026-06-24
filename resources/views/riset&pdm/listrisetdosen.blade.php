@php
    $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();
@endphp

@extends('layouts.app')

@section('title', 'Daftar Dosen & Riset - Pascasarjana UNW')
@section('body_class', 'research-list-page')

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

    <main class="rd-main">
        <div class="rd-container">
            <section class="rd-panel">
                <div class="rd-filter-box">
                    <div class="rd-filter-heading">
                        <div class="rd-filter-title">
                            <div class="rd-filter-icon"><i class="fas fa-filter"></i></div>
                            <div>
                                <h2>Filter Data Dosen</h2>
                                <p>Gunakan pencarian atau pilih jurusan untuk menemukan dosen secara lebih cepat.</p>
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="{{ url()->current() }}" class="rd-form" id="filterForm">
                        <div class="rd-form-group">
                            <label for="searchInput"><i class="fas fa-magnifying-glass"></i>Pencarian</label>
                            <div class="rd-input-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Cari nama dosen atau ID SINTA..." class="rd-input has-icon" autocomplete="off">
                            </div>
                        </div>

                        <div class="rd-form-group">
                            <label for="jurusanSelect"><i class="fas fa-layer-group"></i>Filter Jurusan</label>
                            <select name="jurusan" id="jurusanSelect" class="rd-input">
                                <option value="">Semua Jurusan</option>
                                @foreach ($academicProgramsNav as $jurusan)
                                    <option value="{{ $jurusan['id'] }}" {{ request('jurusan') == $jurusan['id'] ? 'selected' : '' }}>
                                        {{ $jurusan['display_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rd-btn-group">
                            @if (request('search') || request('jurusan'))
                                <a href="{{ url()->current() }}" class="rd-btn rd-btn-reset">
                                    <i class="fas fa-rotate-left"></i>
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="rd-content">
                    <div class="rd-result-info">
                        <div>
                            <h2>Daftar Dosen</h2>
                            <p>Silakan pilih salah satu dosen untuk melihat detail riset dan publikasinya.</p>
                        </div>

                        <div class="rd-result-badge">
                            <i class="fas fa-database"></i>
                            <span>{{ $dosens->total() }} Data</span>
                        </div>
                    </div>

                    <section class="rd-grid" id="dosenGrid">
                        @forelse($dosens as $dosen)
                            <a href="{{ route('riset.detail', $dosen->sinta_id) }}" class="rd-list-item">
                                <div class="rd-list-photo">
                                    @if (file_exists(public_path('assets/images/' . $dosen->sinta_id . '_PL.jpg')))
                                        <img src="{{ asset('assets/images/' . $dosen->sinta_id . '_PL.jpg') }}" alt="{{ $dosen->nama }}" class="rd-photo">
                                    @elseif (file_exists(public_path('assets/images/' . $dosen->sinta_id . '.jpg')))
                                        <img src="{{ asset('assets/images/' . $dosen->sinta_id . '.jpg') }}" alt="{{ $dosen->nama }}" class="rd-photo">
                                    @else
                                        <img src="{{ asset('assets/images/default-user.png') }}" alt="{{ $dosen->nama }}" class="rd-photo">
                                    @endif
                                </div>

                                <div class="rd-list-content">
                                    <h3 class="rd-name">{{ $dosen->nama }}</h3>
                                    <div class="rd-department">{{ $dosen->program_studi }}</div>
                                    <div class="rd-sinta-id">SINTA ID : {{ $dosen->sinta_id }}</div>

                                    <div class="rd-stats">
                                        <div class="rd-stat"><div class="rd-stat-value">{{ number_format($dosen->sinta_score_overall ?? 0) }}</div><div class="rd-stat-label">Overall Score</div></div>
                                        <div class="rd-stat"><div class="rd-stat-value">{{ number_format($dosen->sinta_score_3yr ?? 0) }}</div><div class="rd-stat-label">3 Year Score</div></div>
                                        <div class="rd-stat"><div class="rd-stat-value">{{ number_format($dosen->affil_score ?? 0) }}</div><div class="rd-stat-label">Affil Score</div></div>
                                        <div class="rd-stat"><div class="rd-stat-value">{{ number_format($dosen->affil_score_3yr ?? 0) }}</div><div class="rd-stat-label">Affil 3Yr</div></div>
                                    </div>

                                    <div class="rd-card-action">
                                        <span>Lihat detail riset</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rd-empty">
                                <div class="rd-empty-icon"><i class="fas fa-folder-open"></i></div>
                                <h3>Data Tidak Ditemukan</h3>
                                <p>Tidak ada data dosen yang sesuai dengan kriteria pencarian Anda.</p>
                            </div>
                        @endforelse
                    </section>

                    <div class="rd-pagination">
                        {{ $dosens->withQueryString()->links() }}
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const jurusanSelect = document.getElementById('jurusanSelect');

            jurusanSelect?.addEventListener('change', function() { form.submit(); });

            let debounceTimer;
            searchInput?.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() { form.submit(); }, 800);
            });
        });
    </script>
@endpush
