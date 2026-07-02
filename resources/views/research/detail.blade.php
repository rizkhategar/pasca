@extends('layouts.app')

@section('title', $dosen->nama . ' - Detail Profil & SINTA Dosen')
@section('body_class', 'research-detail-page')

@php
    $safeSintaId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $dosen->sinta_id);
    $customPhotoPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";
    $scrapedPhotoPath = "sinta-lecturers/{$safeSintaId}.jpg";
    $photoUrl = $dosen->profile_photo ?: asset('assets/images/default-user.png');

    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($customPhotoPath)) {
        $photoUrl = url('storage/' . $customPhotoPath);
    } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($scrapedPhotoPath)) {
        $photoUrl = url('storage/' . $scrapedPhotoPath);
    }

    $renderYearlyChart = function (string $title, string $subtitle, $rows, array $series, string $emptyText = 'Tidak ada data statistik tahunan.') {
        $rows = collect($rows ?? [])
            ->filter(fn ($row) => filled(data_get($row, 'year', data_get($row, 'tahun'))))
            ->sortBy(fn ($row) => (int) data_get($row, 'year', data_get($row, 'tahun')))
            ->values();

        if ($rows->isEmpty()) {
            return new \Illuminate\Support\HtmlString('
                <div class="yearly-stat-card">
                    <div class="yearly-chart-head">
                        <div>
                            <h3>' . e($title) . '</h3>
                            <p>' . e($subtitle) . '</p>
                        </div>
                    </div>
                    <div class="yearly-chart-empty">' . e($emptyText) . '</div>
                </div>
            ');
        }

        $width = max(760, 160 + ($rows->count() * 96));
        $height = 290;
        $left = 58;
        $right = 34;
        $top = 42;
        $bottom = 56;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $bottomY = $height - $bottom;
        $count = max(1, $rows->count() - 1);

        $maxValue = 1;
        foreach ($rows as $row) {
            foreach ($series as $serie) {
                $value = (int) data_get($row, $serie['key'], 0);
                $maxValue = max($maxValue, $value);
            }
        }

        $html = '<div class="yearly-stat-card">';
        $html .= '<div class="yearly-chart-head"><div><h3>' . e($title) . '</h3><p>' . e($subtitle) . '</p></div><div class="yearly-chart-legend">';

        foreach ($series as $serie) {
            $total = $rows->sum(fn ($row) => (int) data_get($row, $serie['key'], 0));
            $color = $serie['color'] ?? '#2563eb';
            $html .= '<span style="--legend-color:' . e($color) . '"><i style="background:' . e($color) . '"></i>' . e($serie['label']) . ': <strong>' . number_format($total) . '</strong></span>';
        }

        $html .= '</div></div>';
        $html .= '<div class="yearly-chart-wrap" data-chart-wrap>';
        $html .= '<div class="yearly-chart-scroll-inner">';
        $html .= '<svg class="yearly-chart-svg" viewBox="0 0 ' . $width . ' ' . $height . '" style="width:' . $width . 'px" role="img" aria-label="' . e($title) . '">';
        $html .= '<line x1="' . $left . '" y1="' . $top . '" x2="' . $left . '" y2="' . $bottomY . '" class="chart-axis" />';
        $html .= '<line x1="' . $left . '" y1="' . $bottomY . '" x2="' . ($width - $right) . '" y2="' . $bottomY . '" class="chart-axis" />';
        $html .= '<text x="' . ($left - 10) . '" y="' . ($top + 4) . '" class="chart-label" text-anchor="end">' . number_format($maxValue) . '</text>';
        $html .= '<text x="' . ($left - 10) . '" y="' . ($bottomY + 4) . '" class="chart-label" text-anchor="end">0</text>';

        foreach ([0.25, 0.5, 0.75] as $gridPosition) {
            $gridY = $bottomY - ($plotHeight * $gridPosition);
            $gridValue = (int) round($maxValue * $gridPosition);
            $html .= '<line x1="' . $left . '" y1="' . $gridY . '" x2="' . ($width - $right) . '" y2="' . $gridY . '" class="chart-grid" />';
            $html .= '<text x="' . ($left - 10) . '" y="' . ($gridY + 4) . '" class="chart-label chart-label-soft" text-anchor="end">' . number_format($gridValue) . '</text>';
        }

        foreach ($rows as $index => $row) {
            $year = data_get($row, 'year', data_get($row, 'tahun'));
            $x = $rows->count() === 1 ? $left + ($plotWidth / 2) : $left + (($plotWidth / $count) * $index);
            $html .= '<text x="' . $x . '" y="' . ($bottomY + 30) . '" class="chart-label chart-year-label" text-anchor="middle">' . e($year) . '</text>';
        }

        foreach ($series as $serieIndex => $serie) {
            $color = $serie['color'] ?? '#2563eb';
            $points = [];
            $pointData = [];
            $delay = number_format($serieIndex * 0.16, 2, '.', '');

            foreach ($rows as $index => $row) {
                $year = data_get($row, 'year', data_get($row, 'tahun'));
                $value = (int) data_get($row, $serie['key'], 0);
                $x = $rows->count() === 1 ? $left + ($plotWidth / 2) : $left + (($plotWidth / $count) * $index);
                $y = $bottomY - (($value / $maxValue) * $plotHeight);
                $points[] = round($x, 2) . ',' . round($y, 2);
                $pointData[] = [
                    'x' => round($x, 2),
                    'y' => round($y, 2),
                    'value' => $value,
                    'year' => $year,
                    'label' => $serie['label'],
                    'color' => $color,
                ];
            }

            $html .= '<polyline class="chart-line" pathLength="1" points="' . implode(' ', $points) . '" fill="none" stroke="' . e($color) . '" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" style="--chart-color:' . e($color) . ';--chart-delay:' . $delay . 's" />';

            foreach ($pointData as $pointIndex => $point) {
                $pointDelay = number_format(($serieIndex * 0.16) + 0.42 + ($pointIndex * 0.08), 2, '.', '');
                $labelY = max(16, $point['y'] - 14);
                $html .= '<g class="chart-point-group" tabindex="0" style="--chart-color:' . e($point['color']) . ';--point-delay:' . $pointDelay . 's" data-chart-label="' . e($point['label']) . '" data-chart-year="' . e($point['year']) . '" data-chart-value="' . e(number_format($point['value'])) . '" data-chart-color="' . e($point['color']) . '">';
                $html .= '<circle cx="' . $point['x'] . '" cy="' . $point['y'] . '" r="6" fill="#ffffff" stroke="' . e($point['color']) . '" stroke-width="3" />';

                if ($point['value'] > 0) {
                    $html .= '<text x="' . $point['x'] . '" y="' . $labelY . '" class="chart-point-value" fill="' . e($point['color']) . '" text-anchor="middle">' . number_format($point['value']) . '</text>';
                }

                $html .= '</g>';
            }
        }

        $html .= '</svg>';
        $html .= '<div class="yearly-chart-tooltip" data-chart-tooltip aria-hidden="true"></div>';
        $html .= '</div>';
        $html .= '<div class="yearly-chart-scroll-hint"><i class="fas fa-arrows-left-right"></i><span>Geser grafik untuk melihat data lain</span></div>';
        $html .= '</div></div>';

        return new \Illuminate\Support\HtmlString($html);
    };
@endphp

@push('styles')
    <style>
        .research-detail-page .profile-main-body,
        .research-detail-page .profile-container {
            min-width: 0;
        }

        .research-detail-page .research-layout,
        .research-detail-page .research-card,
        .research-detail-page .content-block {
            min-width: 0;
        }

        .tabs-container-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .tabs-container {
            min-width: max-content;
        }

        .yearly-stat-card {
            margin: 0 0 1.25rem;
            padding: 1.25rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
            box-shadow: 0 18px 42px rgba(15, 23, 42, .07);
            overflow: hidden;
        }

        .yearly-chart-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .yearly-chart-head h3 {
            margin: 0 0 .25rem;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .yearly-chart-head p {
            margin: 0;
            color: #64748b;
            font-size: .9rem;
        }

        .yearly-chart-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .5rem;
        }

        .yearly-chart-legend span {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            padding: .35rem .65rem;
            background: #fff;
            color: #334155;
            font-size: .82rem;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        }

        .yearly-chart-legend span strong {
            color: var(--legend-color, #2563eb);
        }

        .yearly-chart-legend i {
            width: .65rem;
            height: .65rem;
            border-radius: 999px;
            display: inline-block;
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--legend-color, #2563eb) 12%, transparent);
        }

        .yearly-chart-wrap {
            position: relative;
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            padding: .25rem .15rem .55rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .yearly-chart-wrap::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar,
        .tabs-container-wrap::-webkit-scrollbar {
            height: 8px;
        }

        .yearly-chart-wrap::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb,
        .tabs-container-wrap::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(7, 43, 87, .24);
        }

        .yearly-chart-scroll-inner {
            position: relative;
            width: max-content;
            min-width: 100%;
        }

        .yearly-chart-svg {
            max-width: none;
            min-width: 720px;
            height: auto;
            display: block;
            overflow: visible;
        }

        .chart-axis {
            stroke: rgba(15, 23, 42, 0.38);
            stroke-width: 1.5;
        }

        .chart-grid {
            stroke: rgba(148, 163, 184, 0.25);
            stroke-width: 1;
            stroke-dasharray: 5 6;
        }

        .chart-label {
            fill: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .chart-label-soft {
            fill: rgba(100, 116, 139, .72);
            font-size: 11px;
        }

        .chart-year-label {
            fill: #334155;
            font-weight: 800;
        }

        .chart-line {
            stroke-dasharray: 1;
            stroke-dashoffset: 1;
            animation: yearlyChartLineDraw 1.15s cubic-bezier(.65, 0, .25, 1) forwards;
            animation-delay: var(--chart-delay, 0s);
            filter: drop-shadow(0 8px 10px rgba(15, 23, 42, .12));
        }

        .chart-point-group {
            cursor: pointer;
            opacity: 0;
            transform-box: fill-box;
            transform-origin: center;
            animation: yearlyChartPointPop .35s ease forwards;
            animation-delay: var(--point-delay, .45s);
            outline: none;
        }

        .chart-point-group circle {
            transition: r .2s ease, filter .2s ease, stroke-width .2s ease;
        }

        .chart-point-group:hover circle,
        .chart-point-group:focus circle {
            r: 8;
            stroke-width: 4;
            filter: drop-shadow(0 8px 14px color-mix(in srgb, var(--chart-color, #2563eb) 42%, transparent));
        }

        .chart-point-value {
            font-size: 12px;
            font-weight: 900;
            paint-order: stroke;
            stroke: #ffffff;
            stroke-width: 4px;
            stroke-linejoin: round;
            opacity: .96;
        }

        @keyframes yearlyChartLineDraw {
            to {
                stroke-dashoffset: 0;
            }
        }

        @keyframes yearlyChartPointPop {
            from {
                opacity: 0;
                transform: scale(.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .yearly-chart-tooltip {
            position: fixed;
            z-index: 10001;
            min-width: 190px;
            max-width: min(280px, calc(100vw - 32px));
            padding: .85rem .95rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .98));
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: 0 22px 52px rgba(3, 31, 66, .24);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translate3d(-50%, -110%, 0) scale(.96);
            transition: opacity .16s ease, visibility .16s ease, transform .16s ease;
        }

        .yearly-chart-tooltip.is-visible {
            opacity: 1;
            visibility: visible;
            transform: translate3d(-50%, -116%, 0) scale(1);
        }

        .chart-tooltip-top {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin-bottom: .45rem;
        }

        .chart-tooltip-dot {
            width: .8rem;
            height: .8rem;
            border-radius: 999px;
            background: var(--tooltip-color, #2563eb);
            box-shadow: 0 0 0 5px color-mix(in srgb, var(--tooltip-color, #2563eb) 14%, transparent);
            flex: 0 0 auto;
        }

        .chart-tooltip-label {
            color: #0f172a;
            font-size: .86rem;
            font-weight: 900;
            line-height: 1.25;
        }

        .chart-tooltip-year {
            width: fit-content;
            margin-top: .1rem;
            padding: .18rem .48rem;
            border-radius: 999px;
            color: #475569;
            background: rgba(148, 163, 184, .14);
            font-size: .68rem;
            font-weight: 800;
        }

        .chart-tooltip-value {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .8rem;
            padding-top: .55rem;
            border-top: 1px solid rgba(148, 163, 184, .22);
            color: #475569;
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .chart-tooltip-value strong {
            color: var(--tooltip-color, #2563eb);
            font-size: 1.45rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -.03em;
        }

        .yearly-chart-scroll-hint {
            display: none;
            align-items: center;
            gap: .45rem;
            width: fit-content;
            margin: .6rem auto 0;
            padding: .42rem .7rem;
            border-radius: 999px;
            color: #64748b;
            background: rgba(148, 163, 184, .12);
            font-size: .72rem;
            font-weight: 800;
        }

        .yearly-chart-empty {
            padding: 1rem;
            border-radius: .9rem;
            background: rgba(148, 163, 184, 0.12);
            color: #64748b;
            text-align: center;
            font-weight: 600;
        }

        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: .35rem;
            scrollbar-width: thin;
        }

        .table-responsive .table-data {
            min-width: 780px;
        }

        #research .table-responsive .table-data,
        #service .table-responsive .table-data,
        #books .table-responsive .table-data {
            min-width: 860px;
        }

        @media (max-width: 768px) {
            .yearly-stat-card {
                padding: 1rem;
                border-radius: 1rem;
            }

            .yearly-chart-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .yearly-chart-legend {
                justify-content: flex-start;
            }

            .yearly-chart-scroll-hint {
                display: inline-flex;
            }

            .table-responsive {
                margin-inline: -.25rem;
                padding-inline: .25rem;
            }

            .tabs-container-wrap {
                margin-inline: -1rem;
                padding-inline: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="profile-page">
        <section class="profile-hero">
            <div class="hero-dots"></div>
            @include('components.hero-spotlight')

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
                        <span><i class="fas fa-university"></i>{{ $dosen->institusi ?? $dosen->institution ?? 'Universitas Ngudi Waluyo' }}</span>
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
                        <img src="{{ $photoUrl }}" alt="{{ $dosen->nama }}">
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
                        @if($dosen->bidang_minat ?? $dosen->research_interests ?? null)
                            <tr><td class="label">Bidang Minat</td><td class="value">{{ $dosen->bidang_minat ?? $dosen->research_interests }}</td></tr>
                        @endif
                    </table>
                </div>
            </article>

            <div class="tabs-container-wrap">
                <div class="tabs-container">
                    <button class="tab-btn active" type="button" onclick="switchTab(event,'scopus')"><i class="fas fa-globe"></i>Scopus</button>
                    <button class="tab-btn" type="button" onclick="switchTab(event,'scholar')"><i class="fas fa-graduation-cap"></i>Scholar</button>
                    <button class="tab-btn" type="button" onclick="switchTab(event,'garuda')"><i class="fas fa-book-open"></i>Garuda</button>
                    <button class="tab-btn" type="button" onclick="switchTab(event,'research')"><i class="fas fa-search"></i>Penelitian</button>
                    <button class="tab-btn" type="button" onclick="switchTab(event,'service')"><i class="fas fa-hands-helping"></i>Pengabdian</button>
                    <button class="tab-btn" type="button" onclick="switchTab(event,'books')"><i class="fas fa-book"></i>Buku</button>
                </div>
            </div>

            <section class="research-layout">
                <div id="scopus" class="tab-content active content-block research-card">
                    <div class="tab-header">
                        <div><h2 class="block-title">Publikasi Internasional Scopus</h2><p>Daftar publikasi internasional terindeks Scopus.</p></div>
                        <div class="tab-badge"><i class="fas fa-database"></i>{{ $dosen->scopusPublications->count() ?? 0 }} Data</div>
                    </div>

                    {!! $renderYearlyChart('Statistik Tahunan Scopus', 'Jumlah dokumen Scopus per tahun dari tabel sinta_scopus_yearly_stats.', $dosen->scopusYearlyStats, [
                        ['key' => 'count', 'label' => 'Dokumen', 'color' => '#2563eb'],
                    ]) !!}

                    <div class="table-responsive"><table class="table-data"><thead><tr><th class="col-no">No</th><th>Artikel & Info Jurnal</th><th class="col-year-q">Tahun / Q</th><th class="col-citation">Sitasi</th><th class="col-action">Aksi</th></tr></thead><tbody>@forelse($dosen->scopusPublications as $index => $scopus)<tr><td class="table-number">{{ $index + 1 }}</td><td><div class="pub-title">{{ $scopus->judul }}</div><div class="pub-muted">Jurnal: {{ $scopus->journal }}</div><div class="pub-muted">Penulis ke-{{ $scopus->author_order }} atau {{ $scopus->creator ?? '-' }}</div></td><td class="cell-center"><strong>{{ $scopus->tahun }}</strong>@if($scopus->quartile)<br><span class="badge-quartile badge-gap">{{ $scopus->quartile }}</span>@endif</td><td class="cell-center cell-strong cell-primary">{{ $scopus->citation }}</td><td class="cell-center">@if($scopus->url_artikel ?? $scopus->article_url ?? null)<a href="{{ $scopus->url_artikel ?? $scopus->article_url }}" target="_blank" class="pub-link"><i class="fas fa-external-link-alt"></i>Link</a>@else - @endif</td></tr>@empty<tr><td colspan="5" class="empty-text">Tidak ada data publikasi Scopus.</td></tr>@endforelse</tbody></table></div>
                </div>

                <div id="scholar" class="tab-content content-block research-card">
                    <div class="tab-header">
                        <div><h2 class="block-title">Publikasi Google Scholar</h2><p>Daftar publikasi yang terindeks Google Scholar.</p></div>
                        <div class="tab-badge"><i class="fas fa-database"></i>{{ $dosen->scholarPublications->count() ?? 0 }} Data</div>
                    </div>

                    {!! $renderYearlyChart('Statistik Tahunan Google Scholar', 'Diagram dua garis untuk publications dan citations dari tabel sinta_scholar_yearly_stats.', $dosen->scholarYearlyStats, [
                        ['key' => 'publications', 'label' => 'Publications', 'color' => '#7c3aed'],
                        ['key' => 'citations', 'label' => 'Citations', 'color' => '#f97316'],
                    ]) !!}

                    <div class="table-responsive"><table class="table-data"><thead><tr><th class="col-no">No</th><th>Judul Dokumen & Sumber</th><th class="col-year">Tahun</th><th class="col-citation">Sitasi</th><th class="col-action">Aksi</th></tr></thead><tbody>@forelse($dosen->scholarPublications as $index => $scholar)<tr><td class="table-number">{{ $index + 1 }}</td><td><div class="pub-title">{{ $scholar->judul }}</div><div class="pub-muted">Penulis: {{ $scholar->authors }}</div>@if($scholar->source)<div class="pub-muted">Sumber: {{ $scholar->source }}</div>@endif</td><td class="cell-center cell-strong">{{ $scholar->tahun }}</td><td class="cell-center cell-strong cell-primary">{{ $scholar->citation }}</td><td class="cell-center">@if($scholar->url_scholar ?? $scholar->scholar_url ?? null)<a href="{{ $scholar->url_scholar ?? $scholar->scholar_url }}" target="_blank" class="pub-link"><i class="fas fa-external-link-alt"></i>Link</a>@else - @endif</td></tr>@empty<tr><td colspan="5" class="empty-text">Tidak ada data publikasi Google Scholar.</td></tr>@endforelse</tbody></table></div>
                </div>

                <div id="garuda" class="tab-content content-block research-card">
                    <div class="tab-header">
                        <div><h2 class="block-title">Publikasi Nasional Garuda</h2><p>Daftar publikasi nasional terindeks Garuda.</p></div>
                        <div class="tab-badge"><i class="fas fa-database"></i>{{ $dosen->garudaPublications->count() ?? 0 }} Data</div>
                    </div>

                    {!! $renderYearlyChart('Statistik Tahunan Garuda', 'Jumlah artikel Garuda per tahun dari tabel sinta_garuda_yearly_stats.', $dosen->garudaYearlyStats, [
                        ['key' => 'articles', 'label' => 'Artikel', 'color' => '#16a34a'],
                    ]) !!}

                    <div class="table-responsive"><table class="table-data"><thead><tr><th class="col-no">No</th><th>Judul Jurnal & Penerbit</th><th class="col-year">Tahun</th><th class="col-accreditation">Akreditasi</th><th class="col-action">Aksi</th></tr></thead><tbody>@forelse($dosen->garudaPublications as $index => $garuda)<tr><td class="table-number">{{ $index + 1 }}</td><td><div class="pub-title">{{ $garuda->judul }}</div><div class="pub-muted">Jurnal: {{ $garuda->journal }}</div><div class="pub-muted">Penerbit: {{ $garuda->publisher ?? '-' }}</div></td><td class="cell-center cell-strong">{{ $garuda->tahun }}</td><td class="cell-center"><span class="badge-soft">{{ $garuda->accreditation ?? '-' }}</span></td><td class="cell-center">@if($garuda->url_artikel ?? $garuda->article_url ?? null)<a href="{{ $garuda->url_artikel ?? $garuda->article_url }}" target="_blank" class="pub-link"><i class="fas fa-external-link-alt"></i>Link</a>@else - @endif</td></tr>@empty<tr><td colspan="5" class="empty-text">Tidak ada data publikasi Garuda.</td></tr>@endforelse</tbody></table></div>
                </div>

                <div id="research" class="tab-content content-block research-card">
                    <div class="tab-header">
                        <div><h2 class="block-title">Histori Proyek Penelitian</h2><p>Riwayat penelitian dosen.</p></div>
                        <div class="tab-badge"><i class="fas fa-database"></i>{{ $dosen->researches->count() ?? 0 }} Data</div>
                    </div>

                    {!! $renderYearlyChart('Statistik Penelitian Tahunan', 'Jumlah penelitian per tahun dari tabel sinta_research_yearly.', $dosen->researchYearlies, [
                        ['key' => 'count', 'label' => 'Penelitian', 'color' => '#0f766e'],
                    ]) !!}

                    <div class="table-responsive"><table class="table-data"><thead><tr><th class="col-no">No</th><th>Judul Penelitian & Skema</th><th class="col-year">Tahun</th><th>Pendanaan</th><th class="col-status">Status</th></tr></thead><tbody>@forelse($dosen->researches as $index => $res)<tr><td class="table-number">{{ $index + 1 }}</td><td><div class="pub-title">{{ $res->judul }}</div><div class="pub-primary">Skema: {{ $res->skema ?? '-' }}</div><div class="pub-muted">Ketua: {{ $res->leader }} | Personil: {{ $res->personils ?? $res->personnel ?? '-' }}</div></td><td class="cell-center cell-strong">{{ $res->tahun }}</td><td><span class="money-text">{{ $res->dana ?? '-' }}</span></td><td class="cell-center"><span class="badge-success">{{ $res->status ?? 'Selesai' }}</span></td></tr>@empty<tr><td colspan="5" class="empty-text">Tidak ada data histori penelitian.</td></tr>@endforelse</tbody></table></div>
                </div>

                <div id="service" class="tab-content content-block research-card">
                    <div class="tab-header">
                        <div><h2 class="block-title">Histori Pengabdian Masyarakat</h2><p>Riwayat kegiatan pengabdian masyarakat.</p></div>
                        <div class="tab-badge"><i class="fas fa-database"></i>{{ $dosen->services->count() ?? 0 }} Data</div>
                    </div>

                    {!! $renderYearlyChart('Statistik Pengabdian Tahunan', 'Jumlah pengabdian per tahun dari tabel sinta_service_yearly.', $dosen->serviceYearlies, [
                        ['key' => 'count', 'label' => 'Pengabdian', 'color' => '#ca8a04'],
                    ]) !!}

                    <div class="table-responsive"><table class="table-data"><thead><tr><th class="col-no">No</th><th>Judul Pengabdian & Kegiatan</th><th class="col-year">Tahun</th><th>Pendanaan</th><th class="col-status">Status</th></tr></thead><tbody>@forelse($dosen->services as $index => $serv)<tr><td class="table-number">{{ $index + 1 }}</td><td><div class="pub-title">{{ $serv->judul }}</div><div class="pub-primary">Skema: {{ $serv->skema ?? '-' }}</div><div class="pub-muted">Ketua: {{ $serv->leader }} | Personil: {{ $serv->personils ?? $serv->personnel ?? '-' }}</div></td><td class="cell-center cell-strong">{{ $serv->tahun }}</td><td><span class="money-text">{{ $serv->dana ?? '-' }}</span></td><td class="cell-center"><span class="badge-success">{{ $serv->status ?? 'Selesai' }}</span></td></tr>@empty<tr><td colspan="5" class="empty-text">Tidak ada data histori pengabdian masyarakat.</td></tr>@endforelse</tbody></table></div>
                </div>

                <div id="books" class="tab-content content-block research-card">
                    <div class="tab-header">
                        <div><h2 class="block-title">Buku Karya Dosen Terdaftar</h2><p>Daftar buku karya dosen.</p></div>
                        <div class="tab-badge"><i class="fas fa-database"></i>{{ $dosen->books->count() ?? 0 }} Data</div>
                    </div>

                    <div class="table-responsive"><table class="table-data"><thead><tr><th class="col-no">No</th><th>Judul & Kategori Buku</th><th class="col-year">Tahun</th><th>Penerbit</th><th class="col-isbn">ISBN</th></tr></thead><tbody>@forelse($dosen->books as $index => $book)<tr><td class="table-number">{{ $index + 1 }}</td><td><div class="pub-title">{{ $book->judul }}</div><span class="badge-book">{{ $book->kategori ?? 'Umum' }}</span></td><td class="cell-center cell-strong">{{ $book->tahun }}</td><td><div class="pub-title no-margin">{{ $book->penerbit }}</div><div class="pub-muted">{{ $book->kota ?? '-' }}</div></td><td class="cell-center"><span class="isbn-text">{{ $book->isbn ?? '-' }}</span></td></tr>@empty<tr><td colspan="5" class="empty-text">Tidak ada data buku terdaftar.</td></tr>@endforelse</tbody></table></div>
                </div>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
<script>
function switchTab(evt, tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    evt.currentTarget.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function () {
    const chartPoints = document.querySelectorAll('.chart-point-group');

    function moveTooltip(tooltip, event) {
        const padding = 16;
        const tooltipWidth = tooltip.offsetWidth || 220;
        const tooltipHeight = tooltip.offsetHeight || 120;
        let left = event.clientX;
        let top = event.clientY - 16;

        left = Math.max(padding + (tooltipWidth / 2), Math.min(window.innerWidth - padding - (tooltipWidth / 2), left));
        top = Math.max(padding + tooltipHeight, top);

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }

    function showTooltip(point, event = null) {
        const wrap = point.closest('[data-chart-wrap]');
        const tooltip = wrap?.querySelector('[data-chart-tooltip]');

        if (!tooltip) {
            return;
        }

        const color = point.dataset.chartColor || '#2563eb';
        const label = point.dataset.chartLabel || 'Data';
        const year = point.dataset.chartYear || '-';
        const value = point.dataset.chartValue || '0';

        tooltip.style.setProperty('--tooltip-color', color);
        tooltip.innerHTML = `
            <div class="chart-tooltip-top">
                <span class="chart-tooltip-dot"></span>
                <div>
                    <div class="chart-tooltip-label">${label}</div>
                    <div class="chart-tooltip-year">Tahun ${year}</div>
                </div>
            </div>
            <div class="chart-tooltip-value">
                <span>Jumlah data</span>
                <strong>${value}</strong>
            </div>
        `;

        tooltip.classList.add('is-visible');
        tooltip.setAttribute('aria-hidden', 'false');

        if (event) {
            moveTooltip(tooltip, event);
        } else {
            const rect = point.getBoundingClientRect();
            moveTooltip(tooltip, { clientX: rect.left + (rect.width / 2), clientY: rect.top });
        }
    }

    function hideTooltip(point) {
        const wrap = point.closest('[data-chart-wrap]');
        const tooltip = wrap?.querySelector('[data-chart-tooltip]');

        if (!tooltip) {
            return;
        }

        tooltip.classList.remove('is-visible');
        tooltip.setAttribute('aria-hidden', 'true');
    }

    chartPoints.forEach(function (point) {
        point.addEventListener('mouseenter', function (event) {
            showTooltip(point, event);
        });

        point.addEventListener('mousemove', function (event) {
            const wrap = point.closest('[data-chart-wrap]');
            const tooltip = wrap?.querySelector('[data-chart-tooltip]');

            if (tooltip?.classList.contains('is-visible')) {
                moveTooltip(tooltip, event);
            }
        });

        point.addEventListener('mouseleave', function () {
            hideTooltip(point);
        });

        point.addEventListener('focus', function () {
            showTooltip(point);
        });

        point.addEventListener('blur', function () {
            hideTooltip(point);
        });
    });
});
</script>
@endpush
