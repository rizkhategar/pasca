@php
    $dataProgram = isset($program['data']) ? $program['data'] : $program;
    $namaProgram = $dataProgram['unwProgramStudi']['nama'] ?? 'Program Akademik';
    $bodyContent = $dataProgram['body'] ?? '<p class="empty-state">Konten belum tersedia untuk program ini.</p>';
    $createdAt = isset($dataProgram['createdAt'])
        ? \Carbon\Carbon::parse($dataProgram['createdAt'])->translatedFormat('d F Y')
        : null;
@endphp

@extends('layouts.app')

@section('title', $namaProgram . ' - Pascasarjana UNW')
@section('body_class', 'academic-page')

@section('content')
    <section class="page-hero">
        <div class="hero-dots"></div>
        <div class="hero-line"></div>

        <div class="container">
            <div class="hero-inner">
                <a href="javascript:history.back()" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                <div class="category-pill">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Akademik Pascasarjana</span>
                </div>

                <h1 class="title-page">{{ $namaProgram }}</h1>

                <div class="page-meta">
                    @if($createdAt)
                        <span><i class="fas fa-calendar-alt"></i>{{ $createdAt }}</span>
                    @endif

                    <span><i class="fas fa-university"></i>Universitas Ngudi Waluyo</span>
                </div>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <main class="content-section">
        <div class="detail-shell">
            <article class="content-card">
                <div class="content-toolbar">
                    <div class="toolbar-title">
                        <div class="toolbar-icon"><i class="fas fa-file-lines"></i></div>
                        <div>
                            <h2>{{ $namaProgram }}</h2>
                            <p>Informasi akademik program Pascasarjana Universitas Ngudi Waluyo</p>
                        </div>
                    </div>

                    <div class="toolbar-actions">
                        <a href="{{ url()->current() }}" class="toolbar-btn">
                            <i class="fas fa-link"></i>
                            <span>Salin Link</span>
                        </a>
                    </div>
                </div>

                <div class="article-body">
                    <div class="article-body-inner">
                        <div class="content-html">
                            {!! $bodyContent !!}
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </main>
@endsection
