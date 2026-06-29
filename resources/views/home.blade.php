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
            'icon' => 'M12 2 6 4v5c0 3.7 2.5 7.1 6 8 3.5-.9 6-4.3 6-8V4l-6-2Zm1 3v2h2v2h-2v2h-2V9H9V7h2V5h2Zm-8 14c0-2.2 4.7-3.4 7-3.4s7 1.2 7 3.4V22H5v-3Z',
        ],
        [
            'number' => '02',
            'title' => 'Magister Kesehatan Masyarakat',
            'short_title' => 'Kesehatan Masyarakat',
            'slug' => $resolveProgramSlug(['s2-kesehatan-masyarakat', 'magister-kesehatan-masyarakat']),
            'desc' => 'Fokus pada pengembangan ilmu kesehatan masyarakat, kebijakan kesehatan, dan peningkatan kualitas layanan.',
            'tag' => 'Public Health',
            'icon' => 'M12 21s-7.5-4.6-9.7-9.2C.6 8.2 2.6 4 6.5 4c2.2 0 3.7 1.2 4.5 2.6C11.8 5.2 13.3 4 15.5 4c3.9 0 5.9 4.2 4.2 7.8C17.5 16.4 12 21 12 21Zm-1.3-7.7h2.6v-2.1h2.1V8.6h-2.1V6.5h-2.6v2.1H8.6v2.6h2.1v2.1Z',
        ],
        [
            'number' => '03',
            'title' => 'Magister Manajemen Pendidikan',
            'short_title' => 'Manajemen Pendidikan',
            'slug' => $resolveProgramSlug(['s2-manajemen-pendidikan', 'magister-manajemen-pendidikan']),
            'desc' => 'Mengembangkan kepemimpinan, manajemen, dan inovasi pendidikan yang adaptif terhadap kebutuhan zaman.',
            'tag' => 'Education Management',
            'icon' => 'M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2 7-3.8V17l-7 4v-3.6l7 3.8Z',
        ],
        [
            'number' => '04',
            'title' => 'Magister Hukum',
            'short_title' => 'Hukum',
            'slug' => $resolveProgramSlug(['s2-hukum', 'magister-hukum']),
            'desc' => 'Program lanjutan untuk penguatan kompetensi hukum, tata kelola, dan penyelesaian persoalan hukum modern.',
            'tag' => 'Legal Governance',
            'icon' => 'M12 2a1 1 0 0 1 1 1v2h5a1 1 0 1 1 0 2h-1l2.5 5a3.5 3.5 0 0 1-7 0L15 7h-2v11h4a1 1 0 1 1 0 2H7a1 1 0 1 1 0-2h4V7H9l2.5 5a3.5 3.5 0 0 1-7 0L7 7H6a1 1 0 1 1 0-2h5V3a1 1 0 0 1 1-1Zm-4 6-1.6 3h3.2L8 8Zm8 0-1.6 3h3.2L16 8Z',
        ],
    ];
@endphp

@extends('layouts.app')

@section('title', 'Pascasarjana Universitas Ngudi Waluyo')
@section('body_class', 'home-page')

@push('styles')
    <style>
        .home-page .program-section {
            position: relative;
            overflow: hidden;
            padding: clamp(54px, 6vw, 78px) 0 clamp(64px, 7vw, 96px);
            background:
                radial-gradient(circle at 12% 10%, rgba(45, 156, 219, .14), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(247, 181, 0, .22), transparent 25%),
                linear-gradient(180deg, #f8fcff 0%, #f3f8fa 52%, #eef5f6 100%);
        }

        .home-page .program-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(7, 43, 87, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(7, 43, 87, .045) 1px, transparent 1px);
            background-size: 42px 42px;
            pointer-events: none;
        }

        .home-page .program-section::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -170px;
            width: min(760px, 82vw);
            height: 300px;
            transform: translateX(-50%);
            border-radius: 999px;
            background: radial-gradient(circle, rgba(7, 43, 87, .12), transparent 70%);
            pointer-events: none;
        }

        .home-page .program-section .container {
            position: relative;
            z-index: 2;
        }

        .home-page .program-head {
            max-width: 780px;
            margin: 0 auto clamp(34px, 4vw, 54px);
            text-align: center;
        }

        .home-page .program-kicker {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 0 auto 18px;
            padding: 12px 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(7, 43, 87, .10);
            color: #072b57;
            box-shadow: 0 16px 38px rgba(15, 23, 42, .07);
            font-size: 13px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .home-page .program-kicker i {
            color: #f7b500;
        }

        .home-page .program-head h2 {
            margin: 0 0 16px;
            color: #072b57;
            font-size: clamp(34px, 4vw, 52px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -.045em;
            text-transform: uppercase;
        }

        .home-page .program-head p {
            max-width: 720px;
            margin: 0 auto;
            color: #64748b;
            font-size: clamp(15px, 1.45vw, 19px);
            line-height: 1.75;
            font-weight: 600;
        }

        .home-page .program-grid {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 28px !important;
            align-items: stretch;
            perspective: 1200px;
        }

        .home-page .program-card {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            min-height: 310px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 34px 28px 28px;
            border-radius: 0 0 26px 26px;
            border: 1px solid rgba(226, 232, 240, .95);
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 22px 52px rgba(15, 23, 42, .08);
            transform: translateY(0) rotateX(0deg) rotateY(0deg) scale(1);
            transform-style: preserve-3d;
            transition:
                transform .45s cubic-bezier(.2, .9, .22, 1),
                box-shadow .45s ease,
                border-color .35s ease,
                background .35s ease;
            will-change: transform;
        }

        .home-page .program-card::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background:
                radial-gradient(circle at 100% 0%, rgba(247, 181, 0, .18) 0 0, transparent 32%),
                linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(255, 255, 255, .86));
            transition: opacity .35s ease, transform .45s ease;
        }

        .home-page .program-card::after {
            content: "";
            position: absolute;
            top: -74px;
            right: -54px;
            width: 174px;
            height: 174px;
            z-index: -1;
            border-radius: 999px;
            background: rgba(247, 181, 0, .13);
            transition: transform .45s cubic-bezier(.2, .9, .22, 1), background .35s ease, filter .35s ease;
        }

        .home-page .program-card-glow {
            display: block !important;
            position: absolute;
            inset: 0;
            z-index: 4;
            height: auto;
            border-radius: inherit;
            pointer-events: none;
            opacity: 0;
            transform: translateX(-125%) skewX(-18deg);
            background: linear-gradient(115deg, transparent 0%, rgba(255, 255, 255, .16) 38%, rgba(255, 255, 255, .72) 50%, rgba(247, 181, 0, .20) 58%, transparent 72%);
            transition: transform .75s ease, opacity .3s ease;
        }

        .home-page .program-tag {
            display: none !important;
        }

        .home-page .program-card:hover {
            transform: translateY(-16px) rotateX(2deg) rotateY(-2deg) scale(1.018);
            border-color: rgba(247, 181, 0, .62);
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 34px 76px rgba(7, 43, 87, .18), 0 0 0 7px rgba(247, 181, 0, .08);
        }

        .home-page .program-card:hover::before {
            transform: scale(1.04);
        }

        .home-page .program-card:hover::after {
            transform: scale(1.22) translate(-8px, 10px);
            background: rgba(247, 181, 0, .24);
            filter: blur(.2px);
        }

        .home-page .program-card:hover .program-card-glow {
            opacity: 1;
            transform: translateX(125%) skewX(-18deg);
        }

        .home-page .program-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .home-page .program-icon {
            width: 74px;
            height: 74px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            color: #ffffff;
            background: linear-gradient(135deg, #003f78 0%, #075895 100%);
            box-shadow: 0 18px 32px rgba(7, 43, 87, .20);
            transform: translateZ(0) rotate(0deg);
            transition: transform .35s ease, box-shadow .35s ease, background .35s ease;
        }

        .home-page .program-icon svg {
            width: 36px;
            height: 36px;
            fill: currentColor;
            transform-origin: center;
            transition: transform .35s ease;
        }

        .home-page .program-card:hover .program-icon {
            animation: programIconPop .68s cubic-bezier(.2, .9, .22, 1) both;
            background: linear-gradient(135deg, #f7b500 0%, #ffcc35 100%);
            box-shadow: 0 22px 40px rgba(247, 181, 0, .34);
        }

        .home-page .program-card:hover .program-icon svg {
            animation: programIconWiggle .72s ease both;
        }

        .home-page .program-number {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #072b57;
            background: #fff3c4;
            border: 1px solid rgba(247, 181, 0, .42);
            font-size: 14px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: .04em;
            box-shadow: 0 10px 24px rgba(247, 181, 0, .13);
            transition: transform .35s ease, box-shadow .35s ease, background .35s ease;
        }

        .home-page .program-card:hover .program-number {
            animation: programNumberPulse 1.15s ease-in-out infinite;
            background: #ffe08a;
            box-shadow: 0 0 0 7px rgba(247, 181, 0, .13), 0 16px 28px rgba(247, 181, 0, .22);
        }

        .home-page .program-body {
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .home-page .program-title {
            max-width: 250px;
            margin: 0 0 16px;
            color: #072b57;
            font-size: clamp(20px, 1.65vw, 25px);
            line-height: 1.18;
            font-weight: 900;
            letter-spacing: -.025em;
            text-transform: uppercase;
            transition: transform .32s ease, color .32s ease;
        }

        .home-page .program-desc {
            max-width: 270px;
            margin: 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.74;
            font-weight: 600;
            transition: transform .32s ease, color .32s ease;
        }

        .home-page .program-card:hover .program-title {
            color: #075895;
            transform: translateX(5px);
        }

        .home-page .program-card:hover .program-desc {
            color: #475569;
            transform: translateX(5px);
        }

        .home-page .program-detail {
            position: relative;
            z-index: 5;
            width: fit-content;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 26px;
            padding: 10px 20px;
            border-radius: 999px;
            color: #072b57;
            background: #ffffff;
            border: 1px solid #dbe3ee;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
            font-size: 13px;
            line-height: 1;
            font-weight: 900;
            text-transform: none;
            transition: transform .28s ease, color .28s ease, background .28s ease, border-color .28s ease, box-shadow .28s ease;
        }

        .home-page .program-detail::before {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -45%;
            width: 34%;
            transform: skewX(-18deg);
            background: rgba(255, 255, 255, .45);
            opacity: 0;
            transition: left .5s ease, opacity .25s ease;
        }

        .home-page .program-detail span,
        .home-page .program-detail i {
            position: relative;
            z-index: 1;
        }

        .home-page .program-detail i {
            color: #f7b500;
            transition: transform .28s ease, color .28s ease;
        }

        .home-page .program-detail:hover {
            transform: translateY(-3px) scale(1.04);
            color: #ffffff;
            background: #f7b500;
            border-color: #f7b500;
            box-shadow: 0 16px 30px rgba(247, 181, 0, .28);
        }

        .home-page .program-detail:hover::before {
            left: 118%;
            opacity: 1;
        }

        .home-page .program-detail:hover i {
            color: #ffffff;
            transform: translateX(5px);
        }

        @keyframes programIconPop {
            0% { transform: translateY(0) rotate(0deg) scale(1); }
            45% { transform: translateY(-7px) rotate(-5deg) scale(1.08); }
            100% { transform: translateY(-3px) rotate(0deg) scale(1.04); }
        }

        @keyframes programIconWiggle {
            0% { transform: rotate(0deg) scale(1); }
            28% { transform: rotate(-8deg) scale(1.04); }
            58% { transform: rotate(7deg) scale(1.04); }
            100% { transform: rotate(0deg) scale(1.02); }
        }

        @keyframes programNumberPulse {
            0%, 100% { transform: translateY(-3px) scale(1.04); }
            50% { transform: translateY(-7px) scale(1.12); }
        }

        @media (max-width: 1100px) {
            .home-page .program-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 640px) {
            .home-page .program-section {
                padding: 46px 0 58px;
            }

            .home-page .program-kicker {
                padding: 10px 16px;
                font-size: 11px;
            }

            .home-page .program-head h2 {
                font-size: 32px;
            }

            .home-page .program-head p {
                font-size: 14px;
            }

            .home-page .program-grid {
                grid-template-columns: 1fr !important;
                gap: 18px !important;
            }

            .home-page .program-card {
                min-height: 0;
                padding: 28px 24px 24px;
                border-radius: 0 0 24px 24px;
            }

            .home-page .program-icon {
                width: 64px;
                height: 64px;
            }

            .home-page .program-title,
            .home-page .program-desc {
                max-width: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .home-page .program-card,
            .home-page .program-icon,
            .home-page .program-icon svg,
            .home-page .program-number,
            .home-page .program-detail,
            .home-page .program-card-glow {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <section class="hero">
        @if (isset($sliders) && $sliders->count() > 0)
            @foreach ($sliders as $index => $slider)
                <div class="hero-slide {{ $index === 0 ? 'active' : '' }}"
                    style="background-image: url('{{ route('sliders.image', $slider->id) }}');"
                    data-duration="{{ $slider->duration_ms ?? 3000 }}">
                </div>
            @endforeach
        @else
            <div class="hero-slide active"
                style="background-image: url('{{ asset('assets/images/hero-campus.png') }}');" data-duration="3000">
            </div>
        @endif

        <button class="hero-arrow left" id="prevSlide" type="button" aria-label="Slide sebelumnya">‹</button>
        <button class="hero-arrow right" id="nextSlide" type="button" aria-label="Slide berikutnya">›</button>

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

                    <div class="category-filters">
                        <button class="cat-pill active" type="button">Semua</button>
                        <button class="cat-pill" type="button">Umum</button>
                        <button class="cat-pill" type="button">Kemahasiswaan</button>
                        <button class="cat-pill" type="button">Akademik</button>
                        <button class="cat-pill" type="button">PMB</button>
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
                            <svg viewBox="0 0 24 24"><path d="M10 17v-3H3v-4h7V7l5 5-5 5ZM12 3h8a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8v-2h7V5h-7V3Z" /></svg>
                            <span>Login<br>Mahasiswa</span>
                        </button>

                        <div class="edom-card-wrapper" id="edomCardWrapper">
                            <button class="service-card" type="button" id="edomService">
                                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm-1 7V3.5L18.5 9H13ZM8 13h8v2H8v-2Zm0 4h8v2H8v-2Zm0-8h3v2H8V9Z" /></svg>
                                <span>EDOM</span>
                            </button>

                            <div class="edom-popover">
                                <h4>Pilih Mata Kuliah :</h4>
                                <p>Kejelasan Penyampaian Materi Dosen?</p>
                                <div class="small-label">Kejelasan Penyampaian Materi Dosen?</div>
                                <div class="edom-score"><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span></div>
                            </div>
                        </div>

                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24"><path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" /></svg>
                            <span>E-Learning</span>
                        </button>

                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24"><path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" /></svg>
                            <span>Perpustakaan<br>Digital</span>
                        </button>

                        <button class="service-card" type="button">
                            <svg viewBox="0 0 24 24"><path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm0 14.2L5 13.4V17l7 4 7-4v-3.6l-7 3.8Z" /></svg>
                            <span>E-Learning</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
