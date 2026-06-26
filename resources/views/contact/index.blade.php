@php
    $primaryWhatsapp = $whatsappAdmins[0] ?? [
        'name' => 'Admin WhatsApp',
        'number' => '+62 857-3033-9469',
        'url' => 'https://wa.me/6285730339469',
    ];
@endphp

@extends('layouts.app')

@section('title', 'Kontak PMB - Pascasarjana UNW')
@section('body_class', 'contact-page')

@section('content')
    <section class="page-hero contact-hero !bg-gradient-to-br !from-[#031f42] !via-[#064276] !to-[#0b5f9f]">
        <div class="hero-dots"></div>
        <div class="hero-line !right-[-120px] !top-[-78px] !h-[360px] !w-[360px] ![transform:none] !overflow-visible !rounded-full !border !border-white/15 !bg-[radial-gradient(circle_at_36%_36%,rgba(255,255,255,.24),rgba(45,156,219,.18)_34%,rgba(7,43,87,.08)_58%,transparent_72%)] !shadow-[0_0_90px_rgba(45,156,219,.24)]" aria-hidden="true">
            <span class="absolute left-20 top-24 h-16 w-16 rounded-3xl bg-[#f7b500]/20 shadow-[0_20px_52px_rgba(247,181,0,.18)]"></span>
            <span class="absolute bottom-16 right-28 h-28 w-28 rounded-full border border-white/14 bg-white/5 backdrop-blur-md"></span>
            <span class="absolute bottom-28 left-8 h-[3px] w-44 rotate-[-22deg] rounded-full bg-gradient-to-r from-transparent via-white/35 to-transparent"></span>
        </div>
        <div class="absolute inset-0 z-[1] bg-[radial-gradient(circle_at_18%_20%,rgba(45,156,219,.42),transparent_30%)]"></div>
        <div class="absolute inset-0 z-[1] bg-[radial-gradient(circle_at_85%_18%,rgba(247,181,0,.12),transparent_24%)]"></div>
        <div class="absolute bottom-16 left-0 z-[2] h-px w-full bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

        <div class="container">
            <div class="hero-inner">
                <div class="hero-kicker">
                    <i class="fas fa-headset"></i>
                    <span>Layanan Informasi Pascasarjana</span>
                </div>

                <h1 class="page-title">Kontak Pendaftaran Mahasiswa Baru</h1>

                <p class="page-desc">
                    Hubungi layanan resmi Pascasarjana Universitas Ngudi Waluyo untuk informasi pendaftaran,
                    konsultasi program, dan bantuan administrasi calon mahasiswa.
                </p>

                <div class="hero-meta">
                    <span><i class="fas fa-user-graduate"></i>PMB Pascasarjana</span>
                    <span><i class="fas fa-clock"></i>Respon Cepat</span>
                    <span><i class="fas fa-shield-halved"></i>Kontak Resmi</span>
                </div>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="profile-content-heading">
                <div class="profile-heading-title">
                    <div class="profile-heading-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <div>
                        <h2>Informasi Kontak PMB</h2>
                        <p>Gunakan kontak resmi berikut untuk mendapatkan informasi pendaftaran mahasiswa baru.</p>
                    </div>
                </div>
            </div>

            <div class="contact-grid">
                <div class="contact-list">
                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-location-dot"></i>
                        </div>
                        <div class="contact-info">
                            <h2>Sekretariat PMB</h2>
                            <p>Jl. Diponegoro No. 186 Ungaran, Kabupaten Semarang</p>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div class="contact-info">
                            <h2>Email Resmi</h2>
                            <a href="mailto:pmb@unw.ac.id">pmb@unw.ac.id</a>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone-volume"></i>
                        </div>
                        <div class="contact-info">
                            <h2>Telepon / Fax</h2>
                            <p>(024)-6925408</p>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="contact-info">
                            <h2>{{ $primaryWhatsapp['name'] ?? 'Admin WhatsApp' }}</h2>
                            <a href="{{ $primaryWhatsapp['url'] }}" target="_blank" rel="noopener">
                                {{ $primaryWhatsapp['number'] ?? '+62 857-3033-9469' }}
                            </a>
                        </div>
                    </article>
                </div>

                <article class="map-card">
                    <div class="map-top">
                        <div class="map-title-wrap">
                            <div class="map-pin">
                                <i class="fas fa-location-dot"></i>
                            </div>
                            <div>
                                <h2>Lokasi PMB Universitas Ngudi Waluyo</h2>
                                <p>Temukan lokasi sekretariat pendaftaran melalui peta interaktif.</p>
                            </div>
                        </div>

                        <a class="map-action" href="https://www.google.com/maps/search/?api=1&query=PMB%20Universitas%20Ngudi%20Waluyo" target="_blank" rel="noopener">
                            <i class="fas fa-route"></i>
                            Buka Maps
                        </a>
                    </div>

                    <iframe class="map-frame" src="https://www.google.com/maps?q=PMB%20Universitas%20Ngudi%20Waluyo&output=embed" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                </article>
            </div>
        </div>
    </section>

    <a class="wa-floating" href="{{ $primaryWhatsapp['url'] }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp Admin PMB">
        <i class="fab fa-whatsapp"></i>
    </a>

    @include('components.contact-whatsapp-modal')
@endsection
