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
    <section class="contact-hero !pt-16 !pb-20 md:!pt-20 md:!pb-24">
        <div class="hero-pattern-dots"></div>
        <div class="hero-line"></div>

        <div class="container relative z-[4]">
            <div class="hero-inner !max-w-none grid items-center gap-10 lg:grid-cols-[1.28fr_.72fr]">
                <div class="hero-content max-w-[820px]">
                    <div class="hero-badge !mb-6">
                        <i class="fas fa-headset"></i>
                        <span>Layanan Informasi Pascasarjana</span>
                    </div>

                    <h1 class="contact-title !mb-5 max-w-[850px] !text-[clamp(38px,5.2vw,68px)] !leading-[1.02]">
                        Kontak Pendaftaran Mahasiswa Baru
                    </h1>

                    <p class="contact-subtitle max-w-[760px] !text-[clamp(15px,1.45vw,19px)] !leading-[1.8]">
                        Hubungi layanan resmi Pascasarjana Universitas Ngudi Waluyo untuk informasi pendaftaran,
                        konsultasi program, dan bantuan administrasi calon mahasiswa.
                    </p>

                    <div class="hero-meta !mt-7">
                        <span><i class="fas fa-user-graduate"></i>PMB Pascasarjana</span>
                        <span><i class="fas fa-clock"></i>Respon Cepat</span>
                        <span><i class="fas fa-shield-halved"></i>Kontak Resmi</span>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a
                            href="{{ $primaryWhatsapp['url'] }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-[#f7b500] px-6 text-sm font-black uppercase tracking-[.04em] text-[#072b57] shadow-[0_16px_34px_rgba(247,181,0,.28)] transition hover:-translate-y-1 hover:bg-white">
                            <i class="fab fa-whatsapp"></i>
                            Chat Admin
                        </a>

                        <a
                            href="https://www.google.com/maps/search/?api=1&query=PMB%20Universitas%20Ngudi%20Waluyo"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex h-12 items-center justify-center gap-2 rounded-full border border-white/20 bg-white/10 px-6 text-sm font-black uppercase tracking-[.04em] text-white backdrop-blur-md transition hover:-translate-y-1 hover:bg-white hover:text-[#072b57]">
                            <i class="fas fa-location-dot"></i>
                            Lihat Lokasi
                        </a>
                    </div>
                </div>

                <aside class="contact-assist-panel relative overflow-hidden rounded-[34px] border border-white/20 bg-white/12 p-7 text-white shadow-[0_28px_80px_rgba(2,16,38,.22)] backdrop-blur-xl">
                    <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-20 left-10 h-48 w-48 rounded-full bg-[#f7b500]/10"></div>

                    <div class="relative">
                        <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-[#f7b500] text-2xl text-[#072b57] shadow-[0_18px_38px_rgba(247,181,0,.28)]">
                            <i class="fas fa-comments"></i>
                        </div>

                        <h2 class="m-0 mb-3 text-2xl font-black leading-tight">Butuh Bantuan?</h2>
                        <p class="m-0 mb-6 text-sm font-semibold leading-7 text-white/82">
                            Pilih kanal kontak yang tersedia atau buka WhatsApp untuk terhubung langsung dengan admin PMB.
                        </p>

                        <div class="grid gap-3">
                            <div class="flex items-center gap-3 rounded-2xl border border-white/14 bg-white/10 p-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/12 text-[#f7b500]"><i class="fab fa-whatsapp"></i></span>
                                <div class="min-w-0">
                                    <strong class="block text-sm leading-tight">{{ $primaryWhatsapp['name'] ?? 'Admin WhatsApp' }}</strong>
                                    <span class="block truncate text-xs font-semibold text-white/72">{{ $primaryWhatsapp['number'] ?? '+62 857-3033-9469' }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-2xl border border-white/14 bg-white/10 p-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/12 text-[#f7b500]"><i class="fas fa-envelope"></i></span>
                                <div class="min-w-0">
                                    <strong class="block text-sm leading-tight">Email Resmi</strong>
                                    <span class="block truncate text-xs font-semibold text-white/72">pmb@unw.ac.id</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="contact-section !pt-16 !pb-24">
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

                <a class="news-more-link" href="{{ $primaryWhatsapp['url'] }}" target="_blank" rel="noopener">
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
