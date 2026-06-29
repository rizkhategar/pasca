@php
    $primaryWhatsapp = $whatsappAdmins[0] ?? null;
@endphp

@extends('layouts.app')

@section('title', 'Kontak PMB - Pascasarjana UNW')
@section('body_class', 'contact-page')

@section('content')
    <section class="page-hero">
        <div class="hero-dots"></div>
        @include('components.hero-spotlight')

        <div class="container relative z-10">
            <div class="hero-inner !block !ml-0 !mr-auto !max-w-[820px] !text-left">
                <div class="hero-kicker !mx-0">
                    <i class="fas fa-headset"></i>
                    <span>Layanan Informasi Pascasarjana</span>
                </div>

                <h1 class="page-title !mx-0 !max-w-[780px] !text-left">
                    Kontak Pendaftaran Mahasiswa Baru
                </h1>

                <p class="page-desc !mx-0 !max-w-[720px] !text-left">
                    Hubungi layanan resmi Pascasarjana Universitas Ngudi Waluyo untuk informasi pendaftaran,
                    konsultasi program, dan bantuan administrasi calon mahasiswa.
                </p>

                <div class="hero-meta !justify-start">
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
                        <div class="contact-icon"><i class="fas fa-map-location-dot"></i></div>
                        <div class="contact-info">
                            <h2>Sekretariat PMB</h2>
                            <p>Jl. Diponegoro No. 186 Ungaran, Kabupaten Semarang</p>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon"><i class="fas fa-envelope-open-text"></i></div>
                        <div class="contact-info">
                            <h2>Email Resmi</h2>
                            <a href="mailto:pmb@unw.ac.id">pmb@unw.ac.id</a>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon"><i class="fas fa-phone-volume"></i></div>
                        <div class="contact-info">
                            <h2>Telepon / Fax</h2>
                            <p>(024)-6925408</p>
                        </div>
                    </article>

                    @if ($primaryWhatsapp)
                        <article
                            class="contact-card"
                            data-wa-contact-card
                            data-wa-admin-name="{{ $primaryWhatsapp['name'] ?? 'Admin WhatsApp' }}"
                            data-wa-admin-number="{{ $primaryWhatsapp['number'] ?? '' }}"
                            data-wa-admin-url="{{ $primaryWhatsapp['url'] ?? '#' }}">
                            <div class="contact-icon"><i class="fab fa-whatsapp"></i></div>
                            <div class="contact-info">
                                <h2>{{ $primaryWhatsapp['name'] ?? 'Admin WhatsApp' }}</h2>
                                <a href="{{ $primaryWhatsapp['url'] ?? '#' }}" target="_blank" rel="noopener">
                                    {{ $primaryWhatsapp['number'] ?? 'Nomor WhatsApp belum tersedia' }}
                                </a>
                            </div>
                        </article>
                    @endif
                </div>

                <article class="map-card">
                    <div class="map-top">
                        <div class="map-title-wrap">
                            <div class="map-pin"><i class="fas fa-location-dot"></i></div>
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

    @if ($primaryWhatsapp)
        <a
            class="wa-floating"
            href="{{ $primaryWhatsapp['url'] ?? '#' }}"
            target="_blank"
            rel="noopener"
            aria-label="Chat WhatsApp {{ $primaryWhatsapp['name'] ?? 'Admin WhatsApp' }}"
            data-wa-admin-name="{{ $primaryWhatsapp['name'] ?? 'Admin WhatsApp' }}"
            data-wa-admin-number="{{ $primaryWhatsapp['number'] ?? '' }}"
            data-wa-admin-url="{{ $primaryWhatsapp['url'] ?? '#' }}">
            <i class="fab fa-whatsapp"></i>
        </a>

        @include('components.contact-whatsapp-modal')
    @endif
@endsection
