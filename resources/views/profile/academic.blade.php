<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        /*
            Data dari API.
            Aman untuk response:
            1. $program['data']
            2. $program langsung sudah berisi data
        */
        $dataProgram = isset($program['data']) ? $program['data'] : $program;

        $namaProgram = $dataProgram['unwProgramStudi']['nama'] ?? 'Program Akademik';

        $bodyContent = $dataProgram['body']
            ?? '<p class="empty-state">Konten belum tersedia untuk program ini.</p>';

        $createdAt = isset($dataProgram['createdAt'])
            ? \Carbon\Carbon::parse($dataProgram['createdAt'])->translatedFormat('d F Y')
            : null;
    @endphp

    <title>{{ $namaProgram }} - Pascasarjana UNW</title>

    <link rel="icon" href="{{ asset('logo_unwnobg.png') }}" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #062f5f;
            --primary-dark: #03234a;
            --primary-soft: #07457d;
            --blue: #0b5f9f;
            --blue-light: #2d9cdb;
            --accent: #f7b500;

            --white: #ffffff;
            --text: #0f172a;
            --text-soft: #334155;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;

            --shadow-sm: 0 10px 30px rgba(15, 23, 42, .08);
            --shadow-md: 0 22px 60px rgba(15, 23, 42, .12);
            --shadow-blue: 0 24px 70px rgba(6, 47, 95, .24);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 8% 12%, rgba(45, 156, 219, .08), transparent 26%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 44%, #eef5fb 100%);
        }

        a {
            color: inherit;
        }

        .container {
            width: min(100% - 64px, 1180px);
            margin: 0 auto;
        }

        /* ================= HERO ================= */

        .page-hero {
            position: relative;
            overflow: hidden;
            color: #fff;
            min-height: 315px;
            display: flex;
            align-items: center;
            padding: 58px 0 98px;
            background:
                radial-gradient(circle at 14% 18%, rgba(45, 156, 219, .42), transparent 25%),
                radial-gradient(circle at 78% 15%, rgba(255, 255, 255, .18), transparent 22%),
                linear-gradient(135deg, #031f42 0%, #062f5f 45%, #07457d 74%, #0b6eae 100%);
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
            top: -190px;
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
            top: -35px;
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
            height: 90px;
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
            max-width: 960px;
        }

        .back-link {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            padding: 11px 16px;
            border-radius: 999px;
            color: #fff !important;
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .22);
            text-decoration: none;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .5px;
            text-transform: uppercase;
            backdrop-filter: blur(12px);
            transition: .24s ease;
        }

        .back-link:hover {
            transform: translateX(-5px);
            background: var(--accent);
            border-color: var(--accent);
            color: var(--primary) !important;
        }

        .category-pill {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 9px 15px;
            margin-bottom: 17px;
            border-radius: 999px;
            color: #ffe8a1;
            background: rgba(247, 181, 0, .12);
            border: 1px solid rgba(247, 181, 0, .34);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .title-page {
            max-width: 920px;
            margin: 0 0 18px;
            color: #fff;
            font-size: clamp(31px, 4.6vw, 54px);
            line-height: 1.06;
            font-weight: 900;
            letter-spacing: -1px;
        }

        .page-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .page-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            color: rgba(255, 255, 255, .88);
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .16);
            font-size: 14px;
            font-weight: 800;
            backdrop-filter: blur(10px);
        }

        .page-meta i {
            color: var(--accent);
        }

        /* ================= CONTENT ================= */

        .content-section {
            position: relative;
            z-index: 5;
            margin-top: -54px;
            padding: 0 0 90px;
        }

        .detail-shell {
            width: min(100% - 64px, 1120px);
            margin: 0 auto;
        }

        .content-card {
            overflow: hidden;
            border-radius: 30px;
            background: #fff;
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: var(--shadow-md);
        }

        .content-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px 28px;
            background:
                linear-gradient(135deg, rgba(6, 47, 95, .08), rgba(45, 156, 219, .08)),
                #ffffff;
            border-bottom: 1px solid rgba(226, 232, 240, .95);
        }

        .toolbar-title {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
        }

        .toolbar-icon {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            box-shadow: 0 12px 26px rgba(6, 47, 95, .22);
        }

        .toolbar-title h2 {
            margin: 0 0 4px;
            color: var(--text);
            font-size: 20px;
            line-height: 1.2;
            font-weight: 900;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .toolbar-title p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.4;
            font-weight: 600;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 15px;
            border-radius: 999px;
            border: 1px solid rgba(6, 47, 95, .14);
            background: #fff;
            color: var(--primary);
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition: .22s ease;
        }

        .toolbar-btn:hover {
            color: #fff;
            background: var(--primary);
            transform: translateY(-2px);
        }

        .article-body {
            padding: 50px 54px 66px;
        }

        .article-body-inner {
            max-width: 940px;
            margin: 0 auto;
        }

        /* ================= API HTML CONTENT ================= */

        .content-html {
            color: #1f2937;
            font-size: 17px;
            line-height: 1.9;
            word-break: break-word;
        }

        .content-html > *:first-child {
            margin-top: 0 !important;
        }

        .content-html > *:last-child {
            margin-bottom: 0 !important;
        }

        .content-html p {
            margin: 0 0 20px;
            color: #334155;
        }

        .content-html a {
            color: var(--blue);
            font-weight: 800;
            text-decoration: none;
            border-bottom: 1px solid rgba(11, 95, 159, .35);
        }

        .content-html a:hover {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .content-html h1,
        .content-html h2,
        .content-html h3,
        .content-html h4,
        .content-html h5,
        .content-html h6 {
            color: var(--primary);
            line-height: 1.25;
            font-weight: 900;
            letter-spacing: -.3px;
        }

        .content-html h1 {
            font-size: clamp(30px, 3.2vw, 42px);
            margin: 38px 0 18px;
        }

        .content-html h2 {
            font-size: clamp(26px, 2.7vw, 34px);
            margin: 36px 0 16px;
        }

        .content-html h3 {
            font-size: clamp(22px, 2.3vw, 28px);
            margin: 32px 0 14px;
        }

        .content-html h4 {
            font-size: 21px;
            margin: 28px 0 13px;
        }

        .content-html h4.heading-page {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 38px 0 22px;
            padding: 0 0 10px;
            color: var(--primary);
            font-size: 23px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .content-html h4.heading-page::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 82px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--accent), var(--blue));
        }

        .content-html ul,
        .content-html ol {
            margin: 0 0 24px;
            padding-left: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .content-html li {
            position: relative;
            padding: 12px 14px 12px 44px;
            border-radius: 16px;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            line-height: 1.65;
        }

        .content-html ul li::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 14px;
            top: 13px;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            font-size: 11px;
        }

        .content-html ol {
            counter-reset: custom-counter;
        }

        .content-html ol li {
            counter-increment: custom-counter;
        }

        .content-html ol li::before {
            content: counter(custom-counter);
            position: absolute;
            left: 14px;
            top: 13px;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            font-size: 12px;
            font-weight: 900;
        }

        .content-html blockquote {
            position: relative;
            margin: 28px 0;
            padding: 24px 26px 24px 68px;
            border-radius: 22px;
            color: #334155;
            background:
                linear-gradient(135deg, rgba(6, 47, 95, .07), rgba(247, 181, 0, .10)),
                #ffffff;
            border: 1px solid rgba(226, 232, 240, .9);
            box-shadow: 0 14px 34px rgba(15, 23, 42, .06);
        }

        .content-html blockquote::before {
            content: "\f10d";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 24px;
            top: 24px;
            color: var(--accent);
            font-size: 26px;
        }

        .content-html img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 28px auto;
            border: 0 !important;
            outline: 0 !important;
            border-radius: 22px !important;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .12) !important;
        }

        .content-html iframe {
            width: 100%;
            min-height: 420px;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
        }

        .content-html table {
            width: 100% !important;
            margin: 28px 0 !important;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            overflow: hidden;
            border-radius: 18px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
            background: #fff !important;
        }

        .content-html table th {
            color: #fff !important;
            background: linear-gradient(135deg, var(--primary), var(--blue)) !important;
            font-weight: 900 !important;
            text-align: left;
        }

        .content-html table th,
        .content-html table td {
            padding: 14px 16px !important;
            border: 1px solid #e2e8f0 !important;
            vertical-align: top;
            color: #334155;
            line-height: 1.55;
        }

        .content-html table tr:nth-child(even) td {
            background: #f8fafc !important;
        }

        .content-html table img {
            width: 100%;
            height: auto;
            margin: 0 !important;
            border-radius: 16px !important;
            box-shadow: none !important;
        }

        .content-html hr {
            margin: 34px 0;
            border: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cbd5e1, transparent);
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            font-style: italic;
            padding: 46px 18px;
            border-radius: 22px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 768px) {
            .container {
                width: min(100% - 28px, 1180px);
            }

            .page-hero {
                min-height: 300px;
                padding: 44px 0 88px;
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

            .back-link {
                margin-bottom: 18px;
                padding: 10px 14px;
                font-size: 12px;
            }

            .category-pill {
                font-size: 12px;
                padding: 8px 12px;
                margin-bottom: 14px;
            }

            .title-page {
                margin-bottom: 14px;
                letter-spacing: -.6px;
            }

            .page-meta span {
                font-size: 13px;
                padding: 8px 10px;
            }

            .content-section {
                margin-top: -46px;
                padding-bottom: 70px;
            }

            .detail-shell {
                width: min(100% - 24px, 1180px);
            }

            .content-card {
                border-radius: 22px;
            }

            .content-toolbar {
                align-items: flex-start;
                flex-direction: column;
                padding: 20px;
            }

            .toolbar-title h2 {
                white-space: normal;
            }

            .toolbar-actions {
                width: 100%;
            }

            .toolbar-btn {
                width: 100%;
            }

            .article-body {
                padding: 30px 20px 42px;
            }

            .content-html {
                font-size: 16px;
                line-height: 1.8;
            }

            .content-html h4.heading-page {
                font-size: 19px;
                margin: 30px 0 18px;
            }

            .content-html table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .content-html table th,
            .content-html table td {
                padding: 12px 14px !important;
            }

            .content-html iframe {
                min-height: 320px;
            }
        }

        @media(max-width: 480px) {
            .page-hero {
                min-height: 290px;
            }

            .title-page {
                font-size: 30px;
            }

            .toolbar-title {
                align-items: flex-start;
            }

            .toolbar-icon {
                width: 40px;
                height: 40px;
                flex-basis: 40px;
                border-radius: 14px;
            }

            .toolbar-title h2 {
                font-size: 18px;
            }

            .toolbar-title p {
                font-size: 13px;
            }

            .content-html li {
                padding: 11px 12px 11px 40px;
            }
        }
    </style>
</head>

<body>

    @include('component.header')

    <section class="page-hero">
        <div class="hero-dots"></div>
        <div class="hero-line"></div>

        <div class="container">
            <div class="hero-inner">
                <a href="javascript:history.back()" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                <div class="category-pill">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Akademik Pascasarjana</span>
                </div>

                <h1 class="title-page">{{ $namaProgram }}</h1>

                <div class="page-meta">
                    @if($createdAt)
                        <span>
                            <i class="fas fa-calendar-alt"></i>
                            {{ $createdAt }}
                        </span>
                    @endif

                    <span>
                        <i class="fas fa-university"></i>
                        Universitas Ngudi Waluyo
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

    <main class="content-section">
        <div class="detail-shell">
            <article class="content-card">
                <div class="content-toolbar">
                    <div class="toolbar-title">
                        <div class="toolbar-icon">
                            <i class="fas fa-file-lines"></i>
                        </div>

                        <div>
                            <h2>{{ $namaProgram }}</h2>
                            <p>Informasi akademik program Pascasarjana Universitas Ngudi Waluyo</p>
                        </div>
                    </div>

                    <div class="toolbar-actions">
                        <a href="{{ url()->current() }}" class="toolbar-btn">
                            <i class="fas fa-link"></i>
                            <span>Salin Link</span>
                        </a>
                    </div>
                </div>

                <div class="article-body">
                    <div class="article-body-inner">
                        <div class="content-html">
                            {!! $bodyContent !!}
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </main>

    @include('component.footer')

</body>

</html>