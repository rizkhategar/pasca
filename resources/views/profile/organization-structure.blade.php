@php
    $currentOrganizationStructure = $organizationStructure ?? $strukturOrganisasi ?? null;
    $imageUrl = null;
    $imageAlt = 'Struktur Organisasi Pascasarjana UNW';

    if ($currentOrganizationStructure && $currentOrganizationStructure->image_path) {
        $imageUrl = route('organization-structures.image', $currentOrganizationStructure);

        if ($currentOrganizationStructure->updated_at) {
            $imageUrl .= '?v=' . $currentOrganizationStructure->updated_at->timestamp;
        }

        if ($currentOrganizationStructure->title) {
            $imageAlt = $currentOrganizationStructure->title;
        }
    }
@endphp

@extends('layouts.app')

@section('title', 'Struktur Organisasi - Pascasarjana UNW')
@section('body_class', 'organization-structure-page')

@section('content')
    <section class="page-hero">
        <div class="hero-dots"></div>
        <div class="hero-line"></div>

        <div class="so-container">
            <div class="hero-inner">
                <div class="hero-kicker">
                    <i class="fas fa-sitemap"></i>
                    <span>Profil Pascasarjana</span>
                </div>

                <h1 class="page-title">Struktur Organisasi</h1>
                <p class="page-desc">Informasi struktur organisasi Pascasarjana Universitas Ngudi Waluyo.</p>

                <div class="hero-meta">
                    <span><i class="fas fa-university"></i>Universitas Ngudi Waluyo</span>
                    <span><i class="fas fa-users-gear"></i>Tata Kelola Organisasi</span>
                </div>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,74 C180,122 384,36 650,62 C930,90 1120,128 1440,44 L1440,120 L0,120 Z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <section class="content-section">
        <div class="so-container">
            @if ($currentOrganizationStructure && $currentOrganizationStructure->image_path)
                <article class="structure-card">
                    <div class="structure-image-box">
                        <div class="structure-image-inner">
                            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="structure-image">
                        </div>
                    </div>
                </article>
            @else
                <div class="empty-card">
                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                    <h2>Struktur Organisasi Belum Tersedia</h2>
                    <p>Silakan unggah gambar struktur organisasi melalui panel admin Filament terlebih dahulu.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
