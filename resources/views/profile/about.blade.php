@extends('layouts.app')

@section('title', 'Tentang Pascasarjana | Universitas Ngudi Waluyo')
@section('body_class', 'about-page')

@section('content')
    <section class="about-hero">
        <div class="hero-dots"></div>
        @include('components.hero-spotlight')

        <div class="about-container">
            <div class="hero-inner">
                <div class="hero-kicker">
                    <i class="fas fa-university"></i>
                    <span>Profil Pascasarjana</span>
                </div>

                <h1 class="about-title">Tentang Pascasarjana</h1>

                <p class="about-subtitle">
                    Mengenal lebih dekat profil, arah pengembangan, dan komitmen Pascasarjana Universitas Ngudi Waluyo.
                </p>

                <div class="hero-meta">
                    <span><i class="fas fa-building-columns"></i>Universitas Ngudi Waluyo</span>
                    <span><i class="fas fa-graduation-cap"></i>Pascasarjana</span>
                </div>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <main class="about-section">
        <div class="about-container">
            @if($tentang)
                <section class="about-layout">
                    <article class="about-main-card">
                        <div class="section-kicker">
                            <i class="fas fa-circle-info"></i>
                            <span>{{ $tentang->subheading ?? 'Tentang Kami' }}</span>
                        </div>

                        <h2>{{ $tentang->heading ?? 'Tentang Pascasarjana Universitas Ngudi Waluyo' }}</h2>

                        <div class="about-desc">
                            {!! nl2br(e($tentang->description ?? '')) !!}
                        </div>
                    </article>

                    <aside class="about-points-card">
                        <div class="points-header">
                            <h3>Keunggulan Pascasarjana</h3>
                            <p>Informasi utama yang menjadi identitas dan nilai unggulan Pascasarjana UNW.</p>
                        </div>

                        @if(!empty($tentang->points) && is_array($tentang->points))
                            <div class="about-points">
                                @foreach($tentang->points as $point)
                                    <article class="point-card">
                                        <div class="point-icon">
                                            @if(!empty($point['icon']))
                                                <img src="{{ asset('storage/' . $point['icon']) }}" alt="Icon {{ $point['title'] ?? 'Poin Pascasarjana' }}">
                                            @else
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </div>

                                        <div class="point-text">
                                            <h3>{{ $point['title'] ?? 'Poin Unggulan' }}</h3>
                                            <p>{{ $point['description'] ?? '' }}</p>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-points">
                                <em>Belum ada poin fitur yang ditambahkan.</em>
                            </div>
                        @endif
                    </aside>
                </section>

                @if(!empty($tentang->direktur_name) || !empty($tentang->direktur_message))
                    <section class="sambutan-section">
                        <div class="sambutan-title">
                            <div class="section-kicker">
                                <i class="fas fa-comment-dots"></i>
                                <span>{{ $tentang->direktur_heading ?? 'Sambutan Direktur' }}</span>
                            </div>

                            <h2>{{ $tentang->direktur_greeting ?? 'Selamat Datang di Pascasarjana Universitas Ngudi Waluyo' }}</h2>
                        </div>

                        <article class="sambutan-card">
                            <div class="sambutan-img">
                                @if(!empty($tentang->direktur_image))
                                    <img src="{{ asset('storage/' . $tentang->direktur_image) }}" alt="{{ $tentang->direktur_name ?? 'Direktur Pascasarjana' }}">
                                @else
                                    <div class="director-placeholder">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="sambutan-content">
                                <div class="quote-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>

                                <div class="sambutan-text">
                                    {!! $tentang->direktur_message ?? '' !!}
                                </div>

                                <div class="direktur-info">
                                    <h4>{{ $tentang->direktur_name ?? 'Nama Direktur Belum Diisi' }}</h4>
                                    <p>{{ $tentang->direktur_title ?? 'Direktur Pascasarjana Universitas Ngudi Waluyo' }}</p>
                                </div>
                            </div>
                        </article>
                    </section>
                @endif
            @else
                <div class="empty-card">
                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                    <h3>Data Tentang Pascasarjana Belum Diisi</h3>
                </div>
            @endif
        </div>
    </main>
@endsection
