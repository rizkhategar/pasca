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
            min-height: clamp(320px, 32vw, 410px) !important;
            display: flex !important;
            align-items: center !important;
            padding: clamp(54px, 6vw, 76px) 0 clamp(96px, 8vw, 122px) !important;
            color: #fff !important;
            background: linear-gradient(135deg, #062e62 0%, #0b5f9f 60%, #2389cf 100%) !important;
        }

        .page-hero::before,
        .about-hero::before,
        .news-hero::before,
        .rd-hero::before,
        .contact-hero::before,
        .academic-page .page-hero::before,
        .profile-page .profile-hero::before {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            z-index: 1 !important;
            pointer-events: none !important;
            background: none !important;
            background-image: none !important;
            opacity: 0 !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .page-hero::after,
        .about-hero::after,
        .news-hero::after,
        .rd-hero::after,
        .contact-hero::after,
        .academic-page .page-hero::after,
        .profile-page .profile-hero::after {
            content: "" !important;
            position: absolute !important;
            left: -6% !important;
            right: -6% !important;
            top: auto !important;
            bottom: -78px !important;
            width: auto !important;
            height: 142px !important;
            z-index: 2 !important;
            pointer-events: none !important;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0 !important;
            background: #f8fbff !important;
            box-shadow: 0 -18px 42px rgba(3, 31, 66, .08) !important;
            transform: none !important;
        }

        .page-hero [class*="spotlight"],
        .about-hero [class*="spotlight"],
        .news-hero [class*="spotlight"],
        .rd-hero [class*="spotlight"],
        .contact-hero [class*="spotlight"],
        .profile-hero [class*="spotlight"],
        .page-hero [class*="decor"],
        .about-hero [class*="decor"],
        .news-hero [class*="decor"],
        .rd-hero [class*="decor"],
        .contact-hero [class*="decor"],
        .profile-hero [class*="decor"],
        .page-hero [class*="pattern"],
        .about-hero [class*="pattern"],
        .news-hero [class*="pattern"],
        .rd-hero [class*="pattern"],
        .contact-hero [class*="pattern"],
        .profile-hero [class*="pattern"],
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
        .page-hero > [aria-hidden="true"]:not(.hero-wave):not(.rd-hero-wave):not(.hero-shape):not([class*="wave"]):not([class*="shape"]),
        .about-hero > [aria-hidden="true"]:not(.hero-wave):not(.rd-hero-wave):not(.hero-shape):not([class*="wave"]):not([class*="shape"]),
        .news-hero > [aria-hidden="true"]:not(.hero-wave):not(.rd-hero-wave):not(.hero-shape):not([class*="wave"]):not([class*="shape"]),
        .rd-hero > [aria-hidden="true"]:not(.hero-wave):not(.rd-hero-wave):not(.hero-shape):not([class*="wave"]):not([class*="shape"]),
        .contact-hero > [aria-hidden="true"]:not(.hero-wave):not(.rd-hero-wave):not(.hero-shape):not([class*="wave"]):not([class*="shape"]),
        .profile-hero > [aria-hidden="true"]:not(.hero-wave):not(.rd-hero-wave):not(.hero-shape):not([class*="wave"]):not([class*="shape"]) {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }

        .hero-wave,
        .rd-hero-wave,
        .hero-shape,
        .hero-wave svg,
        .rd-hero-wave svg,
        .hero-shape svg {
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
            width: min(900px, 100%) !important;
            max-width: 900px !important;
        }

        .contact-page .contact-hero .hero-inner {
            position: relative !important;
            z-index: 5 !important;
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 340px) !important;
            gap: 32px !important;
            align-items: center !important;
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
            gap: 8px !important;
            margin: 0 0 16px !important;
            padding: 9px 14px !important;
            border-radius: 999px !important;
            color: #fff !important;
            background: rgba(255, 255, 255, .15) !important;
            border: 1px solid rgba(255, 255, 255, .22) !important;
            box-shadow: none !important;
            font-size: 12px !important;
            line-height: 1.15 !important;
            font-weight: 850 !important;
            text-transform: uppercase !important;
            letter-spacing: .45px !important;
            backdrop-filter: blur(8px) !important;
        }

        .page-title,
        .about-title,
        .rd-title,
        .news-title-page,
        .contact-title,
        .title-page,
        .profile-hero-title {
            max-width: 850px !important;
            margin: 0 0 16px !important;
            color: #fff !important;
            font-size: clamp(34px, 5vw, 58px) !important;
            line-height: 1.05 !important;
            font-weight: 900 !important;
            letter-spacing: -1px !important;
            text-shadow: none !important;
            text-wrap: balance !important;
        }

        .page-desc,
        .about-subtitle,
        .rd-desc,
        .contact-subtitle {
            max-width: 760px !important;
            margin: 0 !important;
            color: rgba(255, 255, 255, .91) !important;
            font-size: clamp(14px, 1.7vw, 17px) !important;
            line-height: 1.75 !important;
            font-weight: 600 !important;
            text-wrap: pretty !important;
        }

        .hero-meta,
        .rd-hero-meta,
        .news-meta,
        .page-meta,
        .profile-meta {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
            margin-top: 22px !important;
        }

        .hero-meta span,
        .rd-hero-meta span,
        .news-meta span,
        .page-meta span,
        .profile-meta span {
            min-height: 38px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 8px 13px !important;
            border-radius: 999px !important;
            color: #fff !important;
            background: rgba(255, 255, 255, .14) !important;
            border: 1px solid rgba(255, 255, 255, .20) !important;
            box-shadow: none !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            backdrop-filter: blur(8px) !important;
        }

        .hero-meta i,
        .rd-hero-meta i,
        .news-meta i,
        .page-meta i,
        .profile-meta i {
            color: #f7b500 !important;
        }

        .content-section,
        .about-section,
        .vm-section,
        .so-section,
        .news-section,
        .rd-section,
        .contact-section,
        .news-content-section,
        .visi-misi-section {
            position: relative !important;
            z-index: 6 !important;
            margin-top: -42px !important;
            padding-bottom: 84px !important;
        }

        .content-card,
        .about-card,
        .vm-card,
        .so-card,
        .news-card,
        .rd-card,
        .contact-card,
        .map-card,
        .content-block,
        .news-panel,
        .news-card-detail {
            border-radius: 24px !important;
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 16px 42px rgba(15, 23, 42, .09) !important;
        }

        .contact-page .hero-info-card {
            background: rgba(255, 255, 255, .14) !important;
            border: 1px solid rgba(255, 255, 255, .22) !important;
            box-shadow: none !important;
            backdrop-filter: blur(10px) !important;
        }

        @media (max-width: 768px) {
            .page-hero,
            .about-hero,
            .news-hero,
            .rd-hero,
            .contact-hero,
            .academic-page .page-hero,
            .profile-page .profile-hero {
                min-height: 310px !important;
                align-items: flex-start !important;
                padding: 42px 0 86px !important;
            }

            .page-hero::after,
            .about-hero::after,
            .news-hero::after,
            .rd-hero::after,
            .contact-hero::after,
            .academic-page .page-hero::after,
            .profile-page .profile-hero::after {
                bottom: -64px !important;
                height: 116px !important;
            }

            .contact-page .contact-hero .hero-inner {
                grid-template-columns: 1fr !important;
                gap: 22px !important;
            }

            .page-title,
            .about-title,
            .rd-title,
            .news-title-page,
            .contact-title,
            .title-page,
            .profile-hero-title {
                font-size: clamp(28px, 8.5vw, 40px) !important;
                line-height: 1.08 !important;
                letter-spacing: -.6px !important;
            }

            .page-desc,
            .about-subtitle,
            .rd-desc,
            .contact-subtitle {
                font-size: 14px !important;
                line-height: 1.65 !important;
            }

            .content-section,
            .about-section,
            .vm-section,
            .so-section,
            .news-section,
            .rd-section,
            .contact-section,
            .news-content-section,
            .visi-misi-section {
                margin-top: -34px !important;
                padding-bottom: 68px !important;
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
