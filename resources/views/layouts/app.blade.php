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
    @vite(['resources/css/app.css', 'resources/css/home-fixes.css', 'resources/css/slider-mobile-fix.css', 'resources/css/slider-dot-fix.css', 'resources/js/app.js', 'resources/js/hero-slider-fix.js'])
    @stack('styles')
</head>
<body class="@yield('body_class')">
    @include('components.header')
    <main class="app-main">
        @yield('content')
    </main>
    @include('components.footer')
    @stack('scripts')
</body>
</html>
