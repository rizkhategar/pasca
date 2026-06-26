@extends('layouts.app')

@section('title', 'Kontak PMB - Pascasarjana UNW')
@section('body_class', 'contact-page')

@section('content')
    <section class="contact-hero">
        <div class="hero-pattern-dots"></div>
        <div class="hero-line"></div>

        <div class="container">
            <div class="hero-inner">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-headset"></i>
                        <span>Layanan Informasi Pascasarjana</span>
                    </div>

                    <h1 class="contact-title">Kontak Pendaftaran Mahasiswa Baru</h1>
                    <p class="contact-subtitle">
                        Hubungi layanan resmi Pascasarjana Universitas Ngudi Waluyo untuk informasi pendaftaran,
                        konsultasi program, dan bantuan administrasi calon mahasiswa.
                    </p>

                    <div class="hero-meta">
                        <span><i class="fas fa-user-graduate"></i>PMB Pascasarjana</span>
                        <span><i class="fas fa-clock"></i>Respon Cepat</span>
                        <span><i class="fas fa-shield-check"></i>Kontak Resmi</span>
                    </div>
                </div>

                <div class="hero-info-card">
                    <div class="hero-info-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Butuh Bantuan?</h3>
                    <p>Pilih kanal kontak yang tersedia atau buka WhatsApp untuk terhubung langsung dengan admin PMB.</p>
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

                <a class="news-more-link" href="https://wa.me/6285730339469" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i>
                    Chat Admin
                </a>
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
                            <h2>Admin WhatsApp</h2>
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

    <a class="wa-floating" href="https://wa.me/6285730339469" target="_blank" rel="noopener" aria-label="Chat WhatsApp Admin PMB">
        <i class="fab fa-whatsapp"></i>
    </a>
@endsection
