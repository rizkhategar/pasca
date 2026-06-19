<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Pascasarjana | Universitas Ngudi Waluyo</title>

    <link rel="icon" href="{{ asset('logo_unwnobg.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #062f5f;
            --primary-dark: #031f42;
            --primary-soft: #07457d;
            --blue: #0b5f9f;
            --blue-light: #2d9cdb;
            --yellow: #f7b500;

            --white: #ffffff;
            --bg: #f6f9fc;
            --text: #0f172a;
            --text-soft: #334155;
            --muted: #64748b;
            --border: #e2e8f0;

            --shadow-sm: 0 10px 28px rgba(15, 23, 42, .07);
            --shadow-md: 0 18px 46px rgba(15, 23, 42, .10);
            --shadow-lg: 0 28px 70px rgba(15, 23, 42, .15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Montserrat', Arial, Helvetica, sans-serif;
            color: var(--text);
            overflow-x: hidden;
            background:
                radial-gradient(circle at 8% 12%, rgba(45, 156, 219, .08), transparent 25%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 44%, #eef5fb 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .about-container {
            width: min(100% - 64px, 1180px);
            margin: 0 auto;
        }

        /* ================= HERO ================= */

        .about-hero {
            position: relative;
            overflow: hidden;
            color: #fff;
            min-height: 330px;
            display: flex;
            align-items: center;
            padding: 58px 0 110px;
            background:
                radial-gradient(circle at 13% 18%, rgba(45, 156, 219, .42), transparent 26%),
                radial-gradient(circle at 82% 18%, rgba(255, 255, 255, .18), transparent 23%),
                linear-gradient(135deg, #031f42 0%, #062f5f 46%, #07457d 75%, #0b6eae 100%);
        }

        .about-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .055) 1px, transparent 1px);
            background-size: 46px 46px;
            opacity: .42;
            mask-image: linear-gradient(90deg, #000 0%, transparent 84%);
            z-index: 1;
        }

        .about-hero::after {
            content: "";
            position: absolute;
            right: -170px;
            top: -185px;
            width: 570px;
            height: 570px;
            border-radius: 50%;
            background:
                radial-gradient(circle, rgba(255, 255, 255, .20) 0%, rgba(45, 156, 219, .16) 36%, transparent 68%);
            z-index: 1;
        }

        .hero-dots {
            position: absolute;
            left: 24px;
            top: 18px;
            width: 120px;
            height: 92px;
            background-image: radial-gradient(rgba(255, 255, 255, .72) 2px, transparent 2.6px);
            background-size: 18px 18px;
            opacity: .48;
            z-index: 2;
        }

        .hero-line {
            position: absolute;
            right: 110px;
            top: -36px;
            width: 230px;
            height: 440px;
            background: linear-gradient(120deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .18));
            transform: skewX(-32deg);
            z-index: 1;
        }

        .hero-wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            width: 100%;
            height: 92px;
            z-index: 3;
            pointer-events: none;
        }

        .hero-wave svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .hero-inner {
            position: relative;
            z-index: 4;
            max-width: 900px;
        }

        .hero-kicker {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            margin-bottom: 18px;
            border-radius: 999px;
            color: #ffe8a1;
            background: rgba(247, 181, 0, .12);
            border: 1px solid rgba(247, 181, 0, .34);
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .7px;
            backdrop-filter: blur(10px);
        }

        .about-title {
            margin: 0 0 18px;
            color: #fff;
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -1.1px;
        }

        .about-subtitle {
            max-width: 760px;
            margin: 0;
            color: rgba(255, 255, 255, .86);
            font-size: clamp(15px, 2vw, 18px);
            line-height: 1.75;
            font-weight: 600;
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .hero-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            color: rgba(255, 255, 255, .88);
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .16);
            font-size: 13px;
            font-weight: 800;
            backdrop-filter: blur(10px);
        }

        .hero-meta i {
            color: var(--yellow);
        }

        /* ================= MAIN ================= */

        .about-section {
            position: relative;
            z-index: 5;
            margin-top: -58px;
            padding: 0 0 90px;
        }

        .about-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
            gap: 26px;
            align-items: start;
        }

        .about-main-card,
        .about-points-card,
        .sambutan-card,
        .empty-card {
            background: rgba(255, 255, 255, .97);
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: var(--shadow-lg);
        }

        .about-main-card {
            position: relative;
            overflow: hidden;
            min-height: 100%;
            padding: 34px;
            border-radius: 30px;
        }

        .about-main-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 12% 10%, rgba(45, 156, 219, .08), transparent 25%),
                radial-gradient(circle at 92% 6%, rgba(247, 181, 0, .08), transparent 20%);
            pointer-events: none;
        }

        .section-kicker {
            position: relative;
            z-index: 1;
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 16px;
            padding: 9px 13px;
            border-radius: 999px;
            color: var(--primary);
            background: #f8fafc;
            border: 1px solid #eef2f7;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .7px;
        }

        .section-kicker i {
            color: var(--yellow);
        }

        .about-main-card h2 {
            position: relative;
            z-index: 1;
            margin: 0 0 18px;
            color: var(--primary);
            font-size: clamp(24px, 3vw, 38px);
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: -.7px;
        }

        .about-desc {
            position: relative;
            z-index: 1;
            color: var(--text-soft);
            font-size: 16px;
            line-height: 1.9;
            font-weight: 500;
            text-align: justify;
        }

        .about-desc p {
            margin-bottom: 16px;
        }

        .about-desc p:last-child {
            margin-bottom: 0;
        }

        .about-desc strong,
        .about-desc b {
            color: var(--primary);
            font-weight: 900;
        }

        .about-desc ul,
        .about-desc ol {
            margin: 18px 0;
            padding-left: 22px;
        }

        .about-desc li {
            margin-bottom: 8px;
        }

        /* ================= POINTS ================= */

        .about-points-card {
            overflow: hidden;
            border-radius: 30px;
        }

        .points-header {
            padding: 24px 26px;
            background:
                linear-gradient(135deg, rgba(6, 47, 95, .08), rgba(45, 156, 219, .07)),
                #ffffff;
            border-bottom: 1px solid rgba(226, 232, 240, .95);
        }

        .points-header h3 {
            margin: 0 0 7px;
            color: var(--primary);
            font-size: 22px;
            line-height: 1.25;
            font-weight: 900;
        }

        .points-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .about-points {
            display: grid;
            gap: 14px;
            padding: 22px;
        }

        .point-card {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 54px 1fr;
            align-items: start;
            gap: 16px;
            padding: 18px;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid #eef2f7;
            box-shadow: var(--shadow-sm);
            transition: .24s ease;
        }

        .point-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary), var(--blue-light));
        }

        .point-card:hover {
            transform: translateY(-4px);
            border-color: rgba(45, 156, 219, .35);
            box-shadow: var(--shadow-md);
        }

        .point-icon {
            position: relative;
            z-index: 1;
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            box-shadow: 0 12px 26px rgba(6, 47, 95, .20);
            font-size: 22px;
        }

        .point-icon img {
            width: 30px;
            height: 30px;
            object-fit: contain;
            display: block;
        }

        .point-text {
            position: relative;
            z-index: 1;
            min-width: 0;
        }

        .point-text h3 {
            margin: 0 0 7px;
            color: var(--primary);
            font-size: 17px;
            line-height: 1.3;
            font-weight: 900;
        }

        .point-text p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
            font-weight: 600;
        }

        .empty-points {
            padding: 24px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            font-weight: 600;
        }

        /* ================= SAMBUTAN ================= */

        .sambutan-section {
            margin-top: 34px;
        }

        .sambutan-title {
            max-width: 820px;
            margin: 0 auto 24px;
            text-align: center;
        }

        .sambutan-title .section-kicker {
            margin-left: auto;
            margin-right: auto;
        }

        .sambutan-title h2 {
            margin: 0;
            color: var(--primary);
            font-size: clamp(24px, 3.2vw, 38px);
            line-height: 1.25;
            font-weight: 900;
            letter-spacing: -.7px;
        }

        .sambutan-card {
            overflow: hidden;
            display: grid;
            grid-template-columns: 390px 1fr;
            border-radius: 32px;
        }

        .sambutan-img {
            position: relative;
            min-height: 470px;
            background:
                radial-gradient(circle at 50% 22%, rgba(45, 156, 219, .18), transparent 35%),
                linear-gradient(135deg, var(--primary-dark), var(--primary));
        }

        .sambutan-img::after {
            content: "";
            position: absolute;
            inset: auto 0 0;
            height: 46%;
            background: linear-gradient(180deg, transparent, rgba(3, 31, 66, .28));
            pointer-events: none;
        }

        .sambutan-img img {
            width: 100%;
            height: 100%;
            min-height: 470px;
            display: block;
            object-fit: cover;
            object-position: top center;
        }

        .director-placeholder {
            width: 100%;
            height: 100%;
            min-height: 470px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .52);
            font-size: 82px;
        }

        .sambutan-content {
            position: relative;
            padding: 46px 48px;
            background:
                radial-gradient(circle at 94% 10%, rgba(45, 156, 219, .08), transparent 24%),
                linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .quote-icon {
            width: 58px;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            border-radius: 20px;
            color: var(--primary);
            background: rgba(6, 47, 95, .08);
            font-size: 24px;
        }

        .sambutan-text {
            color: var(--text-soft);
            font-size: 16px;
            line-height: 1.9;
            font-weight: 500;
            margin-bottom: 30px;
        }

        .sambutan-text p {
            margin-bottom: 16px;
        }

        .sambutan-text p:last-child {
            margin-bottom: 0;
        }

        .direktur-info {
            position: relative;
            padding: 20px 22px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid #eef2f7;
            box-shadow: var(--shadow-sm);
        }

        .direktur-info::before {
            content: "";
            position: absolute;
            inset: 18px auto 18px 0;
            width: 4px;
            border-radius: 99px;
            background: var(--yellow);
        }

        .direktur-info h4 {
            margin: 0 0 7px;
            color: var(--primary);
            font-size: 22px;
            line-height: 1.25;
            font-weight: 900;
            letter-spacing: -.4px;
        }

        .direktur-info p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        /* ================= EMPTY ================= */

        .empty-card {
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            gap: 13px;
            padding: 52px 24px;
            border-radius: 30px;
        }

        .empty-icon {
            width: 70px;
            height: 70px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            color: var(--primary);
            background: rgba(6, 47, 95, .08);
            font-size: 30px;
        }

        .empty-card h3 {
            margin: 0;
            color: var(--primary);
            font-size: 23px;
            line-height: 1.3;
            font-weight: 900;
        }

        .empty-card p {
            max-width: 560px;
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            font-weight: 600;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 992px) {
            .about-container {
                width: min(100% - 32px, 1180px);
            }

            .about-layout {
                grid-template-columns: 1fr;
            }

            .sambutan-card {
                grid-template-columns: 1fr;
            }

            .sambutan-img,
            .sambutan-img img,
            .director-placeholder {
                min-height: 420px;
            }
        }

        @media(max-width: 768px) {
            .about-container {
                width: min(100% - 28px, 1180px);
            }

            .about-hero {
                min-height: 305px;
                padding: 48px 0 96px;
            }

            .hero-dots {
                left: 14px;
                top: 12px;
                width: 90px;
                height: 70px;
                background-size: 16px 16px;
                opacity: .34;
            }

            .hero-line {
                display: none;
            }

            .hero-kicker {
                font-size: 12px;
                padding: 8px 12px;
            }

            .about-title {
                font-size: 32px;
                letter-spacing: -.6px;
            }

            .about-subtitle {
                font-size: 15px;
            }

            .hero-meta span {
                font-size: 12px;
                padding: 8px 10px;
            }

            .about-section {
                margin-top: -48px;
                padding-bottom: 70px;
            }

            .about-main-card,
            .about-points-card,
            .sambutan-card,
            .empty-card {
                border-radius: 24px;
            }

            .about-main-card {
                padding: 24px 22px;
            }

            .about-desc {
                font-size: 15px;
                line-height: 1.82;
                text-align: left;
            }

            .points-header {
                padding: 22px;
            }

            .about-points {
                padding: 18px;
            }

            .point-card {
                grid-template-columns: 48px 1fr;
                gap: 14px;
                padding: 16px;
                border-radius: 18px;
            }

            .point-icon {
                width: 48px;
                height: 48px;
                border-radius: 16px;
                font-size: 20px;
            }

            .point-text h3 {
                font-size: 16px;
            }

            .point-text p {
                font-size: 13px;
            }

            .sambutan-title {
                text-align: left;
            }

            .sambutan-title .section-kicker {
                margin-left: 0;
                margin-right: 0;
            }

            .sambutan-title h2 {
                font-size: 25px;
            }

            .sambutan-img,
            .sambutan-img img,
            .director-placeholder {
                min-height: 380px;
            }

            .sambutan-content {
                padding: 28px 22px;
            }

            .sambutan-text {
                font-size: 15px;
                line-height: 1.82;
            }

            .direktur-info h4 {
                font-size: 19px;
            }
        }

        @media(max-width: 480px) {
            .about-hero {
                min-height: 292px;
            }

            .about-title {
                font-size: 30px;
            }

            .about-main-card {
                padding: 22px 18px;
            }

            .about-points {
                padding: 16px;
            }

            .point-card {
                grid-template-columns: 1fr;
            }

            .sambutan-img,
            .sambutan-img img,
            .director-placeholder {
                min-height: 330px;
            }
        }
    </style>
</head>

<body>

    @include('component.header')

    <section class="about-hero">
        <div class="hero-dots"></div>
        <div class="hero-line"></div>

        <div class="about-container">
            <div class="hero-inner">
                <div class="hero-kicker">
                    <i class="fas fa-university"></i>
                    <span>Profil Pascasarjana</span>
                </div>

                <h1 class="about-title">Tentang Pascasarjana</h1>

                <p class="about-subtitle">
                    Mengenal lebih dekat profil, arah pengembangan, dan komitmen Pascasarjana Universitas Ngudi Waluyo.
                </p>

                <div class="hero-meta">
                    <span>
                        <i class="fas fa-building-columns"></i>
                        Universitas Ngudi Waluyo
                    </span>

                    <span>
                        <i class="fas fa-graduation-cap"></i>
                        Pascasarjana
                    </span>
                </div>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <main class="about-section">
        <div class="about-container">

            @if($tentang)
                <section class="about-layout">
                    <article class="about-main-card">
                        <div class="section-kicker">
                            <i class="fas fa-circle-info"></i>
                            <span>{{ $tentang->subheading ?? 'Tentang Kami' }}</span>
                        </div>

                        <h2>{{ $tentang->heading ?? 'Tentang Pascasarjana Universitas Ngudi Waluyo' }}</h2>

                        <div class="about-desc">
                            {!! nl2br(e($tentang->description ?? '')) !!}
                        </div>
                    </article>

                    <aside class="about-points-card">
                        <div class="points-header">
                            <h3>Keunggulan Pascasarjana</h3>
                            <p>Informasi utama yang menjadi identitas dan nilai unggulan Pascasarjana UNW.</p>
                        </div>

                        @if(!empty($tentang->points) && is_array($tentang->points))
                            <div class="about-points">
                                @foreach($tentang->points as $point)
                                    <article class="point-card">
                                        <div class="point-icon">
                                            @if(!empty($point['icon']))
                                                <img src="{{ asset('storage/' . $point['icon']) }}" alt="Icon {{ $point['title'] ?? 'Poin Pascasarjana' }}">
                                            @else
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </div>

                                        <div class="point-text">
                                            <h3>{{ $point['title'] ?? 'Poin Unggulan' }}</h3>
                                            <p>{{ $point['description'] ?? '' }}</p>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-points">
                                <em>Belum ada poin fitur yang ditambahkan.</em>
                            </div>
                        @endif
                    </aside>
                </section>

                @if(!empty($tentang->direktur_name) || !empty($tentang->direktur_message))
                    <section class="sambutan-section">
                        <div class="sambutan-title">
                            <div class="section-kicker">
                                <i class="fas fa-comment-dots"></i>
                                <span>{{ $tentang->direktur_heading ?? 'Sambutan Direktur' }}</span>
                            </div>

                            <h2>{{ $tentang->direktur_greeting ?? 'Selamat Datang di Pascasarjana Universitas Ngudi Waluyo' }}</h2>
                        </div>

                        <article class="sambutan-card">
                            <div class="sambutan-img">
                                @if(!empty($tentang->direktur_image))
                                    <img src="{{ asset('storage/' . $tentang->direktur_image) }}" alt="{{ $tentang->direktur_name ?? 'Direktur Pascasarjana' }}">
                                @else
                                    <div class="director-placeholder">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="sambutan-content">
                                <div class="quote-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>

                                <div class="sambutan-text">
                                    {!! $tentang->direktur_message ?? '<p>Pesan sambutan pimpinan belum ditambahkan.</p>' !!}
                                </div>

                                <div class="direktur-info">
                                    <h4>{{ $tentang->direktur_name ?? 'Nama Direktur Belum Diisi' }}</h4>
                                    <p>{{ $tentang->direktur_title ?? 'Direktur Pascasarjana Universitas Ngudi Waluyo' }}</p>
                                </div>
                            </div>
                        </article>
                    </section>
                @endif
            @else
                <div class="empty-card">
                    <div class="empty-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>

                    <h3>Data Tentang Pascasarjana Belum Diisi</h3>

                </div>
            @endif

        </div>
    </main>

    @include('component.footer')

</body>

</html>