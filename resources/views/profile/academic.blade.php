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

@push('styles')
    <style>
        body.academic-page {
            background:
                radial-gradient(circle at 8% 12%, rgba(45, 156, 219, .10), transparent 28%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 46%, #eef5fb 100%) !important;
            color: #0f172a;
        }

        .academic-page .container {
            width: min(1120px, 92%);
            margin: 0 auto;
        }

        .academic-page .page-hero {
            position: relative;
            overflow: hidden;
            min-height: 360px;
            display: flex;
            align-items: center;
            padding: 64px 0 118px;
            color: #ffffff;
            background:
                radial-gradient(circle at 13% 18%, rgba(45, 156, 219, .44), transparent 27%),
                radial-gradient(circle at 82% 18%, rgba(255, 255, 255, .18), transparent 24%),
                linear-gradient(135deg, #031f42 0%, #062f5f 46%, #07457d 75%, #0b6eae 100%);
        }

        .academic-page .page-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: .42;
            background-image:
                linear-gradient(rgba(255, 255, 255, .055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .055) 1px, transparent 1px);
            background-size: 46px 46px;
        }

        .academic-page .page-hero::after {
            content: "";
            position: absolute;
            right: -170px;
            top: -185px;
            z-index: 1;
            width: 570px;
            height: 570px;
            border-radius: 999px;
            pointer-events: none;
            background: radial-gradient(circle, rgba(255, 255, 255, .22) 0%, rgba(45, 156, 219, .16) 36%, transparent 68%);
        }

        .academic-page .hero-dots {
            position: absolute;
            left: 24px;
            top: 18px;
            z-index: 2;
            width: 120px;
            height: 92px;
            opacity: .48;
            background-image: radial-gradient(rgba(255, 255, 255, .72) 2px, transparent 2.6px);
            background-size: 18px 18px;
        }

        .academic-page .hero-line {
            position: absolute;
            right: 110px;
            top: -36px;
            z-index: 1;
            width: 230px;
            height: 440px;
            transform: skewX(-32deg);
            background: linear-gradient(120deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .18));
        }

        .academic-page .hero-inner {
            position: relative;
            z-index: 4;
            max-width: 900px;
        }

        .academic-page .back-link {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding: 10px 15px;
            border-radius: 999px;
            color: #ffffff !important;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .20);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .4px;
            text-transform: uppercase;
            transition: .22s ease;
            text-decoration: none;
        }

        .academic-page .back-link:hover {
            transform: translateX(-4px);
            background: #f7b500;
            border-color: #f7b500;
            color: #072b57 !important;
        }

        .academic-page .category-pill {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            padding: 10px 15px;
            border-radius: 999px;
            color: #ffe8a1;
            background: rgba(247, 181, 0, .12);
            border: 1px solid rgba(247, 181, 0, .34);
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .7px;
            backdrop-filter: blur(10px);
        }

        .academic-page .title-page {
            margin: 0 0 18px;
            color: #ffffff;
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -1.1px;
        }

        .academic-page .page-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .academic-page .page-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            color: rgba(255, 255, 255, .88);
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .16);
            font-size: 13px;
            font-weight: 800;
            backdrop-filter: blur(10px);
        }

        .academic-page .page-meta i {
            color: #f7b500;
        }

        .academic-page .hero-wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            z-index: 3;
            width: 100%;
            height: 92px;
            pointer-events: none;
        }

        .academic-page .hero-wave svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .academic-page .content-section {
            position: relative;
            z-index: 5;
            margin-top: -58px;
            padding: 0 0 90px;
        }

        .academic-page .detail-shell {
            width: min(100% - 64px, 1060px);
            margin: 0 auto;
        }

        .academic-page .content-card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, .96);
            border-radius: 28px;
            box-shadow: 0 18px 46px rgba(15, 23, 42, .10);
        }

        .academic-page .content-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px 26px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .academic-page .toolbar-title {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .academic-page .toolbar-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            color: #ffffff;
            background: linear-gradient(135deg, #072b57, #0b5f9f);
            font-size: 20px;
        }

        .academic-page .toolbar-title h2 {
            margin: 0;
            color: #072b57;
            font-size: 21px;
            line-height: 1.25;
            font-weight: 900;
        }

        .academic-page .toolbar-title p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .academic-page .toolbar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border: 1px solid rgba(6, 47, 95, .14);
            border-radius: 999px;
            background: #ffffff;
            color: #072b57;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition: .22s ease;
        }

        .academic-page .toolbar-btn:hover {
            transform: translateY(-2px);
            background: #072b57;
            color: #ffffff;
        }

        .academic-page .article-body {
            max-width: 900px;
            margin: 0 auto;
            padding: 44px 42px 60px;
        }

        .academic-page .content-html {
            color: #334155;
            font-size: 17px;
            line-height: 1.92;
            word-break: break-word;
        }

        .academic-page .content-html p {
            margin: 0 0 20px;
            color: #334155;
        }

        .academic-page .content-html h1,
        .academic-page .content-html h2,
        .academic-page .content-html h3,
        .academic-page .content-html h4,
        .academic-page .content-html h5,
        .academic-page .content-html h6 {
            color: #072b57;
            line-height: 1.28;
            font-weight: 900;
            letter-spacing: -.3px;
        }

        .academic-page .content-html h4 {
            position: relative;
            display: inline-block;
            margin: 30px 0 16px;
            padding-bottom: 9px;
            font-size: 21px;
        }

        .academic-page .content-html h4::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 72px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, #f7b500, #0b5f9f);
        }

        .academic-page .content-html ul,
        .academic-page .content-html ol {
            margin: 0 0 24px;
            padding-left: 22px;
        }

        .academic-page .content-html li {
            margin-bottom: 10px;
            color: #334155;
            line-height: 1.75;
        }

        .academic-page .content-html img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 28px auto;
            border-radius: 18px !important;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .07) !important;
        }

        .academic-page .content-html iframe {
            width: 100%;
            min-height: 420px;
            border: 0;
            border-radius: 18px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
        }

        .academic-page .content-html table {
            width: 100% !important;
            margin: 28px 0 !important;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            overflow: hidden;
            border-radius: 16px;
            background: #ffffff !important;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .07);
        }

        .academic-page .content-html table th {
            color: #ffffff !important;
            background: linear-gradient(135deg, #072b57, #0b5f9f) !important;
            font-weight: 900 !important;
            text-align: left;
        }

        .academic-page .content-html table th,
        .academic-page .content-html table td {
            padding: 13px 15px !important;
            border: 1px solid #e2e8f0 !important;
            vertical-align: top;
            color: #334155;
            line-height: 1.55;
        }

        .academic-page .empty-state {
            text-align: center;
            color: #64748b;
            font-style: italic;
            padding: 46px 18px;
            border-radius: 22px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
        }

        @media (max-width: 768px) {
            .academic-page .page-hero {
                min-height: 320px;
                padding: 46px 0 100px;
            }

            .academic-page .title-page {
                font-size: 34px;
            }

            .academic-page .detail-shell {
                width: min(100% - 28px, 1060px);
            }

            .academic-page .content-toolbar {
                align-items: flex-start;
                flex-direction: column;
                padding: 20px;
            }

            .academic-page .toolbar-actions,
            .academic-page .toolbar-btn {
                width: 100%;
            }

            .academic-page .article-body {
                padding: 30px 20px 42px;
            }

            .academic-page .content-html {
                font-size: 15px;
                line-height: 1.85;
            }

            .academic-page .content-html table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
@endpush

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
