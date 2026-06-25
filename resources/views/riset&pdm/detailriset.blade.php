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
    </div>
@endsection
