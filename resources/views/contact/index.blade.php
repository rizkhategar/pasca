@extends('layouts.app')

@section('title', 'Kontak PMB - Pascasarjana UNW')
@section('body_class', 'contact-page')

@section('content')
    <section class="contact-hero">
        <div class="hero-pattern-dots"></div>
        <div class="hero-line"></div>
        <div class="hero-orb"></div>

        <div class="container">
            <div class="hero-inner">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-graduation-cap"></i>
                        Informasi Resmi PMB Pascasarjana
                    </div>

                    <h1 class="contact-title">Kontak Pendaftaran Mahasiswa Baru</h1>
                    <p class="contact-subtitle">PMB Universitas Ngudi Waluyo</p>
                </div>

                <div class="hero-info-card">
                    <div class="hero-info-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Layanan Informasi</h3>
                    <p>Hubungi admin PMB untuk informasi pendaftaran, jadwal, dan layanan akademik.</p>
                </div>
            </div>
        </div>

        <div class="hero-shape">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,72 C220,120 430,20 720,58 C980,92 1180,118 1440,42 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <div class="section-heading">
                <div class="section-kicker">Hubungi Kami</div>
                <h2>Informasi Kontak PMB</h2>
                <p>
                    Silakan gunakan kontak resmi berikut untuk mendapatkan informasi pendaftaran mahasiswa baru
                    Universitas Ngudi Waluyo.
                </p>
            </div>

            <div class="contact-grid">
                <div class="contact-list">
                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-location-dot"></i>
                        </div>
                        <div class="contact-info">
                            <h2>Sekretariat</h2>
                            <p>Jl. Diponegoro No. 186 Ungaran</p>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div class="contact-info">
                            <h2>Email</h2>
                            <a href="mailto:pmb@unw.ac.id">pmb@unw.ac.id</a>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone-volume"></i>
                        </div>
                        <div class="contact-info">
                            <h2>Fax</h2>
                            <p>(024)-6925408</p>
                        </div>
                    </article>

                    <article class="contact-card">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="contact-info">
                            <h2>Whatsapp Admin</h2>
                            <a href="https://wa.me/6285730339469" target="_blank" rel="noopener">
                                +62 857-3033-9469
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
                                <h2>PMB Universitas Ngudi Waluyo</h2>
                                <p>Lokasi sekretariat pendaftaran mahasiswa baru.</p>
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

    <a class="wa-floating" href="https://wa.me/6285730339469" target="_blank" rel="noopener" aria-label="Chat WhatsApp Admin PMB">
        <i class="fab fa-whatsapp"></i>
    </a>
@endsection
