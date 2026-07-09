@php
    $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();
    $programNavBySlug = collect($academicProgramsNav)->keyBy('slug');
    $resolveProgramSlug = function (array $slugs) use ($programNavBySlug) {
        foreach ($slugs as $slug) {
            if ($programNavBySlug->has($slug)) {
                return $slug;
            }
        }
        return $slugs[0];
    };

    $homePrograms = [
        [
            'number' => '01',
            'title' => 'Magister Keperawatan',
            'short_title' => 'Keperawatan',
            'slug' => $resolveProgramSlug(['s2-keperawatan', 'magister-keperawatan']),
            'desc' => 'Mendukung peningkatan profesionalisme keperawatan melalui kajian lanjut, praktik, dan riset kesehatan.',
            'tag' => 'Nursing Leadership',
            'icon' => 'M20 6h-4.18C15.4 4.84 14.3 4 13 4h-2C9.7 4 8.6 4.84 8.18 6H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2Zm-9-1h2c.55 0 1 .45 1 1h-4c0-.55.45-1 1-1Zm7 9h-3v3h-6v-3H6v-4h3V7h6v3h3v4Z',
        ],
        [
            'number' => '02',
            'title' => 'Magister Kesehatan Masyarakat',
            'short_title' => 'Kesehatan Masyarakat',
            'slug' => $resolveProgramSlug(['s2-kesehatan-masyarakat', 'magister-kesehatan-masyarakat']),
            'desc' => 'Fokus pada pengembangan ilmu kesehatan masyarakat, kebijakan kesehatan, dan peningkatan kualitas layanan.',
            'tag' => 'Public Health',
            'icon' => 'M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3Zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z',
        ],
        [
            'number' => '03',
            'title' => 'Magister Manajemen Pendidikan',
            'short_title' => 'Manajemen Pendidikan',
            'slug' => $resolveProgramSlug(['s2-manajemen-pendidikan', 'magister-manajemen-pendidikan']),
            'desc' => 'Mengembangkan kepemimpinan, manajemen, dan inovasi pendidikan yang adaptif terhadap kebutuhan zaman.',
            'tag' => 'Education Management',
            'icon' => 'M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8ZM5 20v-2.2l7 3.8 7-3.8V20l-7 4-7-4Zm15-5h2v6h-2v-6Z',
        ],
        [
            'number' => '04',
            'title' => 'Magister Hukum',
            'short_title' => 'Hukum',
            'slug' => $resolveProgramSlug(['s2-hukum', 'magister-hukum']),
            'desc' => 'Program lanjutan untuk penguatan kompetensi hukum, tata kelola, dan penyelesaian persoalan hukum modern.',
            'tag' => 'Legal Governance',
            'icon' => 'M12 2a1 1 0 0 1 1 1v2h5a1 1 0 1 1 0 2h-.38l2.14 5.35A3.5 3.5 0 0 1 13.27 14L15.68 7H13v12h3a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2h3V7H8.32l2.41 7A3.5 3.5 0 0 1 4.24 12.35L6.38 7H6a1 1 0 1 1 0-2h5V3a1 1 0 0 1 1-1ZM8 8.12 6.18 12h3.64L8 8.12Zm8 0L14.18 12h3.64L16 8.12Z',
        ],
    ];

    $homeNewsCategories = [
        'Semua',
        'Umum',
        'Kemahasiswaan',
        'Akademik',
        'LPPM',
        'Kehumasan',
        'Pengembangan & Perencanaan',
        'Alumni',
        'Panduan Akademik',
        'Dosen',
        'PMB',
        'Beasiswa',
        'Teknologi',
        'PKKS/PP PT',
    ];
@endphp

@extends('layouts.app')

@section('title', 'Pascasarjana Universitas Ngudi Waluyo')
@section('body_class', 'home-page')

@push('styles')
    <style>
        .home-page .info-section .section-title,
        .home-page .info-section .service-title {
            font-weight: 600 !important;
            letter-spacing: -.12px !important;
        }

        .home-page .info-section .category-filters .cat-pill,
        .home-page .info-section .category-filters .cat-pill span,
        .home-page .info-section .news-list .news-item .news-category,
        .home-page .info-section .news-list .news-item .news-date,
        .home-page .info-section .news-more-link,
        .home-page .info-section .service-grid .service-card,
        .home-page .info-section .service-grid .service-card .service-label,
        .home-page .info-section .edom-popover,
        .home-page .info-section .edom-popover *,
        .home-page .info-section .small-label {
            font-weight: 600 !important;
        }

        .home-page .info-section .news-list .news-item .news-title,
        .home-page .info-section .news-list .news-item .news-title a,
        .home-page .info-section .news-list .news-item-link .news-title {
            font-weight: 600 !important;
            letter-spacing: -.08px !important;
        }

        .home-page .info-section .news-list .news-item .news-excerpt,
        .home-page .info-section .news-list .news-item-link .news-excerpt {
            font-weight: 500 !important;
        }

        .home-page .program-section .program-card .program-icon svg {
            width: 30px !important;
            height: 30px !important;
        }
    </style>
@endpush

@section('content')
    <section class="hero">
        @if (isset($sliders) && $sliders->count() > 0)
            @foreach ($sliders as $index => $slider)
                <div class="hero-slide {{ $index === 0 ? 'active' : '' }}"
                    style="background-image: url('{{ $slider->hero_image_url }}');"
                    data-duration="{{ $slider->duration_ms ?? 3000 }}">
                </div>
            @endforeach
        @else
            <div class="hero-slide active"
                style="background-image: url('{{ asset('assets/images/hero-campus.png') }}');" data-duration="3000">
            </div>
        @endif

        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">Pascasarjana<br>Universitas Ngudi Waluyo</h1>
                    <p class="hero-subtitle">Pascasarjana Universitas Ngudi Waluyo</p>
                    <a href="{{ route('contact.index') }}" class="btn-primary">Daftar Sekarang</a>
                </div>
            </div>
        </div>

        <div class="hero-dots" id="heroDots">
            @if (isset($sliders) && $sliders->count() > 0)
                @foreach ($sliders as $index => $slider)
                    <button class="hero-dot {{ $index === 0 ? 'active' : '' }}" type="button"
                        aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            @else
                <button class="hero-dot active" type="button" aria-label="Slide 1"></button>
            @endif
        </div>
    </section>

    <section class="program-section">
        <div class="container">
            <div class="program-head">
                <div class="program-kicker">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Program Pascasarjana</span>
                </div>

                <h2>Pilihan Program Magister</h2>

                <p>
                    Temukan program studi Pascasarjana Universitas Ngudi Waluyo yang sesuai dengan kebutuhan
                    pengembangan karier dan keilmuan Anda.
                </p>
            </div>

            <div class="program-grid">
                @foreach ($homePrograms as $program)
                    <article class="program-card program-card-premium">
                        <div class="program-card-glow"></div>

                        <div class="program-top">
                            <div class="program-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="{{ $program['icon'] }}" />
                                </svg>
                            </div>

                            <span class="program-number">{{ $program['number'] }}</span>
                        </div>

                        <div class="program-body">
                            <div class="program-tag">{{ $program['tag'] }}</div>
                            <h3 class="program-title">{{ $program['title'] }}</h3>
                            <p class="program-desc">{{ $program['desc'] }}</p>
                        </div>

                        <a href="{{ route('akademik.show', $program['slug']) }}" class="program-detail" aria-label="Detail Program {{ $program['title'] }}">
                            <span>Detail Program</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="info-section" id="layanan-mahasiswa">
        <div class="container">
            <div class="info-layout">
                <div class="news-area">
                    <div class="section-header">
                        <h2 class="section-title">Berita Terkini & Agenda</h2>
                    </div>

                    <div class="category-filters" aria-label="Filter kategori berita">
                        @foreach ($homeNewsCategories as $index => $category)
                            <button class="cat-pill {{ $index === 0 ? 'active' : '' }}" type="button"
                                data-category-id="{{ $index === 0 ? 'all' : Str::slug($category) }}">
                                <i class="fas fa-tag" aria-hidden="true"></i>
                                <span>{{ $category }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="news-list">
                        @for ($i = 0; $i < 3; $i++)
                            <article class="news-item">
                                <a class="news-item-link" href="{{ route('news.index') }}">
                                    <div class="news-thumb no-image"><i class="fas fa-newspaper"></i></div>
                                    <div class="news-content">
                                        <div class="news-category"><i class="fas fa-tag"></i>Umum</div>
                                        <h3 class="news-title">Memuat berita terbaru Pascasarjana UNW...</h3>
                                        <p class="news-excerpt">Data berita terbaru akan tampil otomatis dari API.</p>
                                        <div class="news-date"><i class="fas fa-calendar-alt"></i>{{ now()->translatedFormat('d F Y') }}</div>
                                    </div>
                                </a>
                            </article>
                        @endfor
                    </div>

                    <div class="news-more-wrap">
                        <a href="{{ route('news.index') }}" class="news-more-link">
                            Selengkapnya <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="service-area">
                    <h2 class="service-title">Menu Layanan Mahasiswa</h2>

                    <div class="service-grid">
                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17v-3H3v-4h7V7l5 5-5 5ZM12 3h8a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8v-2h7V5h-7V3Z" /></svg>
                            <span class="service-label">Login<br>Mahasiswa</span>
                        </button>

                        <div class="edom-card-wrapper" id="edomCardWrapper">
                            <button class="service-card" type="button" id="edomService">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm-1 7V3.5L18.5 9H13ZM8 13h8v2H8v-2Zm0 4h8v2H8v-2Zm0-8h3v2H8V9Z" /></svg>
                                <span class="service-label">EDOM</span>
                            </button>

                            <div class="edom-popover">
                                <h4>Pilih Mata Kuliah :</h4>
                                <p>Kejelasan Penyampaian Materi Dosen?</p>
                                <div class="small-label">Kejelasan Penyampaian Materi Dosen?</div>
                                <div class="edom-score"><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span></div>
                            </div>
                        </div>

                        <button class="service-card service-card-link" type="button">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" /></svg>
                            <span class="service-label">SIAKAD</span>
                            <i class="service-arrow fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </button>

                        <button class="service-card service-card-link" type="button">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" /></svg>
                            <span class="service-label">Perpustakaan<br>Digital</span>
                            <i class="service-arrow fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </button>

                        <button class="service-card service-card-link" type="button">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" /></svg>
                            <span class="service-label">SIPOLIN</span>
                            <i class="service-arrow fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
