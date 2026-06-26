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

        $width = 720;
        $height = 260;
        $left = 54;
        $right = 24;
        $top = 30;
        $bottom = 48;
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
            $html .= '<span><i style="background:' . e($color) . '"></i>' . e($serie['label']) . ': <strong>' . number_format($total) . '</strong></span>';
        }

        $html .= '</div></div>';
        $html .= '<div class="yearly-chart-wrap">';
        $html .= '<svg class="yearly-chart-svg" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="' . e($title) . '">';
        $html .= '<line x1="' . $left . '" y1="' . $top . '" x2="' . $left . '" y2="' . $bottomY . '" class="chart-axis" />';
        $html .= '<line x1="' . $left . '" y1="' . $bottomY . '" x2="' . ($width - $right) . '" y2="' . $bottomY . '" class="chart-axis" />';
        $html .= '<text x="' . ($left - 10) . '" y="' . ($top + 4) . '" class="chart-label" text-anchor="end">' . number_format($maxValue) . '</text>';
        $html .= '<text x="' . ($left - 10) . '" y="' . ($bottomY + 4) . '" class="chart-label" text-anchor="end">0</text>';

        foreach ([0.25, 0.5, 0.75] as $gridPosition) {
            $gridY = $bottomY - ($plotHeight * $gridPosition);
            $html .= '<line x1="' . $left . '" y1="' . $gridY . '" x2="' . ($width - $right) . '" y2="' . $gridY . '" class="chart-grid" />';
        }

        foreach ($rows as $index => $row) {
            $year = data_get($row, 'year', data_get($row, 'tahun'));
            $x = $rows->count() === 1 ? $left + ($plotWidth / 2) : $left + (($plotWidth / $count) * $index);
            $html .= '<text x="' . $x . '" y="' . ($bottomY + 27) . '" class="chart-label" text-anchor="middle">' . e($year) . '</text>';
        }

        foreach ($series as $serie) {
            $color = $serie['color'] ?? '#2563eb';
            $points = [];
            $pointData = [];

            foreach ($rows as $index => $row) {
                $value = (int) data_get($row, $serie['key'], 0);
                $x = $rows->count() === 1 ? $left + ($plotWidth / 2) : $left + (($plotWidth / $count) * $index);
                $y = $bottomY - (($value / $maxValue) * $plotHeight);
                $points[] = round($x, 2) . ',' . round($y, 2);
                $pointData[] = ['x' => round($x, 2), 'y' => round($y, 2), 'value' => $value, 'label' => $serie['label']];
            }

            $html .= '<polyline points="' . implode(' ', $points) . '" fill="none" stroke="' . e($color) . '" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />';

            foreach ($pointData as $point) {
                $html .= '<circle cx="' . $point['x'] . '" cy="' . $point['y'] . '" r="5" fill="#ffffff" stroke="' . e($color) . '" stroke-width="3"><title>' . e($point['label']) . ': ' . number_format($point['value']) . '</title></circle>';
            }
        }

        $html .= '</svg></div></div>';

        return new \Illuminate\Support\HtmlString($html);
    };
@endphp

@push('styles')
    <style>
        .yearly-stat-card {
            margin: 0 0 1.25rem;
            padding: 1.25rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
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
            font-weight: 600;
        }

        .yearly-chart-legend i {
            width: .65rem;
            height: .65rem;
            border-radius: 999px;
            display: inline-block;
        }

        .yearly-chart-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .yearly-chart-svg {
            width: 100%;
            min-width: 540px;
            height: auto;
            display: block;
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
            font-weight: 600;
        }

        .yearly-chart-empty {
            padding: 1rem;
            border-radius: .9rem;
            background: rgba(148, 163, 184, 0.12);
            color: #64748b;
            text-align: center;
            font-weight: 600;
        }
    </style>
@endpush

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
</script>
@endpush
