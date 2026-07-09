<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pascasarjana Universitas Ngudi Waluyo')</title>
    <link rel="icon" href="{{ asset('logo_unwnobg.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('logo_unwnobg.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('logo_unwnobg.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="pasca-contact-url" content="{{ route('contact.index') }}">
    <meta name="pasca-about-url" content="{{ route('tentang') }}">
    <meta name="pasca-vision-mission-url" content="{{ route('visi-misi') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body {
            background: linear-gradient(180deg, #f8fbff 0%, #f4f8fc 48%, #eef5fb 100%) !important;
        }

        .page-hero,
        .about-hero,
        .news-hero,
        .rd-hero,
        .contact-hero,
        .academic-page .page-hero,
        .profile-page .profile-hero {
            position: relative !important;
            isolation: isolate !important;
            overflow: hidden !important;
            min-height: clamp(78px, 7vw, 112px) !important;
            display: flex !important;
            align-items: center !important;
            padding: clamp(8px, 1vw, 14px) 0 clamp(12px, 1.4vw, 20px) !important;
            color: #fff !important;
            background: linear-gradient(135deg, #062e62 0%, #0b5f9f 63%, #2389cf 100%) !important;
            background-image: linear-gradient(135deg, #062e62 0%, #0b5f9f 63%, #2389cf 100%) !important;
            box-shadow: none !important;
        }

        .page-hero::before,
        .about-hero::before,
        .news-hero::before,
        .rd-hero::before,
        .contact-hero::before,
        .academic-page .page-hero::before,
        .profile-page .profile-hero::before,
        .page-hero::after,
        .about-hero::after,
        .news-hero::after,
        .rd-hero::after,
        .contact-hero::after,
        .academic-page .page-hero::after,
        .profile-page .profile-hero::after {
            content: none !important;
            display: none !important;
            background: none !important;
            background-image: none !important;
            box-shadow: none !important;
        }

        .page-hero [class*="spotlight"],
        .about-hero [class*="spotlight"],
        .news-hero [class*="spotlight"],
        .rd-hero [class*="spotlight"],
        .contact-hero [class*="spotlight"],
        .profile-hero [class*="spotlight"],
        .page-hero [class*="dots"],
        .about-hero [class*="dots"],
        .news-hero [class*="dots"],
        .rd-hero [class*="dots"],
        .contact-hero [class*="dots"],
        .profile-hero [class*="dots"],
        .page-hero [class*="line"],
        .about-hero [class*="line"],
        .news-hero [class*="line"],
        .rd-hero [class*="line"],
        .contact-hero [class*="line"],
        .profile-hero [class*="line"],
        .page-hero [class*="glow"],
        .about-hero [class*="glow"],
        .news-hero [class*="glow"],
        .rd-hero [class*="glow"],
        .contact-hero [class*="glow"],
        .profile-hero [class*="glow"],
        .page-hero [class*="orb"],
        .about-hero [class*="orb"],
        .news-hero [class*="orb"],
        .rd-hero [class*="orb"],
        .contact-hero [class*="orb"],
        .profile-hero [class*="orb"],
        .page-hero [class*="pattern"],
        .about-hero [class*="pattern"],
        .news-hero [class*="pattern"],
        .rd-hero [class*="pattern"],
        .contact-hero [class*="pattern"],
        .profile-hero [class*="pattern"],
        .page-hero [class*="decor"],
        .about-hero [class*="decor"],
        .news-hero [class*="decor"],
        .rd-hero [class*="decor"],
        .contact-hero [class*="decor"],
        .profile-hero [class*="decor"] {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }

        .hero-wave,
        .rd-hero-wave,
        .hero-shape {
            position: absolute !important;
            left: 0 !important;
            right: 0 !important;
            bottom: -1px !important;
            z-index: 3 !important;
            height: clamp(8px, 1.2vw, 18px) !important;
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: none !important;
        }

        .hero-wave svg,
        .rd-hero-wave svg,
        .hero-shape svg {
            width: 100% !important;
            height: 100% !important;
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .hero-inner,
        .rd-hero-inner,
        .academic-page .hero-inner,
        .profile-page .hero-inner {
            position: relative !important;
            z-index: 5 !important;
            max-width: 920px !important;
            width: min(920px, 100%) !important;
        }

        .hero-kicker,
        .rd-kicker,
        .news-category-pill,
        .category-pill,
        .profile-tag,
        .hero-badge {
            width: fit-content !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            margin: 0 0 4px !important;
            padding: 4px 8px !important;
            border-radius: 999px !important;
            color: #fff !important;
            background: rgba(255, 255, 255, .14) !important;
            border: 1px solid rgba(255, 255, 255, .20) !important;
            box-shadow: none !important;
            font-size: 8.5px !important;
            line-height: 1.05 !important;
            font-weight: 650 !important;
            text-transform: uppercase !important;
            letter-spacing: .18px !important;
            backdrop-filter: blur(8px) !important;
        }

        .page-title,
        .about-title,
        .rd-title,
        .news-title-page,
        .contact-title,
        .title-page,
        .profile-hero-title {
            max-width: 860px !important;
            margin: 0 !important;
            color: #fff !important;
            font-size: clamp(19px, 2.4vw, 30px) !important;
            line-height: 1.02 !important;
            font-weight: 650 !important;
            letter-spacing: -.25px !important;
            text-shadow: none !important;
            text-wrap: balance !important;
        }

        .page-desc,
        .about-subtitle,
        .rd-desc,
        .contact-subtitle {
            display: none !important;
            max-width: 0 !important;
            margin: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        .hero-meta,
        .rd-hero-meta,
        .news-meta,
        .page-meta,
        .profile-meta {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 5px !important;
            margin-top: 6px !important;
            height: auto !important;
            overflow: visible !important;
        }

        .hero-meta span,
        .rd-hero-meta span,
        .news-meta span,
        .page-meta span,
        .profile-meta span {
            min-height: 20px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            padding: 3px 7px !important;
            border-radius: 999px !important;
            color: #fff !important;
            background: rgba(255, 255, 255, .13) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            box-shadow: none !important;
            font-size: 8.8px !important;
            line-height: 1.05 !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
            backdrop-filter: blur(6px) !important;
        }

        .hero-meta i,
        .rd-hero-meta i,
        .news-meta i,
        .page-meta i,
        .profile-meta i,
        .hero-kicker i {
            color: #f7b500 !important;
        }

        .profile-menu-page .about-section,
        .profile-menu-page .visi-misi-section,
        .profile-menu-page .content-section {
            position: relative !important;
            z-index: 6 !important;
            margin-top: -8px !important;
            padding-bottom: 90px !important;
        }

        .profile-menu-page .about-container,
        .profile-menu-page .vm-container,
        .profile-menu-page .so-container {
            width: min(1180px, calc(100% - 48px)) !important;
            margin: 0 auto !important;
        }

        .profile-menu-page .about-layout {
            display: grid !important;
            grid-template-columns: minmax(0, 1.18fr) minmax(320px, .82fr) !important;
            gap: 26px !important;
            align-items: stretch !important;
        }

        .profile-menu-page .about-main-card,
        .profile-menu-page .about-points-card,
        .profile-menu-page .sambutan-card,
        .profile-menu-page .visi-misi-card,
        .profile-menu-page .structure-card,
        .profile-menu-page .empty-card,
        .profile-menu-page .empty-state-card {
            border-radius: 26px !important;
            background: #fff !important;
            border: 1px solid rgba(226, 232, 240, .95) !important;
            box-shadow: 0 18px 46px rgba(15, 23, 42, .09) !important;
            overflow: hidden !important;
        }

        .profile-menu-page .about-main-card,
        .profile-menu-page .about-points-card,
        .profile-menu-page .empty-card,
        .profile-menu-page .empty-state-card {
            padding: clamp(24px, 3vw, 34px) !important;
        }

        .profile-menu-page .about-main-card h2,
        .profile-menu-page .sambutan-title h2,
        .profile-menu-page .points-header h3,
        .profile-menu-page .card-header h2,
        .profile-menu-page .empty-card h2,
        .profile-menu-page .empty-card h3,
        .profile-menu-page .empty-state-card h3 {
            color: #062e62 !important;
            letter-spacing: -.35px !important;
        }

        .profile-menu-page .about-desc,
        .profile-menu-page .card-content,
        .profile-menu-page .sambutan-text {
            color: #334155 !important;
            font-size: 15px !important;
            line-height: 1.9 !important;
        }

        .profile-menu-page .section-kicker {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin-bottom: 14px !important;
            padding: 8px 12px !important;
            border-radius: 999px !important;
            color: #062e62 !important;
            background: rgba(6, 46, 98, .07) !important;
            border: 1px solid rgba(6, 46, 98, .10) !important;
            font-size: 12px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
        }

        .profile-menu-page .section-kicker i,
        .profile-menu-page .card-icon,
        .profile-menu-page .point-icon,
        .profile-menu-page .empty-icon,
        .profile-menu-page .empty-state-icon {
            color: #f7b500 !important;
        }

        .profile-menu-page .about-points {
            display: grid !important;
            gap: 14px !important;
        }

        .profile-menu-page .point-card {
            display: grid !important;
            grid-template-columns: 52px minmax(0, 1fr) !important;
            gap: 14px !important;
            align-items: start !important;
            padding: 16px !important;
            border-radius: 18px !important;
            background: #f8fbff !important;
            border: 1px solid #e2e8f0 !important;
        }

        .profile-menu-page .point-icon,
        .profile-menu-page .card-icon {
            width: 52px !important;
            height: 52px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 16px !important;
            background: rgba(247, 181, 0, .12) !important;
            border: 1px solid rgba(247, 181, 0, .20) !important;
            overflow: hidden !important;
        }

        .profile-menu-page .point-icon img {
            width: 30px !important;
            height: 30px !important;
            object-fit: contain !important;
        }

        .profile-menu-page .point-text h3 {
            margin: 0 0 6px !important;
            color: #062e62 !important;
            font-size: 15px !important;
            line-height: 1.35 !important;
            font-weight: 900 !important;
        }

        .profile-menu-page .point-text p,
        .profile-menu-page .points-header p {
            margin: 0 !important;
            color: #64748b !important;
            font-size: 13px !important;
            line-height: 1.7 !important;
            font-weight: 600 !important;
        }

        .profile-menu-page .sambutan-section {
            margin-top: 34px !important;
        }

        .profile-menu-page .sambutan-title {
            max-width: 820px !important;
            margin-bottom: 20px !important;
        }

        .profile-menu-page .sambutan-card {
            display: grid !important;
            grid-template-columns: minmax(260px, 340px) minmax(0, 1fr) !important;
            gap: 0 !important;
            align-items: stretch !important;
        }

        .profile-menu-page .sambutan-img {
            min-height: 360px !important;
            background: #eaf2fb !important;
        }

        .profile-menu-page .sambutan-img img,
        .profile-menu-page .director-placeholder {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .profile-menu-page .sambutan-content {
            padding: clamp(24px, 4vw, 42px) !important;
        }

        .profile-menu-page .visi-misi-wrapper {
            max-width: 1100px !important;
            margin: 0 auto !important;
        }

        .profile-menu-page .visi-misi-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }

        .profile-menu-page .visi-misi-card {
            display: grid !important;
            grid-template-columns: 96px minmax(0, 1fr) !important;
            align-items: stretch !important;
        }

        .profile-menu-page .card-side {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(180deg, #062e62, #0b5f9f) !important;
        }

        .profile-menu-page .card-number {
            color: rgba(255, 255, 255, .86) !important;
            font-size: 28px !important;
            font-weight: 900 !important;
        }

        .profile-menu-page .card-main {
            padding: clamp(22px, 3vw, 34px) !important;
        }

        .profile-menu-page .card-header {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            margin-bottom: 14px !important;
        }

        .profile-menu-page .structure-card {
            padding: clamp(18px, 3vw, 30px) !important;
        }

        .profile-menu-page .structure-image-box {
            padding: clamp(12px, 2vw, 20px) !important;
            border-radius: 22px !important;
            background: linear-gradient(180deg, #f8fbff, #eef5fb) !important;
            border: 1px solid #e2e8f0 !important;
        }

        .profile-menu-page .structure-image-inner {
            overflow: auto !important;
            border-radius: 18px !important;
            background: #fff !important;
            box-shadow: inset 0 0 0 1px #e2e8f0 !important;
        }

        .profile-menu-page .structure-image {
            display: block !important;
            width: 100% !important;
            height: auto !important;
            max-height: 78vh !important;
            object-fit: contain !important;
            margin: 0 auto !important;
        }

        @media (max-width: 900px) {
            .profile-menu-page .about-layout,
            .profile-menu-page .sambutan-card,
            .profile-menu-page .visi-misi-card {
                grid-template-columns: 1fr !important;
            }

            .profile-menu-page .card-side {
                min-height: 72px !important;
            }
        }

        @media (max-width: 768px) {
            .page-hero,
            .about-hero,
            .news-hero,
            .rd-hero,
            .contact-hero,
            .academic-page .page-hero,
            .profile-page .profile-hero {
                min-height: 74px !important;
                align-items: center !important;
                padding: 6px 0 10px !important;
            }

            .hero-wave,
            .rd-hero-wave,
            .hero-shape {
                height: 7px !important;
            }

            .hero-kicker,
            .rd-kicker,
            .news-category-pill,
            .category-pill,
            .profile-tag,
            .hero-badge {
                margin-bottom: 3px !important;
                padding: 3px 7px !important;
                font-size: 8px !important;
                font-weight: 600 !important;
                letter-spacing: .12px !important;
            }

            .page-title,
            .about-title,
            .rd-title,
            .news-title-page,
            .contact-title,
            .title-page,
            .profile-hero-title {
                font-size: clamp(17px, 5.2vw, 22px) !important;
                line-height: 1 !important;
                font-weight: 650 !important;
                letter-spacing: -.18px !important;
            }

            .page-desc,
            .about-subtitle,
            .rd-desc,
            .contact-subtitle {
                display: none !important;
            }

            .hero-meta,
            .rd-hero-meta,
            .news-meta,
            .page-meta,
            .profile-meta {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 4px !important;
                margin-top: 4px !important;
                height: auto !important;
                overflow: visible !important;
            }

            .hero-meta span,
            .rd-hero-meta span,
            .news-meta span,
            .page-meta span,
            .profile-meta span {
                min-height: 17px !important;
                display: inline-flex !important;
                gap: 3px !important;
                padding: 2px 5px !important;
                font-size: 7.5px !important;
                line-height: 1 !important;
                font-weight: 600 !important;
            }

            .profile-menu-page .about-container,
            .profile-menu-page .vm-container,
            .profile-menu-page .so-container {
                width: min(100% - 28px, 1180px) !important;
            }

            .profile-menu-page .about-section,
            .profile-menu-page .visi-misi-section,
            .profile-menu-page .content-section {
                margin-top: -6px !important;
                padding-bottom: 68px !important;
            }

            .profile-menu-page .about-main-card,
            .profile-menu-page .about-points-card,
            .profile-menu-page .sambutan-card,
            .profile-menu-page .visi-misi-card,
            .profile-menu-page .structure-card,
            .profile-menu-page .empty-card,
            .profile-menu-page .empty-state-card {
                border-radius: 22px !important;
            }
        }
    </style>
</head>
<body class="@yield('body_class')">
    @include('components.header')
    <main class="app-main">
        @yield('content')
    </main>
    @include('components.footer')
    <x-impersonate::banner />
    @stack('scripts')
</body>
</html>
