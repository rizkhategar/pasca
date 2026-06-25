@extends('layouts.app')

@section('title', $dosen->nama . ' - Detail Profil & SINTA Dosen')
@section('body_class', 'research-detail-page')

@section('content')
    <div class="profile-page">
        <section class="profile-hero">
            <div class="hero-dots"></div>
            <div class="hero-line"></div>

            <div class="profile-container">
                <div class="hero-inner">
                    <a href="{{ route('riset.dosen') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke Daftar Dosen</span>
                    </a>

                    <div class="profile-tag">
                        <i class="fas fa-user-tie"></i>
                        <span>Profil Resmi Pascasarjana</span>
                    </div>

                    <h1 class="profile-hero-title">{{ $dosen->nama }}</h1>

                    <div class="profile-meta">
                        <span><i class="fas fa-id-badge"></i>SINTA ID: <strong>{{ $dosen->sinta_id }}</strong></span>
                        <span><i class="fas fa-graduation-cap"></i>{{ $dosen->program_studi }}</span>
                        <span><i class="fas fa-university"></i>{{ $dosen->institusi ?? 'Universitas Ngudi Waluyo' }}</span>
                    </div>
                </div>
            </div>

            <div class="hero-wave">
                <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                    <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
                </svg>
            </div>
        </section>

        <section class="profile-main-body">
            <article class="content-block identity-card-grid">
                <div class="profile-photo-section">
                    <div class="profile-photo-frame">
                        @if($dosen->profile_photo)
                            <img src="{{ asset('assets/images/' . $dosen->profile_photo) }}" alt="{{ $dosen->nama }}">
                        @else
                            <img src="{{ asset('assets/images/default-user.png') }}" alt="{{ $dosen->nama }}">
                        @endif
                    </div>

                    <div class="profile-photo-caption">
                        <span><i class="fas fa-user-check"></i>Data Profil Dosen</span>
                        <span><i class="fas fa-chart-line"></i>Rekap Kinerja SINTA</span>
                    </div>
                </div>

                <div class="profile-identity-content">
                    <div class="profile-content-heading">
                        <div class="profile-heading-title">
                            <div class="profile-heading-icon"><i class="fas fa-address-card"></i></div>
                            <div>
                                <h2>Identitas Akademik</h2>
                                <p>Informasi dosen, program studi, dan ringkasan skor SINTA.</p>
                            </div>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-box"><div class="stat-number">{{ number_format($dosen->sinta_score_overall ?? 0) }}</div><div class="stat-desc">Overall Score</div></div>
                        <div class="stat-box"><div class="stat-number">{{ number_format($dosen->sinta_score_3yr ?? 0) }}</div><div class="stat-desc">3 Year Score</div></div>
                        <div class="stat-box"><div class="stat-number">{{ number_format($dosen->affil_score ?? 0) }}</div><div class="stat-desc">Affil Score</div></div>
                        <div class="stat-box"><div class="stat-number">{{ number_format($dosen->affil_score_3yr ?? 0) }}</div><div class="stat-desc">Affil 3Yr</div></div>
                    </div>

                    <h3 class="block-title">Biodata Akademik</h3>
                    <table class="table-profile">
                        <tr><td class="label">Nama Lengkap</td><td class="value highlight">{{ $dosen->nama }}</td></tr>
                        <tr><td class="label">Program Studi</td><td class="value">{{ $dosen->program_studi }}</td></tr>
                        @if($dosen->bidang_minat)
                            <tr><td class="label">Bidang Minat</td><td class="value">{{ $dosen->bidang_minat }}</td></tr>
                        @endif
                    </table>
                </div>
            </article>
        </section>
    </div>
@endsection
