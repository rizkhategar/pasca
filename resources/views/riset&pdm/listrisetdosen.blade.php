@php
    $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Dosen & Riset - Pascasarjana UNW</title>

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
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            min-height: 100vh;
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

        .rd-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: transparent;
        }

        .rd-container {
            width: min(100% - 64px, 1180px);
            margin: 0 auto;
        }

        /* ================= HERO ================= */

        .rd-hero {
            position: relative;
            overflow: hidden;
            color: #fff;
            min-height: 320px;
            display: flex;
            align-items: center;
            padding: 58px 0 108px;
            background:
                radial-gradient(circle at 13% 18%, rgba(45, 156, 219, .42), transparent 26%),
                radial-gradient(circle at 82% 18%, rgba(255, 255, 255, .18), transparent 23%),
                linear-gradient(135deg, #031f42 0%, #062f5f 46%, #07457d 75%, #0b6eae 100%);
        }

        .rd-hero::before {
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

        .rd-hero::after {
            content: "";
            position: absolute;
            right: -170px;
            top: -185px;
            width: 570px;
            height: 570px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .20) 0%, rgba(45, 156, 219, .16) 36%, transparent 68%);
            z-index: 1;
        }

        .rd-hero-dots {
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

        .rd-hero-line {
            position: absolute;
            right: 110px;
            top: -36px;
            width: 230px;
            height: 440px;
            background: linear-gradient(120deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .18));
            transform: skewX(-32deg);
            z-index: 1;
        }

        .rd-hero-wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            width: 100%;
            height: 92px;
            z-index: 3;
            pointer-events: none;
        }

        .rd-hero-wave svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .rd-hero-inner {
            position: relative;
            z-index: 4;
            max-width: 880px;
        }

        .rd-kicker {
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

        .rd-title {
            margin: 0 0 18px;
            color: #fff;
            font-size: clamp(34px, 5vw, 56px);
            line-height: 1.06;
            font-weight: 900;
            letter-spacing: -1.1px;
        }

        .rd-desc {
            max-width: 760px;
            margin: 0;
            color: rgba(255, 255, 255, .86);
            font-size: clamp(15px, 2vw, 18px);
            line-height: 1.75;
            font-weight: 600;
        }

        .rd-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .rd-hero-meta span {
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

        .rd-hero-meta i {
            color: var(--yellow);
        }

        /* ================= MAIN ================= */

        .rd-main {
            position: relative;
            z-index: 5;
            margin-top: -58px;
            padding: 0 0 90px;
        }

        .rd-panel {
            background: rgba(255, 255, 255, .96);
            border: 1px solid rgba(226, 232, 240, .95);
            border-radius: 30px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* ================= FILTER ================= */

        .rd-filter-box {
            padding: 24px;
            background:
                linear-gradient(135deg, rgba(6, 47, 95, .08), rgba(45, 156, 219, .08)),
                #ffffff;
            border-bottom: 1px solid rgba(226, 232, 240, .95);
        }

        .rd-filter-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .rd-filter-title {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
        }

        .rd-filter-icon {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            box-shadow: 0 12px 26px rgba(6, 47, 95, .22);
        }

        .rd-filter-title h2 {
            margin: 0 0 4px;
            color: var(--text);
            font-size: 21px;
            line-height: 1.25;
            font-weight: 900;
        }

        .rd-filter-title p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
            font-weight: 600;
        }

        .rd-form {
            display: grid;
            grid-template-columns: 1fr minmax(260px, 360px) auto;
            gap: 14px;
            align-items: end;
        }

        .rd-form-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .rd-form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .rd-form-group label i {
            color: var(--yellow);
        }

        .rd-input-wrap {
            position: relative;
        }

        .rd-input-wrap i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }

        .rd-input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 1px solid rgba(6, 47, 95, .14);
            border-radius: 16px;
            background: #fff;
            color: var(--primary);
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            outline: none;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
            transition: .22s ease;
        }

        .rd-input.has-icon {
            padding-left: 42px;
        }

        .rd-input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(11, 95, 159, .12);
        }

        .rd-btn-group {
            display: flex;
            align-items: end;
            gap: 10px;
        }

        .rd-btn {
            height: 48px;
            padding: 0 20px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 13px;
            font-family: inherit;
            text-transform: uppercase;
            cursor: pointer;
            transition: .22s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-align: center;
            text-decoration: none;
            white-space: nowrap;
        }

        .rd-btn-reset {
            color: var(--primary);
            background: #fff;
            border: 1px solid rgba(6, 47, 95, .14);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        }

        .rd-btn-reset:hover {
            color: #fff;
            background: var(--primary);
            transform: translateY(-2px);
        }

        /* ================= CONTENT ================= */

        .rd-content {
            padding: 28px;
        }

        .rd-result-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .rd-result-info h2 {
            margin: 0;
            color: var(--text);
            font-size: 22px;
            line-height: 1.25;
            font-weight: 900;
        }

        .rd-result-info p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
            font-weight: 600;
        }

        .rd-result-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            color: var(--primary);
            background: #f8fafc;
            border: 1px solid #eef2f7;
            font-size: 13px;
            font-weight: 900;
        }

        .rd-result-badge i {
            color: var(--yellow);
        }

        .rd-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        /* ================= CARD DOSEN ================= */

        .rd-list-item {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 118px 1fr;
            gap: 20px;
            min-height: 100%;
            padding: 20px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: .26s ease;
        }

        .rd-list-item::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 5px;
            background: linear-gradient(180deg, var(--primary), var(--blue-light));
            opacity: .95;
        }

        .rd-list-item::after {
            content: "";
            position: absolute;
            right: -46px;
            top: -46px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(45, 156, 219, .08);
            transition: .26s ease;
        }

        .rd-list-item:hover {
            transform: translateY(-6px);
            border-color: rgba(45, 156, 219, .35);
            box-shadow: var(--shadow-md);
        }

        .rd-list-item:hover::after {
            transform: scale(1.35);
            background: rgba(45, 156, 219, .13);
        }

        .rd-list-photo {
            position: relative;
            z-index: 1;
            width: 118px;
            height: 150px;
            overflow: hidden;
            border-radius: 20px;
            background:
                linear-gradient(135deg, rgba(6, 47, 95, .08), rgba(45, 156, 219, .08)),
                #eef2f7;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .10);
        }

        .rd-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: .35s ease;
        }

        .rd-list-item:hover .rd-photo {
            transform: scale(1.06);
        }

        .rd-list-content {
            position: relative;
            z-index: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .rd-name {
            margin: 0;
            color: var(--primary);
            font-size: 18px;
            line-height: 1.38;
            font-weight: 900;
            letter-spacing: -.2px;
        }

        .rd-department {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 9px;
            padding: 7px 10px;
            border-radius: 999px;
            color: #14532d;
            background: rgba(20, 83, 45, .08);
            border: 1px solid rgba(20, 83, 45, .12);
            font-size: 11px;
            line-height: 1.2;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .35px;
        }

        .rd-department::before {
            content: "\f19d";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #15803d;
        }

        .rd-sinta-id {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 9px;
            padding: 7px 10px;
            border-radius: 999px;
            color: var(--primary);
            background: #f8fafc;
            border: 1px solid #eef2f7;
            font-size: 11px;
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: .35px;
        }

        .rd-sinta-id::before {
            content: "\f2bb";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: var(--yellow);
        }

        .rd-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: auto;
            padding-top: 16px;
        }

        .rd-stat {
            min-width: 0;
            padding: 12px 8px;
            border-radius: 16px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #eef2f7;
        }

        .rd-stat-value {
            color: var(--primary);
            font-size: 16px;
            line-height: 1.1;
            font-weight: 900;
        }

        .rd-stat-label {
            margin-top: 5px;
            color: var(--muted);
            font-size: 9px;
            line-height: 1.25;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: .25px;
        }

        .rd-card-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 14px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
        }

        .rd-card-action i {
            transition: .22s ease;
        }

        .rd-list-item:hover .rd-card-action i {
            transform: translateX(4px);
        }

        /* ================= EMPTY ================= */

        .rd-empty {
            grid-column: 1 / -1;
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            gap: 12px;
            padding: 42px 24px;
            border-radius: 24px;
            background:
                linear-gradient(135deg, rgba(6, 47, 95, .05), rgba(45, 156, 219, .05)),
                #ffffff;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .rd-empty-icon {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            color: var(--primary);
            background: rgba(6, 47, 95, .08);
            font-size: 26px;
        }

        .rd-empty h3 {
            margin: 4px 0 0;
            color: var(--primary);
            font-size: 20px;
            line-height: 1.3;
            font-weight: 900;
            text-transform: uppercase;
        }

        .rd-empty p {
            max-width: 460px;
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            font-weight: 600;
        }

        /* ================= NATIVE LARAVEL BLADE PAGINATION ================= */

        .rd-pagination {
            margin-top: 34px;
            padding-top: 24px;
            border-top: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rd-pagination nav {
            width: 100%;
        }

        /* Menyembunyikan teks info "Showing X to Y of Z results" bawaan Laravel agar terlihat bersih seperti halaman berita */
        .rd-pagination nav > div:first-child {
            display: none !important;
        }

        /* Menyelaraskan pembungkus tombol utama pagination */
        .rd-pagination nav > div:last-child,
        .rd-pagination nav > div {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px !important;
            flex-wrap: wrap;
        }

        /* Gaya dasar tombol angka & panah (Menyamakan penuh dengan .page-btn) */
        .rd-pagination a,
        .rd-pagination span {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 42px !important;
            min-width: 42px !important;
            border: 1px solid rgba(6, 47, 95, .14) !important;
            border-radius: 13px !important;
            background: #fff !important;
            color: var(--primary) !important;
            font-weight: 900 !important;
            padding: 0 13px !important;
            transition: .2s ease !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .04) !important;
            text-decoration: none !important;
            cursor: pointer;
            box-sizing: border-box !important;
        }

        /* Efek Hover untuk Link Aktif & Gaya untuk Halaman yang Sedang Aktif (Current Page) */
        .rd-pagination a:hover,
        .rd-pagination span[aria-current="page"],
        .rd-pagination .active span,
        .rd-pagination span.active {
            background: linear-gradient(135deg, var(--primary), var(--blue)) !important;
            border-color: transparent !important;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(6, 47, 95, .18) !important;
        }

        /* Gaya untuk Tombol yang Dinonaktifkan (Disabled Previous / Next) */
        .rd-pagination span[aria-disabled="true"] {
            opacity: .45 !important;
            cursor: not-allowed !important;
            background: #f1f5f9 !important;
            color: #94a3b8 !important;
            transform: none !important;
            box-shadow: none !important;
            border: 1px solid rgba(6, 47, 95, .14) !important;
        }

        /* Merapikan elemen tiga titik (...) pemisah halaman jika ada */
        .rd-pagination span:not([aria-disabled="true"]):not([aria-current="page"]) {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            color: #94a3b8 !important;
            font-weight: 900 !important;
            cursor: default;
        }

        /* Menyelaraskan ukuran ikon SVG panah bawaan Laravel */
        .rd-pagination svg {
            width: 16px !important;
            height: 16px !important;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 1024px) {
            .rd-form {
                grid-template-columns: 1fr 1fr;
            }

            .rd-btn-group {
                grid-column: 1 / -1;
            }

            .rd-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 768px) {
            .rd-container {
                width: min(100% - 28px, 1180px);
            }

            .rd-hero {
                min-height: 310px;
                padding: 48px 0 96px;
            }

            .rd-hero-dots {
                display: none;
            }

            .rd-hero-line {
                display: none;
            }

            .rd-kicker {
                font-size: 12px;
                padding: 8px 12px;
            }

            .rd-title {
                font-size: 31px;
                letter-spacing: -.6px;
            }

            .rd-desc {
                font-size: 15px;
            }

            .rd-main {
                margin-top: -48px;
                padding-bottom: 70px;
            }

            .rd-panel {
                border-radius: 24px;
            }

            .rd-filter-box {
                padding: 20px;
            }

            .rd-filter-heading {
                flex-direction: column;
            }

            .rd-form {
                grid-template-columns: 1fr;
            }

            .rd-btn-group {
                width: 100%;
            }

            .rd-btn {
                width: 100%;
            }

            .rd-content {
                padding: 20px;
            }

            .rd-result-info {
                align-items: flex-start;
                flex-direction: column;
            }

            .rd-list-item {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 20px;
            }

            .rd-list-photo {
                width: 132px;
                height: 168px;
                margin: 0 auto;
            }

            .rd-list-content {
                align-items: center;
            }

            .rd-name {
                font-size: 18px;
            }

            .rd-stats {
                width: 100%;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rd-card-action {
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    @include('component.header')

    <div class="rd-wrapper">

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
                        Temukan profil dosen, program studi, dan capaian riset berdasarkan data SINTA Pascasarjana Universitas Ngudi Waluyo.
                    </p>

                    <div class="rd-hero-meta">
                        <span>
                            <i class="fas fa-user-graduate"></i>
                            Profil Dosen
                        </span>
                        <span>
                            <i class="fas fa-chart-line"></i>
                            Data SINTA
                        </span>
                        <span>
                            <i class="fas fa-university"></i>
                            Pascasarjana UNW
                        </span>
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
                                <div class="rd-filter-icon">
                                    <i class="fas fa-filter"></i>
                                </div>

                                <div>
                                    <h2>Filter Data Dosen</h2>
                                    <p>Gunakan pencarian atau pilih jurusan untuk menemukan dosen secara lebih cepat.</p>
                                </div>
                            </div>
                        </div>

                        <form method="GET" action="{{ url()->current() }}" class="rd-form" id="filterForm">
                            <div class="rd-form-group">
                                <label for="searchInput">
                                    <i class="fas fa-magnifying-glass"></i>
                                    Pencarian
                                </label>

                                <div class="rd-input-wrap">
                                    <i class="fas fa-search"></i>
                                    <input
                                        type="text"
                                        name="search"
                                        id="searchInput"
                                        value="{{ request('search') }}"
                                        placeholder="Cari nama dosen atau ID SINTA..."
                                        class="rd-input has-icon"
                                        autocomplete="off">
                                </div>
                            </div>

                            <div class="rd-form-group">
                                <label for="jurusanSelect">
                                    <i class="fas fa-layer-group"></i>
                                    Filter Jurusan
                                </label>

                                <select name="jurusan" id="jurusanSelect" class="rd-input">
                                    <option value="">Semua Jurusan</option>
                                    @foreach ($academicProgramsNav as $jurusan)
                                        <option value="{{ $jurusan['slug'] }}"
                                            {{ request('jurusan') == $jurusan['slug'] ? 'selected' : '' }}>
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
                                        @if ($dosen->profile_photo && file_exists(public_path('assets/images/' . $dosen->sinta_id . '.jpg')))
                                            <img src="{{ asset('assets/images/' . $dosen->sinta_id . '.jpg') }}" alt="{{ $dosen->nama }}" class="rd-photo">
                                        @else
                                            <img src="{{ asset('assets/images/default-user.png') }}" alt="{{ $dosen->nama }}" class="rd-photo">
                                        @endif
                                    </div>

                                    <div class="rd-list-content">
                                        <h3 class="rd-name">{{ $dosen->nama }}</h3>

                                        <div class="rd-department">
                                            {{ $dosen->program_studi }}
                                        </div>

                                        <div class="rd-sinta-id">
                                            SINTA ID : {{ $dosen->sinta_id }}
                                        </div>

                                        <div class="rd-stats">
                                            <div class="rd-stat">
                                                <div class="rd-stat-value">{{ number_format($dosen->sinta_score_overall ?? 0) }}</div>
                                                <div class="rd-stat-label">Overall Score</div>
                                            </div>

                                            <div class="rd-stat">
                                                <div class="rd-stat-value">{{ number_format($dosen->sinta_score_3yr ?? 0) }}</div>
                                                <div class="rd-stat-label">3 Year Score</div>
                                            </div>

                                            <div class="rd-stat">
                                                <div class="rd-stat-value">{{ number_format($dosen->affil_score ?? 0) }}</div>
                                                <div class="rd-stat-label">Affil Score</div>
                                            </div>

                                            <div class="rd-stat">
                                                <div class="rd-stat-value">{{ number_format($dosen->affil_score_3yr ?? 0) }}</div>
                                                <div class="rd-stat-label">Affil 3Yr</div>
                                            </div>
                                        </div>

                                        <div class="rd-card-action">
                                            <span>Lihat detail riset</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="rd-empty">
                                    <div class="rd-empty-icon">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
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

    </div>

    @include('component.footer')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const jurusanSelect = document.getElementById('jurusanSelect');

            if (jurusanSelect) {
                jurusanSelect.addEventListener('change', function () {
                    form.submit();
                });
            }

            let debounceTimer;
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        form.submit();
                    }, 800);
                });
            }
        });
    </script>
</body>
</html>