<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visi, Misi - Pascasarjana UNW</title>

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
            background:
                radial-gradient(circle at 8% 12%, rgba(45, 156, 219, .08), transparent 25%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 44%, #eef5fb 100%);
            color: var(--text);
        }

        a {
            color: inherit;
        }

        .vm-container {
            width: min(100% - 64px, 1180px);
            margin: 0 auto;
        }

        /* ================= HERO ================= */

        .page-hero {
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

        .page-hero::before {
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

        .page-hero::after {
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

        .page-title {
            margin: 0 0 18px;
            color: #fff;
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -1.1px;
        }

        .page-desc {
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

        .visi-misi-section {
            position: relative;
            z-index: 5;
            margin-top: -58px;
            padding: 0 0 90px;
        }

        .visi-misi-wrapper {
            display: grid;
            gap: 22px;
        }

        .visi-misi-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 22px;
        }

        /* ================= CARD ================= */

        .visi-misi-card {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 92px 1fr;
            gap: 0;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: var(--shadow-lg);
            transition: .26s ease;
        }

        .visi-misi-card:hover {
            transform: translateY(-5px);
            border-color: rgba(45, 156, 219, .35);
            box-shadow: var(--shadow-md);
        }

        .card-side {
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 28px 16px;
            background:
                radial-gradient(circle at 30% 18%, rgba(247, 181, 0, .16), transparent 32%),
                linear-gradient(180deg, var(--primary), var(--blue));
        }

        .card-side::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 92px;
            bottom: 28px;
            width: 2px;
            transform: translateX(-50%);
            border-radius: 999px;
            background: rgba(255, 255, 255, .24);
        }

        .card-number {
            position: relative;
            z-index: 2;
            width: 50px;
            height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            color: var(--primary);
            background: var(--yellow);
            box-shadow: 0 14px 28px rgba(0, 0, 0, .18);
            font-size: 15px;
            font-weight: 900;
        }

        .card-main {
            position: relative;
            padding: 30px 34px 34px;
        }

        .card-main::after {
            content: "";
            position: absolute;
            right: -52px;
            top: -52px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(45, 156, 219, .08);
            pointer-events: none;
        }

        .card-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 17px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            box-shadow: 0 12px 26px rgba(6, 47, 95, .20);
        }

        .card-header h2 {
            position: relative;
            margin: 0;
            color: var(--primary);
            font-size: clamp(22px, 2.4vw, 30px);
            line-height: 1.22;
            font-weight: 900;
            letter-spacing: -.4px;
        }

        .card-content {
            position: relative;
            z-index: 1;
            color: var(--text-soft);
            font-size: 16px;
            line-height: 1.9;
            word-break: break-word;
        }

        .card-content > *:first-child {
            margin-top: 0 !important;
        }

        .card-content > *:last-child {
            margin-bottom: 0 !important;
        }

        .card-content p {
            margin: 0 0 18px;
            color: var(--text-soft);
        }

        .card-content strong,
        .card-content b {
            color: var(--primary);
            font-weight: 900;
        }

        .card-content em {
            color: var(--muted);
        }

        .card-content h1,
        .card-content h2,
        .card-content h3,
        .card-content h4,
        .card-content h5,
        .card-content h6 {
            margin: 26px 0 14px;
            color: var(--primary);
            line-height: 1.28;
            font-weight: 900;
        }

        .card-content ul,
        .card-content ol {
            margin: 12px 0 22px;
            padding-left: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .card-content li {
            position: relative;
            padding: 13px 15px 13px 48px;
            border-radius: 17px;
            color: var(--text-soft);
            background: #f8fafc;
            border: 1px solid #eef2f7;
            line-height: 1.7;
        }

        .card-content ul li::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 15px;
            top: 14px;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            font-size: 11px;
        }

        .card-content ol {
            counter-reset: vm-counter;
        }

        .card-content ol li {
            counter-increment: vm-counter;
        }

        .card-content ol li::before {
            content: counter(vm-counter);
            position: absolute;
            left: 15px;
            top: 14px;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: var(--primary);
            background: var(--yellow);
            font-size: 12px;
            font-weight: 900;
        }

        .card-content table {
            width: 100% !important;
            margin: 24px 0 !important;
            border-collapse: collapse !important;
            overflow: hidden;
            border-radius: 18px;
            background: #fff;
            box-shadow: var(--shadow-sm);
        }

        .card-content table th {
            color: #fff !important;
            background: linear-gradient(135deg, var(--primary), var(--blue)) !important;
            font-weight: 900 !important;
            text-align: left;
        }

        .card-content table th,
        .card-content table td {
            padding: 14px 16px !important;
            border: 1px solid var(--border) !important;
            color: var(--text-soft);
            vertical-align: top;
        }

        .card-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 24px auto;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
        }

        /* ================= EMPTY ================= */

        .empty-state-card {
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            gap: 13px;
            padding: 48px 24px;
            border-radius: 30px;
            background: #fff;
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: var(--shadow-lg);
        }

        .empty-state-icon {
            width: 66px;
            height: 66px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            color: var(--primary);
            background: rgba(6, 47, 95, .08);
            font-size: 27px;
        }

        .empty-state-card h3 {
            margin: 0;
            color: var(--primary);
            font-size: 22px;
            font-weight: 900;
        }

        .empty-state-card p {
            max-width: 520px;
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            font-weight: 600;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 768px) {
            .vm-container {
                width: min(100% - 28px, 1180px);
            }

            .page-hero {
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

            .page-title {
                font-size: 32px;
                letter-spacing: -.6px;
            }

            .page-desc {
                font-size: 15px;
            }

            .hero-meta span {
                font-size: 12px;
                padding: 8px 10px;
            }

            .visi-misi-section {
                margin-top: -48px;
                padding-bottom: 70px;
            }

            .visi-misi-card {
                grid-template-columns: 1fr;
                border-radius: 24px;
            }

            .card-side {
                justify-content: flex-start;
                padding: 18px 22px;
            }

            .card-side::after {
                display: none;
            }

            .card-number {
                width: 44px;
                height: 44px;
                border-radius: 15px;
            }

            .card-main {
                padding: 24px 22px 28px;
            }

            .card-header {
                align-items: flex-start;
            }

            .card-icon {
                width: 42px;
                height: 42px;
                flex-basis: 42px;
                border-radius: 15px;
            }

            .card-content {
                font-size: 15px;
                line-height: 1.82;
            }

            .card-content li {
                padding: 12px 14px 12px 44px;
            }

            .card-content table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        @media(max-width: 480px) {
            .page-hero {
                min-height: 292px;
            }

            .page-title {
                font-size: 30px;
            }

            .hero-meta {
                gap: 8px;
            }

            .card-main {
                padding: 22px 18px 26px;
            }

            .card-header h2 {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>

    @include('component.header')

    <section class="page-hero">
        <div class="hero-dots"></div>
        <div class="hero-line"></div>

        <div class="vm-container">
            <div class="hero-inner">
                <div class="hero-kicker">
                    <i class="fas fa-bullseye"></i>
                    <span>Profil Pascasarjana</span>
                </div>

                <h1 class="page-title">Visi & Misi</h1>

                <p class="page-desc">
                    Arah, tujuan, dan komitmen Pascasarjana Universitas Ngudi Waluyo dalam pengembangan pendidikan,
                    penelitian, dan pengabdian kepada masyarakat.
                </p>

                <div class="hero-meta">
                    <span>
                        <i class="fas fa-university"></i>
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

    <main class="visi-misi-section">
        <div class="vm-container">
            <div class="visi-misi-wrapper">

                @if($visiMisi)
                    @php
                        $sections = [
                            [
                                'number' => '01',
                                'icon' => 'fas fa-eye',
                                'title' => 'Visi',
                                'content' => $visiMisi->visi ?? '<p><em>Belum ada konten visi. Silakan isi melalui Admin Panel.</em></p>',
                            ],
                            [
                                'number' => '02',
                                'icon' => 'fas fa-list-check',
                                'title' => 'Misi',
                                'content' => $visiMisi->misi ?? '<p><em>Belum ada konten misi. Silakan isi melalui Admin Panel.</em></p>',
                            ],
                            [
                                'number' => '03',
                                'icon' => 'fas fa-bullseye',
                                'title' => $visiMisi->judul_tujuan ?? 'Tujuan',
                                'content' => $visiMisi->tujuan ?? '<p><em>Belum ada konten tujuan. Silakan isi melalui Admin Panel.</em></p>',
                            ],
                            [
                                'number' => '04',
                                'icon' => 'fas fa-layer-group',
                                'title' => $visiMisi->judul_tujuan_bidang ?? 'Tujuan UNW Dalam Bidang',
                                'content' => $visiMisi->tujuan_bidang ?? '<p><em>Belum ada konten tujuan dalam bidang. Silakan isi melalui Admin Panel.</em></p>',
                            ],
                            [
                                'number' => '05',
                                'icon' => 'fas fa-chart-line',
                                'title' => $visiMisi->judul_sasaran_target ?? 'Sasaran dan Target',
                                'content' => $visiMisi->sasaran_target ?? '<p><em>Belum ada konten sasaran dan target. Silakan isi melalui Admin Panel.</em></p>',
                            ],
                        ];
                    @endphp

                    <div class="visi-misi-grid">
                        @foreach($sections as $section)
                            <section class="visi-misi-card">
                                <div class="card-side">
                                    <div class="card-number">{{ $section['number'] }}</div>
                                </div>

                                <div class="card-main">
                                    <div class="card-header">
                                        <div class="card-icon">
                                            <i class="{{ $section['icon'] }}"></i>
                                        </div>

                                        <h2>{{ $section['title'] }}</h2>
                                    </div>

                                    <div class="card-content">
                                        {!! $section['content'] !!}
                                    </div>
                                </div>
                            </section>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-card">
                        <div class="empty-state-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>

                        <h3>Data Visi & Misi Belum Diisi</h3>

                        <p>
                            Silakan login ke Admin Panel untuk mengisi data visi, misi, tujuan, dan sasaran Pascasarjana.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </main>

    @include('component.footer')

</body>

</html>