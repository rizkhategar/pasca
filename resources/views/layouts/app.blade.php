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
    <style>
        .page-hero,
        .about-hero,
        .news-hero,
        .rd-hero,
        .contact-hero,
        .academic-page .page-hero,
        .profile-page .profile-hero {
            background: linear-gradient(135deg, #062e62 0%, #0b5f9f 62%, #2389cf 100%) !important;
        }

        .page-hero::before,
        .about-hero::before,
        .news-hero::before,
        .rd-hero::before,
        .contact-hero::before,
        .academic-page .page-hero::before,
        .profile-page .profile-hero::before {
            background: none !important;
            background-image: none !important;
            opacity: 0 !important;
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
            left: -5% !important;
            right: -5% !important;
            top: auto !important;
            bottom: -72px !important;
            width: auto !important;
            height: 135px !important;
            z-index: 2 !important;
            pointer-events: none !important;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0 !important;
            background: #f8fbff !important;
            box-shadow: none !important;
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
    </style>
    @stack('styles')
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
