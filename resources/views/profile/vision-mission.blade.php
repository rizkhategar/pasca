@extends('layouts.app')

@section('title', 'Visi, Misi - Pascasarjana UNW')
@section('body_class', 'vision-mission-page')

@section('content')
    <section class="page-hero">
        <div class="hero-dots"></div>
        <div class="hero-line"></div>

        <div class="vm-container">
            <div class="hero-inner">
                <div class="hero-kicker">
                    <i class="fas fa-bullseye"></i>
                    <span>Profil Pascasarjana</span>
                </div>

                <h1 class="page-title">Visi & Misi</h1>

                <p class="page-desc">
                    Arah, tujuan, dan komitmen Pascasarjana Universitas Ngudi Waluyo dalam pengembangan pendidikan,
                    penelitian, dan pengabdian kepada masyarakat.
                </p>

                <div class="hero-meta">
                    <span><i class="fas fa-university"></i>Universitas Ngudi Waluyo</span>
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

    <main class="visi-misi-section">
        <div class="vm-container">
            <div class="visi-misi-wrapper">
                @if($visiMisi)
                    @php
                        $sections = [
                            ['number' => '01', 'icon' => 'fas fa-eye', 'title' => 'Visi', 'content' => $visiMisi->visi ?? '<p><em>Belum ada konten visi. Silakan isi melalui Admin Panel.</em></p>'],
                            ['number' => '02', 'icon' => 'fas fa-list-check', 'title' => 'Misi', 'content' => $visiMisi->misi ?? '<p><em>Belum ada konten misi. Silakan isi melalui Admin Panel.</em></p>'],
                            ['number' => '03', 'icon' => 'fas fa-bullseye', 'title' => $visiMisi->judul_tujuan ?? 'Tujuan', 'content' => $visiMisi->tujuan ?? '<p><em>Belum ada konten tujuan. Silakan isi melalui Admin Panel.</em></p>'],
                            ['number' => '04', 'icon' => 'fas fa-layer-group', 'title' => $visiMisi->judul_tujuan_bidang ?? 'Tujuan UNW Dalam Bidang', 'content' => $visiMisi->tujuan_bidang ?? '<p><em>Belum ada konten tujuan dalam bidang. Silakan isi melalui Admin Panel.</em></p>'],
                            ['number' => '05', 'icon' => 'fas fa-chart-line', 'title' => $visiMisi->judul_sasaran_target ?? 'Sasaran dan Target', 'content' => $visiMisi->sasaran_target ?? '<p><em>Belum ada konten sasaran dan target. Silakan isi melalui Admin Panel.</em></p>'],
                        ];
                    @endphp

                    <div class="visi-misi-grid">
                        @foreach($sections as $section)
                            <section class="visi-misi-card">
                                <div class="card-side">
                                    <div class="card-number">{{ $section['number'] }}</div>
                                </div>

                                <div class="card-main">
                                    <div class="card-header">
                                        <div class="card-icon"><i class="{{ $section['icon'] }}"></i></div>
                                        <h2>{{ $section['title'] }}</h2>
                                    </div>

                                    <div class="card-content">
                                        {!! $section['content'] !!}
                                    </div>
                                </div>
                            </section>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-card">
                        <div class="empty-state-icon"><i class="fas fa-folder-open"></i></div>
                        <h3>Data Visi & Misi Belum Diisi</h3>
                        <p>Silakan login ke Admin Panel untuk mengisi data visi, misi, tujuan, dan sasaran Pascasarjana.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
