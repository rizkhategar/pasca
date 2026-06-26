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
