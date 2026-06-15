@php
    $academicProgramsNav = \App\Http\Controllers\AcademicController::getNavigationData();
    $isHome = request()->routeIs('home');
    $isProfile = request()->routeIs('tentang') || request()->routeIs('visi-misi') || request()->routeIs('profil.*');
    $isAcademic = request()->routeIs('akademik.*');
    $isNews = request()->routeIs('news.*');
    $isResearch = request()->routeIs('riset.*');
    $isContact = request()->routeIs('contact.*');
@endphp

<link rel="icon" href="{{ asset('logo_unwnobg.png') }}" type="image/png" sizes="64x64">
<link rel="shortcut icon" href="{{ asset('logo_unwnobg.png') }}" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('logo_unwnobg.png') }}">

<style>
    :root {
        --primary: #072b57;
        --primary-dark: #052044;
        --yellow: #f7b500;
        --white: #ffffff;
        --light: #eef4f5;
        --text: #111827;
    }

    #siteHeader,
    #siteHeader * {
        box-sizing: border-box;
    }

    #siteHeader {
        position: sticky !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 9999 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .12) !important;
        font-family: Arial, Helvetica, sans-serif !important;
        background: var(--light) !important;
    }

    #siteHeader .top-header {
        width: 100% !important;
        background: var(--light) !important;
        padding: 12px 0 !important;
    }

    #siteHeader .header-container {
        width: min(100% - 32px, 1120px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    #siteHeader .brand-wrapper,
    #siteHeader .brand-unw {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
    }

    #siteHeader .brand-wrapper {
        gap: 16px !important;
        width: 100% !important;
    }

    #siteHeader .brand-unw {
        gap: 14px !important;
        min-width: 0 !important;
        flex: 0 1 auto !important;
    }

    #siteHeader .brand-logo {
        width: 70px !important;
        height: 70px !important;
        min-width: 70px !important;
        max-width: 70px !important;
        object-fit: contain !important;
        flex: 0 0 70px !important;
        display: block !important;
    }

    #siteHeader .brand-main {
        font-size: 44px !important;
        line-height: 1 !important;
        font-weight: 900 !important;
        color: var(--primary) !important;
        letter-spacing: 1px !important;
        margin: 0 !important;
        padding: 0 !important;
        white-space: nowrap !important;
    }

    #siteHeader .brand-sub {
        margin: 4px 0 0 !important;
        font-size: 8px !important;
        line-height: 1.2 !important;
        font-weight: 800 !important;
        color: var(--primary) !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
    }

    #siteHeader .brand-divider {
        width: 2px !important;
        height: 46px !important;
        background: var(--primary) !important;
        opacity: .75 !important;
        flex: 0 0 2px !important;
        display: block !important;
    }

    #siteHeader .brand-school {
        color: var(--primary) !important;
        font-weight: 900 !important;
        font-size: 16px !important;
        line-height: 1.25 !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    #siteHeader .navbar {
        background: var(--primary) !important;
        min-height: 64px !important;
        position: relative !important;
        z-index: 1000 !important;
    }

    #siteHeader .nav-content {
        display: flex !important;
        align-items: stretch !important;
        min-height: 64px !important;
        position: relative !important;
        overflow: visible !important;
    }

    #siteHeader .nav-menu {
        display: flex !important;
        align-items: stretch !important;
        height: 64px !important;
        min-height: 64px !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
        background: transparent !important;
    }

    #siteHeader .nav-item {
        position: relative !important;
        height: 64px !important;
        display: flex !important;
        flex-direction: column !important;
        background: transparent !important;
    }

    #siteHeader .nav-link {
        height: 64px !important;
        padding: 0 14px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        color: var(--white) !important;
        text-transform: uppercase !important;
        text-decoration: none !important;
        transition: .25s ease !important;
        white-space: nowrap !important;
        border: none !important;
        background: transparent !important;
        cursor: pointer !important;
    }

    #siteHeader .nav-link:hover,
    #siteHeader .nav-link.nav-click-active,
    #siteHeader .nav-link.nav-route-active,
    #siteHeader .nav-item.route-active > .nav-link,
    #siteHeader .nav-item:hover > .nav-link,
    #siteHeader .nav-item.open > .nav-link {
        background: var(--yellow) !important;
        color: var(--white) !important;
    }

    #siteHeader .nav-item.route-active::after,
    #siteHeader .nav-item.home-active.route-active::after {
        content: "" !important;
        position: absolute !important;
        left: 16px !important;
        right: 16px !important;
        bottom: 0 !important;
        height: 5px !important;
        border-radius: 8px 8px 0 0 !important;
        background: var(--yellow) !important;
        pointer-events: none !important;
    }

    #siteHeader .nav-item:hover::after,
    #siteHeader .nav-item.open::after,
    #siteHeader .nav-item.home-active.hide-indicator::after {
        display: none !important;
    }

    #siteHeader .chevron {
        width: 7px !important;
        height: 7px !important;
        border-right: 2px solid currentColor !important;
        border-bottom: 2px solid currentColor !important;
        transform: rotate(45deg) translateY(-2px) !important;
        transform-origin: center !important;
        display: inline-block !important;
        flex-shrink: 0 !important;
        margin-left: 2px !important;
        transition: transform .25s ease !important;
    }

    #siteHeader .nav-item:hover .chevron,
    #siteHeader .nav-item.open .chevron {
        transform: rotate(225deg) translateY(-1px) !important;
    }

    #siteHeader .dropdown {
        position: absolute !important;
        top: 64px !important;
        left: 0 !important;
        min-width: 255px !important;
        background: #ffffff !important;
        border-radius: 0 0 6px 6px !important;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .18) !important;
        padding: 8px 0 !important;
        opacity: 0 !important;
        visibility: hidden !important;
        transform: translateY(8px) !important;
        transition: .25s ease !important;
        z-index: 99999 !important;
    }

    #siteHeader .nav-item:hover .dropdown,
    #siteHeader .nav-item.open .dropdown {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
    }

    #siteHeader .dropdown a {
        display: block !important;
        width: 100% !important;
        min-width: 255px !important;
        padding: 11px 18px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        text-decoration: none !important;
        transition: .2s ease !important;
        white-space: nowrap !important;
        background: #ffffff !important;
    }

    #siteHeader .dropdown a:hover,
    #siteHeader .dropdown a:focus,
    #siteHeader .dropdown a.dropdown-route-active {
        background: var(--yellow) !important;
        color: var(--white) !important;
        padding-left: 23px !important;
    }

    #siteHeader .hamburger {
        display: none !important;
        margin-left: auto !important;
        width: 60px !important;
        height: 60px !important;
        border: none !important;
        background: transparent !important;
        cursor: pointer !important;
        padding: 0 !important;
        align-items: center !important;
        justify-content: center !important;
        flex-direction: column !important;
        gap: 6px !important;
        align-self: center !important;
        position: relative !important;
        -webkit-tap-highlight-color: transparent !important;
        outline: none !important;
        appearance: none !important;
    }

    #siteHeader .hamburger span {
        display: block !important;
        width: 32px !important;
        height: 4px !important;
        background: var(--primary) !important;
        border-radius: 999px !important;
        margin: 0 !important;
        flex-shrink: 0 !important;
    }

    @media (max-width: 1200px) {
        #siteHeader .nav-link {
            padding: 0 10px !important;
            font-size: 11px !important;
        }
    }

    @media (max-width: 992px) {
        #siteHeader .top-header {
            padding: 10px 84px 10px 0 !important;
        }

        #siteHeader .brand-logo {
            width: 62px !important;
            height: 62px !important;
            min-width: 62px !important;
            max-width: 62px !important;
            flex-basis: 62px !important;
        }

        #siteHeader .brand-main {
            font-size: 34px !important;
        }

        #siteHeader .brand-school {
            font-size: 13px !important;
        }

        #siteHeader .navbar {
            position: absolute !important;
            top: 10px !important;
            right: 16px !important;
            width: auto !important;
            min-height: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        #siteHeader .navbar .header-container {
            width: auto !important;
            margin: 0 !important;
        }

        #siteHeader .nav-content {
            min-height: 0 !important;
            justify-content: flex-end !important;
        }

        #siteHeader .hamburger {
            display: flex !important;
            width: 62px !important;
            height: 62px !important;
            margin-left: 0 !important;
        }

        #siteHeader .nav-menu {
            display: none !important;
            position: fixed !important;
            top: 90px !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            height: auto !important;
            max-height: calc(100vh - 90px) !important;
            overflow-y: auto !important;
            flex-direction: column !important;
            align-items: stretch !important;
            padding: 0 0 12px !important;
            background: var(--primary) !important;
            z-index: 10050 !important;
        }

        #siteHeader .nav-menu.show {
            display: flex !important;
        }

        #siteHeader .nav-item {
            width: 100% !important;
            height: auto !important;
        }

        #siteHeader .nav-link {
            width: 100% !important;
            height: 50px !important;
            padding: 0 18px !important;
            justify-content: space-between !important;
            font-size: 12px !important;
        }

        #siteHeader .nav-item::after {
            display: none !important;
        }

        #siteHeader .nav-item:hover .dropdown {
            display: none !important;
        }

        #siteHeader .nav-item.open .dropdown {
            display: block !important;
        }

        #siteHeader .dropdown {
            position: static !important;
            min-width: 100% !important;
            width: 100% !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
            display: none !important;
            padding: 5px 0 !important;
        }

        #siteHeader .dropdown a {
            min-width: 100% !important;
            padding: 12px 24px !important;
            white-space: normal !important;
        }
    }

    @media (max-width: 640px) {
        #siteHeader .header-container {
            width: min(100% - 28px, 1120px) !important;
        }

        #siteHeader .top-header {
            padding: 9px 76px 9px 0 !important;
        }

        #siteHeader .brand-wrapper {
            gap: 9px !important;
        }

        #siteHeader .brand-logo {
            width: 54px !important;
            height: 54px !important;
            min-width: 54px !important;
            max-width: 54px !important;
            flex-basis: 54px !important;
        }

        #siteHeader .brand-unw {
            gap: 8px !important;
        }

        #siteHeader .brand-main {
            font-size: 28px !important;
        }

        #siteHeader .brand-sub {
            font-size: 6.5px !important;
        }

        #siteHeader .brand-divider,
        #siteHeader .brand-school {
            display: none !important;
        }

        #siteHeader .navbar {
            top: 7px !important;
            right: 12px !important;
        }

        #siteHeader .hamburger {
            width: 58px !important;
            height: 58px !important;
        }

        #siteHeader .nav-menu {
            top: 80px !important;
            max-height: calc(100vh - 80px) !important;
        }
    }
</style>

<div class="site-header" id="siteHeader">
    <header class="top-header">
        <div class="header-container">
            <div class="brand-wrapper">
                <img src="{{ asset('assets/images/logo-unw.png') }}" alt="Logo UNW" class="brand-logo">
                <div class="brand-unw">
                    <div>
                        <div class="brand-main">UNW</div>
                        <div class="brand-sub">Universitas Ngudi Waluyo<br>Pascasarjana</div>
                    </div>
                    <div class="brand-divider"></div>
                    <div class="brand-school">Postgraduate School<br>Pascasarjana</div>
                </div>
            </div>
        </div>
    </header>

    <nav class="navbar">
        <div class="header-container">
            <div class="nav-content">
                <button class="hamburger" id="hamburger" type="button" aria-label="Menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>

                <ul class="nav-menu" id="navMenu">
                    <li class="nav-item home-active {{ $isHome ? 'route-active' : '' }}" id="homeNavItem">
                        <a href="{{ route('home') }}" class="nav-link {{ $isHome ? 'nav-route-active' : '' }}" data-nav="home">Beranda</a>
                    </li>

                    <li class="nav-item has-dropdown {{ $isProfile ? 'route-active' : '' }}">
                        <a href="#" class="nav-link dropdown-trigger {{ $isProfile ? 'nav-route-active' : '' }}">
                            <span>Profil</span><span class="chevron" aria-hidden="true"></span>
                        </a>
                        <div class="dropdown">
                            <a href="{{ route('tentang') }}" class="{{ request()->routeIs('tentang') ? 'dropdown-route-active' : '' }}">Tentang Pascasarjana</a>
                            <a href="{{ route('visi-misi') }}" class="{{ request()->routeIs('visi-misi') ? 'dropdown-route-active' : '' }}">Visi dan Misi</a>
                            <a href="{{ route('profil.struktur-organisasi') }}" class="{{ request()->routeIs('profil.struktur-organisasi') ? 'dropdown-route-active' : '' }}">Struktur Organisasi</a>
                        </div>
                    </li>

                    <li class="nav-item has-dropdown {{ $isAcademic ? 'route-active' : '' }}">
                        <a href="#" class="nav-link dropdown-trigger {{ $isAcademic ? 'nav-route-active' : '' }}">
                            <span>Akademik</span><span class="chevron" aria-hidden="true"></span>
                        </a>
                        <div class="dropdown">
                            @forelse($academicProgramsNav as $program)
                                <a href="{{ route('akademik.show', $program['slug']) }}" class="{{ request()->is('akademik/' . $program['slug']) ? 'dropdown-route-active' : '' }}">
                                    {{ $program['display_name'] }}
                                </a>
                            @empty
                                <a href="#">Data tidak tersedia</a>
                            @endforelse
                        </div>
                    </li>

                    <li class="nav-item {{ $isNews ? 'route-active' : '' }}">
                        <a href="{{ route('news.index') }}" class="nav-link {{ $isNews ? 'nav-route-active' : '' }}">Berita</a>
                    </li>

                    <li class="nav-item {{ $isResearch ? 'route-active' : '' }}">
                        <a href="{{ route('riset.dosen') }}" class="nav-link {{ $isResearch ? 'nav-route-active' : '' }}">Riset Dosen</a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('home') }}#layanan-mahasiswa" class="nav-link" id="edomNav" data-nav="edom">Edom</a>
                    </li>

                    <li class="nav-item">
                        <a href="https://pmb.unw.ac.id/" class="nav-link nav-external" data-external-nav="true">Admisi</a>
                    </li>

                    <li class="nav-item {{ $isContact ? 'route-active' : '' }}">
                        <a href="{{ route('contact.index') }}" class="nav-link {{ $isContact ? 'nav-route-active' : '' }}">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const siteHeader = document.getElementById('siteHeader');
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');
        const dropdownTriggers = document.querySelectorAll('#siteHeader .dropdown-trigger');
        const navLinks = document.querySelectorAll('#siteHeader .nav-link');

        if (siteHeader) {
            siteHeader.classList.remove('nav-collapsed');
        }

        function isCompactMode() {
            return window.innerWidth <= 992;
        }

        function clearClickActive() {
            navLinks.forEach((item) => item.classList.remove('nav-click-active'));
        }

        function closeNavMenu() {
            if (!navMenu || !hamburger) return;
            navMenu.classList.remove('show');
            hamburger.setAttribute('aria-expanded', 'false');
            document.querySelectorAll('#siteHeader .nav-item.has-dropdown').forEach((item) => item.classList.remove('open'));
        }

        navLinks.forEach((link) => {
            link.addEventListener('click', function() {
                clearClickActive();
                if (link.dataset.externalNav === 'true') return;
                link.classList.add('nav-click-active');
            });
        });

        window.addEventListener('pageshow', function() {
            if (siteHeader) siteHeader.classList.remove('nav-collapsed');
            clearClickActive();
            closeNavMenu();
        });

        if (hamburger && navMenu) {
            hamburger.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                navMenu.classList.toggle('show');
                hamburger.setAttribute('aria-expanded', navMenu.classList.contains('show') ? 'true' : 'false');
            }, true);
        }

        dropdownTriggers.forEach((trigger) => {
            trigger.addEventListener('click', function(event) {
                if (!isCompactMode()) return;
                event.preventDefault();
                event.stopImmediatePropagation();
                const currentItem = trigger.closest('.nav-item');
                document.querySelectorAll('#siteHeader .nav-item.has-dropdown').forEach((item) => {
                    if (item !== currentItem) item.classList.remove('open');
                });
                currentItem.classList.toggle('open');
            }, true);
        });

        document.addEventListener('click', function(event) {
            if (!navMenu || !hamburger || !isCompactMode()) return;
            if (!navMenu.contains(event.target) && !hamburger.contains(event.target)) closeNavMenu();
        });

        window.addEventListener('scroll', function() {
            if (siteHeader) siteHeader.classList.remove('nav-collapsed');
        });

        window.addEventListener('resize', function() {
            if (!siteHeader) return;
            siteHeader.classList.remove('nav-collapsed');
            closeNavMenu();
        });
    });
</script>
