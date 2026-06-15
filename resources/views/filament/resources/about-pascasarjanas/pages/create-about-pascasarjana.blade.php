<x-filament-panels::page>
    <style>
        .native-about-form{max-width:1120px;display:grid;gap:22px}.native-card{border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.035);border-radius:16px;padding:22px}.native-grid{display:grid;gap:18px}.native-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.native-four{display:grid;grid-template-columns:260px 1fr;gap:18px}.native-field label{display:block;margin-bottom:8px;font-weight:700;color:#f4f4f5}.native-field input[type=text],.native-field input[type=file],.native-field textarea{width:100%;border:1px solid rgba(255,255,255,.16);border-radius:10px;background:#111114;color:#f4f4f5;padding:11px 12px}.native-field textarea{min-height:105px;resize:vertical}.native-field input[type=file]::file-selector-button{border:0;border-radius:8px;background:#f59e0b;color:#111827;font-weight:800;padding:8px 12px;margin-right:12px}.native-title{margin:0 0 16px;font-size:18px;font-weight:900;color:#f4f4f5}.native-point{border:1px solid rgba(255,255,255,.10);border-radius:14px;padding:16px;background:rgba(0,0,0,.16)}.native-actions{display:flex;gap:12px;flex-wrap:wrap}.native-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:9px;min-height:42px;padding:10px 18px;font-weight:800;text-decoration:none}.native-primary{border:0;background:#f59e0b;color:#111827}.native-secondary{border:1px solid rgba(255,255,255,.16);color:#f4f4f5;background:transparent}.native-error{border:1px solid rgba(239,68,68,.6);background:rgba(127,29,29,.24);color:#fecaca;border-radius:14px;padding:14px 18px}.native-error ul{margin:8px 0 0;padding-left:20px}@media(max-width:800px){.native-two,.native-four{grid-template-columns:1fr}.native-card{padding:18px}}
    </style>

    <form action="{{ route('admin.about-pascasarjanas.store') }}" method="POST" enctype="multipart/form-data" class="native-about-form">
        @csrf

        @if($errors->any())
            <div class="native-error"><strong>Data belum valid.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="native-card native-grid">
            <h2 class="native-title">Teks Utama Halaman</h2>
            <div class="native-two">
                <div class="native-field"><label>Sub Judul</label><input type="text" name="subheading" value="{{ old('subheading', 'Tentang Kami') }}" required></div>
                <div class="native-field"><label>Judul Utama</label><input type="text" name="heading" value="{{ old('heading') }}" required></div>
            </div>
            <div class="native-field"><label>Deskripsi Panjang</label><textarea name="description" rows="6" required>{{ old('description') }}</textarea></div>
        </section>

        @php($points = old('points', [['title'=>'','description'=>''], ['title'=>'','description'=>''], ['title'=>'','description'=>'']]))
        <section class="native-card native-grid">
            <h2 class="native-title">Poin-Poin Fitur & Keunggulan</h2>
            @foreach($points as $index => $point)
                <div class="native-point native-four">
                    <div class="native-field"><label>Upload Ikon</label><input type="file" name="points[{{ $index }}][icon]" accept="image/jpeg,image/png,image/webp,image/svg+xml"></div>
                    <div class="native-grid">
                        <div class="native-field"><label>Judul Poin</label><input type="text" name="points[{{ $index }}][title]" value="{{ $point['title'] ?? '' }}"></div>
                        <div class="native-field"><label>Deskripsi Singkat</label><textarea name="points[{{ $index }}][description]" rows="3">{{ $point['description'] ?? '' }}</textarea></div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="native-card native-grid">
            <h2 class="native-title">Sambutan Direktur Pascasarjana</h2>
            <div class="native-two">
                <div class="native-field"><label>Label Sambutan</label><input type="text" name="direktur_heading" value="{{ old('direktur_heading', 'Sambutan Direktur') }}"></div>
                <div class="native-field"><label>Kalimat Sapaan</label><input type="text" name="direktur_greeting" value="{{ old('direktur_greeting', 'Selamat Datang di Pascasarjana Universitas Ngudi Waluyo') }}"></div>
            </div>
            <div class="native-four">
                <div class="native-field"><label>Foto Direktur</label><input type="file" name="direktur_image" accept="image/jpeg,image/png,image/webp"></div>
                <div class="native-grid">
                    <div class="native-field"><label>Nama Lengkap</label><input type="text" name="direktur_name" value="{{ old('direktur_name') }}"></div>
                    <div class="native-field"><label>Jabatan</label><input type="text" name="direktur_title" value="{{ old('direktur_title', 'Direktur Pascasarjana Universitas Ngudi Waluyo') }}"></div>
                    <div class="native-field"><label>Isi Pesan / Sambutan</label><textarea name="direktur_message" rows="7">{{ old('direktur_message') }}</textarea></div>
                </div>
            </div>
        </section>

        <div class="native-actions">
            <button type="submit" class="native-btn native-primary">Save</button>
            <a href="{{ \App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource::getUrl('index') }}" class="native-btn native-secondary">Cancel</a>
        </div>
    </form>
</x-filament-panels::page>
